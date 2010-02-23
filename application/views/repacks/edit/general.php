<div class="intro">
    <p>
        Most of the meta data about your customized version of Firefox will be 
        automatically generated using your profile information. The information in this 
        section is for your use, is publicly viewable, and should describe who this 
        browser is tailored for. When referring to the this browser (e.g. on a website, 
        blog post, communique, etc.), you should use the name 
        "<em><?=html::specialchars($repack->title)?></em>".
    </p>

    <p>
        The Title is a label that is used for identifying customized version of 
        Firefox within the BYOB application. If you have multiple customizations 
        defined, it's intended to help differentiate between those builds. 
        Similarly, the descriptive text allows you to provide a lengthy description 
        of your customizations. 
    </p>
</div>
<div class="pane">

    <div>
        <fieldset><legend>Browser details</legend>
            <div class="user_title">
                <p>Enter a one or two word identifier for this version of Firefox. 
                    (required, max length 255 characters):</p>
                <?= form::input('user_title', form::value('user_title')) ?>
            </div>
            <div class="description">
                <p>Describe this customized browser further 
                    (optional, max length 1000 characters):</p>
                <?= form::textarea('description', form::value('description')) ?>
            </div>
            <div class="public">
                <p>Should this browser be included in public lists?</p>
                <ul class="choices">
                    <li><?= form::radio('is_public', '1',
                        !!form::value('is_public')) ?> Yes</li>
                    <li><?= form::radio('is_public', '0',
                        !form::value('is_public')) ?> No</li>
                </ul>
            </div>
        </fieldset>
    </div>

</div>
