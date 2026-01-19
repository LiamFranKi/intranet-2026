<?php
/**
 * 	CrystalTools Framework (http://www.crystaltools.org)
 */ 
 
namespace Core;

abstract class Middleware extends Core{
	
	/**
	 * 	The application object
	 * 	
	 * 	@var Core\Application
	 */ 
	protected $application_object;
	
	/**
	 * 	Constructor
	 * 
	 * 	@param Core\Application $application_object
	 */ 
	function __construct($application_object = null){
		parent::__construct();
		if(isset($application_object)) $this->application_object = $application_object;
	}
	
	/**
	 * 	Returns the application object
	 * 
	 * 	@return Core\Application
	 */
	function getApplicationClass(){
		return $this->getApplicationObject();
	}
	
	function getApplicationObject(){
		return $this->application_object;
	}
	
	/**
	 * 	Allows to you to execute a function
	 * 	using the application object
	 * 
	 * 	@param Closure $function -> The function to execute.
	 * 	@return Mixed
	 */ 
	function hook($function){
		if(!$this->application_object instanceOf Application) return false;
		return $function($this->getApplicationObject());
	}
}
