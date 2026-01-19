<?php
class Enrollment_incidentsApplication extends Core\Application{

	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*',
            'DOCENTE' => '*',
		]);
	}

	function index($r){
		$enrollmentIncidents = EnrollmentIncident::all([
            'conditions' => ['enrollment_id = ? AND assignment_id = ? AND type = 1', $this->enrollment->id, $this->assignment->id]
        ]); 
		$this->render(array('enrollmentIncidents' => $enrollmentIncidents));
	}

	function save(){
		$r = -5;
		$this->enrollmentIncident->set_attributes(array(
            'description' => $this->post->description,
            'enrollment_id' => $this->post->enrollment_id,
            'assignment_id' => $this->post->assignment_id,
            'worker_id' => $this->USUARIO->personal_id,
            'type' => $this->post->type,
            'points' => $this->post->points
		));

		if($this->enrollmentIncident->is_valid()){
			$r = $this->enrollmentIncident->save() ? 1 : 0;
		}
		echo json_encode(array('status' => $r, 'id' => $this->enrollmentIncident->id, 'errors' => $this->enrollmentIncident->errors->get_all()));
	}

	function borrar($r){
		$r = $this->enrollmentIncident->delete() ? 1 : 0;
		echo json_encode(array($r));
	}

    function summary(){
        $incidents = EnrollmentIncident::all([
            'conditions' => ['type = 1 AND SHA1(enrollment_id) = ?', $this->params->enrollment_id]
        ]);

        $stars = EnrollmentIncident::all([
            'conditions' => ['type = 2 AND SHA1(enrollment_id) = ?', $this->params->enrollment_id]
        ]);

        $attendances = Matricula_Asistencia::all([
            'conditions' => ['SHA1(matricula_id) = ?', $this->params->enrollment_id]
        ]);

        $this->render(['incidents' => $incidents, 'stars' => $stars, 'attendances' => $attendances]);
    }

	function __getObjectAndForm(){
		$this->set('__active', 'enrollmentIncidents', true);
		$this->enrollmentIncident = !empty($this->params->id) ? EnrollmentIncident::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new EnrollmentIncident();
		$this->context('enrollmentIncident', $this->enrollmentIncident); // set to template

        if(!empty($this->params->enrollment_id)){
            $this->enrollment = Matricula::find(['conditions' => 'sha1(id) = "'.$this->params->enrollment_id.'"']);
            $this->enrollmentIncident->enrollment_id = $this->enrollment->id;
        }

        if(!empty($this->params->assignment_id)){
            $this->assignment = Asignatura::find(['conditions' => 'sha1(id) = "'.$this->params->assignment_id.'"']);
            $this->enrollmentIncident->assignment_id = $this->assignment->id;
        }

		if(in_array($this->params->Action, array('form', 'form2'))){
			$this->form = $this->__getForm($this->enrollmentIncident);
			$this->context('form', $this->form);
		}
	}

	private function __getForm($object){
		$this->crystal->load('Form:*');

		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
            'enrollment_id' => [
                'type' => 'hidden'
            ],
            'assignment_id' => [
                'type' => 'hidden'
            ],
            'points' => [
                'type' => 'number',
                'class' => 'form-control',
                'data-bv-notempty' => 'true'
            ],
			'description' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true',
                'type' => 'textarea',
                'style' => 'height: 100px;',
			),
		);

		$form = new Form($object, $options);
		return $form;
	}

}
