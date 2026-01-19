<?php
class Avatar_shop_itemsApplication extends Core\Application{

	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*'
		]);
	}

	function index($r){
		$avatarShopItems = AvatarShopItem::all();
		$this->render(array('avatarShopItems' => $avatarShopItems));
	}

	function save(){
		$r = -5;
		$this->avatarShopItem->set_attributes(array(
			'name' => $this->post->name,
            'description' => $this->post->description,
            'level' => $this->post->level,
            'price' => $this->post->price,
		));

        $image = uploadFile('image', ['jpg', 'jpeg', 'png'], './Static/Image/Avatars');
        if(!is_null($image)){
			$this->avatarShopItem->image = $image['new_name'];
		}

		if($this->avatarShopItem->is_valid()){
			$r = $this->avatarShopItem->save() ? 1 : 0;
		}
		echo json_encode(array('status' => $r, 'id' => $this->avatarShopItem->id, 'errors' => $this->avatarShopItem->errors->get_all()));
	}

	function borrar($r){
		$r = $this->avatarShopItem->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	function __getObjectAndForm(){
		$this->set('__active', 'avatarShopItems', true);
		$this->avatarShopItem = !empty($this->params->id) ? AvatarShopItem::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new AvatarShopItem();
		$this->context('avatarShopItem', $this->avatarShopItem); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->avatarShopItem);
			$this->context('form', $this->form);
		}
	}

	private function __getForm($object){
		$this->crystal->load('Form:*');

		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'name' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
            'description' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
            'level' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
            'price' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
            'image' => array(
				'class' => 'form-control',
                'type' => 'file'
			),
		);

		$form = new Form($object, $options);
		return $form;
	}

}
