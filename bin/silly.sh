#!/bin/sh
php htdocs/index.php util/compiletemplates ; 
./bin/extract-po.sh ;
./bin/silly-po.sh ;
./bin/compile-mo.sh ;

