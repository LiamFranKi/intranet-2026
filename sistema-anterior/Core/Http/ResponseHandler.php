<?php
namespace Core\Http;

use Core\Parser\Pattern;
use Core\InstanceHandler;
use Core\Http\Exceptions\NotFoundException;
use Core\Http\Exceptions\RequestException;
use Core\Application;


class ResponseHandler{
	
	private $pattern;
	private $requestHandler;
	private $sites = array();
	private $responseString;
	
	/**	
	 * 	Save the main response
	 */ 
	 
	private $response;
	private $data;
	
	function __construct($requestHandler){
		if(is_array($requestHandler)) return $this->initFromArray($requestHandler);
		$this->requestHandler = $requestHandler;
		$this->pattern = $requestHandler->getMatchedPattern();
		$this->sites = $requestHandler->getSites();
		$this->response = InstanceHandler::getInstance("Core\\Http\\Response");
		$this->responseString = $this->pattern->getOption('response');
		$this->data = $this->getResponseData();
		
	}
	
	function initFromArray($params){
		$this->sites = $params['sites'];
		$this->responseString = $params['response'];
		
		$this->response = InstanceHandler::getInstance("Core\\Http\\Response");
		$this->params = $params['params'];
		
		$this->defaultSite = $params['default_site'];
		
		$this->data = $this->getResponseData();
	}
	
	private function getApplicationFile(){
		$file = $this->sites[$this->data->site]['Applications'].'/'.$this->data->application.'/index.php';
		if(!file_exists($file)){
			throw new NotFoundException('Missing Application File: <code>'.$file.'</code>');
		}
		
		require_once $file;	return $file;
	}
	
	private function getApplicationObject(){
		$file = $this->getApplicationFile();
		
		$class_name = ucwords($this->data->application).'Application';
		$application_name = $class_name;
		$namespace = $this->sites[$this->data->site]['Namespace'] ?? null;
		
		if(isset($namespace)){
			if(is_bool($namespace) && $namespace === true) $namespace = $this->data->site;
			$class_name = $namespace.'\\Applications\\'.$class_name;
		}
		
		if(!class_exists($class_name)){
			
			throw new NotFoundException('Application Class <code>'.$class_name.'</code> not found in <code>'.$file.'</code>');
		}
		
		$application_object = new $class_name($this->data);
		return $application_object;
	}
	
	function sendResponse(){

		//$application_object = is_string($opt_response) ? $this->getApplicationObject($opt_response) : ($opt_response instanceOf Application ? $opt_response : null);
		$application_object = $this->getApplicationObject();
		
		if(is_null($application_object)){
			throw new NotFoundException('The pattern <code>'.$this->pattern->getNonMatchPattern().'</code> must have a valid response.');
		}
		
		$action = $this->data->action;
		$this->response->executeHeaders();
		
		if(!method_exists($application_object, $action)){
			echo $application_object->render($action, $application_object->getContext());
		}else{
			$application_object->$action((object) $this->data->params);
		}
		
		return $application_object;
	}
	
	/**
	 * 	@return the final response data
	 * 	The request pattern must match for call this method
	 */ 
	
	function getResponseData(){
		
		if(isset($this->pattern)) {
			$params = $this->pattern->getRequestParams();
			
			foreach($params As $key => $val){
				if(is_string($val)){
					$this->responseString = str_replace('{'.$key.'}', $val, $this->responseString);
				}
			}
		}
		
		if(isset($this->params)) $params = $this->params;
		
		$ss = explode(':', $this->responseString);
		$data = array();

		switch(count($ss)){
			case 2:
				$data['site'] = isset($this->requestHandler) ? $this->requestHandler->getCurrentSite() : $this->defaultSite;
				$data['application'] = $ss[0];
				$data['action'] = $ss[1];
			break;
			
			case 3:
				$data['site'] = $ss[0];
				$data['application'] = $ss[1];
				$data['action'] = $ss[2];
			break;
			
			default:
				throw new RequestException('Invalid response string: <code>'.$this->responseString.'</code>');
			break;
		}
		
		if(!isset($this->sites[$data['site']])) throw new RequestException('Site <code>'.$data['site'].'</code> is not registered');
		$data['params'] = $params;
		
		if(isset($this->requestHandler)){
			$data['method'] = $this->requestHandler->getRequest()->getMethod();
			
			$data['full_url'] = $this->requestHandler->getRequest()->getFullUrl();
			$data['site_url'] = $this->requestHandler->getUrlRoot($data['site']);
		}
		
		return (object) $data;
	}
	
}
