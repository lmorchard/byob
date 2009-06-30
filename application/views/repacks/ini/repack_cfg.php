<?php
    $aus     = $repack->profile->screen_name . '_' . $repack->short_name;
    $dist_id = $repack->profile->screen_name . '_' . $repack->short_name;
    $version = $repack->version;
    $locales = join(' ', $repack->locales);

    $os = array(
        'linux' => 'false',
        'win'   => 'false',
        'mac'   => 'false',
    );
    $oses = $repack->os;
    if (!empty($oses)) foreach ($os as $os_name=>$val) {
        $os[$os_name] = in_array($os_name, $oses) ?
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
