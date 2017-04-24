<?php
class AccountController extends Controller
{
	public function LoginDisplay()
	{	
		$this->RenderPartialViewByName("_LoginDisplay");
	}
	
	public function Login()
	{
		$referingUrl = $this->_url;
		
		$this->set("referingUrl", $referingUrl);
		$this->RenderViewByName("Login");
	}
	
	public function LoginPost($username = null, $password = null)
	{
		$un = "";
		$pw = "";
		if(isset($username) && isset($password))
		{
			$un = $username;
			$pw = $password;
		}
		else
		{
			$un = $_REQUEST["username"];
			$pw = $_REQUEST["password"];
		}
		
		if(USER_TABLE_ENCRY)
		{
			$pw = md5($pw);
		}
		
		$userModel = USER_MODEL;
		$result = false;
		
		$userData = array(USER_TABLE_USERNAME_COLUMN => $un, USER_TABLE_PASSWORD_COLUMN => $pw);
		
		if(class_exists($userModel))
		{
			$um = new $userModel();
			$result = $um->findByArray($userData);
		}
		else
		{
	    	$userQuery = "SELECT ". USER_TABLE_USERNAME_COLUMN . ", " . USER_TABLE_PASSWORD_COLUMN . " FROM " . USER_TABLE 
	    				. " WHERE " . USER_TABLE_USERNAME_COLUMN ." = :" .USER_TABLE_USERNAME_COLUMN
	    				." AND " .USER_TABLE_PASSWORD_COLUMN . " = :" . USER_TABLE_PASSWORD_COLUMN;
	    			
	    	$result = SQLQuery::QueryOneWithData($userQuery, $userData);
		}
    	if($result)
    	{
    		if (session_status() == PHP_SESSION_NONE)
    	    {
   		 		session_start();
			}
			//set login cookie
			$_SESSION['user'] = $un;
			$_SESSION['pw'] = $pw;
			
			if(isset($_REQUEST['referingUrl']))
			{
				$referingUrl = $_REQUEST['referingUrl'];
				
				$folder = FOLDER;
				if(strlen(trim($folder)) > 0)
				{				
					if (strpos($referingUrl, FOLDER) !== FALSE)
					{
						$referingUrl = trim(str_replace(FOLDER, "", $referingUrl));
					}
				}
				
				//echo "<BR>REF URL: $referingUrl<BR>";
				
				if($referingUrl && $referingUrl != "/Account/Login/" && $referingUrl != "/Account/LoginPost" && $referingUrl != "/Account/Login" && $referingUrl != "Account/Logoff/")
				{
					RouteSystem($referingUrl);
				}
				else
				{
					echo "Login Successful !";
					LoadController("Home", "Index", array(null));					
				}
			}
			else 
			{
				echo "Login Successful";
				LoadController("Home", "Index", array(null));
			}
    	}
    	else
    	{
			echo "Invalid Password";
			
    		$this->set("error", "Invalid Password");
    		$this->Login();	
    	}
	}
	
	public function Logoff()
	{
		if (session_status() == PHP_SESSION_NONE)
		{
			session_start();
		}		
		
		unset($_SESSION['user']);
		unset($_SESSION['pw']);
		
		LoadController("Account", "Login", array(null));
		
	}
	
	public function Register($validationErrors = null)
	{
		$this->set("errors", $validationErrors);
		$this->RenderViewByName("Register");
	}
	
	public function RegisterSubmit()
	{
		$un = $_REQUEST["username"];
		$pw = $_REQUEST["password"];
		$pwConfirm = $_REQUEST["passwordConfirm"];
		
		//Validation
		if($pw != $pwConfirm)
		{
			return $this->Register("Passwords do not match.");
		}
		
		$createUserResult = $this->CreateUser($un, $pw);
		if($createUserResult == true)
		{
			$this->LoginPost($un, $pw);
		}
		else
		{
			return $this->Register($createUserResult);
		}
	}
	
	private function CreateUser($userName, $pw)
	{
		$boolSuccess = false;
		
		$userModel = USER_MODEL;
		if(class_exists($userModel))
		{
			$um = new $userModel();		
			
			$userNameColumn = USER_TABLE_USERNAME_COLUMN;
			$userPwColumn = USER_TABLE_PASSWORD_COLUMN;
			
			// **** FIND EXISTING USER WITH THIS SAME USERNAME ****
			$userDataArray = array(USER_TABLE_USERNAME_COLUMN => $userName);
			$existingUm = new $userModel();
			$userCount = $existingUm->allByArrayCount($userDataArray);

			if($userCount > 0)
			{
				$boolSuccess = "A USER WITH THIS USERNAME ALREADY EXISTS.";
				return $boolSuccess;
			}	
			
			$um->$userNameColumn = $userName;
			
			if(USER_TABLE_ENCRY)
			{
				$um->$userPwColumn = md5($pw);
			}
			else
			{			
				$um->$userPwColumn = $pw;
			}
			
			$boolSuccess = $um->save();
			if($boolSuccess == false)
			{
				$boolSuccess = $um->last_error_message;
			}
			else
			{
				$boolSuccess = true;
			}
						
			return $boolSuccess;
			
		}
		else
		{
			// CHECK FOR EXISTING USER
			if(USER_TABLE_ENCRY)
			{
				$pw = md5($pw);
			}
			
			$userDataCountArray =  array(USER_TABLE_USERNAME_COLUMN => $userName);
			$userDataArray = array(USER_TABLE_USERNAME_COLUMN => $userName, USER_TABLE_PASSWORD_COLUMN=> $pw);
			
			$existSQL  = "SELECT COUNT(*) as usercount FROM " . USER_TABLE . "  WHERE " . USER_TABLE_USERNAME_COLUMN .  " = :" . USER_TABLE_USERNAME_COLUMN;
			
			$exUser = SQLQuery::QueryOneWithData($existSQL, $userDataCountArray);
			if($exUser)
			{
				if($exUser->usercount > 0)
				{
					return "A USER WITH THIS USERNAME ALREADY EXISTS";
				}
				/// CREATE USER WITH SQL
				$nuSQL = "INSERT INTO " . USER_TABLE . " ( " . USER_TABLE_USERNAME_COLUMN . ", " . USER_TABLE_PASSWORD_COLUMN . " ) "
				."VALUES ( :".USER_TABLE_USERNAME_COLUMN . ", " . ":" . USER_TABLE_PASSWORD_COLUMN . ")";
				
				SQLQuery::QueryExecWithData($nuSQL, $userDataArray);
				
				return true;
			}
			else
			{		
				/// CREATE USER WITH SQL
				$nuSQL = "INSERT INTO " . USER_TABLE . " ( " . USER_TABLE_USERNAME_COLUMN . ", " . USER_TABLE_PASSWORD_COLUMN . " ) "
				."VALUES ( :".USER_TABLE_USERNAME_COLUMN . ", " . ":" . USER_TABLE_PASSWORD_COLUMN . ")";
				
				SQLQuery::QueryExecWithData($nuSQL, $userDataArray);
				
				return true;
				
				
			}
		}
		
	}// END OF METHOD - CREATE USER   
	
	
	
}