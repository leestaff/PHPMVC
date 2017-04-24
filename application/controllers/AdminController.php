<?php
 class AdminController extends Controller {
 	
 	public function Authenticate()
 	{
 		$this->_authenticate = true;
 		$result = $this->BaseAuthenticate();
 			
 		return $result;
 	}
 	
 	public function GenerateModelClasses()
 	{
 		$tables = SQLQuery::Query("select TABLE_NAME from information_schema.tables where TABLE_SCHEMA = '". DB_NAME. "'");
 		
 		$this->set("TableNames", $tables);
 		
 		$this->RenderView();
 	}
 	
 	public function GenerateModelClassesSubmit()
 	{
 		
 		$tableName = $_REQUEST['tablename'];
 		
 		$CodeGenerator = new model_generator($tableName);
 		
 		$cn = $CodeGenerator->get_code();
 		
 		$this->set("code", $cn);
 		$this->set("tableName", $tableName);
 		
 		$this->RenderView();
 		
 	}
 }
