<?php

/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 2017-04-25
 * Time: 21:20
 */

class Imperfection {
    var $id, $canon, $name, $description, $icon;

    var $species;

    var $manifestation;

    public function __construct($id = null, $array = null) {
        global $curl;

        $data = isset($id)
            ? $curl->get('imperfection/id/'.$id)['data'][0]
            : $array;

        $this->id = $data['id'];
        $this->canon = $data['canon'];
        $this->name = $data['name'];
        $this->description = isset($data['custom'])
            ? $data['custom']
            : $data['description'];

        $this->icon = '/img/color/imperfection.png';

        $this->species = $data['species_id'];

        $this->manifestation = $data['manifestation_id'];
    }
}