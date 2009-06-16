<?php
    $screen_name = AuthProfiles::get_profile('screen_name');
    $u_screen_name = rawurlencode($screen_name);
?>
<html> 

    <head>  
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
        <title>byob :: <?= slot::get('head_title') ?></title>
        <?=html::stylesheet(array(
            'css/main.css', 
            // 'css/' . Router::$controller . '.css'
        ))?>
        <?= slot::get('head_end') ?>
    </head> 

    <body id="<?= 'ctrl_' . Router::$controller . '_act_' . Router::$method ?>" 
            class="<?= 'ctrl_' . Router::$controller ?> <?= 'act_' . Router::$method ?> <?= 'ctrl_' . Router::$controller . '_act_' . Router::$method ?>">
        <div id="page" class="<?= (slot::exists('sidebar') != '') ? 'with_sidebar' : '' ?>">

            <div id="header">

                <div class="logo"><span></span></div>

                <div class="crumbs">
                    <span class="title"><a href="<?=url::base()?>">byob</a></span>
                    <?= slot::get('crumbs') ?>
                </div>

                <div class="main">
                    <?php if (AuthProfiles::is_logged_in()): ?>
                        <ul class="nav">
                            <li class="first"><a href="<?= url::base() . 'people/' . $u_screen_name ?>">your stuff</a></li> 
                        </ul>
                    <?php else: ?>
                    <?php endif ?>
                </div>

                <div class="sub">

                    <div class="auth">
                        <ul class="nav">
                            <?php if (!AuthProfiles::is_logged_in()): ?>
                                <li class="first"><a href="<?= url::base() . 'login' ?>">login</a></li>
                                <li><a href="<?= url::base() . 'register' ?>">register</a></li>
                            <?php else: ?>
                                <li class="first">logged in as <a href="<?= url::base() . 'people/' . $u_screen_name ?>"><?= html::specialchars($screen_name) ?></a></li>
                                <li><a href="<?= url::base() . 'profiles/' . $u_screen_name . '/settings' ?>">settings</a></li>
                                <li><a href="<?= url::base() . 'logout' ?>">logout</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                </div>

            </div>

            <div id="infobar">
                <?= slot::get('infobar') ?>
            </div>

            <div id="middle">
                <div id="content">
                    <?php if (!empty($message)): ?>
                        <p class="message"><?= html::specialchars($message) ?></p>
                    <?php endif ?>
                    <?php $flash_message = Session::instance()->get('message') ?>
                    <?php if (!empty($flash_message)): ?>
                        <p class="message"><?= html::specialchars($flash_message) ?></p>
                    <?php endif ?>
                    <?= $content ?>
                </div>
                <?php if ( slot::exists('sidebar') ): ?>
                    <div id="sidebar"><?=slot::get('sidebar')?></div>
                <?php endif ?>
            </div>

            <div id="footer">
                <ul class="nav">
                <li class="first"><a href="<?=url::base()?>">byob</a></li>
                    <?= slot::get('footer_nav') ?>
                </ul>
            </div>

        </div>

        <script type="text/javascript">
            if (typeof window.BYOB == 'undefined') window.BYOB = {};
            BYOB.Config = {
                global: {
                    debug: true,
                    base_url: <?= json_encode(url::base()) ?>
                },
                EOF: null
            };
        </script>

        <?=html::script(array(
            'js/jquery-1.3.2.min.js',
            'js/byob/main.js',
            //'js/byob/'.Router::$controller.'.js'
        ))?>

        <?=slot::get('body_end')?>
    </body>
</html>
