<?php class Sanity {
    var $id, $canon, $popularity, $name, $icon;

    var $heal, $lethal;

    public function __construct($id = null, $array = null) {
        global $curl, $system;

        $data = isset($id)
            ? $curl->get('sanity/id/'.$id)['data'][0]
            : $array;

        $this->id = $data['id'];
        $this->canon = $data['canon'];
        $this->popularity = $data['popularity'];
        $this->name = $data['name'];
        $this->icon = $system->defaultIcon['sanity']['icon'];

        $this->heal = isset($data['heal'])
            ? $data['heal']
            : null;

        $this->healIcon = $system->defaultIcon['sanity']['heal'];

        $this->double = isset($data['timestwo'])
            ? $data['timestwo']
            : null;

        $this->value = $this->double
            ? 2
            : 1;
    }

    public function put() {} //todo

    public function view() {} //todo

    public function delete() {} //todo
}