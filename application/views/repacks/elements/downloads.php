<?php
$privs = $repack->checkPrivileges();
$files = (!$privs['download']) ?  array() : $repack->files;
?>
<table class="downloads">
    <?php if (empty($files)): ?>
        <thead><tr><th>None, yet.</th></tr></thead>
    <?php else: ?>
        <?php 
            $downloads = array();
            $locales = array();
            foreach ($files as $file_path) {
                $file_url = "{$repack->url}/downloads/{$file_path}";
                
                $parts = explode('/', $file_path);
                if (count($parts) != 3) continue;
                
                list($os_path, $locale, $fn) = $parts;

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

                if (!isset($downloads[$os_name]))
                    $downloads[$os_name] = array();

                $downloads[$os_name][$locale] = $file_url;
                $locales[$locale]= 1;
            }
            $locales = array_keys($locales);
        ?>
        <thead>
            <tr>
                <th class="empty">&nbsp;</th>
                <?php foreach ($locales as $locale): ?>
                    <th><?=html::specialchars($locale)?></th>
                <?php endforeach ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($downloads as $os=>$files): ?>
                <tr>
                    <td class="os"><?=html::specialchars($os)?></td>
                    <?php foreach ($locales as $locale): ?>
                        <?php 
                            $h = html::escape_array(array(
                                'locale' => $locale,
                                'url'    => $files[$locale],
                            ));
                        ?>
                        <td><a href="<?=$h['url']?>">download</a></td>
                    <?php endforeach ?>
                </tr>
            <?php endforeach ?>
        </tbody>
    <?php endif ?>
</table>
