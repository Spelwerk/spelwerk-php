<?php
/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 15/02/2017
 * Time: 15:05
 */
global $sitemap, $form;
$person = new Person($sitemap->id, $sitemap->hash);
$system = new System();

?>
<div class="sw-l-quicklink">
    <a class="sw-l-quicklink__item" href="/play/<?php echo $person->id; ?>/<?php echo $person->hash; ?>"><img src="/img/return.png"/></a>
</div>

<script src="/js/play.js"></script>

<h2>Edit Skills</h2>
<?php $system->person_purchaseSkill($person, 999); ?>