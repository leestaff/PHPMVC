<?php
class Controller {
     
    protected $_controller;
    protected $_action;
    protected $_template;
    protected $_url;
    protected $_authenticate = false;
    protected $_queryparameters;
    protected $_user;
    protected $_roles;
    
    protected $_requiresRoles;
    
    function __construct( $controller, $action) {
         
        $this->_controller = $controller;
        $this->_action = $action;
                       
        $this->_template = new Template($controller,$action); 
        
        $this->set("HTML", new HTML());
        $this->set("ThisController", $controller);
        $this->set("ThisAction", $action);
        
        
        $QUERYPARAMETERS = array();
		$urlParse = parse_url($_SERVER['REQUEST_URI']);
		
		$this->_queryparameters = array();
		
		if(isset($urlParse['query']))
		{						
			parse_str($urlParse['query'], $QUERYPARAMETERS);	                
	        $this->_queryparameters = $QUERYPARAMETERS;
		}
    }
    
    public function Authenticate()
    {
		return $this->BaseAuthenticate();
    }
    
    
    public function SetUrl($url)
    {
    	$this->_url = FOLDER .'/'. $url;
    }
    
    public function SetQueryParams($q)
    {
    	if(count($q) > 0)
    	{
    		$this->_queryparameters = $q;
    	}
    }
    
    protected function BaseAuthenticate()
    {
    	if($this->_authenticate)
    	{
    		// Check for authentication
    		    	
    		$filter = new filter();
    		$filter->Success = true;
    		
    		$authorized = false;
    	    if (session_status() == PHP_SESSION_NONE)
    	    {
   		 		session_start();
			}
    		
    		if(isset($_SESSION['user']) && isset($_SESSION['pw']))
    		{
    			$user = $_SESSION['user'];
    			$pw = $_SESSION['pw'];
    			
    			$result = UserService::GetUser($user, $pw);
    			
    			if($result)
    			{
    				$authorized = true;
    				$this->_user = $result;
    				$this->set("User", $this->_user);
    				
    				// **** ROLES ***
    				$roleModel = ROLE_MTM_TABLE;    				
    				$roleResult = false;
 				
    				$userKey = USER_TABLE_KEY;
    				$roleResult = UserService::GetUserRoles($result->$userKey);
    				
    				if($roleResult)
    				{
    					$this->_roles = $roleResult;
    					$this->set("Roles", $roleResult);
    				}
    				//_________ END OF ROLES _________
    				
    			} // END OF IF RESULT

    			
    		} // END OF IF ISSET
    		else 
    		{
    			echo "UNAUTHORIZED <BR>";
    		}
    		    		
    		if($authorized)
    		{
    			
    			/// ************ REQUIRES ROLES ****************
				if(isset($this->_requiresRoles))
				{
					if(isset($this->_roles))
					{
						//  CHECK FOR THE ACTION NAME IN THE _requireRoles array
						if(array_key_exists($this->_action, $this->_requiresRoles))
						{
							$requiredRolesForAction = $this->_requiresRoles[$this->_action];
							
							$boolHasValidRole = false;
							if(is_array($requiredRolesForAction))
							{
								foreach($requiredRolesForAction as $reqRole)
								{
									$rolenameColumn = ROLE_TABLE_ROLENAME_COLUMN;
									if($userRoles->$rolenameColumn == $reqRole)
									{
										$boolHasValidRole = true;
									}									
								}
							}
							else
							{
								foreach($this->_roles as $userRoles)
								{
									$rolenameColumn = ROLE_TABLE_ROLENAME_COLUMN;
									
									if($userRoles->$rolenameColumn == $requiredRolesForAction)
									{
										$boolHasValidRole = true;
									}
								} // END OF FOREACH
								
							}  // END OF ELSE NOT ARRAY
							
							if(!$boolHasValidRole)
							{
								//** NOT AUTHORIZED - DOES NOT HAVE THE VALID ROLE NEEDED **
								$filter->Success = false;
					    		$filter->RedirectController = AUTHENTICATION_CONTROLLER;
					    		$filter->RedirectAction = AUTHENTICATION_ACTION;
				    	
				    			return $filter;						
							}
							
								
						} // END OF IF ARRAY_KEY_EXISTS
						
					} // END OF IF ISSET _ROLES
					else
					{
						echo "USER DOES NOT HAVE PERMISSION TO VIEW THIS PAGE. <BR>";
						
						// *** DOES NOT MEET ROLE REQUIREMENTS
						$filter->Success = false;
			    		$filter->RedirectController = AUTHENTICATION_CONTROLLER;
			    		$filter->RedirectAction = AUTHENTICATION_ACTION;
		    	
		    			return $filter;
												
					} // END OF ELSE
				} // END OF IF REQUIRES ROLES
					
    			return $filter;
    		}
    		else 
    		{
    			// ***** COULD NOT AUTHENTICATE **** 
    			
    			$filter->Success = false;
	    		$filter->RedirectController = AUTHENTICATION_CONTROLLER;
	    		$filter->RedirectAction = AUTHENTICATION_ACTION;
    	
    			return $filter;
    		}
    	} // END OF IF AUTHENTICATE
    	else
    	{
    		$filter = new filter();
    		$filter->Success = true;
    		 
    		return $filter;
    	}  	
    } // %%% END OF BASE AUTHENTICATE METHOD %%%%
    
    function beforeAction()
    {
    	 // placeholder to be overrided
    }
    
    function afterAction()
    {
    	//placeholder to be overrided  	
    }
    
    public function Filter($actionName)
    {
    	//placeholder to be overrided
    	
    	$filter = new filter();
    	$filter->Success = true;
    	
    	return $filter;
    }
    
    
 
    function set($name,$value) {
        $this->_template->set($name,$value);
    }
 
    function RenderView()
    {
    	$this->_template->render();
    }
    function RenderViewByName($name)
    {
    	$this->_template->renderByName($name);
    }
    
    function RenderPartialView()
    {    	
    	$this->_template->renderPartial();
    }
    
    function RenderPartialViewByName($name)
    {
    	$this->_template->renderPartialByName($name);	
    }
    
    function RedirectToAction($action, $controller)
    {
    	$this->_controller = $controller;
    	$this->_action = $action;
    	
    	LoadController($controller, $action, array());
    }
    
    
    function __destruct() {
            //$this->_template->render();
    }
         
}