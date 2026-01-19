<?php
/**
 * 	CrystalTools Framework (http://www.crystaltools.org)
 */ 
 
namespace Core;

use Core\Http\Exceptions\NotFoundException;

use Twig_Loader_Filesystem;
use Twig_Environment;

class Template extends Middleware{
	/**
	 * 	The params sended from render()
	 * 	
	 * 	@var Array
	 */ 
	private $params;
	
	/**
	 * 	The template environment
	 * 
	 * 	@var Twig_Environment
	 */ 
	private $twig;
	
	/**
	 * 	The template data
	 */ 
	private $template, $template_name;
	
	/**
	 * 	The template context
	 * 
	 * 	@var Array
	 */ 
	private $context;
	
	/**
	 * 	Constructor
	 * 
	 * 	@param Core\Application $class
	 * 	@param Array $params
	 */ 
	function __construct($class, $params){
		parent::__construct($class);
		if(is_array($params)){
			switch(count($params)){
				case 0:
					$data = $this->getTemplateFromString();
					$this->context = $this->getApplicationClass()->getContext();
				break;
				
				case 1:
					if(is_string($params[0])){
						$data = $this->getTemplateFromString($params[0]);
						$this->context = $this->getApplicationClass()->getContext();
					}	
					if(is_array($params[0])){
						$data = $this->getTemplateFromString();
						$this->context = array_merge($this->getApplicationClass()->getContext(), $params[0]);
					}
				break;
				
				case 2:
					$data = $this->getTemplateFromString($params[0]);
					$this->context = array_merge($this->getApplicationClass()->getContext(), $params[1]);
				break;
			}
		}
		
		/*if(is_string($params)){
			if(!file_exists($params)) throw new NotFoundException('Missing Template <code>'.$params.'</code>');
			$template_directory = dirname($params);
			$this->template = $params;
			$this->template_name = basename($params);
		}*/
	
		$template_directory = $this->settings->sites[$data['site']]['Applications'].'/'.$data['application'].'/views';
		
		$this->template = $template_directory.'/'.$data['action'];
		$template_info = pathinfo($this->template);
		
		$extension = 'php'; // sets the default extension
		
		if(isset($class->viewsExtension)) $extension = $class->viewsExtension;
		$extension = !isset($template_info['extension']) ? $extension : $template_info['extension'];
		
		$this->template = dirname($this->template).'/'.$template_info['filename'].'.'.$extension; 
		$this->template_name = basename($this->template);

		$layout_directory = $this->settings->sites[$data['site']]['Layout'];
		
		if(!file_exists($template_directory)) mkdir($template_directory, 0777, true);
		$template_loader = new Twig_Loader_Filesystem(Array($template_directory,$layout_directory));
		//$cache = $this->settings->environment == 'Production' ? $this->settings->cache_directory.'/' : false;
		$cache = false;
		
		$this->twig = new Twig_Environment($template_loader, array(
			'cache'=> $cache
		));
	}
	
	/**
	 * 	Render the result
	 * 
	 */ 
	function render(){
		if(!file_exists($this->template)) throw new NotFoundException('Missing template <code>'.$this->template.'</code>');
		
		foreach($this->getApplicationClass()->getExtensions() As $extension){
			$this->twig->addExtension($extension);
		}
		$template = $this->twig->loadTemplate($this->template_name);
		echo $template->render($this->context);
	}
	
	/**
	 * 	Returns template data from string
	 * 
	 * 	@param String $str
	 * 	@return Array
	 */ 
	function getTemplateFromString($str = null){
		$data = array();
		if(!isset($str)){
			$data['site'] = $this->getApplicationClass()->data->site;
			$data['application'] = $this->getApplicationClass()->data->application;
			$data['action'] = $this->getApplicationClass()->data->action;
			return $data;
		}
		
		$ss = explode(':', $str);
		
		switch(count($ss)){
			case 1:
				$data['site'] = $this->getApplicationClass()->data->site;
				$data['application'] = $this->getApplicationClass()->data->application;
				$data['action'] = $ss[0];
			break;
			
			case 2:
				$data['site'] = $this->getApplicationClass()->data->site;
				$data['application'] = $ss[0];
				$data['action'] = $ss[1];
			break;
			
			case 3:
				if(!isset($this->settings->sites[$ss[0]])) throw new Exception('Site <code>'.$ss[0].'</code> is not registered');
				$site = $ss[0];
				$data['application'] = $ss[1];
				$data['action'] = $ss[2];
			break;
			
			default:
				throw new Exception('Invalid template definition');
			break;
		}
		
		return $data;	
	}
}
?>
