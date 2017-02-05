<?php

/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 2016-12-03
 * Time: 15:09
 */
class Caste {

    var $id, $name, $description, $icon;

    var $attribute;

    public function __construct($id = null, $array = null) {
        global $curl;

        $data = isset($id)
            ? $curl->get('caste/id/'.$id)['data'][0]
            : $array;

        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->icon = $data['icon_path'];

        $this->attribute = [
            'id' => $data['attribute_id'],
            'name' => $data['attribute_name'],
            'value' => $data['attribute_value']
        ];
    }
}