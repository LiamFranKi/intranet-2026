<?php
/**
 * 	Converts an array to string
 * 	example:
 * 
 * 	$user = array(
 * 		'name' => 'Zarkiel'
 * 	)
 * 
 * 	Output:
 * 
 * 	array(
 * 		'name' => 'Zarkiel'
 * 	)
 * 
 */ 

class ArrayString{
	private $array;
	private $indent;
	private $string;
	private $prefix;
	
	function __construct($array, $indent = 0, $prefix = ''){
		$this->array = $array;
		$this->indent = $indent;
		$this->prefix = $prefix;
	}
	
	function parse_array(&$array, $indent){
		foreach($array As $key => $val){
			if(is_numeric($key)){
				$this->string .= "\n".str_repeat("\t", $indent);
			}
			if(is_string($key)){
				$this->string .= "\n".str_repeat("\t", $indent)."'".$key."' => ";
			}
			
			if(!is_array($val)){
				if(is_numeric($val)){
					$this->string .= $val.",";
				}elseif(is_string($val)){
					$this->string .= "'".$val."',";
				}elseif(is_bool($val)){
					$this->string .= ($val == true ? 'true' : 'false').",";
				}
			}else{
				if(count($val) > 0){
					$this->string .= "array(";
					$this->parse_array($val, $indent + 1);
					$this->string .= "\n".str_repeat("\t", $indent)."),";
				}else{
					$this->string .= "array(),";
				}
			}
		}
	}
	
	function __toString(){
		if(count($this->array) == 0) return 'array()';
		if(empty($this->string)){
			$this->string .= str_repeat("\t", $this->indent).$this->prefix.'array(';
		}
		$this->parse_array($this->array, $this->indent + 1);
		$this->string .= "\n".str_repeat("\t", $this->indent).')'; // leave last ";" for user
		return $this->string;
	}
}
