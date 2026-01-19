<?php
/**
 * 	CrystalTools Framework (http://www.crystaltools.org)
 */ 
 
namespace Core\Parser;

class Pattern{
	
	/**
	 * 	Regular expresion to capture values, this can be replaced in the constraints option.
	 * 	
	 * 	{Key} => Key must match with the expresion.
	 * 
	 * 	@var String
	 */ 
	 
	private $varExpression = '[\w|_|-]+';
	
	
	/**
	 * 	Cleaned url patterns.
	 * 	
	 *	@var String
	 */ 
	private $urlPattern = array();
	
	/**
	 * 	The additional options.
	 * 
	 * 	constraints, defaults, etc.
	 * 
	 * 	@var Array
	 */ 
	private $options;
	
	/**
	 * 	The request neccesary for get the current URI.
	 * 	
	 * 	@var Core\Http\Request
	 */ 
	private $request;
	
	
	/**
	 * 	Constructor
	 * 	
	 * 	@param Core\Http\Request $request
	 * 	@param String $urlPattern
	 * 	@param Array $options
	 */ 
	function __construct($request, $urlPattern, array $options = array()){
		$this->request = $request;
		$this->options = $options;
		$this->urlPattern = $this->cleanPattern($urlPattern);
	}
	
	/**
	 * 	Separate the url pattern in: the cleaned pattern and non match pattern (for debug).
	 * 	
	 * 	@param String $urlPattern
	 * 	@return Array($cleanedPattern, $nonMatchPattern)
	 */ 
	function cleanPattern($urlPattern){
		$pattern_expression = $urlPattern;
		$no_match_patterns = $urlPattern;
		$varExpression = $this->varExpression;
		
		if(preg_match_all('/{([\w|_]+)}/', $pattern_expression, $r)){
			foreach($r[1] As $key=>$val){
				if(isset($this->options['constraints'][$val])){
					$varExpression = $this->options['constraints'][$val];
				}else{
					$varExpression = $this->varExpression;
				}
				
				$no_match_patterns = str_replace('{'.$val.'}','{'.$val.'}('.$varExpression.')', $no_match_patterns);
				$pattern_expression = str_replace('{'.$val.'}', '(?P<'.$val.'>'.$varExpression.')', $pattern_expression);
			}
		}
		
		$pattern_expression = str_replace('/', '\/', $pattern_expression);
		$pattern_expression = '/'.$pattern_expression.'/i';
		
		if(isset($this->options['method']))
			$no_match_patterns .= ' - ['.$this->options['method'].']';
		
		
		return Array($pattern_expression, $no_match_patterns);
	}
	
	/**
	 * 	Return the cleaned pattern
	 * 
	 *	@return String
	 */
	function getUrlPattern(){
		return $this->urlPattern[0];
	}
	
	/**
	 *	Return non matched pattern (for debug)
	 * 
	 * 	@return String
	 */  
	function getNonMatchPattern(){
		return $this->urlPattern[1];
	}
	
	/**
	 * 	Return the captured values from the request URI for example:
	 * 	
	 * 	$pattern = '^users/edit/{id}$';
	 * 	$requestURI = '/users/edit/5';
	 * 
	 * 	Request params will be:
	 * 	
	 * 	stdClass Object([id] => 5);
	 * 	
	 * 	@return stdClass
	 */ 
	function getRequestParams(){
		$params = array();
		
		if(preg_match_all($this->urlPattern[0], $this->request->getUrl(), $r)){
			
			if(isset($this->options['defaults']) && is_array($this->options['defaults'])){
				$params = array_merge($this->options['defaults'], $params);
			}
			
			foreach($r As $key => $val){
				if(is_string($key)){
					$params[$key] = $r[$key][0]; 
				}
			}
		}
		
		return (object) $params;
	}
	
	/**
	 * 	Returns true if request URI match with the pattern
	 * 
	 * 	@return Boolean
	 */ 
	function match($callback = null){
		if(preg_match_all($this->urlPattern[0], $this->request->getUrl(), $r)){
			return true;
		}
		return false;
	}
	
	/**
	 * 	Get the matched part between the pattern and the request URI
	 * 
	 * 	@return String|null
	 */ 
	function getMatchedPart(){
		if(preg_match($this->urlPattern[0], $this->request->getUrl(), $r)){
			return $r[0];
		}
		return null;
	}
	
	/**
	 *  Add params to the request params
	 * 	
	 * 	@param String $key
	 * 	@param String $value
	 */ 
	function addParam($key, $value){
		$this->options['defaults'][$key] = $value;
	}
	
	/**
	 * 	Return the request
	 * 	
	 * 	@return Core\Http\Request
	 */ 
	function getRequest(){
		return $this->request;
	}
	
	/**
	 * 	Return any option defined
	 * 	
	 * 	@param String $key
	 * 	@return Mixed 
	 */ 
	function getOption($key){
		return $this->options[$key];
	}
}
