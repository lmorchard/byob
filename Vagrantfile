Vagrant::Config.run do |config|

  config.vm.box = "lucid32"
  config.vm.network("192.168.10.10")
  config.vm.provisioner = :chef_solo

  # Grab the cookbooks from the Vagrant files
  #config.chef.recipe_url = "http://files.vagrantup.com/getting_started/cookbooks.tar.gz"
  config.chef.cookbooks_path = "cookbooks"

  config.chef.json.merge!({
    :load_limit => 42,
    :chunky_bacon => true
  })

  # Tell chef what recipe to run. In this case, the `vagrant_main` recipe
  # does all the magic.
  config.chef.add_recipe("mozilla_byob")

end
