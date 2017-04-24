<?php
 
/** Check if environment is development and display errors **/
 
function setReporting() {
if (DEVELOPMENT_ENVIRONMENT == true) {
    error_reporting(E_ALL);
    ini_set('display_errors','On');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors','Off');
    ini_set('log_errors', 'On');
    ini_set('error_log', ROOT.DS.'tmp'.DS.'logs'.DS.'error.log');
}
}
 
/** Check for Magic Quotes and remove them **/
 
function stripSlashesDeep($value) {
    $value = is_array($value) ? array_map('stripSlashesDeep', $value) : stripslashes($value);
    return $value;
}
 
function removeMagicQuotes() {
if ( get_magic_quotes_gpc() ) {
    $_GET    = stripSlashesDeep($_GET   );
    $_POST   = stripSlashesDeep($_POST  );
    $_COOKIE = stripSlashesDeep($_COOKIE);
}
}
 
/** Check register globals and remove them **/
 
function unregisterGlobals() {
    if (ini_get('register_globals')) {
        $array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');
        foreach ($array as $value) {
            foreach ($GLOBALS[$value] as $key => $var) {
                if ($var === $GLOBALS[$key]) {
                    //unset($GLOBALS[$key]);
                }
            }
        }
    }
}
 

/** Routing **/

function routeURL($url) {
	
	global $routing;

	foreach ( $routing as $pattern => $result ) {
		if ( preg_match( $pattern, $url ) ) {
			return preg_replace( $pattern, $result, $url );
		}
	}

	return ($url);
}

/** Main Call Function **/
 
function callHook() {
    global $url;

	RouteSystem($url);
}

function RouteSystem($url)
{
	global $default;
	
	//echo "URL: $url  <BR>";
	$queryString = array();

	
	if (!isset($url)) {
		$controller = $default['controller'];
		$action = $default['action'];
	}
	else
	{
		$url = routeURL($url);
				
		$urlArray = array();
		$urlArray = explode("/",$url);
		
		$folder = FOLDER;
		$controllerIndex = 0;
	
		//print_r($urlArray);
		
		
		$controller = trim($urlArray[$controllerIndex]);
		
		
		if(strlen($controller) < 1 && count($urlArray) > 1)
		{
			array_shift($urlArray);
			$controller = trim($urlArray[$controllerIndex]);
		}
		
		
		array_shift($urlArray);
		if (isset($urlArray[$controllerIndex]))
		{
			$action = $urlArray[$controllerIndex];
			array_shift($urlArray);
		}
		else
		{
			$action = 'index'; // Default Action
		}
		$queryString = $urlArray;
		
			// ***** FOR POSTED JSON ****
	$postdata = file_get_contents("php://input");
	$queryParams = (array)json_decode($postdata);
	
	if(count($queryParams) > 0)
	{
		foreach($queryParams as $k => $v)
		{
			$queryString[$k] = $v;
		}
	}
	
	//  GET QUERY PARAMETERS
	$QUERYPARAMETERS = array();
	$urlParse = parse_url($_SERVER['REQUEST_URI']);
	if(isset($urlParse['query']))
	{						
		parse_str($urlParse['query'], $QUERYPARAMETERS);	                
        if(count($QUERYPARAMETERS) > 0)
        {
        	foreach($QUERYPARAMETERS as $k => $v)
        	{
        		$queryString[$k] = $v;
        	}
        }
	}
	
	
	//print_r($queryParams);
	//print_r($queryString);
	}
	
	$controller = ucwords($controller);
	
	LoadController($controller, $action, $queryString, $url);	
}

function LoadController($controller, $action, $queryString, $url = "")
{
	if($controller == null)
	{
		$controller = "Home";
	}
	
	$controllerName = $controller;
	$controller .= 'Controller';
	
	if($action == null)
	{
		$action = "index";
	}

	if(	class_exists($controller))
	{
		$dispatch = new $controller($controllerName,$action);
		
		global $url;
		$dispatch->setUrl($url);
		$dispatch->SetQueryParams($queryString);
		
		if ((int)method_exists($controller, $action))
		{
			 
			//AUTHENTICATION
			$authorized = call_user_func(array($dispatch,"Authenticate"));
			
			if($authorized->Success)
			{				
				//FILTER
				$filterResult = call_user_func_array(array($dispatch,"Filter"), array('actionName' => $action));
				$fr = $filterResult->Success;
					
				if($fr)
				{
					//BEFORE ACTION
					call_user_func_array(array($dispatch,"beforeAction"),$queryString);
						
						
					// ** REORDER $queryString for simulated binding by name
					$refm = new ReflectionMethod($controller, $action);
					
					$orderedQueryString = array();
					$inQueryString = array();
					$outQueryString = array();
					
					//print_r($queryString);
					//print "\n";
					foreach ($refm->getParameters() as $k=> $p)
					{
						if(array_key_exists($p->name, $queryString))
						{
							$inQueryString[$p->name] = $queryString[$p->name];
						}
					} 
					foreach($queryString as $k => $p)
					{
						if(!array_key_exists($k, $inQueryString))
						{
							$outQueryString[$k] = $p;
						}
					}
   					 
   					 //print "\n";
   					 //print_r($inQueryString);
   					 //print "\n";
   					 //print_r($outQueryString);
   					 
   					 // -- CREATE FINAL ORDERED ARRAY
   					foreach ($refm->getParameters() as $k=> $p)
					{
						if(array_key_exists($p->name, $inQueryString))
						{
							$orderedQueryString[$p->name] = $inQueryString[$p->name];
							unset($inQueryString[$p->name]);
						}
						else
						{
							// Named parameter not found in query string, so using first misc query string parameter
							if(count($outQueryString) > 0)
							{
								$orderedQueryString[$p->name] = array_shift($inQueryString);	
							}
							else
							{
								//no misc query string parameters
								$orderedQueryString[$p->name] = null;
							}
						}
					} 
   					 
   					 //print_r($orderedQueryString);
					
						
					// ** CALLED ACTION **
					call_user_func_array(array($dispatch,$action),$orderedQueryString);
					 
					//AFTER ACTION
					call_user_func_array(array($dispatch,"afterAction"),$queryString);
				}
				else
				{
					// Filter Redirect
			
					$newControllerName = $filterResult->RedirectController;
					$newActionName = $filterResult->RedirectAction;
			
					$dispatch = LoadController($newControllerName, $newActionName, $queryString, $url);
				}
			}
			else
			{
				// Authorize Redirect
				
				$newControllerName = $authorized->RedirectController;
				$newActionName = $authorized->RedirectAction;
				
				$dispatch = LoadController($newControllerName, $newActionName, $queryString, $url);			
			}
		}
		else
		{	
			//Class exists but action does not
			/* Redirect to 404 */
			//echo "Controller: $controllerName Action: $action <BR>";			
			include (ROOT . DS . 'application' . DS . 'views' . DS . NOT_FOUND_PAGE . '.php');
		}
	}
	else
	{
		//Controller doesn't exist
		//echo "NO CONTROLLER <BR>";
		//echo "Cannot find class: $controller";
		include (ROOT . DS . 'application' . DS . 'views' . DS . NOT_FOUND_PAGE . '.php');
	}	
}
 
/** Autoload any classes that are required **/
 
function __autoload($className) {
    if (file_exists(ROOT . DS . 'library' . DS . $className . '.class.php')) {
        require_once(ROOT . DS . 'library' . DS . $className . '.class.php');
	}else if(file_exists(ROOT . DS. 'library' . DS . strtolower($className) . '.class.php')) {
		require_once(ROOT . DS. 'library' . DS . strtolower($className) . '.class.php');
    } else if (file_exists(ROOT . DS . 'application' . DS . 'controllers' . DS . $className . '.php')) {
        require_once(ROOT . DS . 'application' . DS . 'controllers' . DS . $className . '.php');
	} else if (file_exists(ROOT . DS . 'application' . DS . 'controllers' . DS . strtolower($className) . '.php')) {
        require_once(ROOT . DS . 'application' . DS . 'controllers' . DS . strtolower($className) . '.php');
    } else if (file_exists(ROOT . DS . 'application' . DS . 'models' . DS . $className . '.php')) {
        require_once(ROOT . DS . 'application' . DS . 'models' . DS . $className . '.php');
	} else if (file_exists(ROOT . DS . 'application' . DS . 'models' . DS . strtolower($className) . '.php')) {
        require_once(ROOT . DS . 'application' . DS . 'models' . DS . strtolower($className). '.php');
    } else {
		/* Error Generation Code Here */
    }
}

 
setReporting();
//removeMagicQuotes();
//unregisterGlobals();
callHook();

