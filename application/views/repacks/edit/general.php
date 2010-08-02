<div class="intro">
    <p><?=sprintf(_('Most of the meta data about your customized version of Firefox will be automatically generated using your profile information. The information in this section is for your use, is publicly viewable, and should describe who this browser is tailored for. When referring to the this browser (e.g. on a website, blog post, communique, etc.), you should use the name "<em>%1$s</em>".'), html::specialchars($repack->title))?></p>
    <p><?=_('The Title is a label that is used for identifying customized version of Firefox within the BYOB application. If you have multiple customizations defined, it\'s intended to help differentiate between those builds.  Similarly, the descriptive text allows you to provide a lengthy description of your customizations.')?></p>
</div>
<div class="pane">

    <div>
        <fieldset><legend><?=_('Browser details')?></legend>
            <div class="user_title">
                <p><?=_('Enter a one or two word identifier for this version of Firefox.  (required, max length 255 characters):')?></p>
                <?= form::input('user_title', form::value('user_title')) ?>
            </div>
            <div class="description">
                <p><?=_('Describe this customized browser further (optional, max length 1000 characters):')?></p>
                <?= form::textarea('description', form::value('description')) ?>
            </div>
            <div class="public">
                <p><?=_('Should this browser be included in public lists?')?></p>
                <ul class="choices">
                    <li><?= form::radio('is_public', '1', !!form::value('is_public')) ?> <?=_('Yes')?></li>
                    <li><?= form::radio('is_public', '0', !form::value('is_public')) ?> <?=_('No')?></li>
                </ul>
            </div>
        </fieldset>
    </div>

</div>
