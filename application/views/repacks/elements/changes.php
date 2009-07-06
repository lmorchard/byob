<?php
    $ignored= array(
        'id','state','modified','created','json_data','files'
    );
?>
<table class="changes">
    <thead>
        <tr>
            <th>Name</th>
            <th>From</th>
            <th>To</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($changes as $name=>$diff): ?>
            <?php 
                if (in_array($name, $ignored)) continue;

                list($release, $change) = $diff;

                if (!is_string($release)) $release = json_encode($release);
                if (!is_string($change)) $change = json_encode($change);

                $h = html::escape_array(compact(
                    'name','release','change'
                ));
            ?>
            <tr>
                <td><?=$h['name']?></td>
                <td><?=$h['release']?></td>
                <td><?=$h['change']?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
