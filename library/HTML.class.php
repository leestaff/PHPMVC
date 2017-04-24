<?php
class HTML
{
	
	public static function Action($action, $controller)
	{
		$queryString = array(null);
		
		$controllerName = $controller;
		$controller .= 'Controller';
		$dispatch = new $controller($controllerName,$action);
		
		if ((int)method_exists($controller, $action)) {
			call_user_func_array(array($dispatch,$action), $queryString);
		} else {
			/* Error Generation Code Here */
		   		   
		}
		
	}
	
	public static function ActionWithData($action, $controller, $data)
	{
		$controllerName = $controller;
		$controller .= 'Controller';
		$dispatch = new $controller($controller,$action);
		
		if ((int)method_exists($controllerName, $action)) {
			call_user_func_array(array($dispatch,$action),$data);
		} else {
			/* Error Generation Code Here */
		
		}		
		
	}
	
	public static function ActionLink($linkText, $action, $controller, $attributes = null)
	{
		$folder = FOLDER;
		if(strlen(trim($folder)) > 0)
		{
			$data =  "<a href='". DS. FOLDER. "/$controller/$action/' ";
			
			if(isset($attributes))
			{
				if(is_array($attributes))
				{
					foreach($attributes as $key => $val)
					{
						$data .= $key . "='" . $val . "' ";
					}
				}
				else
				{
					$data .= "class='" . $attributes . "' ";
				}
			}
			
			$data .= ">$linkText</a>";
		}
		else
		{
			$data = "<a href='/$controller/$action/' ";
			
			if(isset($attributes))
			{
				if(is_array($attributes))
				{
					foreach($attributes as $key => $val)
					{
						$data .= $key . "='" . $val . "' ";
					}
				}
				else
				{
					$data .= "class='" . $attributes . "' ";
				}
			}
			
			$data .= ">$linkText</a>";
		}
		
		echo $data;
	}
	
	public static function ActionLinkTemplate($linkText, $action, $controller)
	{
		$folder = FOLDER;
		if(strlen(trim($folder)) > 0)
		{
			return "<a href='". DS. FOLDER. "/$controller/$action/' >$linkText</a>";
		}
		else
		{
			return "<a href='/$controller/$action/' >$linkText</a>";
		}
	}
	
	public static function ActionLinkWithData($linkText, $action, $controller, $data, $attributes = null)
	{
		$output = "<a href='/$controller/$action/";
		
		$datacount = 0;
		if(count($data) > 0)
		{
			
			foreach($data as $datum =>$v)
			{
				if($datacount == 0)
				{
					$output .= $v."/";
				}
				else if($datacount == 1)
				{
					$output .= "?";
				}
				else
				{
					$output .= "&"; 
				}
				
				if($datacount > 0)
				{
					$output .= $datum ."=".$v;				
				}

				
				$datacount++;
			}	
		}
		$output .= "' ";
		
		if(isset($attributes))
		{
			if(is_array($attributes))
			{
				foreach($attributes as $key => $val)
				{
					$output .= $key . "='" . $val . "' ";
				}
			}
			else
			{
				$output .= "class='" . $attributes . "' ";
			}
		}
				
		$output .= ">$linkText</a>";
		
		echo $output;
	}
	
	public static function Url($action, $controller)
	{
		$folder = FOLDER;
		if(strlen(trim($folder)) > 0)
		{
		
			echo  "/". FOLDER. "/$controller/$action/";
		}
		else
		{
			echo  "/$controller/$action/";
		}
	}
	
	public static function UrlWithData($action, $controller, $data)
	{
		$folder = FOLDER;
		$output = "";
		if(strlen(trim($folder)) > 0)
		{
			$output = FOLDER. "/$controller/$action/";
		}
		else
		{
			$output = "$controller/$action/";
		}
		$datacount = 0;
		if(count($data) > 0)
		{
			foreach($data as $datum =>$v)
			{
				if($datacount == 0)
				{
					$output .=$v . "/";
				}
				else if($datacount == 1)
				{
					$output .= "?";
					$output .= $datum ."=".$v;
				}
				else
				{
					$output .= "&"; 
					$output .= $datum ."=".$v;
				}
								
				$datacount++;
			}	
		}
		
		echo $output;
	}
	
	public static function UrlVar($action, $controller, $data= null)
	{
		if(isset($data))
		{
			$folder = FOLDER;
			$output = "";
			if(strlen(trim($folder)) > 0)
			{
				$output = DS. FOLDER. "/$controller/$action/";
			}
			else
			{
				$output = "/$controller/$action/";
			}
		
			$datacount = 0;
			if(count($data) > 0)
			{
				foreach($data as $datum =>$v)
				{
					if(datacount == 0)
					{
						$output .= "?";
					}
					else
					{
						$output .= "&"; 
					}
					
					$output .= $datum ."=".$v;
					
					$datacount++;
				}	
			}
		
			return  $output;
		}
		else
		{
			return  "/". FOLDER. "/$controller/$action/";
		}
	}
	
	public static function KendoDataSourceRead($action, $controller, $pageSize, $data=null)
	{
		$url = HTML::UrlVar($action, $controller, $data);		
		$dsString = KendoDataSourceRequest::CreateJSReadDataSource($url, $pageSize);
		
		echo "$dsString";
		
	}
	
	public static function getCurrentUserRoles()
	{
		if (session_status() == PHP_SESSION_NONE)
		{
			session_start();
		}
		 
		if(isset($_SESSION['user']) && isset($_SESSION['pw']))
		{
			$user = $_SESSION['user'];
			$pw = $_SESSION['pw'];
			 
			//check db
			$roleQuery = "SELECT " .ROLE_TABLE_ROLENAME_COLUMN . " FROM " . ROLE_TABLE . " R INNER JOIN " . ROLE_MTM_TABLE . " uir on uir.".ROLE_MTM_TABLE_ROLEID_COLUMN . " = R.".ROLE_TABLE_KEY
			. " WHERE uir.". ROLE_MTM_TABLE_USERID_COLUMN ." = :" .USER_TABLE_USERNAME_COLUMN;
			
			//echo "Role Query:" .$roleQuery . "<BR>";
			 
			$userData = array(USER_TABLE_USERNAME_COLUMN => $user);
			$result = SQLQuery::QueryOneWithData($roleQuery, $userData);
			 
			if($result)
			{
				return $result;
			}
		}

		return false;
	}
	
	public static function getCurrentUser()
	{
		if (session_status() == PHP_SESSION_NONE)
		{
			session_start();
		}
		 
		if(isset($_SESSION['user']) && isset($_SESSION['pw']))
		{
			$user = $_SESSION['user'];
			$pw = $_SESSION['pw'];
			 
			//check db
			$userQuery = "SELECT " . USER_TABLE_USERNAME_COLUMN . " FROM " . USER_TABLE
			. " WHERE " . USER_TABLE_USERNAME_COLUMN ." = :" .USER_TABLE_USERNAME_COLUMN
			." AND " .USER_TABLE_PASSWORD_COLUMN . " = :" . USER_TABLE_PASSWORD_COLUMN;
			 
			$userData = array(USER_TABLE_USERNAME_COLUMN => $user, USER_TABLE_PASSWORD_COLUMN => $pw);
			$result = SQLQuery::QueryOneWithData($userQuery, $userData);
			 
			if($result)
			{
				return $result;
			}
		}

		return false;
	}
	
	
	public static function includeImage($fileName)
	{
		$folder = FOLDER;
		$data = "";
		if(strlen(trim($folder)) > 0)
		{
			$data = '<img src="'. DS. FOLDER. DS. 'public/images/'.$fileName.'">';
		}
		else
		{
			$data = '<img src="'. DS. 'public/images/'.$fileName.'">';
		}
		echo $data;		
	}
	
	public static function includeImageLocation($fileName)
	{
		$folder = FOLDER;
		$data = "";
		if(strlen(trim($folder)) > 0)
		{
			$data = DS . FOLDER . DS . 'public/images/' . $fileName;
		}
		else
		{
			$data = DS . 'public/images' . $fileName;
		}
		
		echo $data;
	}
	
	
	public static function includeJs($fileName)
	 {
		$folder = FOLDER;
		$data = "";
		if(strlen(trim($folder)) > 0)
		{
			$data = '<script src="'. DS. FOLDER. DS. 'public/js/'.$fileName.'.js"></script>';
		}
		else
		{
			$data = '<script src="'. DS. 'public/js/'.$fileName.'.js"></script>';
		}
		echo $data;
	}
	
	public static function includeCss($fileName)
	{
		$folder = FOLDER;
		$data = "";
		if(strlen(trim($folder)) > 0)
		{
			$data = '<link href="' . DS. FOLDER. DS.'public/css/'.$fileName.'.css" rel="stylesheet">';
		}
		else
		{
			$data = '<link href="' . DS.'public/css/'.$fileName.'.css" rel="stylesheet">';
		}
		echo $data;
	}
	
	public static function Partial($partialName, $data)
	{
		extract($data);
    		include(ROOT . DS . 'application' . DS . 'views' . DS . $partialName . '.php'); 
	}
	
}