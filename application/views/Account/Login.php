<form id="login-form" action="<?php if(strlen(FOLDER) > 0)  { echo "/".FOLDER; }  ?>/Account/LoginPost" method="post">

	<?php  if(isset($error))
	{
		echo $error . "<BR>";
		
	}?>

	<label>Username:</label>
	<input type="text" name="username" id="username">
	<BR>
	<label>Password:</label>
	<input type="password" name="password" id="password">
	
	<input type="hidden" name='referingUrl' id='referingUrl' value="<?php echo $referingUrl;?>">
	<BR>
	<input type="submit" value="LOGIN">
	
</form>
