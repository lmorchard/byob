<?php
/**
 * Configuration for the repack process.
 */
$base_dir = dirname(APPPATH);
$config['storage']       = "$base_dir/storage/prod";
$config['downloads']     = "$base_dir/downloads";
$config['repack_script'] = "$base_dir/partner-tools/scripts/partner-repack";

