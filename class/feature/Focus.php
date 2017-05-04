<?php

/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 2016-12-03
 * Time: 15:37
 */

class Focus {

    var $id, $canon, $name, $description, $icon;

    var $manifestation;

    var $attribute, $attributeValue;

    public function __construct($id = null, $array = null) {
        global $curl;

        $data = isset($id)
            ? $curl->get('focus/id/'.$id)['data'][0]
            : $array;

        $this->id = $data['id'];
        $this->canon = $data['canon'];
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->icon = $data['icon'];

        $this->manifestation = $data['manifestation_id'];

        $this->attribute = $data['attribute_id'];
        $this->attributeValue = $data['attribute_value'];
    }
}