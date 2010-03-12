<?php slot::set('is_popup', true); ?>

<?php slot::start('head_end') ?>
    <?=html::stylesheet(array('css/repacks-edit.css'))?>
<?php slot::end() ?>

<?php slot::start('body_end') ?>
<script type="text/javascript">
    /**
     * HACK: Adjust the parent iframe to the size of this page's content, if 
     * framed.
     */
    $(document).ready(function () {
        if (top.location.href == window.location.href) return;
        var f_height = $('#search_plugins').height();
        top.jQuery('iframe#search_plugin_uploads').height(f_height);
    })
</script>
<?php slot::end() ?>

<div id="search_plugins">

    <form class="upload" method="POST" enctype="multipart/form-data">

        <div>
            <input type="file" id="upload" name="upload" size="45" />
            <input type="submit" name="submit" value="Upload Search Plugin" />
        </div>

        <?php if (!empty($errors)): ?>
            <ul class="errors">
                <?php foreach ($errors as $error): ?>
                    <li><?=htmlspecialchars($error)?></li>
                <?php endforeach ?>
            </ul>
        <?php endif ?>

    </form>

    <?php if (!empty($repack->search_plugins)): ?>
        <ul class="plugins">
            <?php foreach ($repack->search_plugins as $p_name => $plugin): ?>
                <?php
                    $e = html::escape_array(array(
                        'file' => $p_name,
                        'name' => $plugin->ShortName,
                        'desc' => $plugin->Description,
                        'icon' => $plugin->getIconURL(),
                    ));
                ?>
                <li class="plugin">
                    <form method="POST">
                        <input type="hidden" name="remove_name" value="<?=$e['file']?>" />
                        <input type="submit" value=" x " />

                        <span class="name">
                            <?php if (!empty($e['icon'])): ?>
                                <img class="icon" src="<?=$e['icon']?>" width="16" height="16" />
                            <?php endif ?>
                            <?=$e['name']?></span>
                        <span class="desc"><?=$e['desc']?></span>

                        <ul class="urls">
                            <?php foreach ($plugin->urls as $url): ?>
                                <?php $e2 = html::escape_array($url); ?>
                                <li class="url">
                                    <span class="type"><?=$e2['type']?></span>
                                    <span class="url"><?=$e2['template']?></span>
                                </li>
                            <?php endforeach ?>
                        </ul>

                    </form>
                </li>
            <?php endforeach ?>
        </ul>
    <?php endif?>
    
</div>
