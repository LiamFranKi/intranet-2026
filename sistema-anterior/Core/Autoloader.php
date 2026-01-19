<?php
/**
 * 	CrystalTools Framework (http://www.crystaltools.org)
 */ 
 
namespace Core;

use Core\Exception;

/**
 * 	This class handles the autoloading classes.
 */ 
class Autoloader{

	/**
	 *	The autoload directories
	 * 	
	 * 	@var Array
	 */
	public $autoloadPaths = array('.'); // define the current path by default
	
	/**
	 * 	Constructor
	 * 	
	 * 	@param Array $paths
	 */ 
	function __construct($paths = null){
		if(isset($paths)) $this->setAutoloadPaths($paths);
	}
	
	/**
	 *	Set the autoload paths, the autoloader will search the required classes
	 * 	in order that you define the paths.
	 * 
	 * 	Example:
	 * 	You have defined the following paths:
	 * 	
	 * 	$paths = array(
	 * 		'.', // current directory
	 * 		'lib/classes' => false
	 * 	);
	 * 
	 * 	$class = new MyClass();
	 * 	
	 * 	The autoloader will search from the following order:
	 * 	
	 * 	- ./MyClass.php
	 * 	- lib/classes/MyClass.php
	 * 
	 * 	If any file match with the search patterns this file will loaded and if this 
	 * 	contains the required class, the class will be returned.
	 * 
	 * 	If the value is a false boolean, the namespace requests not will be added to directory path
	 * 
	 * 	@param Array $paths
	 */
	function setAutoloadPaths(array $paths){
		$this->autoloadPaths = $paths;
	}
	
	/**
	 * 	Add some path to the autoloading paths
	 * 	if $runAgain param is set to true, the autoloader will start again.
	 * 	if $namespaces param is set to true, the autoloader will search the path adding namespaces.	
	 * 
	 * 	@param String $path
	 * 	@param Boolean $runAgain
	 * 	@param Boolean $namespaces
	 */ 
	function addAutoloadPath($path, $runAgain = false, $namespaces = true){
		if($namespaces) $this->autoloadPaths[] = $path;
		if(!$namespaces) $this->autoloadPaths[$path] = false;
		if($runAgain) $this->start();
	}
	
	/**
	 *	Register and starts the autoloader
	 */
	function start(){
		$this->unregister();
		spl_autoload_register(array($this, '__autoload'));
	}
	
	/**
	 * 	Unregister the autoloader
	 */ 
	function unregister(){
		spl_autoload_unregister('__autoload');
	}
	
	/**
	 * 	Manage the autoload requests
	 */ 
	function __autoload($className){
		$namespace = $this->getFullNamespace($className);
		$directory = $this->getClassDirectory($className);
		$className = $this->getFileName($className);
		$loaded = false;
		
		foreach($this->autoloadPaths As $key => $val){
			$autoloadPath = $val;
			if(is_bool($val)) $autoloadPath = $key;
			
			$file = $autoloadPath.'/';
			
			if($val !== false){
				$file .= $directory;
			}
			
			$file .= $className.'.php';
			
			if(file_exists($file)){
				//echo $file.'<br />';
				require_once $file;
				break;				
			}
		}
		
		//if(!$loaded) throw new Exception('Can\'t find the class <code>'.$className.'</code>');
	}

	/**
	 * 	Return the namespace pieces
	 * 
	 * 	Class -> Classes\Util\MyClass -> array('Classes', 'Util')
	 * 	
	 * 	@param String $className
	 * 	@return Array
	 */ 
	function getNamespaces($className){
		$namespaces = explode('\\', $className);
		array_pop($namespaces);
		return $namespaces;
	}
	
	/**
	 * 	Return the possible filename for the required class
	 * 	
	 * 	@param String $className
	 * 	@return String 
	 */ 
	function getFileName($className){
		$pieces = explode('\\', $className);
		return array_pop($pieces);
	}
	
	/**
	 *	Return the full recontruyed namespace
	 * 	
	 * 	Class -> Classes\Util\MyClass -> Classes\Util
	 * 
	 * 	@param String $className
	 * 	@return String 
	 */  
	function getFullNamespace($className){
		return implode('\\', $this->getNamespaces($className));
	}
	
	/**
	 * 	Return the registered paths for autoloading
	 * 	
	 * 	@return Array
	 */ 
	function getAutoloadPaths(){
		return $this->autoloadPaths;
	}
	
	/**
	 * 	Return the possible class directory
	 * 	
	 * 	@param String $className
	 * 	@return String
	 */ 
	function getClassDirectory($className){
		$namespaces = $this->getNamespaces($className);
		if(count($namespaces) == 0) return '';
		return implode(DIRECTORY_SEPARATOR, $namespaces).DIRECTORY_SEPARATOR;
	}
}
