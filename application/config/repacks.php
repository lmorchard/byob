<?php
/**
 * Configuration for the repack process.
 */
$base_path = dirname(APPPATH);

$config['storage_path']  = "$base_path/data";
$config['partners_path'] = "{$config['storage_path']}/partners";

$config['repack_script'] = "{$config['storage_path']}/scripts/partner-repacks.py";
