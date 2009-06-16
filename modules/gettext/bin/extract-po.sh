#!/bin/bash
SOURCE_DIRS="application modules/gettext"

touch application/locale/en_US/LC_MESSAGES/messages.po

for sourcedir in $SOURCE_DIRS; do \
    find ./${sourcedir} -name "*.php" | xgettext \
        --output=application/locale/en_US/LC_MESSAGES/messages.po \
        --language=PHP \
        --indent \
        --add-comments=i18n \
        --keyword=___ \
        --keyword=n___:1,2 \
        --keyword="pgettext:1c,2" \
        --force-po \
        --omit-header \
        --join-existing \
        --sort-output \
        --copyright-holder="Mozilla Corporation" \
        --files-from=- # Pull from standard input (our find command) \
done
