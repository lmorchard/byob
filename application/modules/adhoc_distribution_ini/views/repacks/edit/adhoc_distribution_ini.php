<?php slot::start('head_end') ?>
    <?=html::stylesheet(array('application/modules/adhoc_distribution_ini/public/css/adhoc_distribution_ini.css'))?>
<?php slot::end() ?>

<div class="intro">
<p><?=_('You can create an INI overlay to be merged on top of the distribution.ini generated for your repack.')?></p>
</div>
<div class="pane">

    <div>
        <fieldset><legend><?=_('Adhoc distribution.ini overlay')?></legend>

            <div class="adhoc_ini">
                <p><?=_('Overlay source:')?></p>
                <?= form::textarea('adhoc_ini', form::value('adhoc_ini')) ?>
            </div>

            <input class="submit" type="submit" value="<?=_('save and update result')?>" />

            <div class="adhoc_ini_result">
                <p><?=_('distribution.ini result:')?></p>
                <div class="result">
                    <pre><?= $repack->buildDistributionIni() ?></pre>
                </div>
            </div>

        </fieldset>
    </div>

</div>
