<?php
use Core\Initializer;

class {{ SiteName }}Defaults extends Initializer{
	function initialize(){
		$this->hook(function($class){
			$class->set('title', 'Welcome to {{ SiteName }}', true); // adds title to application context
			
		});
	}
}

?>
