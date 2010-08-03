#!/bin/bash
SOURCE_DIRS="application modules js/byob"

cd `dirname $0`/..;
working_dir=$( pwd );

echo -n "Extracting..."
> application/locale/keys.pot;
for sourcedir in $SOURCE_DIRS; do \
    cd $working_dir;
    cd $sourcedir;
    find . -not -path '*/logs/*' -not -path '*/cache/*' -not -path '*/.git_externals/*' -name '*.php' | xgettext \
        --output=$working_dir/application/locale/keys.pot \
        --language=PHP \
        --indent \
        --add-comments=i18n \
        --keyword=_ \
        --keyword=__ \
        --keyword=___ \
        --keyword=n___:1,2 \
        --keyword="pgettext:1c,2" \
        --keyword="npgettext:1c,2,3" \
        --force-po \
        --omit-header \
        --join-existing \
        --sort-output \
        --copyright-holder="Mozilla Corporation" \
        --files-from=- # Pull from standard input (our find command) 
     find . -not -path '*/cache/*' -not -path '*/.git_externals/*' -not -path '*/repack_assets/*' -name '*.js' | xgettext \
         --output=$working_dir/application/locale/keys.pot \
         --language=Python \
         --indent \
         --add-comments=i18n \
         --keyword=_ \
         --keyword=__ \
         --keyword=___ \
         --keyword=n___:1,2 \
         --keyword="pgettext:1c,2" \
         --keyword="npgettext:1c,2,3" \
         --force-po \
         --omit-header \
         --join-existing \
         --sort-output \
         --copyright-holder="Mozilla Corporation" \
         --files-from=- # Pull from standard input (our find command) 
done
echo "done."

echo "Merging & compiling all locales"
cd $working_dir;
for i in `find application/locale -type f -name "messages.po"`; do
    dir=`dirname $i`
    stem=`basename $i .po`

    echo -n "    $dir";

    # msgen will copy the msgid -> msgstr for English.  All other locales will
    # get a blank msgstr
    if [[ "$i" =~ "en_US" ]]; then
        msgen ./application/locale/keys.pot | \
            msgmerge \
                --backup=off \
                --sort-output \
                --indent \
                --width=200 \
                --no-fuzzy-matching \
                -U "$i" \
                -
    else
        msgmerge \
            --backup=off \
            --sort-output \
            --indent \
            --width=200 \
            --no-fuzzy-matching \
            -U "$i" \
            ./application/locale/keys.pot
    fi
    msgfmt -o ${dir}/${stem}.mo $i
done
