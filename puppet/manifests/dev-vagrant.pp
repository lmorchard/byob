#
# puppet magic for dev boxes
#
#import "classes/*.pp"

$PROJ_DIR = "/vagrant"

$DB_NAME = "byob"
$DB_USER = "byob"
$DB_PASS = "byob"

# Do some dirty, dirty things to make development nicer.
class dev_hacks {
    file { "/home/vagrant/logs":
        ensure => directory,
        owner => "vagrant", group => "vagrant", mode => 0755
    }
}

class repos {
    exec { "apt-get-update":
        command => "/usr/bin/apt-get update"
    }
}

# Ensure some handy dev tools are available.
class dev_tools {
    package { 
        [ "git-core", "subversion", "subversion-tools", "vim-nox", "man-db",
            "nfs-common", "telnet", "netcat" ]:
            ensure => latest;
    }
}

class gearman {
    package {
        [ "gearman", "gearman-tools", "gearman-server", "libevent-dev",
            "libgearman-dev", "uuid-dev" ]:
            ensure => present
    }
}

class mysql {
    package { [ "mysql-common", "mysql-server", "mysql-client" ]: ensure => installed; }
}

class php {
    package {
        [ "php5", "php5-dev", "php5-cli", "php5-common", "php5-curl", "php5-gd",
            "php5-mcrypt", "php5-mysql", "php-pear", "libapache2-mod-php5" ]:
            ensure => latest;
    }
    # See: http://jsjoy.com/blog/84/installing-gearman-on-ubuntu-10-4-lucid
    exec { "hack_gearman_la":
        command => "/bin/sed -i 's|-L/usr/lib /usr/lib/libuuid.la|-L/usr/lib -luuid|g' /usr/lib/libgearman.la",
        unless  => "/bin/grep -- '-L/usr/lib -luuid' /usr/lib/libgearman.la",
        require => Package['libgearman-dev']
    }
    exec { "pecl-gearman-install":
        command => '/usr/bin/pecl install channel://pecl.php.net/gearman-0.8.0',
        creates => "/usr/lib/php5/20090626+lfs/gearman.so",
        require => [ Package['php-pear'], Exec["hack_gearman_la"] ]
    }
    exec { "pecl-gearman-enable":
        command => '/bin/echo "extension=gearman.so" > /etc/php5/conf.d/gearman.ini',
        creates => '/etc/php5/conf.d/gearman.ini',
        require => Exec["pecl-gearman-install"]
    }
    exec { "mcrypt-enable":
        command => '/bin/echo "extension=mcrypt.so" > /etc/php5/cli/conf.d/mcrypt.ini',
        onlyif => "/bin/grep '#' /etc/php5/conf.d/mcrypt.ini",
        require => Package["php5-mcrypt"]
    }
    
}

class apache {
    package { "apache2": 
        ensure => present,
        before => File['/etc/apache2/conf.d/site-config.conf']; 
    }
    file { "/etc/apache2/conf.d/site-config.conf":
        source  => "$PROJ_DIR/puppet/files/etc/apache2/conf.d/site-config.conf",
        owner   => "root", group => "root", mode => 0644,
        require => [ Package['apache2'] ];
    }
    exec { "apache2_enable_modules":
        command => "/usr/sbin/a2enmod alias rewrite headers",
        require => Package['apache2'],
    }
}

class site_config {
    exec { "setup_mysql_databases_and_users":
        command => "/usr/bin/mysql -u root < /vagrant/application/config/schema-mysql/init.sql",
        unless => "/usr/bin/mysql -uroot -B -e 'show databases' 2>&1 | grep -q 'byob'",
        require => [ 
            Service["mysql"] 
        ];
    }
    exec { "setup_mysql_tables":
        command => "/usr/bin/mysql -uroot byob < /vagrant/application/config/schema-mysql/current.sql",
        unless => "/usr/bin/mysql -uroot byob -B -e 'show tables' 2>&1 | grep -q 'repacks'",
        require => [ 
            Exec["setup_mysql_databases_and_users"],
            Service["mysql"]
        ];
    }
    file { "/vagrant/application/config/config-local.php":
        source => "/vagrant/puppet/files/vagrant/application/config/config-local.php",
        mode => 0644
    }
    file {
        [ "/vagrant/application/cache", "/vagrant/application/logs",
            "/vagrant/downloads", "/vagrant/workspace" ]:
            ensure => directory, mode => 0777;
    }
    service { "mysql": 
        ensure => running, 
        enable => true, 
        require => Package['mysql-server']
    }
    service { "apache2":
        ensure    => running,
        enable    => true,
        require   => [
            Package['apache2'],
        ],
        subscribe => [
            File['/etc/apache2/conf.d/site-config.conf'],
            Exec["apache2_enable_modules"]
        ]
    }
}

class dev {
    # Include all the classes needed for a dev box and establish setup order
    include repos, gearman, apache, mysql, php, dev_tools, dev_hacks, site_config
    Class['dev_hacks'] -> Class['repos'] -> Class['dev_tools'] ->  
        Class['gearman'] -> Class['mysql'] -> Class['php'] -> Class['apache'] ->
        Class['site_config']
}

include dev
