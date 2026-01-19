<?php
//use Core\Parser\Resource;

/**
 * 	Main url patterns file, you can use any from the following examples.
 * 	the pattern must have in the first param the regexp pattern.
 * 	
 *  For	any url the first "/" is omitted.
 * 
 * 	-------------------------------------
 * 	
 * 	// sets the root path to "home" application, and "index" action
 * 
 * 	$pattern = array('^$', 'home:index'); 
 * 
 * 	-------------------------------------
 * 	
 * 	Sets the "/some_url" to "some_application" application and "some_action" action
 * 
 * 	$pattern = array('^some_url$', 'some_application:some_action');
 * 	
 * 	--------------------------------------
 * 
 * 	Also you can capture params from the url.
 * 	The next pattern will match with: "/users/edit/1" - "/users/edit/bob"
 * 
 * 	$pattern = array('^users/edit/{id}$', 'users:edit');
 * 
 * 	You can add limitations in the pattern.
 * 
 * 	$pattern = array('^users/edit/{id}$', 'users:edit', 'constraints' => array('id' => '\d+'));
 * 
 * 	Now the "id" must be a number.
 * 
 * 	--------------------------------------
 * 
 * 	Many times you'll want use the captured params in the response string.
 * 
 * 	$pattern = array('^{application}/{action}$', '{application}:{action}');
 * 	
 * 	For example: 
 * 		
 * 	Url "/users/list" will respond with: application "users", action "list".
 * 	
 * 	-------------------------------------
 * 
 * 	For REST applications you can use the class Resource.
 * 	first include the Resource class in the file.
 * 	
 * 	use Core\Parser\Resource;
 * 	
 * 	$pattern = Resource::forApplication('users');
 * 
 * 	The resource will create the pattern for the following urls.
 * 
 * 	- /users				[GET]
 * 	- /users/add			[GET]
 *  - /users/add			[POST]
 *  - /users/edit/{id}		[GET]
 * 	- /users/edit			[POST]
 * 	- /users/delete/{id}	[GET]
 */ 

$this->setUrlPatterns(array(

	// se recomienda utilizar el sitio de administrador
	// sólo en etapa de desarrollo
	
	array('^__admin/?','goto' => 'Administration'), 
	
	// root
	array('^$', 'home:index'),
    array('^login$', 'usuarios:login'),
	array('^matricula_online$', 'alumnos:matricula_online'),
	
	array('^{Application}/?$','{Application}:index'),
	array('^{Application}/{Action}/?$','{Application}:{Action}'),
	array('^{Application}/{Action}/{id}/?$','{Application}:{Action}'),
));
