#!/bin/sh
tempfoo=`basename $0`
TMPFILE=`mktemp /tmp/${tempfoo}.XXXXXX` || exit 1

for i in `find application/locale -type f -name "messages.po" | grep -v en_US`; do
    msgattrib \
        --set-fuzzy \
        application/locale/en_US/LC_MESSAGES/messages.po | \
    msgmerge -Ns "$i" - > $TMPFILE
    cp "$TMPFILE" "$i"
done
rm "$TMPFILE"
