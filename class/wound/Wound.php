<?php class Wound {
    var $id, $canon, $popularity, $name, $icon;

    var $heal, $lethal;

    public function __construct($id = null, $array = null) {
        global $curl;

        $data = isset($id)
            ? $curl->get('wound/id/'.$id)['data'][0]
            : $array;

        $this->id = $data['id'];
        $this->canon = $data['canon'];
        $this->popularity = $data['popularity'];
        $this->name = $data['name'];
        $this->icon = 'http://cdn.spelwerk.com/file/40a4cce284ae3b6fd26e9ad69997edb82bcdfd86.png';

        $this->heal = isset($data['heal'])
            ? $data['heal']
            : null;

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