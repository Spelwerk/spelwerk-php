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
    $component->returnButton($world->siteLink.'/expertise');
    $world->postExpertise();
    ?>

<?php elseif($sitemap->context == 'delete'): ?>

    <?php
    $component->returnButton($world->siteLink.'/expertise');
    $world->deleteExpertise();
    ?>

<?php else: ?>

    <?php
    $component->returnButton($world->siteLink);
    $component->h1('Expertise');

    $skillArray = $world->getSkill();
    $manifestationArray = $world->getManifestation();

    foreach($skillArray as $skill) {
        $list = $world->getExpertise('/skill/'.$skill->id);

        if(!$list) continue;

        $component->h2($skill->name);

        foreach($list as $item) {
            $component->listItem($item->name, $item->description, $item->icon);
        }
    }

    foreach($manifestationArray as $manifestation) {
        $list = $world->getExpertise('/manifestation/'.$manifestation->id);

        if(!$list) continue;

        $component->h2($manifestation->name);

        foreach($list as $item) {
            $component->listItem($item->name, $item->description, $item->icon);
        }
    }

    $component->linkButton($world->siteLink.'/expertise/add','Add');
    $component->linkButton($world->siteLink.'/expertise/delete','Delete',true);
    ?>

<?php endif; ?>

<script src="/js/validation.js"></script>
<script src="/js/play.js"></script>