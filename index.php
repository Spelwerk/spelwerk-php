<?php
/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 2016-11-30
 * Time: 12:05
 */

//setcookie('sw_person_list', '', time() -2000); setcookie('sw_world_list', '', time() -2000); setcookie('sw_cookie_policy', '', time() -2000);

require_once('php/config.php');

if(!$maintenanceMode) {
    require_once('class/Component.php');
    require_once('class/Curl.php');
    require_once('class/Form.php');
    require_once('class/Menu.php');
    require_once('class/Sitemap.php');
    require_once('class/User.php');

    $component = new Component();
    $curl = new Curl($config_curl);
    $form = new Form();
    $user = new User();

    $menu = new Menu($user);
    $sitemap = new Sitemap($user);

    $menu->findActive($sitemap->menuID, $sitemap->menuLink);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <link rel="stylesheet" type="text/css" media="screen" href="/dependency/reset.css"/>
    <link rel="stylesheet" type="text/css" media="screen" href="/css/main.css"/>

    <script src="/dependency/prefixfree.min.js"></script>
    <script src="/dependency/jquery-2.2.4.min.js"></script>

    <script src="/js/test.js"></script>

    <title>Spelwerk</title>
</head>
<body>

<?php if(!$maintenanceMode): ?>

    <?php
    if(!isset($_COOKIE['sw_cookie_policy'])) {
        require_once('php/cookiepolicy.php');
    }
    ?>

    <header class="sw-c-header">
        <div class="sw-l-wrap">
            <div class="sw-c-header__top">
                <div class="sw-c-header__logo">spelwerk</div>
                <div class="sw-c-header__tabs">
                    <?php $menu->buildTab(); ?>
                </div>
            </div>
        </div>
        </div>
        <div class="sw-c-header__bottom">
            <div class="sw-l-wrap">
                <?php $menu->buildList(); ?>
            </div>
        </div>
    </header>

    <section id="content">

    <?php
    if(isset($sitemap->page)) {
        require_once($sitemap->page);
    }
    ?>

    </section>

    <footer class="sw-l-footer">
        <div class="sw-l-wrap">

        </div>
    </footer>

    <?php require_once('php/modal.php'); ?>

    <div class="sw-js-modal-mask sw-u-mask sw-is-hidden"></div>
    <div class="sw-js-saved-critical sw-is-hidden">0</div>
    <div class="sw-js-roll-modifier sw-is-hidden">0</div>

<?php else: ?>

    <?php require('page/error/maintenance_mode.php'); ?>

<?php endif; ?>

</body>
</html>