<?php
/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 15/02/2017
 * Time: 18:51
 */
global $sitemap, $component;

require_once('./class/Person.php');
require_once('./class/System.php');

$person = new Person($sitemap->id, $sitemap->hash);
$system = new System();

$component->title('Edit '.$person->nickname);
?>

<div class="sw-l-quicklink">
    <?php $component->linkQuick('/play/'.$person->id.'/'.$person->hash,'Return','/img/return.png'); ?>
</div>

<?php
$component->wrapStart();
$component->h2('Add Bionic');
$component->p('Once attached. Bionics cannot be removed.');
$component->wrapEnd();

$system->person_checkBionic($person);
?>

<script src="/js/play_create.js"></script>