<?php
/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 15/02/2017
 * Time: 18:51
 */
global $sitemap, $form, $component;

require_once('./class/Person.php');

$person = new Person($sitemap->id, $sitemap->hash);

$component->title('Edit '.$person->nickname);

if($person->isOwner) {
    $component->returnButton($person->siteLink);

    $component->h2('Weapon');

    if($sitemap->context == 'add') {
        $person->postWeapon();
    } else {
        $list = $person->getWeapon();

        if(isset($list)) {
            foreach($list as $item) {
                $person->buildRemoval('weapon', $item->id, $item->name, $item->icon);
            }
        }

        $component->linkButton($person->siteLink.'/edit/weapon/add','Add');
    }
}
?>

<script src="/js/validation.js"></script>
