<?php global $component, $sitemap, $system;

$index = is_numeric($sitemap->index) && is_int($sitemap->index + 0) ? intval($sitemap->index) : $sitemap->index;

if(is_int($index)) {
    require_once('./site/play/person/id.php');
} else if($index == 'create') {
    $component->title('Create Person');

    $world = isset($_POST['item--world_id']) ? new World($_POST['item--world_id']) : null;
    $species = isset($_POST['item--species_id']) ? new Species($_POST['item--species_id']) : null;

    $system->createPerson($world, $species);
} else {
    $component->title('Person');
    $component->returnButton('/play');
    $system->listPerson();
    $component->h4('Create');
    $component->linkAction('/play/person/create','Create Person','A person, or a character, is what you use to play the game.','/img/link-person-w.png');
}
?>