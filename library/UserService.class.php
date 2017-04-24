<?php

class UserService {

	public static function GetUserByName($userName)
	{
		$userModel = USER_MODEL;
		$result = false;
		
		// ***  USERS ***
			
		$userData = array(USER_TABLE_USERNAME_COLUMN => $userName);
		
		if(class_exists($userModel))
		{
			$um = new $userModel();
			$result = $um->findByArray($userData);
			
			return $result;
		}
		else
		{
		
			//check db
			$userQuery = "SELECT ".USER_TABLE_KEY . ", ". USER_TABLE_USERNAME_COLUMN . ", " . USER_TABLE_PASSWORD_COLUMN . " FROM " . USER_TABLE 
						. " WHERE " . USER_TABLE_USERNAME_COLUMN ." = :" .USER_TABLE_USERNAME_COLUMN
							;
			
			$result = SQLQuery::QueryOneWithData($userQuery, $userData);
		}
		
		return $result;
	}


	public static function GetUser($userName, $password)
	{
		$userModel = USER_MODEL;
		$result = false;
		
		// ***  USERS ***
			
		$userData = array(USER_TABLE_USERNAME_COLUMN => $userName, USER_TABLE_PASSWORD_COLUMN => $password);
		
		if(class_exists($userModel))
		{
			$um = new $userModel();
			$result = $um->findByArray($userData);
			
		}
		else
		{
		
			//check db
			$userQuery = "SELECT ".USER_TABLE_KEY . ", ". USER_TABLE_USERNAME_COLUMN . ", " . USER_TABLE_PASSWORD_COLUMN . " FROM " . USER_TABLE 
						. " WHERE " . USER_TABLE_USERNAME_COLUMN ." = :" .USER_TABLE_USERNAME_COLUMN
						." AND " .USER_TABLE_PASSWORD_COLUMN . " = :" . USER_TABLE_PASSWORD_COLUMN;
			
			$result = SQLQuery::QueryOneWithData($userQuery, $userData);
		}
		
		return $result;
	}
	
	public static function GetUserRoles($userId)
	{
		$roleModel = ROLE_MTM_TABLE;    				
		$roleResult = false;
	
		$userKey = USER_TABLE_KEY;
		$roleData = array(ROLE_MTM_TABLE_USERID_COLUMN => $userId);
		
		$roleQuery = "SELECT  rt.". ROLE_TABLE_KEY . ", rt.". ROLE_TABLE_ROLENAME_COLUMN. " FROM " . ROLE_TABLE . " rt INNER JOIN "
		. ROLE_MTM_TABLE . " uir ON uir.". ROLE_MTM_TABLE_ROLEID_COLUMN."=  rt." .ROLE_TABLE_KEY.  " WHERE uir."
		. ROLE_MTM_TABLE_USERID_COLUMN . " = :" .ROLE_MTM_TABLE_USERID_COLUMN;
		
		$roleResult = SQLQuery::QueryWithData($roleQuery, $roleData);
		
		return $roleResult;
	}


}
