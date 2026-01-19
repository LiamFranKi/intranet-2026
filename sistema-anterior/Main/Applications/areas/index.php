<?php
class AreasApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
		]);
	}
	
	function index($r){
		$areas = Area::all();
		$this->render(array('areas' => $areas));
	}
	
	function save(){
		$r = -5;
		$this->area->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'nombre' => $this->post->nombre,
			'nivel_id' => $this->post->nivel_id,
		));
		
		if($this->area->is_valid()){
			$r = $this->area->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->area->id, 'errors' => $this->area->errors->get_all()));
	}

	function borrar($r){
		$r = $this->area->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	function save_cursos(){
        Area_Curso::table()->delete(array('area_id' => $this->area->id));
        if(isset($this->post->curso)){
            foreach($this->post->curso As $curso_id){
                Area_Curso::create(array(
                    'area_id' => $this->area->id,
                    'curso_id' => $curso_id
                ));
            }
        }
        echo json_encode(array(1));
    }
	
	function __getObjectAndForm(){
		$this->set('__active', 'areas', true);
		$this->area = !empty($this->params->id) ? Area::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Area();
		$this->context('area', $this->area); // set to template
		if(in_array($this->params->Action, array('form', 'cursos'))){
			$this->form = $this->__getForm($this->area);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'colegio_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'nombre' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'nivel_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__dataset' => true,
				'__options' => array($this->COLEGIO->niveles, 'id', '$object->nombre'),
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
