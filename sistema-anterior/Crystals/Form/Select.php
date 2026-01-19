<?php
class Select extends FormControl{
	
	public $template = '<select {ATTRIBUTES}>{NODE_VALUE}</select>';
	private $options = array();
	
	function __construct($attributes, array $options = null){
		parent::__construct($attributes);
		if(isset($options)) $this->getOptions($options);
	}
	
	function getOptions($options){
		foreach($options As $option){
			$this->options[] = new Option($option);
		}
	}
	
	function getNodeValue(){
		$value = '';
		foreach($this->options As $option){
			$value .= $option->getControl();
		}
		return $value;
	}
	
	function getControl(){
		parent::getControl();
		return $this->template;
	}
	
	
}
