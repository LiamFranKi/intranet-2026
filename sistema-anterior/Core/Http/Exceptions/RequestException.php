<?php
namespace Core\Http\Exceptions;

use Core\Exception;

class RequestException extends Exception{
	public $statusCode = 400;
}
