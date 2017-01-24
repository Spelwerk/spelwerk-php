<?php

/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 2016-12-03
 * Time: 15:09
 */
class Caste {

    var $id, $name, $description;

    var $attribute;

    var $icon;

    public function __construct($array) {

        $this->id = $array['id'];

        $this->name = $array['name'];

        $this->description = $array['description'];

        $this->attribute = [
            'id' => $array['attribute_id'],
            'name' => $array['attribute_name'],
            'value' => $array['attribute_value']
        ];

        $this->icon = $array['icon_path'];

    }

}