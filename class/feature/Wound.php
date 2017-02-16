<?php

/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 15/02/2017
 * Time: 11:16
 */

class Wound
{
    var $id, $name, $popularity, $hidden, $aid, $heal, $lethal, $icon;

    public function __construct($id = null, $array = null) {
        global $curl;

        $data = isset($id)
            ? $curl->get('wound/id/'.$id)['data'][0]
            : $array;

        $this->id = $data['id'];
        $this->name = $data['name'];

        $this->popularity = isset($data['popularity'])
            ? $data['popularity']
            : null;

        $this->hidden = isset($data['hidden'])
            ? $data['hidden']
            : null;

        $this->aid = isset($data['aid'])
            ? $data['aid']
            : null;

        $this->heal = isset($data['heal'])
            ? $data['heal']
            : null;

        $this->lethal = isset($data['lethal'])
            ? $data['lethal']
            : null;

        $this->icon = $this->lethal == 1
            ? '/img/wound-lethal.png'
            : '/img/wound-serious.png';
    }
}