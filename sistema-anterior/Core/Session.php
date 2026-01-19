<?php
/**
 * 	CrystalTools Framework (http://www.crystaltools.org)
 */ 
 
namespace Core;

class Session{
	
	/**
	 * 	Adds a prefix to session names
	 * 
	 * 	@var String
	 */ 
	static $prefix = '';
	
	/**
	 * 	The session name
	 * 
	 * 	@var String
	 */ 
	public static $__name = null;
	
	/**
	 * 	Constructor
	 */ 
	function __construct(){
		if(empty(static::$__name)) static::$__name = sha1(dirname(__DIR__)); // set default session name
		session_name(static::$__name);
		session_start();
	}

	/**
	 * 	Returns the session name
	 * 
	 * 	@return String
	 */
	function getSessionName(){
		return static::$__name;
	}
	
	/**
	 * 	Sets the session name
	 * 
	 * 	@param String $name
	 */ 
	function setSessionName($name){
		self::$__name = $name;
	}

	/**
	 *	Encodes a value 
	 * 	
	 * 	@param String $val
	 * 	@return String
	 */
	function encode($val){
		return base64_encode(base64_encode(base64_encode(base64_encode($val))));
	}
	
	/**
	 * 	Decodes a encoded string
	 * 
	 * 	@param String $val
	 * 	@return String
	 */ 
	function decode($val){
		return base64_decode(base64_decode(base64_decode(base64_decode($val))));
	}
	
	/**
	 * 	Sets a session value
	 * 
	 * 	@param String $key
	 * 	@param Mixed $val
	 */ 
	function __set($key,$val){
		$this->set($key, $val);
	}
	
	function set($key,$val){
		$_SESSION[static::$prefix.$key] = $val;
	}
	
	/**
	 * 	Returns a session value
	 * 
	 * 	@param String $key
	 * 	@return Mixed
	 */ 
	function __get($key){
		return $this->get($key);
	}
	
	function get($key){
		$val = $_SESSION[static::$prefix.$key];
		return $val;
	}
	
	/**
	 * 	Returns all session values
	 * 	
	 * 	@return Array
	 */ 
	function getValues(){
		$values = $_SESSION;
		if(!empty(static::$prefix)){
			$values = Array();
			foreach($_SESSION As $key => $val){
				$key = preg_replace('/^'.static::$prefix.'/', '', $key);
				$values[$key] = $val;
			}
		}
		
		return $values;
	}
	
	/**
	 * 	Sets a prefix to session names
	 * 
	 * 	@param String $prefix
	 */ 
	function setPrefix($prefix){
		static::$prefix = $prefix;
	}
	
	/**
	 * 	Destroy an initialized session
	 * 
	 * 	@param String $session
	 */ 
	function destroy($session){
		unset($_SESSION[static::$prefix.$session]);
	}
	
	/**
	 * 	Returns true if a session is active
	 * 	Returns all active sessions if param $session is null
	 * 	
	 * 	@param String|Null $session
	 * 	@return Boolean|Array
	 */ 
	function active($session = null){
		if(isset($session)){
			if(isset($_SESSION[static::$prefix.$session]) && !empty($_SESSION[static::$prefix.$session])){
				return true;
			}
			return false;
		}else{
			return $_SESSION;
		}
	}
}
?>
