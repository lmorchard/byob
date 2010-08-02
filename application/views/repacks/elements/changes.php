<?php
    $ignored= array(
        'id','state','modified','created','json_data','files'
    );
?>
<table class="changes">
    <thead>
        <tr>
            <th><?=_('Name')?></th>
            <th><?=_('From')?></th>
            <th><?=_('To')?></th>
        </tr>
    </thead>
    <tbody>
        <?php $idx = 0; ?>
        <?php foreach ($changes as $name=>$diff): ?>
            <?php 
                if (in_array($name, $ignored)) continue;

                list($release, $change) = $diff;

                if (!is_string($release)) {
                    $release = str_replace('","', '", "', json_encode($release));
                }
                if (!is_string($change)) {
                    $change = str_replace('","', '", "', json_encode($change));
                }

                $h = html::escape_array(compact(
                    'name','release','change'
                ));
            ?>
            <tr class="<?=(($idx++)%2)==0 ? 'even' : 'odd' ?>">
                <td class="name"><?=$h['name']?></td>
                <td class="release"><?=$h['release']?></td>
                <td class="change"><?=$h['change']?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
