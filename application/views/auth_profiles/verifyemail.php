<?php if (false && !empty($invalid_token)): ?>
    <p>Invalid email verification token.</p>
<?php else: ?>
    <?php slot::set('head_title', 'account verification successful'); ?>
    <h2>Account verification successful</h2>
    <p>
        Welcome to Mozilla's Build Your Own Browser (BYOB) web application.
        Your account has been successfully activated, and we've logged you in. 
        We've also set a cookie for you that will keep you logged in to the 
        application, so if you're using a computer that isn't always under your 
        control, you should log out when you're finished using BYOB by using the 
        "logout" link in the upper-right corner of every page.
    </p>
    <p>
        <br />
        <?php
            $profile_url = url::base() .
                "profiles/".authprofiles::get_profile('screen_name');
        ?>
        <a href="<?=$profile_url?>" class="button yellow large">Get Started Building Your Browser Now!</a>
    </p>
<?php endif ?>
