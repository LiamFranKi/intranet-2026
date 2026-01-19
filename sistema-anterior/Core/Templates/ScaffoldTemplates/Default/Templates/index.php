<?php
/**
 * 	Resource {{ Name|capitalize }}
 * 	REST Application generated for the Admin Site
 * 	add the pattern
 * 
 * 	Resource::forApplication('{{ Name }}')
 * 
 *  in the url patterns
 */ 
 
class {{ Name|capitalize }}Application extends Core\Application{

	// GET /{{ Name }}
	function index($r){
		${{ Name }} = {{ Model }}::all();
		$this->render(Array('{{ Name }}' => ${{ Name }}));
	}
	
	// GET /{{ Name }}/add
	function add(){
		$form = $this->__getForm(new {{ Model }}());
		$this->render(array('form' => $form));
	}
	
	// POST /{{ Name }}/add
	function do_add(){
		${{ Model|lower }} = new {{ Model }}((array) $this->post);
		
		if(${{ Model|lower }}->is_valid()){
			$message = ${{ Model|lower }}->save() ? 'Data successfully added' : 'Can\'t add the data';
		}
		
		$form = $this->__getForm(${{ Model|lower }});
		$this->render('add', array('form' => $form, 'message' => $message));
	}
	
	// GET /{{ Name }}/edit/id
	function edit($r){
		${{ Model|lower }} = {{ Model }}::find($r->id);
		$form = $this->__getForm(${{ Model|lower }});
		$this->render(Array('form' => $form,'{{ Model|lower }}' => ${{ Model|lower }}));
	}
	
	// POST /{{ Name }}/edit
	function do_edit($r){
		
		${{ Model|lower }} = {{ Model }}::find($this->post->id);
		${{ Model|lower }}->set_attributes((array) $this->post);
		if(${{ Model|lower }}->is_valid()){
			$message = ${{ Model|lower }}->save() ? 'Data successfully modified' : 'Can\'t modify the data';
		}
		$form = $this->__getForm(${{ Model|lower }});
		$this->render('edit', Array('form' => $form,'{{ Model|lower }}' => ${{ Model|lower }}, 'message' => $message));
	}

	// GET /{{ Name }}/id
	function delete($r){
		${{ Model|lower }} = {{ Model }}::find($r->id);
		$message = ${{ Model|lower }}->delete() ? 'Data successfully deleted' : 'Can\'t delete the data';
		$this->render(Array('message' => $message));
	}
	
	function __getForm($object, $xfield_options = array()){
		$this->crystal->load('Form:*');
		
		//-- define here the form options
		$field_options = Array(
			'__exclude' => Array('id'),
			'id' => array('type' => 'hidden')
		);
		//--
		
		$field_options = array_merge($field_options, $xfield_options);
		
		$form = new Form($object, $field_options);
		return $form;
	}
	
}
