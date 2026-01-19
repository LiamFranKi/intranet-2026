<?php
class Form {
	private $object;

	// default control template -> optimized for bootstrap forms
	
	public $control_template = '<div class="form-group form-group-sm">
	    <label class="col-sm-4 control-label" for="">{LABEL}</label>
	    <div class="col-sm-6">{CONTROL}</div>
	</div>
	';

	
	private $fields = array();
	private $full_fields = array();
	private $options = array();
	private $crystal;
	
	function __construct($object, $options){
		/*if(!$object instanceOf ActiveRecord\Model){
			throw new Exception('Object must be an ActiveRecord Model');
		}*/
		$this->crystal = Core\InstanceHandler::getInstance('Core\\CrystalManager');
		$this->object = $object;
		$this->options = $options;
		// creamos los campos
		$this->createFields();
	
	}
	
	function getObject(){
		return $this->object;
	}
	
	function parseRawType($type = null){
		//!isset($_options['type']) ? $field_type : $_options['type']
		
		switch($type){
			case 'int':
				return 'number';
			break;
			case 'varchar':
				return 'text';
			break;
			
			case 'text':
				return 'textarea';
			break;
			
			case 'enum':
			case 'combobox':
				return 'select';
			break;
			
			default:
				return 'text';
			break;
		}
	}
	
	function addField($field_name, $field_type = null, $column = null){
		$prefix = $this->options['__prefix'];
		$sufix = $this->options['__sufix'];

		$_options = $this->options[$field_name];
		$label = !isset($_options['__label']) ? ucwords($field_name) : $_options['__label'];
		$_options['type'] = isset($_options['type']) ? $_options['type'] : $this->parseRawType($field_type);
		
		if(isset($_options['__prefix'])) $prefix = $_options['__prefix'];
		if(isset($_options['__sufix'])) $sufix = $_options['__sufix'];


		
		if(!isset($_options['name'])) $_options['name'] = $prefix.$field_name.$sufix;
		if(!isset($_options['id'])) $_options['id'] = $prefix.$field_name.$sufix;


		if(!isset($_options['value']) && isset($column)) $_options['value'] = !empty($this->object->$field_name) || is_numeric($this->object->$field_name) ? $this->object->$field_name : $_options['__default'];
		
		unset($_options['__label']);
		unset($_options['__prefix']);unset($_options['__default']);
		unset($prefix);
		
		// revisamos si el modelo tiene validaciones
		
		if(!is_null($this->object)){
			$model_name = get_class($this->object); // obtenemos el modelo
			$validations = isset($model_name::$validates_presence_of) ? $model_name::$validates_presence_of : Array();
			
			foreach($validations As $validation){
				if(isset($validation[0]) && $validation[0] == $field_name){
					$_options['class'] .= ' required ';
				}
			}
		}
		

		
		switch($_options['type']){
			case 'text':
			case 'hidden':
				$field = new Input($_options);
			break;
			
			case 'textarea':
				$_options['__node'] = $_options['value'];
				unset($_options['value']);
				$field = new TextArea($_options);
			break;
			
			case 'select':
				$this->crystal->load('Form:Select');
				$this->crystal->load('Form:Option');
				
				$options = $this->getSelectOptions($field_name, $_options['value'], isset($_options['__options']) ? $_options['__options'] : (is_array($column->length) ? array_combine(array_values($column->length), array_values($column->length)) : array()), $_options['__first'], $_options['__last']);
				unset($_options['__dataset']);
				unset($_options['__options']);
				unset($_options['__first']);
				unset($_options['__last']);
				unset($_options['value']);
				unset($_options['type']);
				//print_r($_options);
				$field = new Select($_options, $options);
			break;
			
			default:
				$field = new Input($_options);
			break;
		}
		$this->fields[$field_name] = (object) array('label' => $label, 'control' => $field);
	}
	
	function createFields(){
		$this->crystal->load('Form:Input');
		$this->crystal->load('Form:TextArea');
		// create fields from table columns
		
		$columns = $this->getDatabaseColumns();
		foreach($columns As $field_name => $column){
			//if($column->raw_type == 'text') $column->raw_type = 'textarea';
			if(!preg_match('/^__/', $field_name)){
				$this->addField($field_name, $column->raw_type, $column);
			}
		}
		
		// create additional columns
		// dont recommended use this if you will use a scaffold
		
		foreach($this->options As $field_name => $field){
			if(!preg_match('/^__/', $field_name) && !isset($columns[$field_name])){
				$this->addField($field_name, $field['type']);
			}
		}
	}
	
	function getDatabaseColumns(){
		return ($this->object instanceOf ActiveRecord\Model ? $this->object->table()->columns : array());
	}
	
	function render(){
		$options =  $this->options;
		$columns = $this->getDatabaseColumns();
		$order = array();
		$form_node = '';
		if(isset($options['__order']) && is_string($options['__order'])){
			$order = explode(',', preg_replace('/ +/', '', $options['__order']));
		}
		
		foreach($this->getFields() As $key => $val){
			if(!in_array($key, $order)){
				$order[] = $key;
			}
		}
		
		if(isset($this->object->errors)){
			$errors = $this->object->errors->get_all();
		}
		
		foreach($order As $field_name){
			if(isset($options['__exclude']) && in_array($field_name, $options['__exclude'])) continue;
			
			$control = $this->getFieldControl($field_name)->getControl();
			if(isset($errors[$field_name])){
				$control = $control.' <label class="error">'.$errors[$field_name][0].'</label>';
			}
			if(is_bool($options[$field_name]['__template']) && !($options[$field_name]['__template'])){
				$form_node .= $control; 
				continue;
			}
			
			$form_node .= $this->getFullField($field_name, $control);
		}
		
		return $form_node;
	}

	function getNodeValue(){
		return $this->render();
	}
	
	function getFullField($key, $control = null){
		if(!isset($control)) $control = $this->getFieldControl($key)->getControl();
		$label = $this->getFieldLabel($key);
		return str_replace(array('{LABEL}', '{CONTROL}'), array($label, $control), $this->control_template);
	}
	
	function getField($key){
		if(!isset($this->fields[$key])) throw new UndefinedFormControlException('Undefined Control <code>'.$key.'</code>');
		return $this->fields[$key];
	}
	
	function getFieldLabel($key){
		return $this->getField($key)->label;
	}
	
	function getFieldControl($key){
		return $this->getField($key)->control;
	}
	
	function getFields(){
		return $this->fields;
	}
	
	function getAttributes(){
		return $this->object->attributes();
	}
	
	/**
	 * 	SELECT FUNCTIONS
	 */ 
	
	function getSelectOptions($name, $value, $options = array(), $first = null, $last = null){
		//print_r($options);
		//$name = $column->name;
		$x_options = $options;
		$y_options = array();
		if(is_array($first)) $y_options[] = array('value' => $first[0], '__node' => $first[1]);
		if($this->options[$name]['__dataset']){
			// 1 -> the dataset
			// 2 -> the value field
			// 3 -> the node string
			foreach($x_options[0] As $object){
				if(is_string($x_options[2])) eval('$node_value = '.$x_options[2].';');
                if($x_options[2] instanceOf Closure) $node_value = $x_options[2]($object);
				$x_data = array(
					'value' => $object->{$x_options[1]},
					'__node' => $node_value
				);
				if($value == $object->{$x_options[1]})
					$x_data['selected'] = true;
				
				$y_options[] = $x_data;
				
			}
			if(is_array($last)) $y_options[] = array('value' => $last[0], '__node' => $last[1]);
			return $y_options;
		}
        
        if($x_options[1] instanceOf Closure){
            foreach($x_options[0] As $key => $val){
                $data = $x_options[1]($key, $val);
                $x_data = array(
					'value' => $data[0],
					'__node' => $data[1]
				);
                
                if($value === $data[0])
                    $x_data['selected'] = true;
                $y_options[] = $x_data;
            }
            if(is_array($last)) $y_options[] = array('value' => $last[0], '__node' => $last[1]);
            return $y_options;
        }

		foreach($x_options As $key => $val){
			$x_data = array(
				'value' => $key,
				'__node' => $val
			);
			if($value === $key)
				$x_data['selected'] = true;
			$y_options[] = $x_data;
		}
		if(is_array($last)) $y_options[] = array('value' => $last[0], '__node' => $last[1]);
		return $y_options;
	}
	
	function __get($key){
		$control = $this->getFieldControl($key);
		$control->label = $this->getFieldLabel($key);
		$control->full = $this->getFullField($key);
		return $control;
	}
}
