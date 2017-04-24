<?php
class Template {
     
    protected $variables = array();
    protected $_controller;
    protected $_action;
    
    protected $templateVars = array();
     
    function __construct($controller,$action) {
        $this->_controller = $controller;
        $this->_action = $action;
    }
 
    /** Set Variables **/
 
    function set($name,$value) {
        $this->variables[$name] = $value;
    }
    
    public function startsWith($haystack, $needle)
	{
     $length = strlen($needle);
     return (substr($haystack, 0, $length) === $needle);
	}
    
    function CustomTemplateReplace($templateResult)
    {
    	
	
    	// **** Customer Template Varibles ***
     	$templateResult = preg_replace_callback("/@(.*?)@/im",     
    	function($m) {
    		
    		$foundValue = $m[1];
    		
    		if (strpos($foundValue, ':=') !== FALSE)
    		{
    			$tempVar = explode(":=", $foundValue);
    			$this->templateVars[trim($tempVar[0])] = trim($tempVar[1]);    			
    			return "";
    		}
    		
			return $m[0];
    	}, $templateResult);   	
    	
    	
    	//  ****    Custom Template Replace ****
    	
    	
    	$templateResult = preg_replace_callback("/@(.*?)@/im",     
    	function($m) {
    	
    		//print $m[1]. "<BR>";
    		
    		#HTML Functions
    		if($this->startsWith($m[1], "["))
    		{
    			//print "HELLO <BR>";
    			
    			$closingBracket = strpos($m[1], "]");
    			$evalStr = substr($m[1], 1, $closingBracket-2);
    			extract($this->variables);
    			ob_start();
    			eval($evalStr);
    			$resultStr = ob_get_contents();
			ob_end_clean();
				//print "Result str: ". $resultStr . "<BR>";
    			return $resultStr;
    		}
    		
    		if($this->startsWith($m[1], "HTML") || $this->startsWith($m[1], "Html"))
    		{   			
    			$codeSnipit = $m[1];
    			
    			# Get Function Name
    			if(preg_match("/\.(.*?)\(/im", $codeSnipit, $matches))
    			{
    				$HtmlFuncName = $matches[1];
    				//echo "HTML ACTION ". $HtmlFuncName . " CALLED <BR>";	
    				
    				# Get Passed Value 
    				$args = "";
    				if(preg_match("/\((.*?)\)/im", $codeSnipit, $argMatches))
    				{
    					$args = $argMatches[1];
    				}
    				
    				//echo "Agruments: " .$args . "<BR>";
    				
    				$agrsArray = explode(',', $args);
    				
    				//print_r($agrsArray);
    				
    				#---ActionLink---
    				if($HtmlFuncName == "ActionLink")
    				{    					
    					return HTML::ActionLinkTemplate($agrsArray[0], $agrsArray[1], $agrsArray[2]);
    				}

    			}
    			
    			return "";
    		}
    		
    		# Prop Vars
    		if (strpos($m[1], '.') !== FALSE)
    		{
    			$returnVar = "";
    			
    			$propArray = explode('.', $m[1]);
    			
    			$clName = $propArray[0];
    			
    			if(isset($this->variables[$clName]))
    			{
    				//echo "got tree 1 <BR>";
    				
    				$returnVar = $this->variables[$propArray[0]];
    				if(isset($returnVar->{$propArray[1]}))
    				{
    					//echo "got tree 2 <BR>";
    					
    	    			$returnVar = $returnVar->{$propArray[1]};	
    	    						
    					if(count($propArray) >= 3)
    					{
    						if(isset($returnVar->{$propArray[2]}))
    						{
    							$returnVar = $returnVar->{$propArray[2]};
    						}
    						else
    						{
    							$returnVar = "";
    						}
    					}
    					

    				}
    				else
    				{
    					$returnVar = "";
    				}
    			}
    			
    			return $returnVar;
    			
    		}
    		
    		# Quick Var Output
    		if(isset($this->variables[$m[1]]))
    		{
    			
    			return $this->variables[$m[1]];
    		}
    		else
    		{
    			//print $m[1]. "<BR>";
    			//print_r($this->templateVars);
    			if(isset($this->templateVars[$m[1]]))
    			{
    				return $this->templateVars[$m[1]];
    			}

    		}
    		
			return "@";
    	}, $templateResult);
    	
    	return $templateResult;
    }
    
    function renderByName($viewName)
    {
            extract($this->variables);
         ob_start();
            if (file_exists(ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'header.php')) {
                include (ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'header.php');
            } else {
                include (ROOT . DS . 'application' . DS . 'views' . DS . 'header.php');
            }
 
        include (ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $viewName . '.php');       
             
            if (file_exists(ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'footer.php')) {
                include (ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'footer.php');
            } else {
                include (ROOT . DS . 'application' . DS . 'views' . DS . 'footer.php');
            }
          $templateResult = ob_get_clean(); 
          ob_end_flush();
           
          echo $this->CustomTemplateReplace($templateResult);
    }
    
    function renderPartialByName($viewName)
    {
    	extract($this->variables);
    	ob_start();
    	if(file_exists(ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $viewName . '.php'))
    	{
    		include (ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $viewName . '.php');
    	}
    	else
    	{
    		// shared view in root view folder    	
    		include(ROOT . DS . 'application' . DS . 'views' . DS . $viewName . '.php');   		 
    	}   	
          $templateResult = ob_get_clean(); 
           
         echo $this->CustomTemplateReplace($templateResult);
    }
    
    /** Display Template **/
     
    function render() {
        extract($this->variables);
         ob_start();
            if (file_exists(ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'header.php')) {
                include (ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'header.php');
            } else {
                include (ROOT . DS . 'application' . DS . 'views' . DS . 'header.php');
            }
            
		if( file_exists(ROOT . DS. 'application' .DS . 'views' . DS . $this->_controller . DS  . $this->_action . '.php'))
		{
        include (ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $this->_action . '.php');       
		}
		else if(file_exists(ROOT . DS. 'application' .DS . 'views' . DS . $this->_controller . DS  . strtolower($this->_action) . '.php'))
		{
			include(ROOT . DS. 'application' .DS . 'views' . DS . $this->_controller . DS  . strtolower($this->_action) . '.php');
		}
		else
		{
			echo "NO VIEW FOUND FOR " .ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $this->_action . '.php';
		}
              
            if (file_exists(ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'footer.php')) {
                include (ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . 'footer.php');
            } else {
                include (ROOT . DS . 'application' . DS . 'views' . DS . 'footer.php');
            }
          
          $templateResult = ob_get_clean(); 
          ob_end_flush();
           
          echo $this->CustomTemplateReplace($templateResult);
    }
    
    function renderPartial() {
    	extract($this->variables);
    	ob_start();
    	if(file_exists(ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $this->_action . '.php'))
    	{
    		include (ROOT . DS . 'application' . DS . 'views' . DS . $this->_controller . DS . $this->_action . '.php');
    	}
    	else
    	{
    		// shared view in root view folder
    	 	include(ROOT . DS . 'application' . DS . 'views' . DS . $this->_action . '.php');	
    	
    	}
          $templateResult = ob_get_clean(); 
           
          echo $this->CustomTemplateReplace($templateResult);
    }
 
}