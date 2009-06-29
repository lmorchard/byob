<?php
    $aus     = $repack->profile->screen_name . '_' . $repack->short_name;
    $dist_id = $repack->profile->screen_name . '_' . $repack->short_name;
    $version = $repack->version;
    $locales = join(' ', $repack->locales);
    
    $os = array();
    if (!is_array($repack->oses)){
        var_dump($repack->oses);
        die;
    }
    foreach (array('linux','mac','win') as $os_name) {
        $os[$os_name] = (in_array($os_name, $repack->oses)) ?
            'true' : 'false';
    }
?>
aus="<?=$aus?>"
dist_id="<?=$dist_id?>"
dist_version="<?=$version?>"
locales="<?=$locales?>"
linux-i686=<?=$os['linux']."\n"?>
mac=<?=$os['mac']."\n"?>
win32=<?=$os['win']."\n"?>
