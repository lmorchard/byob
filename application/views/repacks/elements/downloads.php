<?php
$privs = $repack->checkPrivileges();
$files = (!$privs['download']) ?  array() : $repack->files;
?>
<ul>
    <?php if (empty($files)): ?>
        <li>None, yet.</li>
    <?php else: ?>
        <?php 
            $downloads = array();
            foreach ($files as $file_path) {
                $file_url = "{$repack->url}/downloads/{$file_path}";
                
                $parts = explode('/', $file_path);
                if (count($parts) != 3) continue;
                
                list($os_path, $locale, $fn) = $parts;

                if (!isset($downloads[$locale]))
                    $downloads[$locale] = array();

                $os_names = array(
                    'linux' => 'Linux',
                    'mac'   => 'Mac OS X',
                    'win32' => 'Windows',
                );
                $os_name = 'Generic';
                foreach ($os_names as $os=>$name) {
                    if (strpos($os_path, $os)!==FALSE) {
                        $os_name = $name; break;
                    }
                }

                $downloads[$locale][] = array(
                    $file_url, "{$os_name}"
                );
            }
        ?>
        <?php foreach ($downloads as $locale=>$files): ?>
            <li>
                <span><?=$locale?></span>
                <ul>
                    <?php foreach ($files as $file): ?>
                        <?php list($url, $title) = $file; ?>
                        <li><a href="<?=$url?>"><?=$title?></a></li>
                    <?php endforeach ?>
                </ul>
            </li>
        <?php endforeach ?>
    <?php endif ?>
</ul>
