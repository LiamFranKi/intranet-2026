<?php
/**
 * 	CrystalTools Framework (http://www.crystaltools.org)
 */ 
 
namespace Core\Parser;

/**
 * 	Resource class.
 * 	Useful for create request for REST applications
 */ 

class Resource{

	/**
	 * 	Create useful url patterns for an application
	 * 
	 * 	@param String $applicationName
	 * 	@return Array
	 */ 
	static function forApplication($applicationName){
		$constraints = array(
			'id' => '\d+'
		);
		
		$pattern = array('^'.$applicationName.'/?', array(
			
			// the index
			array('^$', $applicationName.':index', 'method' => 'GET'),
			
			// the form
			array('^add$', $applicationName.':add', 'method' => 'GET'),
			
			// the form handler
			array('^add$', $applicationName.':do_add', 'method' => 'POST'),
			
			// the update form
			array('^edit/{id}$', $applicationName.':edit', 'constraints' => $constraints, 'method' => 'GET'),
			
			// the update handler
			array('^edit$', $applicationName.':do_edit', 'constraints' => $constraints, 'method' => 'POST'),
			
			// delete
			array('^delete/{id}$', $applicationName.':delete', 'method' => 'GET','constraints' => $constraints),
			
			// view
			//array('^{id}$', $applicationName.':view', 'method' => 'GET','constraints' => $constraints),
		));
		return $pattern;
	}
}
