<?php
# Whether to actually send deferred events off to gearman or process them 
# immediately.
$config['deferred_events'] = TRUE;

# A comma separated list of job servers in the format host:port. 
# If no port is specified, it defaults to 4730. 
$config['servers'] = '127.0.0.1:4730';

# How many jobs to process before the worker exits, hopefully restarted by 
# wrapper script. Should allow for code refresh.
$config['max_jobs'] = '10';
