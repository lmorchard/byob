<?php slot::set('head_title', 'contact us'); ?>
<?php slot::set('crumbs', 'contact us'); ?>

<?php if (isset($email_sent)): ?>
    <h3>Contact us</h3>
    <p>Your contact request has been sent.</p>
<?php else: ?>
    <?= 
    form::build(url::current(), array('class'=>'contact'), array(
        form::hidden('referer', @$_SERVER['HTTP_REFERER']),
        form::fieldset('Contact us', array(), array(
            '<p>All fields are required.</p>',

            form::field('input', 'name', 'Name', array('class'=>'required')),
            form::field('input', 'email', 'Email', array('class'=>'required')),
            form::field('dropdown', 'category', 'Category', array(
                'class' => 'required',
                'options' => array(
                    'general'       => 'Question About BYOB (General Inquiries)',
                    'customization' => 'Customization Help',
                    'approvals'     => 'Approvals/Rejections',
                    'techsupport'   => 'Technical Support/Help',
                    'marketing'     => 'Marketing/Use of Mozilla Marks',
                    'suggestion'    => 'Suggestions/Requests for Enhancements',
                ),
            )),
            form::field('textarea', 'comments', 'Comments', array('class'=>'required')),
            '<li class="required"><label for="recaptcha">Captcha</label><span>' . recaptcha::html() . '</span></li>',
            form::field('submit', 'contact', null, array('class'=>'required','value'=>'Contact us'))
        ))
    ));
    ?>
<?php endif ?>
