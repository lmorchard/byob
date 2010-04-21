From: byob-notify-noreply@mozilla.com
Subject: [BYOB] New user registration (<?=$login['login_name']?>)

A new login named <?=$login['login_name']?> has been registered.

<?= url::base() . 'profiles/' . $profile['screen_name'] . "\n" ?>

<?php foreach (array('login'=>$login,'profile'=>$profile) as $name=>$data): ?>
<?= $name . "\n" ?>
-------
<?php foreach ($data as $name=>$value): ?>
<?=$name?>:
    "<?=$value?>"
<?php endforeach ?>

<?php endforeach ?>
