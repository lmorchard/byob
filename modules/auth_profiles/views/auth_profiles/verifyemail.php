<?php if (!empty($invalid_token)): ?>
    <p>Invalid email verification token.</p>
<?php else: ?>
    <p>New email address verified.  <?=html::anchor('login', 'Login?')?></p>
<?php endif ?>
