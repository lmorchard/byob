From: <?= $email . "\n" ?>
Subject: [BYOB] Contact us (<?=$category?>)

Contact form (<?=$category?>) submission from <?=$name?> <<?=$email?>>

<?php if (isset($referer)): ?>
Referring page: <?=$referer . "\n"?>
<?php endif ?>

<?=$comments?>
