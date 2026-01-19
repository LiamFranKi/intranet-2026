<?php
class Tag extends FormControl{
	public $template = '<{TAG} {ATTRIBUTES}>{NODE_VALUE}</TAG>';
	public $tag;
	
	function __construct($tag, $attributes){
		parent::__construct($attributes);
		$this->tag = $tag;
	}
	
	function getControl(){
		parent::getControl();
		$this->replaceTemplate('TAG', $this->tag);
	}
	
}
