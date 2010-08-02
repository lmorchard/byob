#!/bin/bash

# This requires GNU talkfilters
# http://www.hyperrealm.com/main.php?s=talkfilters

echo "Generating teh silly...";
for fn in chef b1ff pirate warez; do 
    dn=xx_${fn};
    echo -n "    $fn";
    mkdir -p application/locale/$dn/LC_MESSAGES; 
    rm application/locale/$dn/LC_MESSAGES/messages.po;
    msgen \
        ./application/locale/keys.pot | \
    msgfilter \
        --indent \
        --strict \
        -i - \
        -o "application/locale/$dn/LC_MESSAGES/messages.po" \
        $fn;
    echo;
done
