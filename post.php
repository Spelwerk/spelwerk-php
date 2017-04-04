<?php
/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 03/02/2017
 * Time: 11:23
 */

require_once('class/Curl.php');
require_once('php/config.php');

$curl = new Curl($config_curl);

$POST_ERROR = null;

$POST_DO = isset($_POST['post--do'])
    ? $_POST['post--do']
    : null;

$POST_RETURN = isset($_POST['post--return'])
    ? $_POST['post--return']
    : null;

$POST_RETURNID = isset($_POST['post--returnid'])
    ? $_POST['post--returnid']
    : null;

$POST_RETURNAFTER = isset($_POST['post--returnafter'])
    ? $_POST['post--returnafter']
    : null;

$POST_ID = isset($_POST['post--id'])
    ? $_POST['post--id']
    : null;

$POST_HASH = isset($_POST['post--hash'])
    ? $_POST['post--hash']
    : null;

$POST_USER = isset($_POST['post--user'])
    ? $_POST['post--user']
    : null;

$USER_TOKEN = null;

function redirect($url) {
    ob_start();
    header('Location: '.$url, true, 303);
    ob_end_flush();
    exit;
}

function checkError($resultArray) {
    global $POST_ERROR;

    if($resultArray) {
        foreach($resultArray as $key => $res) {
            if(isset($res['error'])) {
                $POST_ERROR[] = $res['error'];
            }
        }
    }
}

function cookiePerson($personNickname, $personId, $personHash) {
    if(isset($_COOKIE['sw_person_list'])) {
        $cookie = unserialize($_COOKIE['sw_person_list']);
    }

    $cookie[] = ['name' => $personNickname, 'person_id' => $personId, 'person_hash' => $personHash];

    setcookie('sw_person_list', serialize($cookie), time() + (9 * 365 * 24 * 60 * 60));
}


function unsetUser() {
    unset($_COOKIE['sw_user_token']);

    setcookie('sw_user_token', '', time() - 3600, '/');

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}

function setUser($token) {
    setcookie('sw_user_token', $token, time() + (86400 * 30), "/");
}



function manifestation_post($postData) {
    global $curl;

    $postArray = null;
    $resultArray = null;

    // Attribute Type
    $attributeTypePost = [
        'name' => $postData['name'],
        'protected' => 0,
        'maximum' => $postData['attribute_maximum']
    ];
    $attributeTypeResult = $curl->post('attributetype', $attributeTypePost);
    $attributeTypeId = $attributeTypeResult['id'];
    $resultArray[] = $attributeTypeResult;

    // Attribute for Power
    $attributePost = [
        'name' => $postData['attribute_power'],
        'protected'  => 0,
        'attributetype_id' => 7
    ];
    $attributeResult = $curl->post('attribute', $attributePost);
    $attributeId = $attributeResult['id'];
    $resultArray[] = $attributeResult;

    // Expertise
    $expertiseTypePost = [
        'name' => $postData['name'],
        'maximum' => $postData['expertise_maximum'],
        'skill_attribute_required' => $postData['skill_attribute_required'],
        'skill_attribute_increment' => $postData['skill_attribute_increment'],
        'startsat' => $postData['startsat']
    ];
    $expertiseTypeResult = $curl->post('expertisetype', $expertiseTypePost);
    $expertiseTypeId = $expertiseTypeResult['id'];
    $resultArray[] = $expertiseTypeResult;

    // Manifestation
    $postArray['name'] = $postData['name'];
    $postArray['description'] = $postData['description'];
    $postArray['attributetype_id'] = $attributeTypeId;
    $postArray['skill_attributetype_id'] = $postData['skill_attributetype_id'];
    $postArray['expertisetype_id'] = $expertiseTypeId;
    $postArray['power_attribute_id'] = $attributeId;

    $result = $curl->post('manifestation', $postArray);
    $resultArray[] = $result;

    checkError($resultArray);

    return $result;
}

function manifestation_discipline($postData, $manifestationId) {
    global $curl;

    $postArray = null;
    $resultArray = null;

    // Attribute Discipline (Give Attribute Id in Expertise)
    $attributePost = [
        'name' => $postData['name'],
        'description' => $postData['description'],
        'protected'  => 0,
        'attributetype_id' => $postData['attributetype_id'],
        'icon_id' => $postData['icon']
    ];
    $attributeResult = $curl->post('attribute', $attributePost);
    $attributeId = $attributeResult['id'];
    $resultArray[] = $attributeResult;

    // Expertise Creation of Discipline
    $expertisePost = [
        'name' => $postData['name'],
        'description' => $postData['description'],
        'hidden'  => 0,
        'expertisetype_id' => $postData['expertisetype_id'],
        'manifestation_id' => $manifestationId,
        'skill_attribute_id' => $postData['attribute_id'],
        'give_attribute_id' => $attributeId
    ];
    $expertiseResult = $curl->post('expertise', $expertisePost);
    $resultArray[] = $expertiseResult;

    checkError($resultArray);
}

function manifestation_focus($postData, $manifestationId) {
    global $curl;

    $resultArray = null;

    $post = [
        'name' => $postData['name'],
        'description' => $postData['description'],
        'attribute_id' => 3,
        'attribute_value' => 1,
        'manifestation_id' => $manifestationId,
        'icon_id' => $postData['icon']
    ];

    $resultArray[] = $curl->post('attribute', $post);

    checkError($resultArray);
}


function person_postPerson($postData) {
    global $curl;

    $postArray = null;
    $resultArray = null;

    $age = intval($postData['age']);

    $world = $curl->get('world/id/'.$postData['world_id'])['data'][0];
    $item = $curl->get('species/id/'.$postData['species_id'])['data'][0];

    $defaultList = $curl->get('world-attribute/id/'.$postData['world_id'].'/species/'.$postData['species_id'])['data'];
    $speciesList = $curl->get('species-attribute/id/'.$postData['species_id'])['data'];

    $split_supernatural = floor($age / $world['split_supernatural']);
    $split_milestone = floor($age / $world['split_milestone']);
    $split_skill = floor($age / $world['split_skill']) * intval($item['multiply_skill']);
    $split_expertise = floor($age / $world['split_expertise']) * intval($item['multiply_expertise']);
    $split_relationship = floor($age / $world['split_relationship']);

    $point_supernatural = $split_supernatural < $world['max_supernatural']
        ? $split_supernatural
        : $world['max_supernatural'];

    $point_money = $split_milestone < $world['max_milestone']
        ? $split_milestone
        : $world['max_milestone'];

    $point_milestone = $split_milestone < $world['max_milestone']
        ? $split_milestone
        : $world['max_milestone'];

    $point_skill = $split_skill < $world['max_skill']
        ? $split_skill
        : $world['max_skill'];

    $point_expertise = $split_expertise < $world['max_expertise']
        ? $split_expertise
        : $world['max_expertise'];

    $point_relationship = $split_relationship < $world['max_relationship']
        ? $split_relationship
        : $world['max_relationship'];

    $postData['point_supernatural'] = $point_supernatural;
    $postData['point_power'] = 1;
    $postData['point_money'] = $point_money;
    $postData['point_skill'] = $point_skill;
    $postData['point_expertise'] = $point_expertise;
    $postData['point_milestone'] = $point_milestone;
    $postData['point_characteristic_gift'] = intval($world['max_characteristic_gift']);
    $postData['point_characteristic_imperfection'] = intval($world['max_characteristic_imperfection']);
    $postData['point_relationship'] = $point_relationship;

    $result = $curl->post('person', $postData);

    $personId = $result['id'];
    $personHash = $result['hash'];

    foreach($defaultList as $default) {
        if($default['protected'] || $default['attributetype_id'] == $world['skill_attributetype_id']) {
            $postArray[] = ['person_id' => $personId, 'attribute_id' => $default['id'], 'value' => $default['default_value']];
        }
    }

    foreach($postArray as $key => $array) {
        foreach($speciesList as $item) {
            if($array['attribute_id'] == $item['attribute_id']) {
                $postArray[$key]['value'] += $item['value'];
            }
        }
    }

    foreach($postArray as $array) {
        $resultArray[] = $curl->post('person-attribute', $array);
    }

    checkError($resultArray);

    return [
        'id' => $personId,
        'hash' => $personHash,
        'nickname' => $postData['nickname']
    ];
}

function person_putPerson($postData, $personHash) {
    global $curl;

    $resultArray = null;

    $resultArray[] = $curl->put('person/hash/'.$personHash, $postData);

    checkError($resultArray);
}

function person_removeFrom($table, $thingId, $personId) {
    global $curl;

    $resultArray = null;

    $resultArray[] = $curl->delete('person-'.$table.'/id/'.$personId.'/id/'.$thingId);

    checkError($resultArray);
}

function person_postTo($table, $postData) {
    global $curl;

    $resultArray = null;

    $resultArray[] = $curl->post('person-'.$table, $postData);

    checkError($resultArray);
}

function person_putTable($table, $personId, $tableId, $post) {
    global $curl;

    $resultArray = null;

    $resultArray[] = $curl->put('person-'.$table.'/id/'.$personId.'/id/'.$tableId, $post);

    checkError($resultArray);
}

function person_postAttribute($postData, $personId, $allowsAll = false) {
    global $curl;

    $postArray = null;
    $resultArray = null;

    foreach($postData as $key => $value) {
        $explode = explode('__', $key);
        $attributeId = $explode[1];
        $attributeValue = intval($value);

        if($allowsAll || $value > 0) {
            $postArray[] = ['person_id' => $personId, 'attribute_id' => $attributeId, 'value' => $attributeValue];
        }
    }

    foreach($postArray as $post) {
        $resultArray[] = $curl->post('person-attribute',$post);
    }

    checkError($resultArray);
}

function person_putAttribute($postData, $personId, $allowsAll = false) {
    global $curl;

    $postArray = null;
    $resultArray = null;

    print_r($postData);

    foreach($postData as $key => $value) {
        $explode = explode('__',$key);
        $attributeId = $explode[1];
        $attributeValue = intval($value);

        $current = $curl->get('person-attribute/id/'.$personId.'/attribute/'.$attributeId);

        if(isset($current['data'])) {
            $currentValue = $current['data'][0]['value'];

            $attributeValue += intval($currentValue);
        }

        if($allowsAll || $value > 0) {
            $postArray[] = ['person_id' => $personId, 'attribute_id' => $attributeId, 'value' => $attributeValue];
        }
    }

    foreach($postArray as $post) {
        $resultArray[] = $curl->post('person-attribute',$post);
    }

    checkError($resultArray);
}

function person_putAttributeFromTable($table, $tableId, $personId, $currentId = null) {
    global $curl;

    $resultArray = null;

    // first we do removal

    if(isset($currentId)) {
        $currentPost = null;

        $personList= $curl->get('person-attribute/id/'.$personId)['data'];
        $currentList = $curl->get($table.'/id/'.$currentId)['data'];

        foreach($personList as $person) {
            foreach($currentList as $current) {
                if($person['id'] == $current['attribute_id']) {
                    if(isset($current['attribute_value'])) {
                        $value = intval($person['value']) - intval($current['attribute_value']);
                    } else {
                        $value = intval($person['value']) - intval($current['value']);
                    }

                    $currentPost[] = ['person_id' => $personId, 'attribute_id' => $current['attribute_id'], 'value' => $value];
                }
            }
        }

        if(isset($currentPost)) {
            foreach($currentPost as $post) {
                $resultArray[] = $curl->post('person-attribute', $post);
            }
        }
    }

    // then we do addition

    $changePost = null;

    $personList= $curl->get('person-attribute/id/'.$personId)['data'];
    $changeList = $curl->get($table.'/id/'.$tableId)['data'];

    foreach($personList as $person) {
        foreach($changeList as $change) {
            if($person['id'] == $change['attribute_id']) {

                if(isset($change['attribute_value'])) {
                    $value = intval($person['value']) + intval($change['attribute_value']);
                } else {
                    $value = intval($person['value']) + intval($change['value']);
                }

                $changePost[] = ['person_id' => $personId, 'attribute_id' => $change['attribute_id'], 'value' => $value];
            }
        }
    }

    if(isset($changePost)) {
        foreach($changePost as $post) {
            $resultArray[] = $curl->post('person-attribute', $post);
        }
    }

    checkError($resultArray);
}

function person_postAugmentation($postData, $personId) {
    global $curl;

    $resultArray = null;
    $returnArray = null;
    $postAugmentation = null;
    $postAttribute = null;
    $postWeapon = null;

    foreach($postData as $key => $value) {
        $postAugmentation[] = ['person_id' => $personId, 'augmentation_id' => $key, 'augmentationquality_id' => 4];

        $augmentation = $curl->get('augmentation/id/'.$key)['data'][0];

        if(isset($augmentation['attribute_id'])) {
            $currentValue = $curl->get('person-attribute/id/'.$personId.'/attribute/'.$augmentation['attribute_id'])['data'][0]['value'];

            $newValue = intval($augmentation['attribute_value']) + intval($currentValue);

            $postAttribute[] = ['person_id' => $personId, 'attribute_id' => $augmentation['attribute_id'], 'value' => $newValue];
        }

        if(isset($augmentation['weapon_id'])) {
            $postWeapon[] = ['person_id' => $personId, 'weapon_id' => $augmentation['weapon_id'], 'weaponquality_id' => 3, 'equipped' => 1];
        }
    }

    if(isset($postAugmentation)) {
        foreach($postAugmentation as $post) {
            $resultArray[] = $curl->post('person-augmentation', $post);
        }
    }

    if(isset($postAttribute)) {
        foreach($postAttribute as $post) {
            $resultArray[] = $curl->post('person-attribute', $post);
        }
    }

    if(isset($postWeapon)) {
        foreach($postWeapon as $post) {
            $resultArray[] = $curl->post('person-weapon', $post);
        }
    }

    checkError($resultArray);
}

function person_postBionic($postData, $personId) {
    global $curl;

    $resultArray = null;
    $postBionic = null;
    $postAttribute = null;

    foreach($postData as $key => $value) {
        $postBionic[] = ['person_id' => $personId, 'bionic_id' => $key, 'bionicquality_id' => 4];

        $bionic = $curl->get('bionic/id/'.$key)['data'][0];

        if(isset($bionic['attribute_id'])) {
            $currentValue = $curl->get('person-attribute/id/'.$personId.'/attribute/'.$bionic['attribute_id'])['data'][0]['value'];

            $newValue = intval($bionic['attribute_value']) + intval($currentValue);

            $postAttribute[] = ['person_id' => $personId, 'attribute_id' => $bionic['attribute_id'], 'value' => $newValue];
        }
    }

    if(isset($postBionic)) {
        foreach($postBionic as $post) {
            $resultArray[] = $curl->post('person-bionic', $post);
        }
    }

    if(isset($postAttribute)) {
        foreach($postAttribute as $post) {
            $resultArray[] = $curl->post('person-attribute', $post);
        }
    }

    checkError($resultArray);
}

function person_postExpertise($postData, $personId) {
    global $curl;

    $resultArray = null;
    $postExpertise = null;
    $postAttribute = null;

    foreach($postData as $key => $value) {
        $explode = explode('__',$key);
        $expertiseId = $explode[1];

        if($value > 0) {
            $previousLevel = 0;

            $expertise = $curl->get('expertise/id/'.$expertiseId)['data'][0];
            $currentExpertise = $curl->get('person-expertise/id/'.$personId.'/expertise/'.$expertiseId);

            if(isset($currentExpertise['data'][0])) {
                $previousLevel = $currentExpertise['data'][0]['level'];
            }

            $postExpertise[] = ['person_id' => $personId, 'expertise_id' => $expertiseId, 'level' => $value];

            if(isset($expertise['give_attribute_id'])) {
                $currentValue = $curl->get('person-attribute/id/'.$personId.'/attribute/'.$expertise['give_attribute_id'])['data'][0]['value'];

                $newValue = intval($value) + intval($currentValue) - intval($previousLevel);

                $postAttribute[] = ['person_id' => $personId, 'attribute_id' => $expertise['give_attribute_id'], 'value' => $newValue];
            }
        }
    }

    if(isset($postExpertise)) {
        foreach($postExpertise as $post) {
            $resultArray[] = $curl->post('person-expertise', $post);
        }
    }

    if(isset($postAttribute)) {
        foreach($postAttribute as $post) {
            $resultArray[] = $curl->post('person-attribute', $post);
        }
    }

    checkError($resultArray);
}

function person_postProtection($postData, $personId) {
    global $curl;

    $resultArray = null;
    $postArray = null;

    foreach($postData as $key => $value) {
        $postArray[] = ['person_id' => $personId, 'protection_id' => $key, 'protectionquality_id' => 3, 'equipped' => 1];
    } // todo quality should not be hardcoded

    foreach($postArray as $post) {
        $resultArray[] = $curl->post('person-protection', $post);
    }

    checkError($resultArray);
}

function person_postWeapon($postData, $personId) {
    global $curl;

    $resultArray = null;
    $postArray = null;

    foreach($postData as $key => $value) {
        $post = ['person_id' => $personId, 'weapon_id' => $key, 'weaponquality_id' => 3, 'equipped' => 1];

        $resultArray[] = $curl->post('person-weapon', $post);
    }

    // todo quality should not be hardcoded

    checkError($resultArray);
}

function person_woundAdd($postData, $personId, $woundId) {
    global $curl;

    $resultArray = null;

    $post = ['person_id' => $personId, 'wound_id' => $woundId, 'aid' => 0, 'heal' => 0, 'lethal' => $postData['lethal']];

    $resultArray[] = $curl->post('person-wound', $post);

    checkError($resultArray);
}


function user_postUser($postData) {
    global $curl;

    $resultArray = null;

    if($postData['password'] == $postData['password2']) {
        $resultArray[] = $curl->post('user', $postData);
    }

    checkError($resultArray);
}

function user_verifyUser($postData) {
    global $curl;

    $resultArray = null;

    $resultArray[] = $curl->post('user/verify', $postData);

    checkError($resultArray);
}

function user_resendVerification($postData) {
    global $curl;

    $resultArray = null;

    $resultArray[] = $curl->post('user/verify/again', $postData);

    checkError($resultArray);
}

function user_loginPass($postData) {
    global $curl;

    unsetUser();

    if($postData['username'] && $postData['password']) {
        $result = $curl->post('user/login/password', $postData);
    }

    $token = isset($result['token'])
        ? $result['token']
        : null;

    setUser($token);
}

function user_loginMail($postData) {
    global $curl;

    $resultArray[] = null;

    if($postData['email']) {
        $resultArray[] = $curl->post('user/login/mail/start', $postData);
    }

    checkError($resultArray);
}

function user_loginMailVerify($postData) {
    global $curl;

    unsetUser();

    if($postData['verification']) {
        $result = $curl->post('user/login/mail/verify', $postData);
    }

    $token = isset($result['token'])
        ? $result['token']
        : null;

    setUser($token);
}

function user_passMail($postData) {
    global $curl;

    $resultArray = null;

    if($postData['email']) {
        $resultArray[] = $curl->put('user/password/start', $postData);
    }

    checkError($resultArray);
}

function user_passVerify($postData) {
    global $curl;

    $resultArray = null;

    if($postData['verification'] && $postData['password']) {
        $resultArray[] = $curl->put('user/password/set', $postData);
    }

    checkError($resultArray);
}

function user_savePerson($userId, $personId, $personHash) {
    global $curl;

    $resultArray = null;

    $post = isset($personHash)
        ? ['user_id' => $userId, 'person_id' => $personId, 'hash' => $personHash]
        : ['user_id' => $userId, 'person_id' => $personId];

    $resultArray[] = $curl->post('user-person', $post);

    checkError($resultArray);
}

function user_saveWorld($userId, $worldId, $worldHash) {
    global $curl;

    $resultArray = null;

    $post = isset($worldHash)
        ? ['user_id' => $userId, 'world_id' => $worldId, 'hash' => $worldHash]
        : ['user_id' => $userId, 'world_id' => $worldId];

    $resultArray[] = $curl->post('user-world', $post);

    checkError($resultArray);
}


function world_postWorld($postData) {
    global $curl;

    $resultArray = null;

    $postData['template'] = 0;
    $postData['popularity'] = 0;
    $postData['hidden'] = 1;
    $postData['calculated'] = 0;

    $postData['split_supernatural'] = 0;
    $postData['split_skill'] = 0;
    $postData['split_expertise'] = 0;
    $postData['split_milestone'] = 0;
    $postData['split_relationship'] = 0;

    $postData['max_characteristic_gift'] = 0;
    $postData['max_characteristic_imperfection'] = 0;
    $postData['max_supernatural'] = 0;
    $postData['max_skill'] = 0;
    $postData['max_expertise'] = 0;
    $postData['max_milestone'] = 0;
    $postData['max_relationship'] = 0;

    $result = $curl->post('world', $postData);

    $worldId = $result['id'];
    $worldHash = $result['hash'];

    return [
        'id' => $worldId,
        'hash' => $worldHash,
        'name' => $postData['name']
    ];
}

function world_putWorld($postData, $worldHash) {
    global $curl;

    $resultArray = null;

    $resultArray[] = $curl->put('world/hash/'.$worldHash, $postData);

    checkError($resultArray);
}

function world_has($postData, $worldId, $thing) {
    global $curl;

    $resultArray = null;
    $postArray = null;

    $get = 'world-'.$thing;

    foreach($postData as $key => $value) {
        $post = ['world_id' => $worldId, $thing.'_id' => $value];

        $resultArray[] = $curl->post($get, $post);
    }

    checkError($resultArray);
}

function world_hasDefault($postData, $worldId) {
    global $curl;

    $resultArray = null;
    $protectedList = $curl->get('attribute/protected')['data'];

    foreach($protectedList as $attribute) {
        $defaultValue = 0;

        foreach($postData as $key => $value) {
            $explode = explode('__', $key);

            if($attribute['id'] == $explode[1]) {
                $defaultValue = $value;
            }
        }

        $post = ['world_id' => $worldId, 'attribute_id' => $attribute['id'], 'default_value' => $defaultValue];

        $resultArray[] = $curl->post('world-attribute', $post);
    }

    checkError($resultArray);
}

function world_hasSkill($postData, $worldId) {
    global $curl;

    $resultArray = null;
    $postArray = null;

    foreach($postData as $key => $value) {
        $post = ['world_id' => $worldId, 'attribute_id' => $value, 'default_value' => 0];

        $resultArray[] = $curl->post('world-attribute', $post);
    }

    checkError($resultArray);
}

function world_hasExpertise($postData, $worldId) {
    global $curl;

    $resultArray = null;
    $postArray = null;

    foreach($postData as $key => $value) {
        $post = ['world_id' => $worldId, 'expertise_id' => $value];

        $resultArray[] = $curl->post('world-expertise', $post);
    }

    checkError($resultArray);
}

function world_hasManifestation($postData, $worldId) {
    global $curl;

    $resultArray = null;
    $attributeArray = null;
    $expertiseArray = null;
    $focusArray = null;

    foreach($postData as $key => $id) {
        $manifestation = $curl->get('manifestation/id/'.$id)['data'][0];
        $attributeList = $curl->get('attribute/type/'.$manifestation['attributetype_id'])['data'];
        $expertiseList = $curl->get('expertise/type/'.$manifestation['expertisetype_id'])['data'];
        $focusList = $curl->get('focus')['data'];

        $post = ['world_id' => $worldId, 'manifestation_id' => $id];

        $resultArray[] = $curl->post('world-manifestation', $post);

        foreach($attributeList as $item) {
            $post = ['world_id' => $worldId, 'attribute_id' => $item['id'], 'default_value' => 0];

            $resultArray[] = $curl->post('world-attribute', $post);
        }

        foreach($expertiseList as $item) {
            $post = ['world_id' => $worldId, 'expertise_id' => $item['id']];

            $resultArray[] = $curl->post('world-expertise', $post);
        }

        foreach($focusList as $item) {
            $post = ['world_id' => $worldId, 'focus_id' => $item['id']];

            $resultArray[] = $curl->post('world-focus', $post);
        }
    }

    checkError($resultArray);
}

function world_woundAdd($postData) {
    global $curl;

    $resultArray = null;

    $post = ['name' => $postData['name'], 'popularity' => 0, 'hidden' => 1];

    $resultArray[] = $curl->post('wound', $post);

    checkError($resultArray);

    return $resultArray[0]['id'];
}



function switch_manifestation($do) {
    global $POST_DATA, $POST_ID, $POST_HASH, $POST_USER;

    switch($do) {
        default: break;

        case 'manifestation--post':
            $result = manifestation_post($POST_DATA);
            $POST_ID = $result['id'];
            break;

        case 'manifestation--discipline':
            manifestation_discipline($POST_DATA, $POST_ID);
            break;

        case 'manifestation--focus':
            manifestation_focus($POST_DATA, $POST_ID);
            break;
    }
}

function switch_person($do) {
    global $POST_DATA, $POST_ID, $POST_HASH, $POST_USER, $POST_ERROR;

    switch($do) {
        default: break;

        case 'person--post':
            $result = person_postPerson($POST_DATA);

            if(!$POST_ERROR) {
                $POST_ID = $result['id'];
                $POST_HASH = $result['hash'];
                $resultNickname = $result['nickname'];

                if($POST_USER) {
                    user_savePerson($POST_USER, $POST_ID, $POST_HASH);
                }

                cookiePerson($resultNickname, $POST_ID, $POST_HASH);
            }
            break;

        case 'person--put':
            person_putPerson($POST_DATA, $POST_HASH);

            if(!$POST_ERROR) {
                if(isset($POST_DATA['background_id'])) {
                    person_putAttributeFromTable('background-attribute', $POST_DATA['background_id'], $POST_ID);
                    //todo person has asset from background
                }

                if(isset($POST_DATA['nature_id'])) {
                    person_putAttributeFromTable('nature', $POST_DATA['nature_id'], $POST_ID);
                }

                if(isset($POST_DATA['identity_id'])) {
                    person_putAttributeFromTable('identity', $POST_DATA['identity_id'], $POST_ID);
                }

                if(isset($POST_DATA['focus_id'])) {
                    person_putAttributeFromTable('focus', $POST_DATA['focus_id'], $POST_ID);
                }
            }
            break;

        case 'person--augmentation':
            person_postAugmentation($POST_DATA, $POST_ID);
            break;

        case 'person--bionic':
            person_postBionic($POST_DATA, $POST_ID);
            break;

        case 'person--edit--attribute':
            person_postAttribute($POST_DATA, $POST_ID, true);
            break;

        case 'person--edit--feature':
            if(isset($POST_DATA['species_id'])) {
                person_putPerson(['species_id' => $POST_DATA['species_id']], $POST_HASH);
                person_putAttributeFromTable('species-attribute', $POST_DATA['species_id'], $POST_ID, $POST_DATA['current_id']);
            }

            if(isset($POST_DATA['background_id'])) {
                person_putPerson(['background_id' => $POST_DATA['background_id']], $POST_HASH);
                person_putAttributeFromTable('background-attribute', $POST_DATA['background_id'], $POST_ID, $POST_DATA['current_id']);
            }

            if(isset($POST_DATA['nature_id'])) {
                person_putPerson(['nature_id' => $POST_DATA['nature_id']], $POST_HASH);
                person_putAttributeFromTable('nature', $POST_DATA['nature_id'], $POST_ID, $POST_DATA['current_id']);
            }

            if(isset($POST_DATA['identity_id'])) {
                person_putPerson(['identity_id' => $POST_DATA['identity_id']], $POST_HASH);
                person_putAttributeFromTable('identity', $POST_DATA['identity_id'], $POST_ID, $POST_DATA['current_id']);
            }

            if(isset($POST_DATA['focus_id'])) {
                person_putPerson(['focus_id' => $POST_DATA['focus_id']], $POST_HASH);
                person_putAttributeFromTable('focus', $POST_DATA['focus_id'], $POST_ID, $POST_DATA['current_id']);
            }
            break;

        case 'person--equip':
            $post = ['equipped' => $POST_DATA['value']];
            person_putTable($POST_DATA['table'], $POST_ID, $POST_DATA['id'], $post);
            break;

        case 'person--expertise':
            person_postExpertise($POST_DATA, $POST_ID);

            if(!$POST_ERROR) {
                person_putPerson(['point_expertise' => 0], $POST_HASH);

                if(isset($_POST['post--experience'])) {
                    $key = 'attribute_id__'.$_POST['post--experience'];
                    $value = intval($_POST['post--points']);

                    person_postAttribute([$key => $value], $POST_ID, true);
                }
            }
            break;

        case 'person--milestone':
            $post = ['person_id' => $POST_ID, 'milestone_id' => $POST_DATA['milestone']];
            person_postTo('milestone', $post);

            if(!$POST_ERROR) {
                person_putAttributeFromTable('milestone', $POST_DATA['milestone'], $POST_ID);

                $calc = intval($_POST['post--points']) - 1;
                person_putPerson(['point_milestone' => $calc], $POST_HASH);
            }
            break;

        case 'person--gift':
            $post = ['person_id' => $POST_ID, 'characteristic_id' => $POST_DATA['characteristic']];
            person_postTo('characteristic', $post);

            if(!$POST_ERROR) {
                person_putAttributeFromTable('characteristic', $POST_DATA['characteristic'], $POST_ID);

                if(!$POST_ERROR) {
                    $calc = intval($_POST['post--points']) - 1;
                    person_putPerson(['point_characteristic_gift' => $calc], $POST_HASH);
                }
            }
            break;

        case 'person--imperfection':
            $post = ['person_id' => $POST_ID, 'characteristic_id' => $POST_DATA['characteristic']];
            person_postTo('characteristic', $post);

            if(!$POST_ERROR) {
                person_putAttributeFromTable('characteristic', $POST_DATA['characteristic'], $POST_ID);

                if(!$POST_ERROR) {
                    $calc = intval($_POST['post--points']) - 1;
                    person_putPerson(['point_characteristic_imperfection' => $calc], $POST_HASH);
                }
            }
            break;

        case 'person--money':
            person_putAttribute($POST_DATA, $POST_ID, true);

            if(!$POST_ERROR) {
                person_putPerson(['point_money' => 0], $POST_HASH);
            }
            break;

        case 'person--protection':
            person_postProtection($POST_DATA, $POST_ID);
            break;

        case 'person--skill':
            person_postAttribute($POST_DATA, $POST_ID);

            if(!$POST_ERROR) {
                person_putPerson(['point_skill' => 0], $POST_HASH);

                if(!$POST_ERROR) {
                    if(isset($_POST['post--experience'])) {
                        $key = 'attribute_id__'.$_POST['post--experience'];
                        $value = intval($_POST['post--points']);

                        person_postAttribute([$key => $value], $POST_ID, true);
                    }
                }
            }
            break;

        case 'person--supernatural':
            person_postAttribute($POST_DATA, $POST_ID);

            if(!$POST_ERROR) {
                person_putPerson(['point_supernatural' => 0], $POST_HASH);

                if(!$POST_ERROR) {
                    if(isset($_POST['post--experience'])) {
                        $key = 'attribute_id__'.$_POST['post--experience'];
                        $value = intval($_POST['post--points']);

                        person_postAttribute([$key => $value], $POST_ID, true);
                    }
                }
            }
            break;

        case 'person--supernatural--power':
            person_putAttribute($POST_DATA, $POST_ID, true);

            if(!$POST_ERROR) {
                person_putPerson(['point_power' => 0], $POST_HASH);
            }
            break;

        case 'person--supernatural--expertise':
            $post = ['expertise_id__'.$POST_DATA['expertise_id'] => 1];
            person_postExpertise($post, $POST_ID);
            break;

        case 'person--weapon':
            person_postWeapon($POST_DATA, $POST_ID);

            if(!$POST_ERROR) {
                person_putPerson(['calculated' => 1], $POST_HASH);
            }
            break;

        case 'person--remove--thing':
            person_removeFrom($POST_DATA['table'], $POST_DATA['id'], $POST_ID);
            break;

        case 'person--wound--add':
            $returnId = world_woundAdd($POST_DATA);
            person_woundAdd($POST_DATA, $POST_ID, $returnId);
            break;

        case 'person--wound--aid':
            $post = ['aid' => $POST_DATA['value']];
            person_putTable('wound', $POST_ID, $POST_DATA['id'], $post);
            break;

        case 'person--wound--heal':
            $post = ['heal' => $POST_DATA['value']];
            person_putTable('wound', $POST_ID, $POST_DATA['id'], $post);
            break;
    }
}

function switch_user($do) {
    global $POST_DATA, $POST_ID, $POST_HASH, $POST_USER;

    switch($do) {
        default: break;

        case 'user--new':
            user_postUser($POST_DATA);
            break;

        case 'user--new--verify':
            user_verifyUser($POST_DATA);
            break;

        case 'user--new--timeout':
            user_resendVerification($POST_DATA);
            break;

        case 'user--login--pass':
            user_loginPass($POST_DATA);
            break;

        case 'user--login--mail':
            user_loginMail($POST_DATA);
            break;

        case 'user--login--verify':
            user_loginMailVerify($POST_DATA);
            break;

        case 'user--reset':
            user_passMail($POST_DATA);
            break;

        case 'user--password':
            user_passVerify($POST_DATA);
            break;

        case 'user--logout':
            unsetUser();
            break;

        case 'user--save':
            user_savePerson($POST_USER, $POST_ID, $POST_HASH);
            break;
    }
}

function switch_world($do) {
    global $POST_DATA, $POST_ID, $POST_HASH, $POST_USER;

    switch($do) {
        default: break;

        case 'world--post':
            $result = world_postWorld($POST_DATA);

            $POST_ID = $result['id'];
            $POST_HASH = $result['hash'];
            $resultName = $result['name'];

            if($POST_USER) {
                user_saveWorld($POST_USER, $POST_ID, $POST_HASH); // todo save world to user
            }
            break;

        case 'world--put':
            world_putWorld($POST_DATA, $POST_HASH);
            break;

        case 'world--has':
            world_has($POST_DATA, $POST_ID, $_POST['post--thing']);
            break;

        case 'world--default':
            world_hasDefault($POST_DATA, $POST_ID);
            break;

        case 'world--skill':
            world_hasSkill($POST_DATA, $POST_ID);
            break;

        case 'world--expertise':
            world_hasExpertise($POST_DATA, $POST_ID);
            break;

        case 'world--manifestation':
            world_hasManifestation($POST_DATA, $POST_ID);
            break;

        case 'world--augmentation':
            world_has($POST_DATA, $POST_ID, 'augmentation');
            world_putWorld(['calculated' => 1], $POST_HASH);
            break;
    }
}


if(isset($POST_DO) && isset($POST_RETURN)) {
    $POST_DATA = [];

    unset($_POST['x']);
    unset($_POST['y']);

    foreach($_POST as $key => $value) {
        $explode = explode('--', $key);

        if($explode[0] != 'post') {
            $POST_DATA[$explode[1]] = $value;
        }
    }

    $topSwitch = explode('--', $POST_DO)[0];

    switch($topSwitch)
    {
        default: break;

        case 'manifestation':
            switch_manifestation($POST_DO);
            break;

        case 'person':
            switch_person($POST_DO);
            break;

        case 'user':
            switch_user($POST_DO);
            break;

        case 'world':
            switch_world($POST_DO);
            break;
    }
}

$r = isset($POST_RETURN)
    ? '/'.$POST_RETURN
    : null;

$i = isset($POST_ID)
    ? '/'.$POST_ID
    : null;

$h = isset($POST_HASH)
    ? '/'.$POST_HASH
    : null;

$d = isset($POST_RETURNID)
    ? '#'.$POST_RETURNID
    : '#content';

$a = isset($POST_RETURNAFTER)
    ? '/'.$POST_RETURNAFTER
    : null;

if(!$POST_ERROR) {
    redirect($baseUrl.$r.$i.$h.$a.$d);
} else {
    print_r($POST_ERROR);
}

echo '<a href="'.$baseUrl.$r.$i.$h.$a.$d.'">'.$baseUrl.$r.$i.$h.$a.$d.'</a>';