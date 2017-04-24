	<?php 
		$authResult = $HTML::getCurrentUser();
	
		$roleResult = $HTML::getCurrentUserRoles();
	
		if($authResult != false)
		{
			if($roleResult != false)
			{
				$roleName = ROLE_TABLE_ROLENAME_COLUMN;
				if($roleResult->$roleName == "Admin")
				{
					echo "<a href='/Admin'>Admin</a><BR>";
				}
			}
			
			
			$userName = USER_TABLE_USERNAME_COLUMN;
			echo "Welcome: " . $authResult->$userName . "<BR>";
			$HTML::ActionLink("Logoff", "Logoff", "Account");
		}
		else 
		{
			$HTML::ActionLink("Login", "Login", "Account");
		}		
	?>
