<?php slot::set('is_popup', true); ?>

<?php slot::start('head_end') ?>
    <?=html::stylesheet(array('css/repacks-edit.css'))?>
    <?=html::stylesheet(array('application/modules/addon_management/public/css/addon_management.css'))?>
<?php slot::end() ?>

<?php slot::set('body_class', 'repack_upload_iframe') ?>

<?php slot::start('body_end') ?>
    <?=html::script(array(
        'application/modules/addon_management/public/js/addon_management.js',
    ))?>
    <script type="text/javascript">
        /**
         * HACK: Adjust the parent iframe to the size of this page's content, if 
         * framed.
         */
        window.adjustHeight = function () {
            if (top.location.href == window.location.href) return;
            var f_height = $('#content').height();
            if (f_height)
                top.jQuery('iframe#tab-searchplugins-upload').height(f_height);
        };
        window.adjustHeight();
        //$(document).ready(window.adjustHeight);
    </script>
<?php slot::end() ?>

<div class="searchplugin_upload upload_form">

    <form method="POST" enctype="multipart/form-data">
        <fieldset class="upload"><legend>Upload a search engine plug-in:</legend>
            <div>
                <div class="pretty_upload">
                    <input type="file" class="upload" id="sp_upload" name="sp_upload" />
                </div>
                <button name="submit" class="button blue">Upload</button>
            </div>
        </fieldset>
    </form>

    <?php if (!empty($errors)): ?>
        <ul class="errors">
            <?php foreach ($errors as $error): ?>
                <li class="error"><?= html::specialchars($error) ?></li>
            <?php endforeach ?>
        </ul>
    <?php endif ?>

    <?php if (!empty($search_plugins)): ?>
        <ul class="uploads">
            <?php foreach ($search_plugins as $idx=>$plugin): ?>
                <?php
                    $e = html::escape_array(array(
                        'id'        =>$idx,
                        'icon'      => $plugin->getIconUrl(),
                        'name'      => $plugin->ShortName,
                        'summary'   => $plugin->Description,
                    ));
                ?>
                <li class="uploads">
                    <label class="icon" for="searchplugin_ids-<?=$idx?>">
                        <img src="<?=$e['icon']?>" alt="<?=$e['name']?>" 
                            width="16" height="16" />
                    </label>
                    <label class="meta" for="searchplugin_ids-<?=$idx?>">
                        <span class="name"><?=$e['name']?></span>
                    </label>
                </li>
            <?php endforeach ?>
        </ul>
    <?php endif ?>

</div>