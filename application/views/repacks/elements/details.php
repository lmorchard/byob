<?php
$display = array(
    //'short_name' => 'Short Name',
    //'title' => 'Title',
    'description' => 'Description'
);
?>
<div>
    <h3>Details</h3>
    <dl>
        <?php foreach ($display as $name => $label): ?>
            <?php if (empty($repack->{$name})) continue ?>
            <dt><?= html::specialchars($label) ?></dt>
                <dd><?= html::specialchars($repack->{$name}) ?></dd>
        <?php endforeach ?>
    </dl>
</div>
