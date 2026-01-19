<?php
class Avatar_shop_salesApplication extends Core\Application{

	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'ALUMNO' => 'shop, save'
		]);
	}

    function shop(){
        $items = AvatarShopItem::all([
            'order' => 'level asc, name asc'
        ]);

        $balance = $this->USUARIO->is('ALUMNO') ? $this->USUARIO->alumno->getStarsAmount() : 0;

        $redeemed = AvatarShopSale::all(['conditions' => ['student_id = ?', $this->USUARIO->alumno_id]]);
        $redeemed = array_map(function($item){return $item->item_id;}, $redeemed);
        //print_r($redeemed);
        $this->render(['items' => $items, 'balance' => $balance, 'redeemed' => $redeemed]);
    }

	function index($r){
		$avatarShopSales = AvatarShopSale::all();
		$this->render(array('avatarShopSales' => $avatarShopSales));
	}

	function save(){
		$r = -5;
        $item = AvatarShopItem::find(['conditions' => 'sha1(id) = "'.$this->params->item_id.'"']);
        $balance = $this->USUARIO->is('ALUMNO') ? $this->USUARIO->alumno->getStarsAmount() : 0;

        if($balance < $item->price){
            echo json_encode(['status' => -1]);
            return false;
        }


		$this->avatarShopSale->set_attributes(array(
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
			'item_id' => $item->id,
            'student_id' => $this->USUARIO->alumno_id
		));

		if($this->avatarShopSale->is_valid()){
			$r = $this->avatarShopSale->save() ? 1 : 0;
		}
		echo json_encode(array('status' => $r, 'id' => $this->avatarShopSale->id, 'errors' => $this->avatarShopSale->errors->get_all()));
	}

	function borrar($r){
		$r = $this->avatarShopSale->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	function __getObjectAndForm(){
		$this->set('__active', 'avatarShopSales', true);
		$this->avatarShopSale = !empty($this->params->id) ? AvatarShopSale::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new AvatarShopSale();
		$this->context('avatarShopSale', $this->avatarShopSale); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->avatarShopSale);
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
		);

		$form = new Form($object, $options);
		return $form;
	}

}
