<?php
class SecureAction{
	private $class;

	function __construct($class){
		$this->class = $class;
	}

	function process($secure_session, $checkedValue){
		
		$class = $this->class;
		$action = $class->data->action;
		$class->crystal->load('Security:InactiveSessionException');
		
		if(isset($secure_session)){
			
			$secured_actions = explode(',',preg_replace('/ +/', '',implode(',',array_values($secure_session))));
			if(isset($secure_session['__exclude'])){
				$exclude_actions = explode(',',preg_replace('/ +/', '',$secure_session['__exclude']));
				if(in_array($action, $exclude_actions)) return true;
			}
			if(!in_array($action, $secured_actions) && !in_array('*', $secured_actions)) return true;
			$allow = 0;
			foreach($secure_session As $expectedValue => $actions){
				$secured_actions = explode(',',preg_replace('/ +/', '', $actions));
				if(in_array($action, $secured_actions) || in_array('*', $secured_actions)){
					if($expectedValue == $checkedValue){
						$allow++;
					}
				}
			}
			if($allow > 0) return true;
			if(method_exists($class,'__NoSession')){
				$class->__NoSession($class->params);
				die();
			}
			throw new InactiveSessionException('You aren\'t authorized to see this action');
			return false;
		}
		
	}
}