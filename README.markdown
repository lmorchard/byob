# Mozilla's Build Your Own Browser
* http://byob.mozilla.com
* lorchard@mozilla.com

## Overview

Build Your Own Browser is a web application that helps people configure and
distribute customized versions of Firefox.

## Installation

BYOB has a number of moving parts, so installation may be a bit more complex
than the usual LAMP stack PHP app.

* Prerequisites
    * MySQL 5.0+
    * PHP 5.3+, with at least the following modules:
        * gearman, curl, gd, mcrypt, mysql, mysqli
    * Gearman 0.7.0
    * An OS X server, at least for the gearman repack worker

* Filesystem
    * Ensure the following directories exist and are writable by the web server:
        * `application/cache`
        * `application/logs`
        * `downloads`
        * `workspace`
    * For a clustered environment:
        * Unique per web server: `application/cache` and `application/logs`
        * Can be on a shared mount: `downloads` and `workspace`

* MySQL
    * Create a new database using the current schema:
        * `application/config/schema-mysql/current.sql`
    * Though `current.sql` should always contain the latest schema, changes to the DB will appear here:
        * `application/config/schema-mysql/changes/`

* Recaptcha
    * Visit `recaptcha.net` to obtain a public / private key pair for the domain where you intend to install the app.

* Application config
    * All editable configuration files reside under `application/config`
    * Copy `config-local.php-dist` to `config-local.php` and edit to make installation-specific changes.
        * The `database.local` structure should be given the MySQL credentials to access the database created in the previous step.
        * The `database.shadow` structure should be given the same MySQL credentials as `database.local`, or configured to point at a read-only replica of `database.local`.
        * Change the `recaptcha` settings to reflect the domain, public key, and private key data acquired from `recaptcha.net`
        * Change the email.* settings to reflect local email environment.
            * Set `email.driver` to 'native' if PHP itself is setup to send email
            * Set `email.driver` to 'smtp' and update `email.options` if an external SMTP server is to be used.
        * Set `core.display_errors` to `FALSE` to prevent verbose error messages
        * Set `core.log_threshold` to 0 to disable logging to `application/logs`
        * Change `core.site_domain` to the domain name of the web host, deleting the code to guess the domain name for dev servers.
    * Copy `repacks.php-dist` to `repacks.php` and edit to make installation-specific changes.
        * In particular, the locations of the `downloads` and `workspace` directories can be changed.

* Gearman and repack worker
    * Important details:
        * BYOB performs browser customizations asynchronously from the web application by using a gearman job server and a worker process.
        * Although the web application can run on a Linux server, the gearman worker itself must currently run on an OS X server. 
            * This is because the browser repack script requires access to OS X utilities in order to build OS X disk images for browser distribution.
    * Configure the addresses of gearman servers:
        * `application/config/config-local.php`
    * A shell script wrapper that runs the gearman repack worker is here:
        * `modules/gearman_events/bin/gearman-worker.sh`
        * This shell script repeatedly restarts the PHP worker, which allows the PHP worker to exit occasionally in order to clean up and refresh its code.
        * This shell script should be started up as a daemon when the server boots up, after MySQL, gearman, and apache are all available.

