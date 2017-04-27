<?php
/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 04/04/2017
 * Time: 22:31
 */
global $user, $component, $curl, $cookieArray;

$component->title('Person');
$component->linkAction('/play/person/new','Create Person','A person, or a character, is what you use to play the game.','/img/link-person-w.png');

$result = $curl->get('person/popular',null,['order-by' => '{"popularity":"DESC", "thumbsup":"DESC", "nickname":"ASC"}', 'limit-from' => 5]);

$userList = $user->isActive
    ? $user->getPerson()
    : null;

$cookieList = isset($_COOKIE[$cookieArray['person']])
    ? unserialize($_COOKIE[$cookieArray['person']])
    : null;

$popularList = isset($result['data'])
    ? $result['data']
    : null;

$filterList = null;

if($userList) {
    $component->h1('Your saved persons');

    foreach($userList as $item) {
        if(isset($item['person_id'])) {
            $filterList[] = $item['person_id'];

            $isOwner = isset($item['person_hash'])
                ? true
                : false;

            $link = $isOwner
                ? 'play/person/id/'.$item['person_id'].'/'.$item['person_hash']
                : 'play/person/id/'.$item['person_id'];

            $person = $curl->get('person/id/'.$item['person_id'].'/short')['data'][0];

            $component->linkButton($link,$person['nickname'].' ('.$person['occupation'].') ('.$person['age'].')');
        }
    }
}

if($cookieList) {
    $component->h1('Persons found in cookies');

    foreach($cookieList as $item) {
        if($filterList && in_array($item['person_id'],$filterList)) {}
        else {
            if(isset($item['person_id'])) {
                $filterList[] = $item['person_id'];

                $isOwner = isset($item['person_hash'])
                    ? true
                    : false;

                $link = $isOwner
                    ? '/play/person/id/'.$item['person_id'].'/'.$item['person_hash']
                    : '/play/person/id/'.$item['person_id'];

                $person = $curl->get('person/id/'.$item['person_id'].'/short')['data'][0];

                $component->linkButton($link,$person['nickname'].' ('.$person['occupation'].')');
            }
        }
    }
}

if($popularList) {
    $component->h1('Most Popular Persons');

    foreach($popularList as $person) {
        $component->linkButton('/play/person/id/' . $person['id'], $person['firstname'].' "'.$person['nickname'].'" '.$person['surname'].' ('.$person['occupation'].')');
    }
}
?>