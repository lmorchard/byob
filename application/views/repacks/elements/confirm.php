<?php slot::set('head_title', html::specialchars($head_title)) ?>
<?php slot::start('page_title') ?>
    <a href="<?= url::current() ?>"><?=html::specialchars($page_title)?></a>
<?php slot::end() ?>

<?php if (!empty($repack)): ?>
    <?=View::factory('repacks/elements/details')
        ->set('repack', $repack)->render() ?> 
<?php endif ?>

<form action="<?=$url?>" method="POST">
    <h3><?=$message?></h3>
    <p>
        Comments:
        <textarea name="comments" id="comments" cols="50" rows="7"></textarea>
    </p>
    <p>
        Are you sure?
        <button name="confirm" id="confirm" value="yes">yes</button>  /  
        <button name="cancel" id="cancel" value="no">no</button> 
    </p>
</form>
