<?php
namespace Core\Http;

class Request{
	
	private $params = array();
	
	function __construct($params = array()){
		if(is_string($params)) $params = array('REQUEST_URI' => $params); // initialize with a url string
		if(is_array($params)) $this->setParams($params);
	}
	
	function setParams(array $params){
		$this->params = array_merge($_SERVER, $params);
	}
	
	function getParams(){
		return $this->params;
	}
	
	function getMethod(){
		return $this->params['REQUEST_METHOD'];
	}
	
	function getFullUrl(){
		return $this->params['REQUEST_URI'];
	}
	
	/**
	 * 	Returns the full url but removed the first "/"
	 * 	
	 * 	Example:	
	 * 	
	 * 	$url = '/'; 		// return ''
	 * 	$url = '/users'; 	// return 'users'
	 */ 
	function getUrl(){
		return preg_replace('/^\//', '', $this->getFullUrl());
	}
	
	
}
