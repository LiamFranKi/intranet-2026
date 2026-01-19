<?php

use Core\Http\Request;
use Core\Http\Response;
use Core\Parser\Pattern;

function match($pattern, Closure $callback, $constraints = array(), $method){
	$request = new Request();
	
	if(trim($pattern) == '/') $pattern = '^$';
	$pattern = new Pattern($request, $pattern, $constraints);
	
	if($pattern->match() && $request->getMethod() == $method){
		$response = new Response();
		$response->write($callback($pattern->getRequestParams(), $response));
		die($response->getBody()); // terminate the request
	}
}

function get($pattern, $callback, $constraints = array()){
	return match($pattern, $callback, $constraints, 'GET');
}

function post($pattern, $callback, $constraints = array()){
	return match($pattern, $callback, $constraints, 'POST');
}

function put($pattern, $callback, $constraints = array()){
	return match($pattern, $callback, $constraints, 'PUT');
}

function delete($pattern, $callback, $constraints = array()){
	return match($pattern, $callback, $constraints, 'DELETE');
}
