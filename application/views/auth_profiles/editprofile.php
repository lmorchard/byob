<?php slot::set('head_title', 
    html::specialchars($profile->screen_name).' :: profile update'); ?>
<?php slot::start('crumbs') ?>
    <a href="<?= url::site('profiles/' . urlencode($profile->screen_name))?>"><?=html::specialchars($profile->screen_name)?></a> ::
    profile update
<?php slot::end() ?>

<?php slot::start('roles') ?><?php if ($profile->checkPrivilege('edit_roles')): ?>
<?php
    $checked_roles = array();
    foreach ($roles as $role) {
        $checked_roles[] = $role->name;
    }
?>
<div>
    <ul class="list roles">
        <?php foreach ($role_choices as $role=>$label): ?>
            <?php if (in_array($role, array('guest','member'))) { continue; } ?>
            <li class="field checkbox">
                <label><?=html::specialchars($label)?></label>
                <input type="checkbox" name="roles[]" value="<?=html::specialchars($role)?>" 
                    <?= in_array($role, $checked_roles) ? 'checked="checked"' : '' ?> />
            </li>
        <?php endforeach ?>
    </ul>
</div>
<?php endif ?><?php slot::end() ?>

<?php
    echo form::build(url::current(), array('class'=>'details'), array(
        form::field('hidden', 'crumb', '', array('value'=>$crumb)),
        form::fieldset('Account details', array('class'=>'account'), array(
            form::field('input',    'first_name',  'First Name', array('class'=>'required'), array(
                'Your given name.'
            )),
            form::field('input',    'last_name',   'Last Name', array('class'=>'required'), array(
                'Your surname.'
            )),
            form::field('radio', 'is_personal', 'Account type?',
                array('options' => array(
                    '1' => 'Personal',
                    '0' => 'Organization'
                )), 
                array(
                    "Please indicate whether you are using the versions of ",
                    "Firefox you create for personal use (i.e. sharing with ",
                    "friends and family, etc.) or on behalf of an organization."
                )
            ),

            form::field('dropdown', 'org_type',    'Organization Type', array(
                'options' => array(
                    'corp'      => 'Corporation', 
                    'nonprofit' => 'Non-Profit', 
                    'other'     => 'Other',
                ),
                'class'=>'required'
            )),
            form::field('input',    'org_type_other', '(other)'),
            form::field('input',    'org_name',    'Organization Name', array('class'=>'required'), array(
                "Please enter the full, legal name of the organization you represent here."
            )),
            form::field('input',    'phone',       'Phone', array('class'=>'required'), array(
                'Your daytime contact number, with country code (US/Canada is "1").'
            )),
            form::field('input',    'fax',         'Fax', array(), array(
                'Your fax number, with country code (US/Canada is "1")'
            )),
            form::field('input',    'website',     'Website', array(), array(
                'Please provide the URL for your organizational or personal website.'
            )),

            form::field('input',    'address_1', 'Street Address 1', array('class'=>'required')),
            form::field('input',    'address_2', 'Street Address 2'),
            form::field('input',    'city',      'City', array('class'=>'required')),
            View::factory('auth_profiles/elements/states')->render(),
            form::field('input',    'zip',       'Zip / Postal Code', array('class'=>'required', 'class'=>'required'), array(
            )),
            View::factory('auth_profiles/elements/countries')->render(),
        )),
        (!$profile->checkPrivilege('edit_roles')) ? '' :
            form::fieldset('Additional Roles', array(), array( slot::get('roles') )),
        form::fieldset('finish', array('class'=>'finish'), array(
            form::field('submit_button', 'details', null, array('button_params'=>array('class'=>'button yellow required'),'class'=>'required','value'=>'Update')),
        ))
    ));
?>
