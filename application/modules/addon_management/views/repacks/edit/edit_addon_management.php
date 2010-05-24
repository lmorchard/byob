<?php
$popular_extensions    = addon_management::get_popular_extensions();
$popular_personas      = addon_management::get_popular_personas();
$popular_themes        = addon_management::get_popular_themes();
$popular_searchplugins = addon_management::get_popular_searchplugins();

$selected_extension_ids = 
    empty($repack->managed_addons['extension_ids']) ?
        array() : $repack->managed_addons['extension_ids'];

$selected_search_plugin_filenames = 
    empty($repack->managed_addons['search_plugin_filenames']) ?
        array() : $repack->managed_addons['search_plugin_filenames'];

$selected_theme_id = 
    empty($repack->managed_addons['theme_id']) ?
        '' : $repack->managed_addons['theme_id'];

$selected_persona_url = 
    empty($repack->managed_addons['persona_url']) ?
        '' : $repack->managed_addons['persona_url'];

$selected_persona_url_hash = md5($selected_persona_url);
?>

<?php slot::start('head_end') ?>
    <?=html::stylesheet(array('application/modules/addon_management/public/css/addon_management.css'))?>
<?php slot::end() ?>

<?php slot::start('body_end') ?>
    <?=html::script(array(
        'application/modules/addon_management/public/js/addon_management.js',
    ))?>
<?php slot::end() ?>

<div class="intro">
    <p>
        With BYOB, you can bundle many popular add-ons with your browser.
    </p>
</div>
<div class="pane pane-addon-management">

    <div class="selections">
        <fieldset><legend>Selected add-ons:</legend>
            <ul class="addon-selections clearfix">
                <li class="template" data-selection-index="">
                    <span class="name"></span>
                    <a href="#" class="remove">Remove</a>
                </li>
            </ul>
        </fieldset>
    </div>

    <div class="choices">
        <div class="sub-tab-set">
            <ul class="sub-tabs">
                <li class="selected"><a href="#tab-extensions">Extensions</a></li>
                <li><a href="#tab-searchengines">Search Engines</a></li>
                <li><a href="#tab-personas">Personas</a></li>
                <li><a href="#tab-themes">Themes</a></li>
            </ul>

            <div class="sub-tab-content selected" id="tab-extensions">

                <?php if ($repack->checkPrivilege('addon_management_xpi_upload')): ?>
                    <fieldset class="upload">
                        <iframe id="tab-extensions-upload" 
                            src="<?=$repack->url()?>/addons;upload_extension"
                            scrolling="no"></iframe>
                    </fieldset>
                <?php endif ?>

                <fieldset><legend>Choose from these popular add-ons:</legend>
                    <ul class="extensions"><?php foreach ($popular_extensions as $id=>$addon): ?>
                        <?php
                            $e = html::escape_array(array(
                                'id'        => $addon->id,
                                'icon'      => $addon->icon,
                                'version'   => $addon->version,
                                'name'      => $addon->name,
                                'summary'   => $addon->summary,
                                'thumbnail' => $addon->thumbnail,
                                'learnmore' => $addon->learnmore,
                            ));
                            $selected = in_array($addon->id, $selected_extension_ids);
                        ?>
                        <li>
                            <input class="checkbox" type="checkbox" id="extension_ids-<?=$id?>" 
                                name="extension_ids[]" value="<?=$e['id']?>"
                                <?=($selected)?'checked="checked"':''?> />
                            <label class="icon" for="extension_ids-<?=$id?>">
                                <img src="<?=$e['icon']?>" alt="<?=$e['name']?>" 
                                    width="32" height="32" />
                            </label>
                            <div class="meta">
                            <span class="name"><?=$e['name']?> <?=$e['version']?></span>
                                <p class="summary"><?=$e['summary']?></p>
                                <a target="new" href="<?=$e['learnmore']?>" class="learn">Learn more...</a>
                            </div>
                        </li>
                    <?php endforeach ?></ul>
                </fieldset>
            </div>

            <div class="sub-tab-content" id="tab-searchengines">

                <fieldset class="upload">
                    <iframe id="tab-searchplugins-upload" 
                        src="<?=$repack->url()?>/addons;upload_searchplugin"
                        scrolling="no"></iframe>
                </fieldset>

                <fieldset><legend>Choose from these popular search engines:</legend>
                    <ul class="searchplugins"><?php foreach ($popular_searchplugins as $fn=>$plugin): ?>
                        <?php
                            $e = html::escape_array(array(
                                'filename'  => $fn,
                                'icon'      => $plugin->getIconUrl(),
                                'name'      => $plugin->ShortName,
                                'summary'   => $plugin->Description,
                            ));
                            $selected = in_array($fn, $selected_search_plugin_filenames);
                        ?>
                        <li>
                            <input class="checkbox" type="checkbox" id="search_plugin_filenames-<?=$fn?>" 
                                name="search_plugin_filenames[]" value="<?=$e['filename']?>" 
                                <?=($selected)?'checked="checked"':''?> />
                            <label class="icon" for="search_plugin_filenames-<?=$fn?>">
                                <img src="<?=$e['icon']?>" alt="<?=$e['name']?>" 
                                    width="16" height="16" />
                            </label>
                            <label class="meta" for="search_plugin_filenames-<?=$fn?>">
                                <span class="name"><?=$e['name']?></span>
                            </label>
                        </li>
                    <?php endforeach ?></ul>
                </fieldset>
            </div>

            <div class="sub-tab-content" id="tab-personas">

                <fieldset class="divider"><legend>Enter the URL of a Persona to include:</legend>
                    <input type="text" name="persona_url" class="persona_url text"
                        value="<?=(empty($popular_personas[md5($selected_persona_url)])) ? $selected_persona_url : '' ?>" />
                </fieldset>

                <fieldset><legend>Choose from these popular Personas:</legend>
                    <ul class="personas">
                        <li class="none">
                            <input type="radio" name="persona_url_hash" value="" 
                                id="persona_id_none" 
                                <?=empty($selected_persona_url)?'checked="checked"':''?> />
                            <label for="persona_id_none" class="none">No Persona</label>
                        </li>
                        <?php foreach ($popular_personas as $url_hash=>$persona): ?>
                            <?php
                                $e = html::escape_array(array(
                                    'id'          => $persona->id,
                                    'url'         => $persona->url,
                                    'name'        => $persona->name,
                                    'description' => $persona->description,
                                    'iconURL'     => $persona->iconURL,
                                    'previewURL'  => $persona->previewURL,
                                ));
                                $selected = ($url_hash == $selected_persona_url_hash);
                            ?>
                            <li>
                                <input type="radio" name="persona_url_hash" value="<?=$url_hash?>" 
                                    id="persona_id_<?=$url_hash?>" 
                                    <?=($selected)?'checked="checked"':''?> />
                                <label for="persona_id_<?=$url_hash?>">
                                    <img src="<?=$e['previewURL']?>" alt="<?=$e['name']?>" />
                                    <span class="name"><?=$e['name']?></span>
                                </label>
                            </li>
                        <?php endforeach ?></ul>
                </fieldset>
            </div>
            <div class="sub-tab-content" id="tab-themes">
                <fieldset>
                    <ul class="themes">
                        <li class="none">
                            <input type="radio" name="theme_id" id="theme_id_none" value="" 
                                <?= empty($selected_theme_id) ? 'checked="checked"' : '' ?> />
                            <label for="theme_id_none" class="none">Use the default theme</label>
                        </li>
                        <?php foreach ($popular_themes as $idx => $addon): ?>
                            <?php
                                $e = html::escape_array(array(
                                    'id'        => $addon->id,
                                    'icon'      => $addon->icon,
                                    'name'      => $addon->name,
                                    'summary'   => $addon->summary,
                                    'thumbnail' => $addon->thumbnail,
                                    'learnmore' => $addon->learnmore,
                                ));
                                $selected = ($addon->id == $selected_theme_id);
                            ?>
                            <li>
                                <input type="radio" name="theme_id" value="<?=$e['id']?>" 
                                    id="theme_id_<?=$idx?>" 
                                    <?=($selected)?'checked="checked"':''?> />
                                <label class="meta" for="theme_id_<?=$idx?>">
                                    <span class="name"><?=$e['name']?></span>
                                    <!--<a target="new" href="<?=$e['learnmore']?>">More info</a>-->
                                </label>
                                <label class="icon" for="theme_id_<?=$idx?>">
                                    <img src="<?=$e['thumbnail']?>" alt="<?=$e['name']?>" />
                                    <a target="new" href="<?=$e['learnmore']?>">More info</a>
                                </label>
                            </li>
                        <?php endforeach ?></ul>
                </fieldset>
            </div>
        </div>
    </div>

</div>
