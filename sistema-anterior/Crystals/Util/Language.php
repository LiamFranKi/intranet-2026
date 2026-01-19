<?php
/**
 * 	CrystalTools Framework (http://www.crystaltools.org)
 */ 
 
use Core\Middleware;
use Core\Exception;

class Language extends Middleware{
	
	/**
	 * 	The default language
	 * 
	 * 	@var String
	 */
	public $default;
	
	/**
	 * 	The language files directory
	 * 
	 * 	@var String
	 */ 
	public $directory;
	
	/**
	 * 	The language messages
	 * 
	 * 	@var Array
	 */ 
	public $messages = Array();
	
	/**
	 * 	The language session name
	 * 
	 * 	@var String
	 */ 
	public $session_name;

	/**
	 * 	Set a message
	 * 
	 * 	@param String $key
	 * 	@param String $message
	 */ 
	function message($key, $message = null){
		if(!isset($message)){
			return $this->messages[$key];
		}
		$this->messages[$key] = $message;
	}
	
	/**
	 * 	Sets the language messages
	 * 
	 * 	@param Array messages 
	 */ 
	function setMessages($messages = Array()){
		$this->messages = array_merge($this->messages,$messages);
	}
	
	/**
	 * 	Starts the language handler
	 * 	if param $lang is defined, forces initialize with this language.
	 * 
	 * 	@param String $lang
	 */ 
	function start($force_lang = null){

		$session_name = $this->session_name;
		$lang = isset($force_lang) ? $force_lang : $this->default;
		
		// try to get the session value
		// if session not exists create new session
		if(isset($force_lang)) $this->session->$session_name = $lang;
		if(!$this->session->active($session_name)){
			$this->session->$session_name = $lang;
		}
		// loads the file with messages definition
		
		$this->load_messages_file();
		
		// adds messages to default context from application
		
		foreach($this->messages As $key=>$val){
			$this->getApplicationObject()->context($key, $val);
		}
	}
	
	/**
	 * 	Change the active language
	 * 	
	 * 	@param String $lang
	 */ 
	function change($lang){
		$this->session->set($this->session_name, $lang);
		$this->start();
	}
	
	/**
	 * 	Returns the active language
	 * 
	 * 	@return String
	 */ 
	function getActive(){
		return $this->session->get($this->session_name);
	}

	/**
	 * Loads the file with messages definition
	 */
	function load_messages_file(){
		$file = $this->directory.'/'.$this->getActive().'.php';
		if(!file_exists($file)){
			throw new Exception('El archivo de idioma <code>'.$file.'</code> no existe');
		}
		require $file;
	}

	/**
	 *	Configure the language manager 
	 * 	
	 * 	@param Array $settings
	 */ 
	function configure($settings){
		$settings = array_merge(Array(
			'default' => 'es', 
			'directory' => './language', 
			'session' => 'language'
		), $settings);
		
		$this->directory = $settings['directory'];
		$this->default = $settings['default'];
		$this->session_name = $settings['session'];
	}
	
	/**
	 * 	Load the language messages from the database
	 */ 
	function loadFromDB($model = null){
		if(!isset($model)) $model = 'LanguageFromDB';
		$messages = $model::find_all_by_language($this->get_active());
		foreach($messages As $message){
			$this->getApplicationClass()->context($message->key, $message->val);
		}
	}
	
}
?>
