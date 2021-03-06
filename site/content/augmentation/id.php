<?php global $component, $sitemap, $system;

if($sitemap->index) {
    $augmentation = new Augmentation($sitemap->index);

    $component->title($augmentation->name);

    switch($sitemap->context)
    {
        default:
            $augmentation->view();
            break;

        case 'edit':
            $augmentation->put();
            break;

        case 'attribute':
            require_once('attribute.php');
            break;

        case 'skill':
            require_once('skill.php');
            break;
    }
}
?>