<?php
/**
 * 	CrystalTools Framework (http://www.crystaltools.org)
 */ 
 
namespace Core;

use Core\Middleware;

class Scaffold extends Middleware implements \Core\Interfaces\Scaffold{
	
	/**
	 * 	The application's site
	 * 
	 * 	@var String $site
	 */ 
	private $site;
	
	/**
	 * 	The model columns
	 * 
	 * 	@var Array $columns
	 */ 
	protected $columns;
	
	/**
	 * 	The template's context
	 * 	
	 * 	@var Array $context
	 */ 
	protected $context = Array();
	
	/**
	 * 	Loader of templates
	 * 	
	 * 	@var Twig_Environment
	 */ 
	
	private $loader;
	
	/**
	 * 	Sets and Gets the context
	 * 
	 * 	@param String $key
	 * 	@param Mixed $val
	 * 	@return Mixed 
	 */ 
	function context($key, $val = null){
		if(!isset($val)) return $this->context[$key];
		$this->context[$key] = $val;
	}
	
	/**
	 * 	Returns the scaffold name
	 * 
	 * 	@return String
	 */ 
	function getScaffoldName(){
		return trim(str_replace('Scaffold','', get_class($this)));
	}
	
	/**
	 *  Return the columns from the table
	 * 
	 * 	@return Array
	 */
	function getColumns(){
		return $this->columns;
	}
	
	/**
	 * 	Returns the application directory
	 * 
	 * 	@return String
	 */ 
	function getApplicationDirectory(){
		return $this->settings->sites[$this->post->site]['Applications'].'/'.$this->getApplicationName();
	}
	
	/**
	 * 	Returns the application name
	 * 
	 * 	@return String
	 */ 
	function getApplicationName(){
		return $this->context['Name'];
	}
	
	/**
	 * 	Constructor
	 */ 
	function __construct(){
		parent::__construct();
		// creates the template loader
		$this->loader = __TemplateLoader($this);
		
		// set the names
		$context['Name'] = $this->post->name; // The name of application
		$context['Model'] = $this->post->model; // The name of model

		// sets all columns
		$this->columns = $context['Model']::table()->columns;
		$this->context = $context;
	}
	
	/**
	 *	@overridable
	 * 	Define how create the application file
	 */
	function CreateApplicationFile(){}
	
	/**
	 * 	@overridable
	 * 	Define how create the templates
	 */ 
	function CreateTemplates(){}
	
	/**
	 * 	Renders the template
	 * 
	 * 	@param String $from -> The template which we use, the route if prefixed with Scaffold Template Directory
	 * 	@param String $to -> The result file, the route is prefixed with ApplicationDirectory
	 */ 
	function render($from, $to){
		$template = $this->loader->loadTemplate('ScaffoldTemplates/'.$this->getScaffoldName().'/Templates/'.$from);
		file_put_contents($this->getApplicationDirectory().'/'.$to, $template->render($this->context));
	}
	
}
