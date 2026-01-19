<?php
/**
 * 	CrystalTools Framework (http://www.crystaltools.org)
 */ 
 
namespace Core;

use Core\Http\RequestHandler;
use Core\Http\Request;
use Core\Http\ResponseHandler;
use Core\Http\Exceptions\NotFoundException;

use Twig_Autoloader;
use Twig_Loader_Filesystem;
use Twig_Environment;

class CrystalTools extends Core{
	
	/**
	 * 	The exception templates
	 * 	@var String
	 */ 
	private $TExceptionDevelopment = 'TExceptionDevelopment.php';
	private $TExceptionProduction = 'TExceptionProduction.php';
	
	/**
	 * 	Constructor
	 */ 
	function __construct(){
		parent::__construct();
	}
	
	/**
	 * 	Get the current url
	 * 
	 * 	@return String
	 */ 
	private function getUrl(){
		$url = $_SERVER['PHP_SELF'];
		
		if(empty($_REQUEST['zkHttpRequest'])){
			$url = $_SERVER['PHP_SELF'];
			$url = preg_replace('/(.*)\/?index\.php\/?/', '', $url);
			$_REQUEST['zkHttpRequest'] = $url;
		}
		$_REQUEST['zkHttpRequest'] = str_replace(preg_replace('/^\//','',INSTALL_DIRECTORY), '', $_REQUEST['zkHttpRequest']);
		return $_REQUEST['zkHttpRequest'];
	}
	
	/**
	 * 	Run the main application
	 */ 
	function runApplication(){
		
		
		$request = new Request(array(
			'REQUEST_URI' => $this->getUrl()
		));
	
		if(file_exists('./'.$this->getUrl()) && preg_match('/\.(\w+)$/', $this->getUrl())) return header('Location: '.INSTALL_DIRECTORY.'/'.$this->getUrl());//echo INSTALL_DIRECTORY.'/'.$this->getUrl(); 
		
		$requestHandler = new RequestHandler($request, $this->settings->sites, $this->settings->default_site);
		$requestHandler->loadSitePatternsFile();
		
		if($requestHandler->getMatch()){
			$responseHandler = new ResponseHandler($requestHandler);
			$responseHandler->sendResponse();
		}else{
			throw new NotFoundException('Current url <code>'.$request->getFullUrl().'</code> not match, we have tried with the following url patterns', $requestHandler->getNonMatchPatterns());
		}
	}
	
	/**
	 * 	Create a new application, and execute it
	 * 	
	 * 	@param String $definition
	 * 	@param Array $params
	 */ 
	function call($definition, $params = null){
		$settings = InstanceHandler::getInstance('Settings');
		$responseHandler = new ResponseHandler(array(
			'sites' => $settings->sites,
			'response' => $definition,
			'params' => $params,
			'default_site' => $settings->default_site
		));
		
		$responseHandler->sendResponse();
	}
	
	/**
	 * 	If any exception is throwed save the application
	 * 	
	 * 	@param Exception $e
	 */ 
	function saveApplication($e){
		//print_r($e);
		$exception = $e;
		$type = get_class($e);
		$message = $e->getMessage();
		try{
			$details = !method_exists($e, 'getDetails') ? Array() : $e->getDetails();
			$handler = $this->settings->error_handlers[$type];
			if(isset($handler)){
				if(is_string($handler)){
					self::call($handler);
				}
			}else{
				$this->crystal->load('Twig:Autoloader');
				Twig_Autoloader::register();
				$loader = new Twig_Loader_Filesystem(Array('./Core/Templates'));
				$twig = new Twig_Environment($loader);
				if(($this->settings->environment == 'Development')){
					$template = $twig->loadTemplate($this->TExceptionDevelopment);
				}else{
					$template = $twig->loadTemplate($this->TExceptionProduction);
				}
				
				$statusCode = !method_exists($e, 'getStatusCode') ? 200 : $e->getStatusCode();
				
				$this->response->setStatusCode($statusCode);
				$this->response->write($template->render(Array(
					'global' => Array(
						'GET'=>$_GET,
						'POST'=>$_POST,
						'SESSION'=>$_SESSION,
						'COOKIE'=>$_COOKIE,
						'SERVER'=>$_SERVER
					),
					'type' => $type,
					'message' => $message,
					'details' => $details,
					'trace' => $e->getTraceAsString(),
					'INSTALL_DIRECTORY' =>$this->getInstallDirectory(),
				)));

				echo $this->response->getBody();
			}
		}catch(Exception $e){
			
			// many exceptions with the same type
			// prevent recursion
			
			if($type == get_class($e)){
				return $this->saveApplication(new Exception('<b>Recursively Prevent:</b> ( '.$message.' )'));
			}
			
			$this->saveApplication($e);
		}
	}
	
	/**
	 * 	Starts the exception handler
	 */ 
	function startExceptionHandler(){
		set_exception_handler(array($this, 'saveApplication'));
	}

	/**
	 * Check if there is composer autoload
	 * @return type
	 */
	function setComposerAutoload(){
		if(file_exists('./vendor/autoload.php')){
			require 'vendor/autoload.php';
		}
	}
}
