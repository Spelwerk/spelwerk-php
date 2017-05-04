<?php

/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 2016-12-03
 * Time: 15:15
 */

class Expertise {
    var $id, $canon, $name, $description, $icon;

    var $level, $bonus;

    var $required, $increment, $maximum;

    var $skill;

    var $species;

    var $manifestation;

    var $doctrine;

    public function __construct($id = null, $array = null, $skill = null) {
        global $curl;

        $data = isset($id)
            ? $curl->get('expertise/id/'.$id)['data'][0]
            : $array;

        $defaults = $curl->get('system/expertise');

        $this->id = $data['id'];
        $this->canon = $data['canon'];
        $this->name = $data['name'];
        $this->description = isset($data['custom'])
            ? $data['custom']
            : $data['description'];

        $this->icon = $data['icon'];

        $this->level = $data['level'];
        $this->bonus = $data['bonus'];

        $this->required = $defaults['required'];
        $this->increment = $defaults['increment'];
        $this->maximum = $defaults['maximum'];
        $this->start = isset($data['manifestation_id'])
            ? 0
            : 1;

        $this->skill = $data['skill_id'];
        $this->species = $data['species_id'];
        $this->manifestation = $data['manifestation_id'];
        $this->doctrine = $data['doctrine_id'];

        $this->dice = intval($this->start) + intval($this->level) - 1;
    }
}