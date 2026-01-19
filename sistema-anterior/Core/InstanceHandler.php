<?php
/**
 * 	CrystalTools Framework (http://www.crystaltools.org)
 */ 
 
namespace Core;

/**
 * 	Class Handler, this manage the main object instances
 */ 
class InstanceHandler{
	
	/**
	 * 	The created instances
	 * 	
	 * 	@var Array
	 */ 
	public static $instances = array();
	
	/**
	 * 	Creates an instance, and returns it.
	 * 
	 * 	@param String $class
	 * 	@return get_type($class)
	 */ 
	static function getInstance($class){
		if(!isset(self::$instances[$class])){
			self::$instances[$class] = new $class;
		}
		return self::$instances[$class];
	}
}
