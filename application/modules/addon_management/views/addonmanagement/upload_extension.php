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
                top.jQuery('iframe#tab-extensions-upload').height(f_height);
        };
        window.adjustHeight();
        if (top.BYOB_Repacks_Edit_AddonManagement && top.BYOB_Repacks_Edit_AddonManagement.updateSelectionsPane)
            top.BYOB_Repacks_Edit_AddonManagement.updateSelectionsPane();
        //$(document).ready(window.adjustHeight);
    </script>
<?php slot::end() ?>

<div class="extension_upload upload_form">

    <form method="POST" enctype="multipart/form-data">
        <fieldset class="upload"><legend><?=_('Upload an extension XPI file:')?></legend>
            <div>
                <div class="pretty_upload">
                    <input type="file" class="upload" id="xpi_upload" name="xpi_upload" />
                </div>
                <button name="submit" class="button blue"><?=_('Upload')?></button>
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

    <?php if (!empty($extensions)): ?>
        <ul class="uploads">
            <?php foreach ($extensions as $extension): ?>
                <li class="xpi"><a href="#" class="remove_link">
                    <span class="name"><?= html::specialchars($extension->name) ?></span>
                    <span class="version"><?= html::specialchars($extension->version) ?></span>
                    <form class="delete" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="method" value="delete" />
                        <input type="hidden" name="xpi_fn" 
                            value="<?= html::specialchars(basename($extension->xpi_fn)) ?>" />
                            <button name="submit" class="remove"><?=_('Remove')?></button>
                    </form>
                </a></li>
            <?php endforeach ?>
        </ul>
    <?php endif ?>

</div>
