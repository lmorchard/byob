#
# Vagrant setup for BYOB
#
Vagrant::Config.run do |config|

    # To rebuild from mostly scratch, use this CentOS 5.6 (32 bit) image:
    config.vm.box = "base"
    config.vm.box_url = "http://files.vagrantup.com/lucid32.box"
    # config.vm.share_folder("v-root", "/vagrant", ".")

    # On OS X and Linux you can use an NFS mount; virtualbox shared folders are slow.
    # see also: http://vagrantup.com/docs/nfs.html
    config.vm.share_folder("v-root", "/vagrant", ".", :nfs => true)

    # Once you've gotten a successful initial from-scratch build, export the
    # box for faster destroy/up next time.
    #  $ vagrant package
    #  $ vagrant box add kuma kuma.box
    config.package.name = "byob.box"

    # Uncomment this line to use your packaged box
    #config.vm.box = "byob"

    # This thing can be a little hungry for memory
    config.vm.customize do |vm|
        vm.memory_size = 512
    end

    # Increase vagrant's patience during hang-y CentOS bootup
    # see: https://github.com/jedi4ever/veewee/issues/14
    config.ssh.max_tries = 50
    config.ssh.timeout   = 300

    # uncomment to enable VM GUI console, mainly for troubleshooting
    #config.vm.boot_mode = :gui

    # Add to /etc/hosts: 192.168.10.75 dev.byob.mozilla.org
    config.vm.network("192.168.10.75")

    config.vm.provision :puppet do |puppet|
        puppet.manifests_path = "puppet/manifests"
        puppet.manifest_file = "dev-vagrant.pp"
    end

end
