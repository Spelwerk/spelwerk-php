<?php class Protection {
    var $id, $canon, $popularity, $name, $description, $price, $icon;

    var $bodypart;

    var $equipped;

    var $quality, $bonus;

    public function __construct($id = null, $array = null) {
        global $curl;

        $data = isset($id)
            ? $curl->get('protection/id/'.$id)['data'][0]
            : $array;

        $this->id = $data['id'];
        $this->canon = $data['canon'];
        $this->popularity = $data['popularity'];
        $this->name = $data['name'];
        $this->icon =  $data['icon'];
        $this->description = isset($data['custom'])
            ? $data['custom']
            : $data['description'];

        $this->price = intval($data['price']);
        $this->bodypart = $data['bodypart_id'];

        $this->equipped = isset($data['equipped'])
            ? $data['equipped']
            : false;

        if(isset($data['quality_id'])) {
            $this->quality = $data['quality_id'];
            $this->price += intval($data['quality_price']);
        }

        $this->siteLink = '/content/protection/'.$this->id;
    }

    public function verifyOwner() {
        global $system;

        return $system->verifyOwner('protection', $this->id);
    }

    public function put() {} //todo

    public function view() {
        global $component;

        $component->returnButton('/content/protection');

        $component->roundImage($this->icon);
        $component->h1('Description');
        $component->p($this->description);
        $component->h1('Attribute');
        $this->listAttribute();

        if($this->verifyOwner()) {
            $component->h1('Manage');
            $component->linkButton($this->siteLink.'/edit','Edit');
            $component->linkButton($this->siteLink.'/attribute/add','Add Attribute');
            $component->linkButton($this->siteLink.'/attribute/delete','Delete Attribute',true);
        }
    }

    public function delete() {} //todo

    // GET

    public function getAttribute($override = null) {
        global $system;

        $get = isset($override)
            ? 'protection/id/'.$this->id.'/attribute'.$override
            : 'protection/id/'.$this->id.'/attribute';

        return $system->getAttribute($get);
    }

    // POST

    public function postAttribute() {
        if(!$this->verifyOwner()) exit;

        global $component, $form, $curl;

        $form->form([
            'do' => 'context--post',
            'context' => 'protection',
            'id' => $this->id,
            'context2' => 'attribute',
            'return' => 'content/protection'
        ]);

        $attributeType = $curl->get('system/attribute')['type']['protection'];
        $attributeList = $curl->get('attribute/type/'.$attributeType)['data'];

        $component->wrapStart();
        $form->select(true,'insert_id',$attributeList,'Attribute','Which Damage Type do you wish your gear to protect against?');
        $form->number(true,'value','Value',null,null,1,4,1);
        $component->wrapEnd();

        $form->submit();
    }

    // DELETE

    public function deleteAttribute() {
        if(!$this->verifyOwner()) exit;

        global $system;

        $system->contentSelectList('protection','attribute','delete',$this->id,$this->getAttribute());
    }

    // LIST

    public function listAttribute() {
        global $component;

        $list = $this->getAttribute();

        if($list[0]) {
            foreach($list as $item) {
                $component->listItem($item->name.' ('.$item->value.')', $item->description, $item->icon);
            }
        }
    }
}