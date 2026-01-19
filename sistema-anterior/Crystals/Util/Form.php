<?php
/**
 *  FORM -> This class creates a form from a ActiveRecordModel
 */

class Form{
	private $type;
	private $object;
	private $options;
	private $control_container_template;
	private $control_template;
	private $textarea_template;
	private $select_template;
	private $custom_templates = Array();
	  
	function __construct($object, $options = Array()){
		$this->object = $object;
		$this->options = $options;
		$this->set_templates();
		if($object InstanceOf ActiveRecord\Model){
			$this->type = 'Model';
		}else{
			throw new Exception('Invalid Object');
		}
	}
	
	function get_columns(){
		return (Array) $this->object->table()->columns;
	}
	
	function render(){
		$columns = $this->get_columns();
		$attributes = $this->object->attributes();
		$form_content = '';
		
		foreach($columns As $key=>$val){
			$options = $this->options[$key];
			if(is_array($this->options['__exclude'])){
				if(in_array($key, $this->options['__exclude'])) continue;
			}
			// obtenemos el nombre del campo
			
			$name = isset($options['name']) ? $options['name'] : $key;
			$id = isset($options['id']) ? $options['id'] : $name;
			
			// revisamos si el modelo tiene validaciones
			
			$xc = get_class($this->object); // obtenemos el modelo
			$validations = isset($xc::$validates_presence_of) ? $xc::$validates_presence_of : Array();
			
			foreach($validations As $validation){
				if(isset($validation[0]) && $validation[0] == $name){
					$options['class'] .= ' required ';
				}
			}
			
			// definimos atributos principales
			
			$value = isset($attributes[$key]) ?  $attributes[$key] : (isset($options['value']) ? $options['value'] : '');
			$label = isset($options['label']) ? $options['label']: ucwords($name);
			$class = isset($options['class']) && !empty($options['class']) ? 'class="'.$options['class'].'"' : '';
			$style = isset($options['style']) ? 'style="'.$options['style'].'"' : '';
			$params = isset($options['params']) ? $options['params'] : '';
			
			$type = $val->raw_type;
			$length = $val->length;
			
			$control = !isset($options['control']) ? $this->get_control($type, $name, $id, $value, $class, $params, $style, $length) : $options['control'];
				
			if(isset($this->object->errors)){
				$errors = $this->object->errors->get_all();
				if(isset($errors[$name])){
					$control = $control.' <label class="error">'.$errors[$name][0].'</label>';
				}
			}
			// obtiene el contenedor principal
			
			$container = $this->get_container($label, $control);
			
			$form_content .= $container;
		}
		return $form_content;
	}
	
	// define los templates principales
	
	function set_templates(){
		$this->control_container_template = '<div class="form-group form-group-sm">
		    <label class="col-sm-4 control-label" for="">{label}</label>
		    <div class="col-sm-6">{control}</div>
		</div>'; 
		$this->control_template = '<input type="{type}" name="{name}" id="{id}" value="{value}"{style}{class}{params} />';
		$this->select_template = '<select name="{name}" id="{id}"{style}{class}{params}>{options}</select>';
	}
	
	// define el contenedor de los controles
	
	function set_container_template($template){
		$this->control_container_template = $template;
	}
	
	// obtiene el contenedor combinado con los controles
	
	function get_container($label, $control){
		$container = $this->control_container_template;
		$container = str_replace('{label}', $label, $container);
		$container = str_replace('{control}', $control, $container);
		return $container;
	}
	
	// obtiene un elemento select
	
	function get_select($name, $value, $length){
		$control = $this->select_template;
		$options = '';
		$_options = $length;
		
		if(isset($this->options[$name]['options']) && is_array($this->options[$name]['options'])){
			$_options = $this->options[$name]['options'];
		}
		
		// colocamos el primer elemento en caso de que haya sido definido
		$first = $this->options[$name]['first'];
		if(isset($first) && is_array($first)){
			$options .= '<option value="'.$first[0].'" '.($first[0] == $value ? 'selected':'').'>'.$first[1].'</option>'."\n";
		}
		if(isset($_options) && is_array($_options)){
			if(!is_array($_options[0])){
				foreach($_options As $key => $option){
					$options .= '<option value="'.(!is_numeric($key) ? $key : $option).'" '.((!is_numeric($key) ? $key : $option) == $value ? 'selected':'').'>'.$option.'</option>'."\n";
				}
			}else{
				foreach($_options[0] As $option){
					if(isset($_options[3]) && is_array($_options[2])){
						//$_options[3] -> format
						//$_options[4] -> keys
						$values = Array();
						foreach($_options[2] As $x){
							$values[] = $option->$x;
						}
						$options .= '<option value="'.$option->$_options[1].'" '.($option->$_options[1] == $value ? 'selected':'').'>'.call_user_func_array('sprintf', array_merge((array)$_options[3], $values)).'</option>'."\n";
					}else{
						$options .= '<option value="'.$option->$_options[1].'" '.($option->$_options[1] == $value ? 'selected':'').'>'.$option->$_options[2].'</option>'."\n";
					}
				}
			}
		}
		
		$control = str_replace('{options}', $options, $control);
		return $control;
	}
	
	// obtiene el control final
	
	function get_control($raw_type, $name, $id, $value, $class, $params, $style, $length =  null){
		$raw_type = !isset($this->options[$name]['type']) ? $raw_type : $this->options[$name]['type'];
		if(isset($this->options['__prefix'])){
			$name = $this->options['__prefix'].$name;
			$id = $this->options['__prefix'].$id;
		}
		switch($raw_type){
			case 'textarea':
			case 'text':
				$control = '<textarea name="{name}" id="{id}" {class} {style} {params}>{value}</textarea>';
			break;
			
			case 'file':
				$control = '<input type="file" name="{name}" id="{id}" {class} {style} {params} />';
			break;
			
			case 'select':
			case 'enum':
			case 'set':
				$control = $this->get_select($name, $value, $length);
			break;
			
			case 'password':
				$type = 'password';
				$control = $this->control_template;
			break;
			case 'hidden':
				$type = 'hidden';
				$control = $this->control_template;
			break;
			default:
				$type = 'text';
				$control = $this->control_template;
			break;
		}

		$control = str_replace('{name}', $name, $control);
		$control = str_replace('{id}', $id, $control);
		$control = str_replace('{value}', $value, $control);
		$control = str_replace('{type}', $type, $control);
		$control = str_replace('{class}', ' '.$class, $control);
		$control = str_replace('{style}', ' '.$style, $control);
		$control = str_replace('{params}', ' '.$params, $control);
		return $control;
	}
}
