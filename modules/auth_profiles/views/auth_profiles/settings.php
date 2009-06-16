<?php
    $screen_name = AuthProfiles::get_profile('screen_name');
    $u_screen_name = rawurlencode($screen_name);
?>
<?php
usort($sections, create_function(
    '$b,$a', '
        $a = @$a["priority"];
        $b = @$b["priority"];
        return ($a==$b)?0:(($a<$b)?-1:1);
    '
))
?>
<ul class="sections">
    <?php foreach ($sections as $section): ?>
        <li class="section">
            <h2><?= html::specialchars($section['title']) ?></h2>
            <dl>
                <?php foreach ($section['items'] as $item): ?>
                    <dt><a href="<?= url::base() . html::specialchars($item['url']) ?>"><?= html::specialchars($item['title']) ?></a></dt>
                    <dd><?= $item['description'] ?></dd>
                <?php endforeach ?>
            </dl>
        </li>
    <?php endforeach ?>
</ul>
