<?php
/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 2017-04-26
 * Time: 16:18
 */
global $sitemap, $user, $form, $component;

require_once('./class/World.php');

if(isset($sitemap->id)) {
    $world = new World($sitemap->id);

    $component->title($world->name);
}
?>

<?php if($sitemap->context == 'add'): ?>

    <?php
    $component->returnButton($world->siteLink.'/weapon');
    $world->postWeapon();
    ?>

<?php elseif($sitemap->context == 'delete'): ?>

    <?php
    $component->returnButton($world->siteLink.'/weapon');
    $world->deleteWeapon();
    ?>

<?php else: ?>

    <?php
    $component->returnButton($world->siteLink);
    $component->h1('Weapon');

    $list = $world->getWeapon();

    if($list[0]) {
        foreach($list as $item) {
            $component->listItem($item->name, $item->description, $item->icon);
        }
    }

    $component->linkButton($world->siteLink.'/weapon/add','Add');
    $component->linkButton($world->siteLink.'/weapon/delete','Delete',true);
    ?>

<?php endif; ?>

<script src="/js/validation.js"></script>
<script src="/js/play.js"></script>