<?php
class Collection{
	protected $attr = Array();
	
	function __construct($data = Array()){
		$this->attr = $data;
		$this->parse($this->attr);
	}
	
	function parse(&$array){
		if(is_array($array)){
			foreach($array As $key => $val){
				if(is_array($val)){
					$this->parse($array[$key]);
				}else{
					$array[$key] = $this->clean($val);

					//echo $key."\n";
				}
			}
		}
	}
	
	function clean($input) {
		$search = array(
		'@<script [^>]*?>.*?@si',            // Strip out javascript
		'@< [/!]*?[^<>]*?>@si',            // Strip out HTML tags*/
		'@<style [^>]*?>.*?</style>@siU',    // Strip style tags properly
		'@< ![sS]*?--[ tnr]*>@'         // Strip multi-line comments
		);
		
		//$output = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		//return stripslashes($output);
		return $input;
	}
	
	function get_attributes(){
		return (object) $this->attr;
	}
	
	function __get($key){
		return $this->attr[$key];
	}
	
	function __set($key, $val){
		return $this->attr[$key] = $this->to_string ? new String($val) : $val;
	}
	
	function __toString(){
		return 'Array';
	}
	
}
