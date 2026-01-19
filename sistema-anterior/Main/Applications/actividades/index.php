<?php
class ActividadesApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
            'DOCENTE' => '*',
			'USUARIO_ID' => 'index, actividades_json, detalles'
		]);
	}
	
	function index($r){
		$actividades = Actividad::all();
		$this->render(array('actividades' => $actividades));
	}
	
	function save(){
		$r = -5;
		$this->actividad->set_attributes(array(
			'colegio_id' => $this->COLEGIO->id,
			'descripcion' => $this->post->descripcion,
			'lugar' => $this->post->lugar,
			'detalles' => $this->post->detalles,
			'fecha_inicio' => $this->post->fecha_inicio,
			'fecha_fin' => $this->post->fecha_fin,
			'usuario_id' => $this->USUARIO->id,
		));
		
		if($this->actividad->is_valid()){
			$r = $this->actividad->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->actividad->id, 'errors' => $this->actividad->errors->get_all()));
	}

	function borrar($r){
		$r = $this->actividad->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', 'actividades', true);
		$this->actividad = !empty($this->params->id) ? Actividad::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Actividad();
		$this->context('actividad', $this->actividad); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->actividad);
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
				'data-bv-notempty' => 'true'
			),
			'lugar' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'detalles' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'fecha_inicio' => array(
				'class' => 'form-control calendar',
				'data-bv-notempty' => 'true',
				'__default' => date('Y-m-d'),
                'type' => 'date'
			),
			'fecha_fin' => array(
				'class' => 'form-control calendar',
				'data-bv-notempty' => 'true',
				'__default' => date('Y-m-d'),
                'type' => 'date'
			),
			'usuario_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);

		if(!$object->is_new_record()){
			$options['fecha_inicio']['value'] = date('Y-m-d', strtotime($object->fecha_inicio));
			$options['fecha_fin']['value'] = date('Y-m-d', strtotime($object->fecha_fin));
		}
		
		$form = new Form($object, $options);
		return $form;
	}

	function actividades_json(){
		$actividades = Actividad::all([
			'conditions' => 'DATE(fecha_inicio) BETWEEN DATE("'.$this->get->start.'") AND DATE("'.$this->get->end.'")'
		]);

		$data = [];
		foreach($actividades As $actividad){
			$data[] = array(
                'id' => sha1($actividad->id),
                'title' => $actividad->descripcion,
                'start' => date('Y-m-d', strtotime($actividad->fecha_inicio)),
                'end' => $actividad->fecha_inicio == $actividad->fecha_fin ? date('Y-m-d', strtotime($actividad->fecha_fin)) : date('Y-m-d', strtotime($actividad->fecha_fin.' +1 day')),
                //'color' => $registro->categoria != -1 ? $colores[$registro->getCategorias()[0]] : ''
                
            );
		}

		echo json_encode($data);
	}
	
}
