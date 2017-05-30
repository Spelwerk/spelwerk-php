<?php global $component, $sitemap, $system;

$index = is_numeric($sitemap->index) && is_int($sitemap->index + 0) ? intval($sitemap->index) : $sitemap->index;

if(is_int($index)) {
    require_once('./site/content/milestone/id.php');
} else if($index == 'create') {
    $system->createMilestone();
} else {
    $component->title('Milestone');
    $component->returnButton('/content');
    $system->listMilestone();
    $component->h4('Create');
    $component->linkButton('/content/milestone/create','Create New');
}
?>