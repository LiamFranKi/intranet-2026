<?php
class {{Name|capitalize}}Application extends Core\Application{
	
	public $beforeFilter = array('__checkSession', '__getObjectAndForm');

	function __checkSession(){
		$this->security('SecureSession', [
			'ADMINISTRADOR' => '*'
		]);
	}
	
	function index($r){
		${{Name}} = {{Model}}::all();
		$this->render(array('{{Name}}' => ${{Name}}));
	}
	
	function save(){
		$r = -5;
		$this->{{ Model|lower }}->set_attributes(array(
		{% for column in columns %}{% if _key != "id" %}	'{{ _key }}' => $this->post->{{ _key }},
		{% endif %}{% endfor %}
));
		
		if($this->{{ Model|lower }}->is_valid()){
			$r = $this->{{ Model|lower }}->save() ? 1 : 0;
		}
		echo json_encode(array($r, 'id' => $this->{{ Model|lower }}->id, 'errors' => $this->{{ Model|lower }}->errors->get_all()));
	}

	function borrar($r){
		$r = $this->{{ Model|lower }}->delete() ? 1 : 0;
		echo json_encode(array($r));
	}
	
	function __getObjectAndForm(){
		$this->set('__active', '{{Name}}', true);
		$this->{{ Model|lower }} = !empty($this->params->id) ? {{Model}}::find(['conditions' => 'sha1(id) = "'.$this->params->id.'"']) : new {{Model}}();
		$this->context('{{ Model|lower }}', $this->{{ Model|lower }}); // set to template
		if(in_array($this->params->Action, array('form'))){
			$this->form = $this->__getForm($this->{{ Model|lower }});
			$this->context('form', $this->form);
		}
	}
	
	private function __getForm($object){
		$this->crystal->load('Form:*');
		
		$options = array(
			'id' => array('type' => 'hidden', 'value' => $object->is_new_record() ? '' : sha1($object->id) ),
			{% for column in columns %}{% if _key != 'id' %}'{{ _key }}' => array(
				'class' => 'form-control',
				'data-bv-notempty' => 'true'
			),
			{% endif %}{% endfor %}

		);
		
		$form = new Form($object, $options);
		return $form;
	}
	
}
