<?php
/**
 * Configuration for the repack process.
 */
$base_path = dirname(APPPATH);

$config['storage']   = "$base_path/storage";
$config['partners']  = "{$config['storage']}/partners";

$config['repack_script'] = "$base_path/bin/partner-repacks.py";

$config['downloads_private'] = "$base_path/downloads/private";
$config['downloads_public']  = "$base_path/downloads/public";
