<?php slot::set('head_title', 
    html::specialchars($profile->screen_name).' :: profile update'); ?>
<?php slot::start('crumbs') ?>
    <a href="<?= url::base() . 'profiles/' . urlencode($profile->screen_name)?>"><?=html::specialchars($profile->screen_name)?></a> ::
    profile update
<?php slot::end() ?>

<?php slot::start('roles') ?><?php if ($profile->checkPrivilege('edit_roles')): ?>
<div class="listbuilder">
    <ul class="list roles">
        <?php if (!empty($roles)) foreach ($roles as $role): ?>
            <li class="role">
                <input type="hidden" name="roles[]" value="<?= html::specialchars($role->name) ?>" />
                <a href="#" class="delete">[x]</a>
                <span><?= html::specialchars( @$role_choices[$role->name] ) ?></span>
            </li>
        <?php endforeach ?>
        <li class="role template">
            <input type="hidden" name="roles[]" value="" />
            <a href="#" class="delete">[x]</a>
            <span></span>
        </li>
    </ul>
    <div class="roles-add">
        <select class="choices" name="role_choices">
            <?php foreach ($role_choices as $role=>$label): ?>
                <option value="<?= html::specialchars($role) ?>"><?= html::specialchars($label) ?></option>
            <?php endforeach ?>
        </select>
        <a href="#" class="add">+ add role</a>
    </div>
</div>
<?php endif ?><?php slot::end() ?>

<?php
    echo form::build(url::current(), array('class'=>'details'), array(
        form::fieldset('Account details', array('class'=>'account'), array(
            form::field('input',    'first_name',  'First Name', array('class'=>'required'), array(
                'Your given name.'
            )),
            form::field('input',    'last_name',   'Last Name', array('class'=>'required'), array(
                'Your surname.'
            )),
            form::field('checkbox', 'is_personal', 'Personal account?', array('value'=>'1'), array(
                "Please check this box if you are using the versions of ",
                "Firefox you create for personal use (i.e. sharing with ",
                "friends and family, etc.)"
            )),

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
            form::fieldset('roles', array(), array( slot::get('roles') )),
        form::fieldset('finish', array(), array(
            form::field('submit', 'details', null, array('value'=>'Update')),
        ))
    ));
?>
