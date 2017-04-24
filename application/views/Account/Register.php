<form id="login-form" action="<?php echo "/".FOLDER; ?>/Account/RegisterSubmit" method="post">

	<?php  if(isset($errors))
	{
		echo $errors . "<BR>";
		
	}?>
	<h3>Register a New User</h3>
	<label>Username:</label>
	<input type="text" name="username" id="username">
	<BR>
	<label>Password:</label>
	<input type="password" name="password" id="password">
	<BR>
	<label>Confirm Password:</label>
	<input type="password" name="passwordConfirm" id="passwordConfirm">	
	
	<BR>
	<input type="submit" value="Register User">
	
</form>