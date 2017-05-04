<?php
/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 15/02/2017
 * Time: 15:06
 */
global $sitemap, $form, $component;

require_once('./class/Person.php');

$person = new Person($sitemap->id, $sitemap->hash);

$component->title('Cheat '.$person->nickname);

if($person->isOwner) {
    $component->returnButton($person->siteLink);
    $component->h2('Milestone');

    if ($sitemap->context == 'add') {
        $person->postMilestone(1);
    } else {
        $list = $person->getMilestone();

        $component->wrapStart();

        foreach ($list as $item) {
            $person->buildRemoval($item->id, $item->name, $item->icon, 'milestone');
        }

        $component->linkButton($person->siteLink . '/cheat/milestone/add', 'Add Milestone');
        $component->wrapEnd();
    }
}
?>

<script src="/js/validation.js"></script>
