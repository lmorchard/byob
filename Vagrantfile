Vagrant::Config.run do |config|

  config.vm.box = "lucid32"
  config.vm.network("192.168.10.10")
  config.vm.provisioner = :chef_solo

  # TODO: Wrap this cookbooks dir up in a tarball on github?
  config.chef.cookbooks_path = "cookbooks"

  config.chef.add_recipe("mozilla_byob")

end
