<?php
/**
 * 	CrystalTools Framework (http://www.crystaltools.org)
 */ 
 
namespace Core;

use Core\Exceptions\CrystalNotFoundException;

class CrystalManager{
	
	/**
	 * 	The settings object
	 * 
	 * 	@var Settings
	 */ 
	
	private $settings;
	
	/**
	 * 	The crystals directory
	 * 
	 * 	@var String
	 */ 
	private $crystals_directory = './Crystals'; // set default directory
	
	/**
	 * 	The loaded crystals
	 * 
	 * 	@var Array
	 */ 
	static $loaded = array();
	
	/**
	 * 	Constructor
	 */ 
	function __construct(){
		$this->settings = InstanceHandler::getInstance('Settings');
		$this->crystals_directory = $this->settings->crystals_directory;
		if(!file_exists($this->crystals_directory)){
			throw new Exception('Missing Crystals Directory: <code>'.$this->crystals_directory.'</code>');
		}
	}
	
	/**
	 * 	Loads a Crystal Library
	 * 
	 * 	@param String $crystal
	 */ 
	function load($crystal){
		$crystal = trim($crystal);
		$ss = explode(':',$crystal);
		
		$name = $ss[0];	$directory = $name;
		
		// only loads the defined file
		
		if(count($ss) >= 2){
			$name = end($ss);
			$directory = dirname(implode('/',$ss));
		}
		
		// loads all crystals in the directory
		
		if(trim($name) == '*'){
			$xdirectory = $this->crystals_directory.'/'.$directory;
			if(!file_exists($xdirectory)) throw new CrystalNotFoundException('Missing CrystalsDirectory: <code>'.$this->crystals_directory.'/'.$directory.'</code>');
			$this->executePackage($xdirectory);
			$xdirectory = dir($xdirectory);
			while($file = $xdirectory->read()){
				if(preg_match('/(.*)\.php$/i', $file, $r)){
					$name = $r[1];
					$crystal = str_replace('/', ':', $directory).':'.$name;
					$this->load($crystal);
				}
			}
		}

		$file = $this->crystals_directory.'/'.$directory.'/'.$name.'.php';
		
		if(!file_exists($file)) throw new CrystalNotFoundException('Missing CrystalFile: <code>'.$file.'</code>');
		
		// load the crystal if this isn't loaded
		
		if(!$this->loaded(str_replace('/',':',$directory).':'.$name)){
			self::$loaded[] = str_replace('/',':',$directory).':'.$name;
			require_once $file;
		}
	}
	
	/**
	 * 	If package.json file exists load crystals following ruled defined here
	 * 
	 * 	@param String $directory
	 */ 
	function executePackage($directory){
		if(file_exists($directory.'/package.json')){
			$data = json_decode(file_get_contents($directory.'/package.json'));
			foreach($data->require->crystals As $crystal){
				$this->load($crystal);
			}
		}
	}
	
	/**
	 * 	Returns the crystals loaded
	 * 
	 * 	@param String $crystal -> is this crystals is loaded returns true or false
	 *  @params null $crystal -> returns all crystals loaded
	 * 	@return Array $crystals_loaded
	 */ 
	function loaded($crystal = null){
		if(isset($crystal)){
			return in_array($crystal, self::$loaded);
		}
		return self::$loaded;
	}
	
	/**
	 * 	Returns the crystals names from crystals loaded
	 * 
	 * 	@param String $crystal_string
	 *	@return String|null
	 */ 
	function get($crystal_string = null){
		$crystal_string = trim($crystal_string);
		$cs = explode(':', $crystal_string);
		$package = str_replace('/',':', dirname(str_replace(':', '/', $crystal_string)));
			
			if(end($cs) == '*'){
				$response = Array();
				foreach(self::$loaded As $crystal){
					if(preg_match('/^'.$package.'\:(.+)/i', $crystal, $r)){
						$response[] = $r[1];
					}
				}
				return $response;
			}else{
				foreach(self::$loaded As $crystal){
					if($package != '.' && preg_match('/^'.$package.'\:'.end($cs).'$/i', $crystal, $r)){
						return end($cs);
					}
					if($package == '.' && preg_match('/\:'.end($cs).'$/i', $crystal, $r)){
						return end($cs);
					}
				}
			}
		
		return null;
	}
	
}
?>
