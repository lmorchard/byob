require_recipe "apt"

require_recipe "git"
require_recipe "screen"
require_recipe "vim"

require_recipe "apache2"
require_recipe "openssl"
require_recipe "mysql"
require_recipe "mysql::server"
require_recipe "memcached"
require_recipe "php::php5"
require_recipe "php::pear"
require_recipe "php::module_mysql"
require_recipe "php::module_memcache"
require_recipe "php::module_curl"

case node[:platform]
when "debian", "ubuntu"
    package "curl"

    package "php5-dev"
    package "php5-mcrypt"

    package "python-software-properties"
    execute "add-apt-repository ppa:gearman-developers/ppa"
    execute "apt-get update"

    package "libevent-dev"
    package "uuid-dev"
    package "gearman-job-server"
    package "libgearman2"
    package "libgearman-dev"
    package "gearman"
    package "gearman-tools"

    execute "pecl install channel://pecl.php.net/gearman-0.7.0"

    template "/etc/php5/conf.d/gearman.ini" do
        source "gearman.ini.erb"
        owner "root"
        group "root"
        mode 0644
        notifies :reload, resources("service[apache2]"), :delayed
    end
    
    package "sendmail"
    package "p7zip-full"
else 
    # ubuntu, pretty much assumed here, but a worker could be on an OS X machine
end

template "/vagrant/application/config/config-local.php" do
    source "config-local.ini.erb"
end

execute "disable-default-site" do
    command "sudo a2dissite default"
    notifies :reload, resources(:service => "apache2"), :delayed
end

web_app "project" do
    template "project.conf.erb"
    notifies :reload, resources(:service => "apache2"), :delayed
end
