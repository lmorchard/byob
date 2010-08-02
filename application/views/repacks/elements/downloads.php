<?php
$locale_choices = locale_selection::get_all_locales();
$files = (!$repack->checkPrivilege('download')) ?
    array() : $repack->files;
?>
<?php if (!empty($files)): ?>
<div id="downloads">
<h3>Download</h3>
<table class="downloads" cellspacing="0" border="0">
    <?php 
        $downloads = array();
        $oses = array();
        foreach ($files as $file_path) {
            $file_url = "{$repack->url}/downloads/{$file_path}";
            
            $parts = explode('/', $file_path);
            if (count($parts) != 3) continue;
            
            list($os_path, $locale, $fn) = $parts;

            $os_names = array(
                'win32' => _('Windows'),
                'mac'   => _('Mac OS X'),
                'linux' => _('Linux'),
            );
            $os_name = 'Generic';
            foreach ($os_names as $os_name=>$os_label) {
                if (strpos($os_path, $os_name)!==FALSE) {
                    break;
                }
            }

            if (!isset($downloads[$locale]))
                $downloads[$locale] = array();

            $downloads[$locale][$os_name] = $file_url;
            $oses[$os_name] = 1;
        }
        if (isset($downloads['ja']) && isset($downloads['ja-JP-mac']['mac'])) {
            $downloads['ja']['mac'] = $downloads['ja-JP-mac']['mac'];
            unset( $downloads['ja-JP-mac'] );
        }
        $oses = array_keys($oses);
    ?>
    <thead>
        <tr>
            <th class="empty"><?=_('Language')?></th>
            <th class="empty"><?=_('Version')?></th>
            <?php foreach ($oses as $os_name): ?>
                <?php $os_label = $os_names[$os_name]; ?>
                <th class="<?=$os_label?>"><?=$os_label?></th>
            <?php endforeach ?>
        </tr>
    </thead>
    <tbody>
        <?php $idx = 0; ?>
        <?php foreach ($downloads as $locale=>$files): ?>
            <?php if ('ja-JP-mac' == $locale) continue; ?>
            <?php if ('ja' == $locale) continue; ?>
            <tr class="<?= ($idx % 2) == 0 ? 'even' : 'odd' ?>">
                <td class="locale">
                    <?=html::specialchars($locale_choices[$locale]['English'])?>
                </td>
                <td class="version">
                    <?=html::specialchars($repack->product->version)?>
                </td>
                <?php foreach ($oses as $os_name): ?>
                    <?php 
                        $os_label = $os_names[$os_name];
                        $h = html::escape_array(array(
                            'os'  => $os_label,
                            'url' => isset($files[$os_name]) ? 
                                $files[$os_name] : '',
                        ));
                    ?>
                    <td class="os <?=$os_name?>">
                        <?php if (empty($h['url'])): ?>
                            &nbsp;
                        <?php else: ?>
                            <a href="<?=$h['url']?>"><?=_('Download')?></a>
                        <?php endif ?>
                    </td>
                <?php endforeach ?>
            </tr>
            <?php $idx++; ?>
        <?php endforeach ?>
    </tbody>
</table>
</div>
<?php endif ?>
