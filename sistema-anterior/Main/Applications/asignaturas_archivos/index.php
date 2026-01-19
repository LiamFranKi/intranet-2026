<?php
class Asignaturas_archivosApplication extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'SECRETARIA' => '*',
			'DOCENTE' => '*'
		]);
	}
	
	function index($r){
		$asignaturas_archivos = Asignatura_Archivo::all();
		$this->render(array('asignaturas_archivos' => $asignaturas_archivos));
	}
	
	function save(){
		$r = -5;
		$this->asignatura_archivo->set_attributes(array(
			'asignatura_id' => $this->post->asignatura_id,
			'trabajador_id' => $this->USUARIO->personal_id,
			'nombre' => $this->post->nombre,
			'ciclo' => $this->post->ciclo,
            'enlace' => $this->post->enlace
		));

		$archivo = uploadFile('archivo', ['pdf', 'doc', 'docx'], './Static/Archivos');
		
		if($this->asignatura_archivo->is_new_record()){
			$this->asignatura_archivo->fecha_hora = date('Y-m-d H:i:s');
			$this->asignatura_archivo->orden = time();
		}

		if(!is_null($archivo)){
			$this->asignatura_archivo->archivo = $archivo['new_name'];
		}

		if($this->asignatura_archivo->is_valid()){
			$r = $this->asignatura_archivo->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->asignatura_archivo->id, 'errors' => $this->asignatura_archivo->errors->get_all()));
	}

	function borrar($r){
		$r = $this->asignatura_archivo->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

	function update_orden(){
        foreach($this->post->data As $orden => $archivo_id){
            Asignatura_Archivo::table()->update(array(
                'orden' => $orden
            ), array(
                'id' => $archivo_id
            ));
        }
    }
	
	function __getObjectAndForm(){
		$this->set('__active', 'asignaturas_archivos', true);
		$this->asignatura_archivo = !empty($this->params->id) ? Asignatura_Archivo::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new Asignatura_Archivo();
		$this->context('asignatura_archivo', $this->asignatura_archivo); // set to template
		if(!empty($this->params->asignatura_id)){
			$this->asignatura = Asignatura::find([
				'conditions' => ['sha1(id) = ?', $this->params->asignatura_id]
			]);
			$this->context('asignatura', $this->asignatura);
		}

		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->asignatura_archivo);
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			'asignatura_id' => array(
				'type' => 'hidden',
				'__default' => $this->asignatura->id
			),
			'trabajador_id' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'nombre' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'archivo' => array(
				'class' => 'form-control',
				'type' => 'file'
			),
			'visto' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'fecha_hora' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			'ciclo' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
				'type' => 'select',
				'__options' => array_combine(range(1, $this->COLEGIO->total_notas), range(1, $this->COLEGIO->total_notas)),
			),
			'enlace' => array(
				'class' => 'form-control',
			),
			'orden' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			
		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
