<?php
    $screen_name = authprofiles::get_profile('screen_name');
    if (empty($screen_name)) $screen_name = 'guest';
    $e_screen_name = html::specialchars($screen_name);
    $u_screen_name = rawurlencode($screen_name);
    $page_id = Router::$controller . '_' . Router::$method;
    $locale = Gettext_Main::$current_language; 
    $locale_dir = Gettext_Main::$current_dir;
?>
<html lang="<?=$locale?>" dir="<?=$locale_dir?>"> 

    <head>  
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
        <title><?=sprintf(_('build your own browser :: %1$s'), slot::get('head_title')) ?></title>
        <link rel="shortcut icon" href="<?=url::base()?>favicon.ico" type="image/x-icon" />

        <?php
            $css = array(
                'css/reset-min.css', 
                'css/smoothness/jquery-ui-1.8.1.custom.css',
                'css/main.css',
            );

            // HACK: Include CSS files only if they exist.
            // TODO: Replace this with config and minimization
            $try = array(
                "css/".Router::$controller.".css",
                "css/".Router::$controller."-".Router::$method.".css",
            );
            foreach ($try as $url) {
                if (is_file(APPPATH . '../' . $url)) {
                    $css[] = $url;
                }
            }
        ?>
        <?=html::stylesheet($css)?>

        <!--[if IE]>
        <?=html::stylesheet(array('css/ie.css'))?>
        <![endif]-->

        <?= slot::get('head_end') ?>
    </head> 

    <body id="<?= 'ctrl_' . Router::$controller . '_act_' . Router::$method ?>" 
        class="locale-<?=$locale?> <?= 'ctrl_' . Router::$controller ?> <?= 'act_' . Router::$method ?> <?= 'ctrl_' . Router::$controller . '_act_' . Router::$method ?> <?=slot::get('body_class')?>">

        <div id="wrap" class="<?= (slot::exists('sidebar') != '') ? 'with_sidebar' : '' ?>">
            <?php if (slot::get('is_popup')): ?>
                <div id="main" class="clearfix">
                    <div class="popup"><div id="content"><?= $content ?></div></div>
                </div>
            <?php else: ?>
            <div id="main" class="clearfix">
                <div id="header">
                    <div class="crumbs">
                        <h1 class="site_title"><a href="http://www.mozilla.com">mozilla</a></h1>
                        <h2 class="title"><a href="<?=url::site()?>"><?=_('build your own browser')?></a></h2>
                    </div>
                    <div class="sub">
                        <div class="auth">
                            <div class="welcome">
                                <?=sprintf(_('Welcome, <span class="screen_name">%1$s</span>'), $e_screen_name)?>. 
                            </div>
                            <ul class="nav">
                                <?php if (!authprofiles::is_logged_in()): ?>
                                    <li class="first"><a href="<?= url::site('register') ?>"><?=_('Sign up')?></a></li>
                                    <li><a class="login" href="<?= url::site('login') ?>"><?=_('Log in')?></a></li>
                                <?php else: ?>
                                <li class="first"><a href="<?= url::site('profiles/' . $u_screen_name) ?>"><?=_('My profile')?></a></li>
                                    <?php if (!empty($approval_queue_allowed) && $approval_queue_count > 0): ?>
                                        <li><a href="<?= url::site('search/approvalqueue') ?>"><?=sprintf(_('Queue (%1$s)'), $approval_queue_count)?></a></li>
                                    <?php endif ?>
                                    <!--
                                        <li><a href="<?= url::site('profiles/' . $u_screen_name . '/settings') ?>">edit profile</a></li>
                                    -->
                                    <?php if (authprofiles::is_allowed('admin', 'index')): ?>
                                        <li><a href="<?= url::site('admin/') ?>"><?=_('Manage')?></a></li>
                                    <?php endif ?>
                                    <li><a href="<?= url::site('logout') ?>"><?=_('Log out')?></a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <?php if (authprofiles::is_allowed('search', 'search') || 
                                  authprofiles::is_allowed('search', 'search_repack')): ?>
                            <div class="search">
                                <?=form::open('search', array('method'=>'get'))?>
                                    <?=form::hidden('m', 'repack')?>
                                    <?=form::input(array(
                                        'name'  => 'q',
                                        'value' => @$_GET['q'],
                                        'size'  => '30', 
                                        'title' => _('Search Browsers')
                                    ))?>
                                </form>
                            </div>
                        <?php endif ?>
                    </div>
                </div>

                <div id="middle" class="clearfix">
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

            </div>
        </div>

        <div id="footer">
            <div class="content">
                <div class="copyright">
                    <p><strong><?=_('Copyright &copy; 2005 - 2010 Mozilla')?></strong></p>
                    <p><?=_('All rights reserved')?></p>
                </div>
                <ul class="nav">
                    <li class="first"><a href="http://www.mozilla.com/<?=$locale?>/privacy-policy.html"><?=_('Privacy Policy')?></a></li>
                    <li><a href="http://www.mozilla.com/<?=$locale?>/about/legal.html"><?=_('Legal Notices')?></a></li>
                    <li><a href="<?=url::site('contact')?>"><?=_('Contact us')?></a></li>
                </ul>
            </div>
        </div>
        <?php endif ?>

        <script type="text/javascript">
            if (typeof window.BYOB == 'undefined') window.BYOB = {};
            BYOB.Config = {
                global: {
                    debug: true,
                    base_url: <?= json_encode(url::site()) ?>
                },
                EOF: null
            };
        </script>

        <?=html::script(array(
            'js/json2.js',
            'js/class.js',
            'js/sugar-arrays-min.js',
            'js/sprintf.js',
            'js/jquery-1.4.2.min.js',
            'js/jquery-ui-1.8.1.custom.min.js',
            'js/jquery.cookies.2.0.1.min.js',
            'js/jquery.simplemodal-1.3.min.js',
            'js/jquery.cloneTemplate.js',
            'js/jquery.input-hint.js',
            'js/byob/main.js',
            //'js/byob/'.Router::$controller.'.js'
        ))?>

        <script type="text/javascript" src="<?=url::site()?>/l10n/translations?callback=BYOB_Main.loadTranslations"></script>

        <script type="text/javascript">
            var tb_pathToImage = "<?=url::base()?>img/loadingAnimation.gif";
        </script>

        <?=slot::get('body_end')?>

<!-- START OF SmartSource Data Collector TAG -->
<!-- Copyright (c) 1996-2010 WebTrends Inc.  All rights reserved. -->
<!-- Version: 8.6.2 -->
<!-- Tag Builder Version: 3.0  -->
<!-- Created: 6/29/2010 2:26:47 AM -->
<script src="<?=url::base()?>js/webtrends.js" type="text/javascript"></script>
<!-- ----------------------------------------------------------------------------------- -->
<!-- Warning: The two script blocks below must remain inline. Moving them to an external -->
<!-- JavaScript include file can cause serious problems with cross-domain tracking.      -->
<!-- ----------------------------------------------------------------------------------- -->
<script type="text/javascript">
//<![CDATA[
var _tag=new WebTrends();
_tag.dcsGetId();
//]]>>
</script>
<script type="text/javascript">
//<![CDATA[
// Add custom parameters here.
//_tag.DCSext.param_name=param_value;
_tag.dcsCollect();
//]]>>
</script>
<noscript>
<div><img alt="DCSIMG" id="DCSIMG" width="1" height="1" src="http://statse.webtrendslive.com/dcsc5owwq00000oqkg7rs741p_2w6v/njs.gif?dcsuri=/nojavascript&amp;WT.js=No&amp;WT.tv=8.6.2"/></div>
</noscript>
<!-- END OF SmartSource Data Collector TAG -->

    </body>
</html>
