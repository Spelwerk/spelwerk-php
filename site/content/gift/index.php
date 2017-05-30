<?php global $component, $sitemap, $system;

$index = is_numeric($sitemap->index) && is_int($sitemap->index + 0) ? intval($sitemap->index) : $sitemap->index;

if(is_int($index)) {
    require_once('./site/content/gift/id.php');
} else if($index == 'create') {
    $system->createGift();
} else {
    $component->title('Gift');
    $component->returnButton('/content');
    $system->listGift();
    $component->h4('Create');
    $component->linkButton('/content/gift/create','Create New');
}
?>