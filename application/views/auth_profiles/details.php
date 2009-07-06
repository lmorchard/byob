<?php slot::set('head_title', 
    html::specialchars($profile->screen_name).' :: profile update'); ?>
<?php slot::start('crumbs') ?>
    <a href="<?= url::base() . 'profiles/' . urlencode($profile->screen_name)?>"><?=html::specialchars($profile->screen_name)?></a> ::
    profile update
<?php slot::end() ?>
<?php
    echo form::build(url::current(), array('class'=>'details'), array(
        form::fieldset('personal details', array('class'=>'profile'), array(
            form::field('input',    'full_name', 'Full Name'),
            form::field('input',    'phone',     'Phone'),
            form::field('input',    'fax',       'Fax'),
        )),
        form::fieldset('organization details', array('class'=>'organization'), array(
            form::field('input',    'org_name',    'Name'),
            form::field('textarea', 'org_address', 'Address'),
            form::field('dropdown', 'org_type',    'Type', array(
                'options' => array(
                    'corp'      => 'Corporation', 
                    'nonprofit' => 'Non-Profit', 
                    'other'     => 'Other',
                )
            )),
            form::field('input',    'org_type_other', 'Type (other)'),
        )),
        form::fieldset('finish', array(), array(
            form::field('submit', 'details', null, array('value'=>'Update')),
        ))
    ));
?>
