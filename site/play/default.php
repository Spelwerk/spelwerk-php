<?php
/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 2016-12-08
 * Time: 10:55
 */
global $sitemap, $user, $form, $component;

require_once('./class/Person.php');

$person = null;
$userOwner = null;

if(isset($sitemap->id) && isset($sitemap->hash)) {
    $person = new Person($sitemap->id, $sitemap->hash);
} else if (isset($sitemap->id)) {
    $person = new Person($sitemap->id);
}
?>

<?php if(!isset($person) || !$person->isCalculated): ?>

    <?php
    $world = isset($_POST['item--world_id'])
        ? new World($_POST['item--world_id'])
        : null;

    $species = isset($_POST['item--species_id'])
        ? new Species($_POST['item--species_id'])
        : null;
    ?>

    <?php
    require_once('./class/System.php');
    $system = new System();
    $system->createPerson($person, $world, $species);
    ?>

    <script src="/js/play_create.js"></script>

<?php endif; ?>

<?php if($person && $person->isCalculated): ?>

    <?php
    if(isset($user)) {
        $list = $user->getPerson();

        if($list) {
            foreach($list as $p) {
                if($sitemap->id == $p['person_id'] && $sitemap->hash == $p['person_hash']) {
                    $userOwner = true;
                }
            }
        }
    }
    ?>

    <?php $component->title($person->nickname); ?>

    <?php if($person->isOwner): ?>

        <div class="sw-l-quicklink">
            <?php
            $component->linkQuick('/play/'.$person->id.'/'.$person->hash.'/wound','Wound','/img/wound.png');
            $component->linkQuick('/play/'.$person->id.'/'.$person->hash.'/edit/weapon','Weapon','/img/weapon.png');
            $component->linkQuick('/play/'.$person->id.'/'.$person->hash.'/edit/protection','Protection','/img/armor.png');

            if($person->world->existsBionic) {
                $component->linkQuick('/play/'.$person->id.'/'.$person->hash.'/edit/bionic','Bionic','/img/bionic.png');
            }

            $component->linkQuick('/play/'.$person->id.'/'.$person->hash.'/edit','Edit','/img/edit.png');
            ?>
        </div>

        <?php if(isset($user) && $userOwner != true): ?>

            <?php
            $form->formStart();
            $form->hidden('return', 'play', 'post');
            $form->hidden('do', 'user--save', 'post');
            $form->hidden('id', $person->id, 'post');
            $form->hidden('hash', $person->hash, 'post');
            $form->hidden('user', $user->id, 'post');
            $form->formEnd(false, 'Save this person');
            ?>

        <?php endif; ?>

    <?php endif; ?>

    <?php
    $component->p('<p>This is '.$person->firstname.' "'.$person->nickname.'" '.$person->surname.'. '.$person->firstname.' is a '.$person->age.' years old '.$person->gender.' '.$person->occupation.'</p>');

    $component->h2('Skill');
    $component->wrapStart();
    $person->makeButton($person->getAttribute($person->world->attributeSkill), 'skill');
    $person->makeExpertise();
    $person->makeSupernatural();
    $component->wrapEnd();

    $component->h2('Attribute');
    $component->wrapStart();
    $person->makeButton($person->getAttribute($person->world->attributeReputation), 'skill');
    $person->makeButton($person->getAttribute($person->world->attributeCombat), 'skill');
    $component->wrapEnd();

    $component->h2('Consumable');
    $component->wrapStart();
    $person->makeButton($person->getAttribute($person->world->attributeConsumable), 'consumable');
    $component->wrapEnd();

    $component->h2('Weapon', 'weapon');
    $component->wrapStart();
    $person->makeButton($person->getWeapon(1), 'weapon');
    $component->wrapEnd();

    $component->h2('Wound', 'wound');
    $component->wrapStart();
    $person->makeProtection();
    /* $person->buildCard($person->getAttribute($person->world->attributeBody)); */
    /* $person->buildCard($person->getAttribute($person->world->attributeDamage)); */
    $person->makeWound();

    if($person->isOwner) {
        $component->link('/play/'.$person->id.'/'.$person->hash.'/wound','Add Wound');
    }

    $component->wrapEnd();

    $component->h2('Equipment');
    $component->h3('Weapon');
    $person->makeWeaponEquip();

    if($person->isOwner) {
        $component->link('/play/'.$person->id.'/'.$person->hash.'/edit/weapon','Edit Weapon');
    }

    $component->h3('Protection','protection');
    $person->makeProtectionEquip();

    if($person->isOwner) {
        $component->link('/play/'.$person->id.'/'.$person->hash.'/edit/protection','Edit Protection');
    }

    if($person->world->existsBionic) {
        $component->h3('Bionic','bionic');
        $person->makeList($person->getBionic());

        if($person->isOwner) {
            $component->link('/play/'.$person->id.'/'.$person->hash.'/edit/bionic/bionic','Edit Bionic');
        }
    }

    if($person->world->existsAugmentation) {
        $component->h3('Augmentation','augmentation');
        $person->makeList($person->getAugmentation());

        if($person->isOwner) {
            $component->link('/play/'.$person->id.'/'.$person->hash.'/edit/bionic/augmentation','Edit Augmentation');
        }
    }

    $component->h2('Description');
    $component->wrapStart();

    if($person->description != null) {
        $component->p($person->description);
    }

    if($person->personality != null) {
        $component->p($person->personality);
    }

    if($person->isOwner) {
        $component->link('/play/'.$person->id.'/'.$person->hash.'/edit/description','Edit Description');
    }

    $component->wrapEnd();

    $component->h2('Features');
    $person->makeFeatures();
    $person->makeExpertiseList();

    if($person->isSupernatural) {
        $person->makeCard($person->getAttribute($person->world->attributePower));
    }

    $person->makeCard($person->getAttribute($person->world->attributeExperience));

    if($person->isOwner) {
        $component->linkButton('/play/'.$person->id.'/'.$person->hash.'/edit', 'Level Up', '/img/arrow-up.png');
    }

    ?>

    <script src="/js/play.js"></script>

<?php endif; ?>
