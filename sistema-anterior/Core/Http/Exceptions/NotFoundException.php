<?php
namespace Core\Http\Exceptions;

use Core\Exception;

class NotFoundException extends Exception{
	public $statusCode = 404;
}
