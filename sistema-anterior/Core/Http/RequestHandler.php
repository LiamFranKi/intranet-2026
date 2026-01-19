<?php
namespace Core\Http;

use Core\Http\Exceptions\RequestException;
use Core\Parser\Pattern;
use Core\Http\Response;
use Core\Http\RequestMiddleware;
use Core\Middleware;
use Core\InstanceHandler;

class RequestHandler {
	
	/**
	 * 	Request $request
	 * 
	 * 	Object needed to compare patterns
	 */ 
	
	private $request;
	
	/**
	 * 	String $url
	 * 	
	 *  Temporal url for comparison
	 */ 
	
	private $url;
	
	/**
	 * 	Array $url_patterns
	 * 
	 * 	The patterns to compare with the current url
	 */ 
	
	private $urlPatterns = Array();
	
	/**
	 *	Array $match_pattern 
	 * 
	 * 	Save the true pattern if comparison is true
	 */
	
	private $matchPattern;
	
	/**
	 * 	Array $no_match_patterns
	 * 
	 * 	Save the all non match patterns to debug
	 */ 
	
	private $nonMatchPatterns = Array();
	
	/**
	 * 	String $parent_match
	 * 
	 * 	If the patterns needs recursive parse
	 * 	save the previous pattern
	 */ 
	
	private $parentMatch; 

	/**
	 * 	String $current_site
	 * 
	 * 	Save the current site, default value is defined in Settings
	 * 	if there is 'goto' clause this value may change
	 */ 

	private $currentSite;
	private $defaultSite;

	/**
	 * 	Save the url sites that go to another site
	 */ 
	
	private $urlRoots = array();
	
	/**
	 * 	Save the registered sites
	 */ 
	 
	private $sites = array();
	
	/**	
	 * 	Save the main response
	 */ 
	 
	private $response;
	
	/**
	 * 	Constructor 
	 * 
	 * 	@param HttpRequest $request
	 * 
	 * 	Constructor needs the request object to compare the patterns
	 */ 

	function __construct($request, array $sites = array(), $defaultSite = ''){
		$this->request = $request;
		$this->url = $request->getUrl();
		$this->sites = $sites;
		$this->matchExpression = false;
		
		$this->response = InstanceHandler::getInstance("Core\\Http\\Response");
		
		if(!empty($defaultSite)){
			$this->defaultSite = $defaultSite;
			$this->currentSite = $defaultSite;
			$this->urlRoots[$defaultSite] = '/';
		}
	}
	
	/**
	 * 	Sets the url patterns collection or UrlConf File
	 * 	
	 * 	@param Array $patterns	-> The collection of patterns
	 * 	@param String @patterns -> The file where is defined the patterns
	 */ 

	function setUrlPatterns($patterns){
		if(is_string($patterns)){
			if(!file_exists($patterns)){
				throw new RequestException('Missing UrlConf File <code>'.$patterns.'</code>');
			}
			require_once $patterns;
		}else{
			$this->urlPatterns = $patterns;
		}
	}
	
	/**
	 * 	Loads the site UrlConf.php file
	 */ 

	function loadSitePatternsFile(){
		if(empty($this->currentSite)) throw new RequestException('Default site if not defined');
		$file = $this->sites[$this->currentSite]['Applications'].'/UrlConf.php';
		if(!file_exists($file)) throw new RequestException('<code>UrlConf.php</code> file for site <code>'.$this->currentSite.'</code> not exists.');
			require_once $file;
		
	}
	
	/**
	 * 	Compare all patterns defined
	 * 
	 * 	@param Array $patterns -> The collection of patterns
	 * 
	 * 	@return -> true if any patterns match with the current url
	 */ 
	
	function getMatch($patterns = null){
		if(isset($patterns)) $this->setUrlPatterns($patterns);
		if(!is_array($this->urlPatterns) || count($this->urlPatterns) == 0) return false;
		
		$request = new Request($this->url);
		
		foreach($this->urlPatterns As $rh_pattern){
			
			$pattern = new Pattern($request, $rh_pattern[0], array(
				'defaults' => $rh_pattern['defaults'] ?? null,
				'constraints' => $rh_pattern['constraints'] ?? null,
				'response' => $rh_pattern[1] ?? null,
				'method' => isset($rh_pattern['method']) ? $rh_pattern['method'] : 'GET',
			));
			
			$this->nonMatchPatterns[] = $this->parentMatch.' '.$pattern->getNonMatchPattern();
			
			if(isset($rh_pattern['method'])){
				if(strtoupper($rh_pattern['method']) != $request->getMethod()) continue;
			}
			
			if($pattern->match()){

				$this->matchExpression = true;
				
				$this->matchPattern = $pattern;
				
				if(isset($rh_pattern['middleware'])){
					$middleware = $rh_pattern['middleware'];
					/**
					 * 	Use a middleware function to manage the url requests.
					 * 	
					 * 	$middleware = function($matchedPattern, $request, $response){}
					 * 
					 * 	If the middleware function returns (bool) false, 
					 * 	the pattern will be ignored, and will pass to the next pattern.
					 */	 
					
					if($middleware instanceOf \Closure){
						$result = $middleware($pattern, $this->request, $this->response);
						if(is_bool($result) && $result === false){
							$this->matchExpression = false;
							continue;
						}
					}
					
					/**
					 * 	Use a RequestMiddleware object 
					 * 	$middleware = new SomeRequestMiddleware();
					 * 	
					 * 	namespace MySite\Middlewares\SomeRequestMiddleware;
					 * 
					 * 	use Core\Http\RequestMiddleware;
					 * 
					 * 	class SomeMiddleware extends RequestMiddleware{
					 * 		function execute($pattern, $request, $response){
					 * 			// execute middleware
					 * 		}
					 * 	}
					 */ 
					
					if($middleware instanceOf RequestMiddleware){
						if(!method_exists($middleware, 'execute')) throw new RequestException('Middleware <code>'.get_class($middleware).'</code> must have the <code>execute</code> method.');
						$result = $middleware->execute($pattern, $this->request, $this->response);
						if(is_bool($result) && $result === false){
							$this->matchExpression = false;
							continue;
						}
					}
				}
				
				$next_url = (str_replace($pattern->getMatchedPart(), '', $this->url));
				
				/**
				 * 	Go to another site and load the respective UrlConf.php file.
				 * 
				 * 	$pattern = array('^__admin/?', 'goto' => 'Administration');
				 * 
				 * 	This pattern goes to the "Administration" site. The required site must be registered
				 * 	in the $sites var.
				 */ 
				
				if(isset($rh_pattern['goto'])){
					$another_site = $rh_pattern['goto'];
					if(!isset($this->sites[$another_site])) throw new RequestException('Site <code>'.$another_site.'</code> is not registered');
					$this->parentMatch .= ' '.$rh_pattern[0];
					$this->matchExpression = false;
					$this->url = $next_url;
					
					$this->currentSite = $another_site;
					$this->urlRoots[$another_site] = '/'.$pattern->getMatchedPart(); // including the first "/"
					
					return $this->getMatch($this->sites[$another_site]['Applications'].'/UrlConf.php');
				}
				
				/**
				 * 	Includes a file with url patterns. We recommend use the full path.
				 * 
				 * 	$pattern = array('^some_url$', 'include' => '/var/www/some_file.php');
				 * 
				 */ 
				
				if(isset($rh_pattern['include'])){
					$this->parentMatch .= ' '.$rh_pattern[0];
					$this->matchExpression = false;
					$this->url = $next_url;
					
					return $this->getMatch($rh_pattern['include']);
				}
				
				/**
				 * 	Redirect to any url
				 */ 
				
				if(isset($rh_pattern['redirect'])){
					$this->response->redirect($rh_pattern['redirect']);
				}
				
				/**
				 * 	Reparse the url patterns with child patterns defined in the second parameter
				 * 
				 * 	$pattern = array('^parent_url/?', array(
				 * 		array('^child1$'),
				 * 		array('^child2$')
				 * 	));
				 */ 
				
				if(isset($rh_pattern[1]) && is_array($rh_pattern[1])){
					$this->parentMatch .= ' '.$rh_pattern[0];
					$this->matchExpression = false;
					$this->url = $next_url;
					return $this->getMatch($rh_pattern[1]);
				}

				return $this->matchExpression;
			}
		}
		
		return $this->matchExpression;
	}
	
	function getMatchedPattern(){
		return $this->matchPattern;
	}
	
	function getNonMatchPatterns(){
		return $this->nonMatchPatterns;
	}
	
	function getUrlRoots(){
		return $this->urlRoots;
	}
	
	function getUrlRoot($siteName){
		$url = $this->urlRoots[$siteName];
		
		if(!isset($url)) return null;
		if(trim($url) == '/') return $url;
		$url = preg_replace('/\/$/', '', $url);
		return $url;
	}
	
	function getSites(){
		return $this->sites;
	}
	
	function getDefaultSite(){
		return $this->defaultSite;
	}
	
	function getCurrentSite(){
		return $this->currentSite;
	}
	
	function getRequest(){
		return $this->request;
	}

}
