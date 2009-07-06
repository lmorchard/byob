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
        (!$profile->checkPrivilege('edit_roles')) ? '' :
            form::fieldset('roles', array(), array( slot::get('roles') )),
        form::fieldset('finish', array(), array(
            form::field('submit', 'details', null, array('value'=>'Update')),
        ))
    ));
?>
