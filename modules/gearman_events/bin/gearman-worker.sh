#!/bin/bash
PATH=/opt/local/bin:/usr/local/bin:/bin:/sbin:/usr/bin:/usr/sbin:$PATH
cd $(dirname $0)/../../../;

while [ 1 ]; do
    # Perpetual loop, allows php script to exit and restart to refresh code.
    php index.php gearman_events/worker
done
