<?php slot::set('head_title', $head_title . ' :: ' . html::specialchars($repack->title)); ?>
<?php slot::start('crumbs') ?>
    <a href="<?=$repack->url() ?>"><?= html::specialchars($repack->display_title) ?></a> :: <?=$crumbs?>
<?php slot::end() ?>

<?php if (!empty($repack)): ?>
    <?=View::factory('repacks/elements/details')->set(array(
        'repack' => $repack,
        'hide_actions' => true,
    ))->render() ?> 
<?php endif ?>

<form action="<?=$url?>" method="POST">
    <fieldset><legend><?=$message?></legend>
        <p>
            Comments:
        </p>
            <textarea name="comments" id="comments" cols="50" rows="7"></textarea>
        <p>
            Are you sure?
            <button name="confirm" id="confirm" value="yes">yes</button>  /  
            <button name="cancel" id="cancel" value="no">no</button> 
        </p>
    </fieldset>
</form>
