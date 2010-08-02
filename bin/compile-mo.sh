#!/bin/bash
echo "Compiling messages...";
for lang in `find application/locale -type f -name "messages.po"`; do
    dir=`dirname $lang`
    stem=`basename $lang .po`
    echo "    ${dir}/${stem}.mo";
    msgfmt -o ${dir}/${stem}.mo $lang
done
