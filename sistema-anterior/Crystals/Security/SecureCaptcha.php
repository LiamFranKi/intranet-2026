<?php
use Core\Middleware;
use Core\Interfaces\Security;

class SecureCaptcha extends Middleware implements Security{
	
	function process_security($secure_captcha){
		return $this->hook(function($class) use($secure_captcha){
			$action = $class->data->action;
			if(isset($secure_captcha)){
				$secured_actions = explode(',',preg_replace('/ +/', '',$secure_captcha));
				if(!in_array($action, $secured_actions) && !in_array('*', $secured_actions)) return true;
				$allow = 0;
				if(in_array($action, $secured_actions) | in_array('*', $secured_actions)){
					if($class->session->captcha == $class->post->codigo){
						return true;
					}
				}
				if(method_exists($class,'InvalidCaptcha')){
					die($class->InvalidCaptcha($class->params));
				}
				return false;
			}
		});
	}
}
?>
