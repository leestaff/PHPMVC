# PHPMVC

This is a MVC based framework for PHP. 

I orgnially started it based off this tutorial:  http://anantgarg.com/2009/03/13/write-your-own-php-mvc-framework-part-1/
but it did not have all the features and flexibility that I wanted.   I have updated the framework to bring it some of the concepts from .NET MVC site that I had been working on.   

I have gotten this framework to work on both LAMP installations as well as WIMP (Windows, IIS, MySql, PHP).  For the WIMP setup use the web.config files.   For Apache use the .htaccess files.  

Installation:
The first thing that you need to setup is the config.php file.  This file is in the Config folder.  Set your MySQL database name and login information there.   If you want to have login on the site configure the rest of the config.php items to define the name of your users, roles and users in roles table. 

For me it was as simple as three db tables

users_table:
Id int PK
UserName varchar(75)
Password varchar(256)

roles_table:
Id Int PK
RoleName varchar(50)

usersinroles_table:
RoleId
UserId

To create models based on your existing databaes schema use the AdminController.  There is a site for GenerateModelClasses.   That will query your db schema and generate the PHP code for the models with all metadata required.   This metadata includes column type (used for validation) and relationships for foriegn keys.    

If you have questions or would like to add features to this framework, contact me at http://leestaffordconsulting.com











