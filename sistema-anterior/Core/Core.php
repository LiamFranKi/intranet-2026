<?php
/**
 * 	CrystalTools Framework (http://www.crystaltools.org)
 */ 
 
namespace Core;

use Twig_Autoloader;
use Collection;
use Core\CrystalManager;
use Core\Exceptions\DatabaseException;

class Core{
	
	/**
	 * 	The Settings defined in Settings.php file
	 * 	
	 * 	@var Settings
	 */ 
	public $settings;
	
	/**
	 * 	The http response object
	 * 
	 * 	@var Core\Http\Response
	 */ 
	public $response;
	
	/**
	 * 	The autoloader that handles the autoloading requests
	 * 
	 * 	@var Core\Autoloader
	 */ 
	public $autoloader;
	
	/**
	 * 	The crystal loader that manage the crystal loadings
	 * 
	 * 	@var Core\CrystalManager
	 */ 
	public $crystal;
	
	/**
	 * 	The session handler
	 * 
	 * 	@var Core\Session
	 */ 
	public $session;

	/**
	 * 	The global vars
	 */ 
	
	public $get, $post, $request;
	
	/**
	 * 	The current version of the framework
	 */ 
	const VERSION = '3.0';
	
	/**
	 * 	Constructor
	 */
	function __construct(){
		$this->settings = InstanceHandler::getInstance('Settings');
		$this->autoloader = InstanceHandler::getInstance('Core\\Autoloader');
		$this->autoloader->start();
		$this->crystal = InstanceHandler::getInstance('Core\\CrystalManager');
		$this->response = InstanceHandler::getInstance('Core\\Http\\Response');
		
		$this->session = InstanceHandler::getInstance('Core\\Session');
		if(isset($this->settings->session_name) && !empty($this->settings->session_name)) 
			$this->session->setSessionName($this->settings->session_name);
		
		if(!defined('INSTALL_DIRECTORY'))
			define('INSTALL_DIRECTORY', $this->getInstallDirectory());
		
		$this->loadGlobalVars();
	}
	
	/**
	 * 	Returns the install directory
	 * 
	 * 	@return String
	 */ 
	function getInstallDirectory(){
		$install_directory = '';
		if(preg_match('/(.*)\/?index\.php\/?/i', $_SERVER['PHP_SELF'], $r)){
			$r[1] = preg_replace('/\/$/', '', $r[1]);
			$install_directory = $r[1];
		}
		return $install_directory;
	}
	
	/**
	 * 	Configure the database environment
	 * 	
	 * 	@param String $site -> the registered site
	 */ 
	function loadDatabase($site, $connection = null){
		$this->crystal->load('ActiveRecord');
		
		$DatabaseDirectory = $this->settings->sites[$site]['Models'];

		// set the models directory in the autoloader
		
		$this->autoloader->addAutoloadPath($DatabaseDirectory, true, false);

		$Environment = isset($connection) ? $connection : $this->settings->environment;
		if(!file_exists($DatabaseDirectory)) mkdir($DatabaseDirectory, 0777, true);
		$Connections = $this->settings->database_connections;
		\ActiveRecord\Config::initialize(function($cfg) use ($Connections,$DatabaseDirectory,$Environment){
			$cfg->set_model_directory($DatabaseDirectory);
			$cfg->set_connections($Connections);
			$cfg->set_default_connection($Environment);
		});
	}
	
	/**
	 * 	Parse global vars GET, POST, REQUEST and create standard object to access it.
	 */ 
	function loadGlobalVars(){
		$this->crystal->load('Util:Collection');
		
		
		
		$get = new Collection($_GET);
		$post = new Collection($_POST);
		$request = new Collection($_REQUEST);
		// GET 
		$this->get = $get->get_attributes();
		// POST 
		$this->post = $post->get_attributes();
		// REQUEST
		$this->request = $request->get_attributes();
		unset($this->get->zkHttpRequest);
		unset($this->request->zkHttpRequest);
	}
	
	/**
	 * 	Loads all required files for template engine
	 * 	
	 * 	@param String $site
	 */ 
	function loadTemplateEngine($site){
		$this->crystal->load('Twig:Autoloader');
		Twig_Autoloader::register();
		$path = $this->settings->sites[$site]['Extensions'];
		if(!file_exists($path)) return false;
		$directory = dir($path);
		while($file = $directory->read()){
			if(preg_match('/(.*)\.php$/', $file, $r)){
				require_once $directory->path.'/'.$file;
				if(!class_exists($r[1])) throw new Exception('Extension class <code>'.$r[1].'</code> not found in <code>'.$directory->path.'/'.$file.'</code>');
				$extension = new $r[1]($this);
				$this->extensions[$site.':'.$r[1]] = $extension;
			}
		}
	}
}
