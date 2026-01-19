<?php
abstract class FormControl{
	public $template;
	
	private $attributes = array();
	private $node_value;
	private $attribute_quote = '"';
	
	
	function __construct($attributes){
		if(isset($attributes['__node'])){
			$this->node_value = $attributes['__node']; unset($attributes['__node']);
		}
		$this->attributes = $attributes;
	}
	
	function getNodeValue(){
		return $this->node_value;
	}
	
	function setNodeValue($val){
		return $this->node_value = $val;
	}
	
	function set($key, $value){
		$this->attributes[$key] = $value;
	}
	
	function get($key){
		return $this->attributes[$key];
	}

	function getAttributesString(){
		$attributes_string = '';
		foreach($this->attributes As $key => $val){
			if($val === null) continue;
			$attributes_string .= ' '.$key.'='.$this->attribute_quote.$val.$this->attribute_quote;
		}
		return trim($attributes_string);
	}
	

	function getTemplate(){
		return $this->template;
	}
	
	function setTemplate($template){
		$this->template = $template;
	}
	
	function replaceTemplate($key, $val){
		$this->template = str_replace('{'.$key.'}', $val, $this->template);
	}
	
	function getControl(){
		$this->replaceTemplate('ATTRIBUTES', $this->getAttributesString());
		$this->replaceTemplate('NODE_VALUE', $this->getNodeValue());
		
		return $this->template;
	}
	
	function __toString(){
		return $this->getControl();
	}

}
