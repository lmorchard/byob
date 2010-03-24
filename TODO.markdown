# TODO

## v1.0.7

* Convert old-style bookmarks into new-style
* Finish accepting new-style bookmarks
* Build INI content from new-style bookmarks

## v1.1.0

* Separate repack editor tabs into application-internal modules
* Split LMO_Utils module up into individual bits, like plugindir
* Make customization fully modular - eg. tabs are encapsulated mini-MVCs

## v1.2.0

* Enhance security on passwords?
    * Every new password stored in a form like {algo}-{salt}-{hash}
        * {algo} is {SHA-256},
        * {salt} is a salt unique per-user,
        * {hash} is algo(salt + password)
    * Migration of old passwords
        * If a password fails the new verification, yet passes a simple md5 check -
            replace the existing MD5 with the new form and call it verified. (Might run
            afoul of DB replication?)

## Bugs

See also: [bugzilla bugs][bugzilla] at Mozilla.

[bugzilla]: https://bugzilla.mozilla.org/buglist.cgi?query_format=advanced&product=Websites&component=byob.mozilla.com&bug_status=UNCONFIRMED&bug_status=NEW&bug_status=ASSIGNED
