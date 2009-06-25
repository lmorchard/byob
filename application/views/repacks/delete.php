<?php slot::set('head_title', ' :: delete :: ' . html::specialchars($repack->title)); ?>
<?php slot::start('page_title') ?>
    :: delete :: <a href="<?= url::current() ?>"><?= html::specialchars($repack->title) ?></a>
<?php slot::end() ?>

<?php 
View::factory('repacks/details')
    ->set('repack', $repack)->render(true); 
?>

<form action="<?= url::base() . url::current() ?>" method="POST">
    <h3>Delete this browser?</h3>
    <p>
        Are you sure? 
        <input type="submit" name="confirm" id="confirm" value="yes" />  /  
        <input type="submit" name="cancel" id="cancel" value="no" /> 
    </p>
</form>
