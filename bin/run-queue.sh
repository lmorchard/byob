#!/bin/bash
###########################################################################
# This script is a simple driver for the message queue, repeatedly 
# running the exhaust method of the queue controller.  This allows PHP 
# and Kohana to exit on a regular basis to clear out memory, caches, and  
# log buffers
###########################################################################

while [ 1 ]; do
    php index.php messagequeue/exhaust
    sleep 10;
done;
