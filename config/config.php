<?php
 
/** Configuration Variables **/
 
define ('DEVELOPMENT_ENVIRONMENT',true);
 
define('DB_NAME', 'dbname');
define('DB_USER', 'dbname');
define('DB_PASSWORD', '******');
define('DB_HOST', 'localhost');

define('USER_TABLE', 'users_table');
define('USER_TABLE_USERNAME_COLUMN', 'UserName');
define('USER_TABLE_PASSWORD_COLUMN', 'Password');
define('USER_TABLE_ENCRY', true);
define('USER_TABLE_KEY', 'Id');
define('USER_MODEL', '');

define('ROLE_TABLE_KEY', 'Id');
define('ROLE_TABLE', 'roles_table');
define('ROLE_TABLE_ROLENAME_COLUMN', 'RoleName');
define('ROLE_MTM_TABLE', 'usersinroles_table');
define('ROLE_MTM_TABLE_ROLEID_COLUMN', 'RoleId');
define('ROLE_MTM_TABLE_USERID_COLUMN', 'UserId');

define('AUTHENTICATION_CONTROLLER', 'Account');
define('AUTHENTICATION_ACTION', 'Login');
define('NOT_FOUND_PAGE', '404');
define('FOLDER', '');
