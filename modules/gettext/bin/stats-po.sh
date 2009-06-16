#!/bin/bash

# syntax:
# stats-po.sh

echo "Printing number of fuzzy flags found in locales:"

for lang in `find $1 -type f -name "messages.po"`; do
    dir=`dirname $lang`
    stem=`basename $lang .po`
    count=$(grep -c "fuzzy" $lang)
    echo -e "$(dirname $dir)\t$count"
done
