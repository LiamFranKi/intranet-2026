<?php
/**
 * 	CrystalTools Framework (http://www.crystaltools.org)
 */ 
 
namespace Core;

use Core\Interfaces\Security;

abstract class Application extends Core{
	
	/**
	 * 	Methods to execute at initialize
	 * 
	 * 	@var Array
	 */ 
	 public $beforeFilter = array();
	 
	/**
	 * 	The application's metadata
	 * 	
	 * 	@var stdClass
	 */ 
	public $data;
	
	/**
	 * 	The executed initializers
	 * 	
	 * 	@var Array
	 */ 
	protected $initializers = Array();
	
	/**
	 * 	The attributes registered with set() method
	 * 
	 * 	@var Array
	 */ 
	protected $attributes;
	
	/**
	 * 	The extension registered
	 * 
	 * 	@var Array
	 */ 
	protected $extensions = Array();
	
	/**
	 * 	The context that will send to the template
	 * 	
	 * 	@var Array
	 */ 
	protected $context;
	
	/**
	 * Stores the application params
	 * 
	 * @var Object 
	 */ 
	
	public $params;
	
	/**
	 * 	Constructor
	 * 
	 * 	@param $data -> Data sended from ResponseHandler
	 */
	 
	 
	function __construct($data){
		parent::__construct();
		$this->data = $data;
		// GLOBAL DATA
		if(count($_GET) > 0) $this->context('get', (Array) $this->get);
		if(count($_POST) > 0) $this->context('post',(Array) $this->post);
		if(count($_REQUEST) > 0) $this->context('request',(Array) $this->request);
		
		$this->params = (array) $this->getParams();
		$this->params = array_merge($this->params, (array) $this->request);
		$this->params = (object) $this->params;
		
		$this->context('params', (array) $this->params);
		
		$this->context('INSTALL_DIRECTORY', $this->getInstallDirectory());
		
		$this->autoload_crystals();
		$this->load_helpers();
		$this->loadTemplateEngine($this->data->site);
		$this->loadDatabase($this->data->site);
		$this->load_initializers();
		
		// extensions
		$this->setExtension('Util:Assets');
		$this->context('session', $this->session);
		$this->context('__application', $this); // set self into context
		
		// execute beforeFilter methods
		if(count($this->beforeFilter) > 0){
			foreach($this->beforeFilter As $method){
				if(method_exists($this, $method)){
					$this->$method();
				}
			}
		}
		// calls the initializer
		$this->initialize();
	}
	
	
	/**
	 * 	Returns all twig extensions
	 * 	
	 * 	@return Array
	 */ 
	function getExtensions(){
		return $this->extensions;
	}
	
	/**
	 *	Adds an extension, the extension must be defined in the crystals
	 * 	If $code param is set to true, the $crystal param will be executed.	
	 * 
	 * 	@param String $crystal
	 * 	@param Boolean $code
	 */
	function setExtension($crystal, $code = false){
		if(!$code){
			$this->crystal->load($crystal);
			$class_name = $this->crystal->get($crystal);
			if(!class_exists($class_name)) throw new Exception('Extension class <code>'.$class_name.'</code> not found in crystal <code>'.$crystal.'</code>');
		}else{
			eval($crystal);
		}
		
		$extension = new $class_name($this);
		$this->extensions[$crystal] = $extension;
	}
	
	/**
	 * 	Execute a Crystal Security
	 * 	
	 * 	@param $crystal -> The crystal to load and execute
	 * 	@param $data -> The params to send to process_security($data)
	 */ 
	function security($crystal, $data = null){
		$this->crystal->load('Security:'.$crystal);
		$class_name = $this->crystal->get($crystal);
		if(!class_exists($class_name)) throw new Exception('Security class <code>'.$class_name.'</code> not found in crystal <code>'.$crystal.'</code>');
		$secure = new $class_name($this);
		if(!in_array('Core\\Interfaces\\Security', class_implements($secure))) throw new Exception('Class <code>'.$class_name.'</code> must implements Security Interface');
		$this->crystal->load('Security:SecurityException');
		
		if(!$secure->process_security($data)) throw new \SecurityException('You aren\'t authorized to see this action');
	}
	
	/**
	 * 	Loads helpers defined if those exists
	 */ 
	function load_helpers(){
		$helpers = Array(
			'./Core/Helper.php',
			$this->settings->sites[$this->data->site]['Applications'].'/Helper.php',
			$this->settings->sites[$this->data->site]['Applications'].'/'.$this->data->application.'/Helper.php'
		);

		foreach($helpers As $val){
			if(file_exists($val)) require_once $val;
		}
	}
	
	/**
	 * 	Loads all crystals defined for autoload in Settings file
	 */ 
	function autoload_crystals(){
		if(is_array($this->settings->autoload)){
			foreach($this->settings->autoload As $key => $val){
				$this->crystal->load($val);
			}
		}
	}
	
	/**
	 * 	Returns the template context
	 * 
	 * 	@return Array
	 */ 
	function getContext(){
		return $this->context;
	}
	
	/**
	 * 	Adds a value in the template context
	 * 
	 * 	@param String $key -> The key for context
	 * 	@param String $value -> The value
	 */ 
	function context($key, $val = null){
		if(!isset($val)) return $this->context[$key];
		$this->context[$key] = $val;
	}
	
	/**
	 * 	Loads all initializers in the directory
	 */ 
	function load_initializers(){
		//print_r($this->data);
		$path = $this->settings->sites[$this->data->site]['Initializers'];
		if(!file_exists($path)) return false;
		$directory = dir($path);
		while($file = $directory->read()){
			if(preg_match('/(.*)\.php$/', $file, $r)){
				
				require_once $directory->path.'/'.$file;
				if(!class_exists($r[1])) throw new Exception('Initializer class <code>'.$r[1].'</code> not found in <code>'.$directory->path.'/'.$file.'</code>');
				$initializer = new $r[1]($this);
				if($initializer instanceOf Initializer){
					//throw new CT_Exception('<code>'.$r[1].'</code> initializer is not valid');
					$this->initializers[$this->data->site.':'.$r[1]] = $initializer;
					$initializer->initialize();
				}
			}
		}
	}

	/**
	 * 	Renders a template from Twig_Template
	 * 	
	 * 	@param String $template
	 * 	@param Array $context
	 * 	@return String 
	 */ 
	function render(/*($context) or ($template) or ($template, $context)*/){
		$params = func_get_args();
		$template = new Template($this, $params);
		return $template->render();
	}
	
	/**
	 * 	Renders with json extension
	 * 	
	 * 	@param Array $data
	 */ 
	function render_json(array $data){
		header('Content-Type: application/x-json');
		echo json_encode($data);
	}
	
	/**
	 * 	Initializer from the current application
	 */
	function initialize(){}
	
	/**
	 * 	Returns a initializer loaded 
	 * 
	 * 	@param String $i -> The initializer site and name
	 */ 
	function initializer($i){
		if(isset($this->initializers[$i])) return $this->initializers[$i];
	}
	
	/**
	 * 	Returns the additional attributes
	 * 	
	 * 	@return Array
	 */ 
	function getAttributes(){
		return $this->attributes;
	}
	
	/**
	 * 	Sets an attribute, if the $context param is set to true
	 * 	the value will be added in the template context
	 * 
	 * 	@param String $key
	 * 	@param Mixed $value
	 * 	@param Boolean $context
	 */ 
	function set($key, $value, $context = false){
		$this->attributes[$key] = $value;
		if($context) $this->context($key, $value);
	}
	
	/**
	 * 	Returns an attribute defined in $attributes
	 * 	
	 * 	@param String $key
	 * 	@return Mixed
	 */ 
	function get($key){
		return $this->attributes[$key];
	}
	
	/**
	 * 	Sets an attribute
	 * 
	 * 	@param String $key
	 * 	@param Mixed $value
	 */ 
	function __set($key, $value){
		$this->attributes[$key] = $value;
	}
	
	/**
	 * 	Return an attribute defined in $attributes
	 * 	
	 * 	@param String $key
	 * 	@return Mixed
	 */
	function __get($key){
		return $this->attributes[$key];
	}
	
	/**
	 * 	Creates a new application
	 * 
	 * 	@param String $context -> the definition for call (Site:Application:Action)
	 * 	@param Array $params -> Additional params 
	 */ 
	function call($context, $params = array()){
		
		$data = explode(':', $context);
		// adds the current site
		if(count($data) == 2) $context = $this->getData('site').':'.$context;
		
		CrystalTools::call($context, (object) $params);
	}

	/**
	 * 	Returns the application name
	 * 
	 * 	@return String
	 */ 
	function getApplicationName(){
		return $this->getData('application');
	}
	
	/**
	 * 	Returns the action name
	 * 
	 * 	@return String
	 */
	function getActionName(){
		return $this->getData('action');
	}
	
	/**
	 * 	Returns the application data
	 * 	
	 * 	@param String $key
	 * 	@return Mixed
	 */
	function getData($key){
		return $this->data->$key;
	}
		
	/**
	 * 	Returns true if is XMLHttpRequest
	 * 
	 * 	@return Boolean
	 */ 
	function isXMLHttpRequest(){
		return !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
	}
	
	/**
	 * 	Redirects to any defined url
	 * 	
	 * 	@param String $url
	 */ 
	function redirect($url){
		$this->response->redirect($url);
	}
	
	function getRootPath(){
		return $this->getInstallDirectory();
	}
	
	function getParams(){
		return $this->data->params;
	}
}

/**
 * 	Defines the older application class, for older versions
 * 
 * 	@deprecated
 */ 

class zkApplication extends Application{}
