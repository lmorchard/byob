<?php slot::set('head_title', 'create new browser') ?>
<?php slot::start('crumbs') ?>
    create new browser
<?php slot::end() ?>

<h3>Create a new browser?</h3>
<form action="<?=url::base() . url::current()?>" method="POST">
    <button name="confirm" id="confirm" value="yes">yes</button>  /  
    <button name="cancel" id="cancel" value="no">no</button> 
</form>
