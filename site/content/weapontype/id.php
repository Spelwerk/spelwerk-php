<?php global $component, $sitemap, $system;

if($sitemap->index) {
    $weapontype = new WeaponType($sitemap->index);

    $component->title($weapontype->name);

    switch($sitemap->context)
    {
        default:
            $weapontype->view();
            break;

        case 'edit':
            $weapontype->put();
            break;
    }
}
?>