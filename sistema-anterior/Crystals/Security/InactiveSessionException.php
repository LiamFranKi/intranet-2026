<?php
class InactiveSessionException extends Core\Exception{
	function getStatusCode(){
		return 500;
	}

}
