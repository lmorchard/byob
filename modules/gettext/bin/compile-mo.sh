#!/bin/bash

# syntax:
# compile-mo.sh {locale-dir/}

function usage() {
    echo "syntax:"
    echo "compile.sh locale-dir/"
    exit 1
}

# check if file and dir are there
dir=$1
if [[ ($# -ne 1) || (! -d "$dir") ]]; then 
    dir="application/locale"
fi

for lang in `find $dir -type f -name "messages.po"`; do
    dir=`dirname $lang`
    stem=`basename $lang .po`
    echo $lang;
    msgfmt -v -o ${dir}/${stem}.mo $lang
done
