Vagrant::Config.run do |config|

  config.vm.box = "lucid32"
  config.vm.network("192.168.10.10")

  config.vm.provision :chef_solo, :cookbooks_path => "cookbooks",
                                    :run_list => ["recipe[mozilla_byob]"]

end
