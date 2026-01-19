<?php
/**
 * 	CrystalTools Framework (http://www.crystaltools.org)
 */ 
 
namespace Core;

class Exception extends \Exception{
	/**
	 * 	The exception's details
	 * 	
	 * 	@var Array
	 */ 
	 
	private $details;
	
	/**
	 * 	The http status code
	 * 
	 * 	@var Integer $statusCode
	 */ 
	public $statusCode = 500;

	/**
	 * 	Constructor
	 * 	
	 * 	@param String $message
	 * 	@param Array $details
	 */ 
	function __construct($message, $details = null){
		parent::__construct($message);
		if(isset($details)) $this->setDetails($details);
	}
	
	/**
	 * 	Sets the exception's details
	 * 	
	 * 	@param Array $details
	 */ 
	function setDetails(Array $details){
		$this->details = $details;
	}
	
	/**
	 *	Returns the exception's details
	 * 
	 * 	@return Array
	 */ 
	function getDetails(){
		return $this->details;
	}
	
	/**
	 * 	Returns the http status code
	 * 
	 * 	@return Integer
	 */ 
	function getStatusCode(){
		return $this->statusCode;
	}
}

