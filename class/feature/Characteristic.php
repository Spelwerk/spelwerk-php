<?php

/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 2016-12-03
 * Time: 15:11
 */
class Characteristic {

    var $id, $name, $description, $gift, $icon;

    var $species, $manifestation, $attribute;

    public function __construct($id = null, $array = null) {
        global $curl;

        $data = isset($id)
            ? $curl->get('characteristic/id/'.$id)['data'][0]
            : $array;

        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->gift = $data['gift'];
        $this->icon = $data['icon_path'];

        if(isset($data['species_id'])) {
            $this->species = [
                'id' => $data['species_id'],
                'name' => $data['species_name']
            ];
        }

        if(isset($data['manifestation_id'])) {
            $this->manifestation = [
                'id' => $data['manifestation_id'],
                'name' => $data['manifestation_name']
            ];
        }

        $this->attribute = [
            'id' => $data['attribute_id'],
            'name' => $data['attribute_name'],
            'value' => $data['attribute_value']
        ];
    }
}