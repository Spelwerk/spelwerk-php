<?php class Skill {
    var $id, $canon, $popularity, $name, $description, $icon;

    var $species;

    var $value;

    public function __construct($id = null, $array = null) {
        global $curl, $system;

        $data = isset($id)
            ? $curl->get('skill/id/'.$id)['data'][0]
            : $array;

        $this->id = $data['id'];
        $this->canon = $data['canon'];
        $this->popularity = $data['popularity'];
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->icon = $data['icon'];

        $this->maximum = $system->defaultSkill['maximum'];
        $this->required = $system->defaultSkill['required'];
        $this->increment = $system->defaultSkill['increment'];

        $this->species = $data['species_id'];

        $this->value = isset($data['value']) ? intval($data['value']) : 0;

        $this->dice = $system->defaultDice['amount'];
        $this->diceText = $this->value > 0
            ? $this->dice.'d'.$system->defaultDice['value'].'+'.$this->value
            : $this->dice.'d'.$system->defaultDice['value'];
        $this->diceData = 'data-roll-type="skill" 
                           data-roll-dice="'.$this->dice.'" 
                           data-roll-bonus="'.$this->value.'"';

        $this->siteLink = '/content/skill/'.$this->id;
    }

    public function verifyOwner() {
        global $system;

        return $system->verifyOwner('skill', $this->id);
    }

    public function put() {
        if($this->verifyOwner()) {
            global $component, $form;

            $form->form([
                'do' => 'put',
                'return' => 'content/skill',
                'context' => 'skill',
                'id' => $this->id
            ]);
            $component->wrapStart();
            $form->varchar(true,'name','Name',null,null,$this->name);
            $form->text(false,'description','Description',null,null,$this->description);
            $form->icon();
            $component->wrapEnd();
            $form->submit();
        }
    }

    public function view() {
        global $component;

        $component->returnButton('/content/skill');

        $component->roundImage($this->icon);
        $component->h1('Description');
        $component->p($this->description);
        $component->h1('Data');
        $component->p('Species ID: '.$this->species); //todo api return name

        if($this->verifyOwner()) {
            $component->h1('Manage');
            $component->linkButton($this->siteLink.'/edit','Edit');
            //todo link to delete();
        }
    }

    public function delete() {} //todo
}