<?php
class Asignaturas_criteriosApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'DOCENTE' => '*'
		]);
	}
	
	function index($r){
		$asignaturas_criterios = $this->asignatura->getCriterios();
		$this->render(array('asignaturas_criterios' => $asignaturas_criterios));
	}
	
	function save(){
		$r = -5;
		$this->asignatura_criterio->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'descripcion' => $this->post->descripcion,
            'asignatura_id' => $this->post->asignatura_id,
            'ciclo' => $this->post->ciclo,
            'abreviatura' => $this->post->abreviatura,
            'peso' => $this->post->peso,
		));
		
		if($this->asignatura_criterio->is_new_record()){
			$this->asignatura_criterio->orden = time();
		}

		if($this->asignatura_criterio->is_valid()){
			$r = $this->asignatura_criterio->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->asignatura_criterio->id, 'errors' => $this->asignatura_criterio->errors->get_all()));
	}

	function borrar($r){
		$r = $this->asignatura_criterio->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	function update_orden_criterios(){
        foreach($this->post->data As $orden => $criterio_id){
            Asignatura_Criterio::table()->update(array(
                'orden' => $orden
            ), array(
                'id' => $criterio_id
            ));
        }
    }

    function cargar_criterios(){
       
        $this->asignatura->loadCriterios();
        echo json_encode(array(1));
    }
	
	function __getObjectAndForm(){
		$this->set('__active', 'asignaturas_criterios', true);
		$this->asignatura_criterio = !empty($this->params->id) ? Asignatura_Criterio::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Asignatura_Criterio();
		$this->context('asignatura_criterio', $this->asignatura_criterio); // set to template
		if(!empty($this->params->asignatura_id)){
			$this->asignatura = Asignatura::find([
				'conditions' => ['sha1(id) = ?', $this->params->asignatura_id]
			]);
			$this->context('asignatura', $this->asignatura);
		}
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->asignatura_criterio);
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
			'descripcion' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'text'
			),
			'abreviatura' => array(
				'class' => 'form-control',
				
			),
			'asignatura_id' => array(
				'type' => 'hidden',
				'__default' => $this->asignatura->id
			),
			'ciclo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'orden' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'peso' => array(
				'class' => 'form-control',
				'data-bv-numeric' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
