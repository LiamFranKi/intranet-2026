<?php
class Assets extends Twig_Extension{
	private $class;
	private $settings;
	private $site;
	public $style, $script, $image, $vendor;
		
	function __construct($class){
		$this->class = $class;
		$this->settings = $class->settings;
		$this->site = $class->getData('site');
		$this->set_default_assets();
	}
	
	function set_default_assets(){
		$this->style = INSTALL_DIRECTORY.$this->getDirectory('stylesheets');
		$this->script = INSTALL_DIRECTORY.$this->getDirectory('javascripts');
		$this->image = INSTALL_DIRECTORY.$this->getDirectory('images');
		$this->vendor = $this->getDirectory('vendor');
	}
	
	function getDirectory($type){
		if(!isset($this->settings->sites[$this->site]['Assets'])){
			return $this->settings->assets[$type];
		}
		return $this->settings->sites[$this->site]['Assets'][$type];
	}
	
	function getFunctions(){
		return Array(
			'asset' => new Twig_Function_Method($this, 'asset'),
			'css' => new Twig_Function_Method($this, 'css'),
			'js' => new Twig_Function_Method($this, 'js'),
			'img' => new Twig_Function_Method($this, 'img'),
			'icon' => new Twig_Function_Method($this, 'icon'),
			'vendor' => new Twig_Function_Method($this, 'vendor'),
			'call' => new Twig_Function_Method($this, 'call'),
			'render' => new Twig_Function_Method($this, 'render'),
		);
	}
	
	function css($cssFile = null,$params = null, $directory = null){
		if(!preg_match('/^https?:\/\//', $cssFile)){
			$directory = !isset($directory) ? $this->style : $directory;
			$cssFile = $directory.'/'.$cssFile.'.css';
		}
		
		if($cssFile){
			echo '<link href="'.$cssFile.'" type="text/css" rel="stylesheet" '.$params.' />';
		}
	}
	
	function js($jsFile = null,$params = null, $directory = null){
		if(!preg_match('/^https?:\/\//', $jsFile)){
			$directory = !isset($directory) ? $this->script : $directory;
			$jsFile = $directory.'/'.$jsFile.'.js';
		}
		if($jsFile){
			echo '<script type="text/javascript" src="'.$jsFile.'" '.$params.'></script>';
		}
	}
	
	function img($imgFile = null,$params=null){
		if(!preg_match('/^https?:\/\//', $imgFile)){
			$directory = !isset($directory) ? $this->image : $directory;
			$imgFile = $directory.'/'.$imgFile;
		}

		if($imgFile){
			echo '<img src="'.$imgFile.'" '.$params.'/>';
		}
	}
	
	function icon($icon, $print = true){
		$data = '<img src="/Static/img/icons/'.$icon.'.ico" data-rel="icon" class="x-icon" />';
		if(!$print) return $data;
		echo $data;
	}
	
	function vendor($package){
		$vendor_package = $this->vendor[0].'/'.$package.'/package.json';
		
		if(!file_exists($vendor_package)) return false;
		
		$data = json_decode(file_get_contents($vendor_package));
		
		// loads all stylesheets
		
		if(count($data->css) > 0){
			foreach($data->css As $key=>$val){
				$this->css($val, null, INSTALL_DIRECTORY.$this->vendor[1].'/'.$package);
				echo "\n";
			}
		}
		
		// loads all javascripts
		
		if(count($data->js) > 0){
			foreach($data->js As $key=>$val){
				$this->js($val, null, INSTALL_DIRECTORY.$this->vendor[1].'/'.$package);
				echo "\n";
			}
		}
	}
	
	// all an action from template
	
	function call($application, $params = null){
		$this->class->call($application, $params);
	}
	
	// call a template from other template
	
	function render($view, $context = Array()){
		$this->class->render($view, $context);
	}
	
	function getName(){
		return 'Assets';
	}
}
