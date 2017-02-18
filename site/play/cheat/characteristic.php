<?php
/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 15/02/2017
 * Time: 15:06
 */
global $sitemap, $form;

require_once('./class/Person.php');

$person = new Person($sitemap->id, $sitemap->hash);
?>

<div class="sw-l-quicklink">
    <a class="sw-l-quicklink__item" href="/play/<?php echo $person->id; ?>/<?php echo $person->hash; ?>"><img src="/img/return.png"/></a>
</div>

<?php if($sitemap->thing == 'gift'): ?>

    <script src="/js/play.js"></script>

    <h2>Gift</h2>
    <?php
    global $form;

    $system = new System();

    $system->person_selectCharacteristic($person, 1, 1);
    ?>

<?php endif; ?>

<?php if($sitemap->thing == 'imperfection'): ?>

    <script src="/js/play.js"></script>

    <h2>Imperfection</h2>
    <?php
    global $form;

    $system = new System();

    $system->person_selectCharacteristic($person, 0, 1);
    ?>

<?php endif; ?>

<?php if(!$sitemap->thing): ?>

    <h2>Characteristic</h2>
    <?php
    $list = $person->getCharacteristic();

    foreach($list as $item) {
        $person->buildRemoval($item->id, $item->name, $item->icon, 'characteristic');
    }
    ?>
    <div class="sw-l-content__wrap">
        <a class="sw-c-link" href="/play/<?php echo $person->id; ?>/<?php echo $person->hash; ?>/cheat/characteristic/gift">Add Gift</a>
        <a class="sw-c-link" href="/play/<?php echo $person->id; ?>/<?php echo $person->hash; ?>/cheat/characteristic/imperfection">Add Imperfection</a>
    </div>

<?php endif; ?>