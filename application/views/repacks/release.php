<?php slot::set('head_title', ' :: release :: ' . html::specialchars($repack->title)); ?>
<?php slot::start('page_title') ?>
    :: release :: <a href="<?= url::current() ?>"><?= html::specialchars($repack->title) ?></a>
<?php slot::end() ?>

<?php 
View::factory('repacks/details')
    ->set('repack', $repack)->render(true); 
?>

<form action="<?= url::base() . url::current() ?>" method="POST">
    <input type="hidden" name="confirm" value="yes" />
    <h3>Generate a new release for this browser?</h3>
    <p>
        Are you sure? <input type="submit" value="yes" />  /  
        <a href="<?= $repack->url() ?>">no</a>
    </p>
</form>
