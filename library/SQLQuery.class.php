<?php
class SQLQuery extends BaseClass {
	
	protected $last_error_message;
	protected $db;
	protected $properties = array();
	protected $table_name;
	protected $key_name;
	protected $auto_key;
	protected $relationships = array();

	protected $metadata = array();

	protected $validationErrors = array();
	
	protected $select_query;
	protected $binding_array;
	
	protected $additionalData = array();
	
	
	public function connect()
	{
		$this->db =	new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
		global $db;
	}
  	############################################################
  	### MAGIC METHODS - Constructor / Getter / Setter
  	############################################################

     public function __construct($data_array = null)
     {
      	if(isset($data_array) && is_array($data_array))
      	 {
      		$this->properties = $data_array;
      	 }
      	 
         $this->connect();
      }
      	
	public function __get($property) {
		if (property_exists($this, $property)) {	
			
			$this->GetRelationship($property);
								
			return $this->$property;
		}
		else
		{
			// check if it exists in additional data
			
			if(array_key_exists($property, $this->additionalData))
			{
				return $this->additionalData[$property];
			}
			
		}
	}
	
	public function __set($property, $value) {
		if (property_exists($this, $property)) {
			$this->$property = $value;
		}
		else
		{
			// Add to additional data
			$this->additionalData[$property] = $value;
		}
	}
	
	public function GetRelationship($property)
	{
		
		//echo "GETTING $property RELATIONSHIP on table $this->table_name<BR>";
		
		// Check if this is a Relationship Property
			
		foreach($this->relationships as $rel)
		{
			//echo "MODEL PROPERTY: $rel->ModelProperty <BR>";
			
			if($rel->ModelProperty == $property)
			{
				$className = $rel->ClassName;
				$eClass = new $className;
				
				$relType = $rel->RelationshipType;
				$rKey = $this->{$rel->ModelKey};
				$fKey = $rel->ForeignKey;
				$relArray = array();
				$relArray[$fKey] = $rKey;
				
				if($relType == 0) // ONE TO MANY
				{				
					$eClass = $eClass->allByArray($relArray);
				}
				else // ONE TO ONE
				{					
					$eClass = $eClass->findByArray($relArray);								
				}
								
				$this->$property = $eClass;
				
				return $eClass;
			}
		}
			
	}


	############################################################
	### FINDER METHODS
	############################################################

	public function last()
    {
		$this->connect();
		$con = $this->db;
			
		$sql = "SELECT * FROM ".  $this->table_name ." ORDER BY ".  $this->key_name ." DESC LIMIT 1";
		
		$this->select_query = $sql;
		
		$q = $con->prepare($sql);
	    $q->execute();
		$q->setFetchMode(PDO::FETCH_CLASS, get_called_class());
		$return_var = $q->fetch();
		
		$this->last_error_message = $q->errorInfo();
		
		return $return_var;
	}
	
	public function first() {
		
		$this->connect();
		$con = $this->db;
		
		$sql = "SELECT * FROM ". $this->table_name ." ORDER BY " . $this->key_name. " ASC LIMIT 1";
		$this->select_query = $sql;
		
		$q = $con->prepare($sql);
		$q->execute();
		$q->setFetchMode(PDO::FETCH_CLASS , get_called_class());
		$return_var = $q->fetch();
		return $return_var;		
		
	}

	public function all()
	 {
		 $this->connect();
		 $con = $this->db;
		
		 $sql = "SELECT * FROM ".  $this->table_name;
		 $this->select_query = $sql;
		//echo "SQL QUERY: ". $sql;

	     $q = $con->prepare($sql);
	     $q->setFetchMode(PDO::FETCH_CLASS , get_called_class());
	     $q->execute();
	     $return_var = $q->fetchAll();
	     
	     $this->last_error_message = $q->errorInfo();
	    	     
	   	 
	    	     
	     return $return_var;
	}
	
	public function ItemCount()
	{
		 $this->connect();
		 $con = $this->db;
		 $itemCount =  $con->query('select count(*) from '. $this->table_name)->fetchColumn(); 
		 
		 return $itemCount;
	}
	
	public function allPaged($page, $pageSize)
	{
		 $this->connect();
		 $con = $this->db;
		
		 $sqlCount = "SELECT Count(*) as Count FROM ".  $this->table_name;
		 $countQ = $con->prepare($sqlCount);
		 $countQ->setFetchMode(PDO::FETCH_OBJ);
		 $countQ->execute();
		 
		 $itemCount = $countQ->fetch()->Count;
		 
		 $start = ($page - 1) * $pageSize;
		 $sql = "SELECT * FROM ".  $this->table_name;
		 $sql = $sql." LIMIT $start,$pageSize";
		 $this->select_query = $sql;
		 
		 $q = $con->prepare($sql);
	     $q->setFetchMode(PDO::FETCH_CLASS , get_called_class());
	     $q->execute();
	     
	     $dataSet = $q->fetchAll();
	     $this->last_error_message = $q->errorInfo();
	     
	     $pagedResult = new PagedResult($dataSet, $page, $pageSize, $itemCount);
	     
	     return $pagedResult;
	}

	public function find($id)
	{
		$this->connect();
		$con = $this->db;
		
		$sql = "SELECT * FROM ". $this->table_name." WHERE " .$this->key_name . " = ?";
		$this->select_query = $sql;
		
		//echo $sql. "<BR>";
		
      	$q = $con->prepare($sql);
      	$q->execute(array($id));
      	$q->setFetchMode(PDO::FETCH_CLASS , get_called_class());
      	$objects = $q->fetchAll();
      	
		$this->last_error_message = $q->errorInfo();
		
		if(count($objects) == 0)
		{
			//echo "NO objects <BR>";
			return null;
		}
      	if(count($objects) == 1)
        {
        	//echo "here 1 <BR>";
      		return $objects[0];
      		
        }
        else
        {
        	//echo "here 2 <BR>";
        	//print_r($objects);
      	     return $objects;
      	}
    }

      public function findByArray( array $array, $operatorArray = null, $logicalOperator = null)
      {
      	$this->connect();
      	$con = $this->db;
      	
      	if(count($array) == 0) { return $this->all(); }

		$logOp = "AND";
      	if(isset($logicalOperator))
      	{
      		$logOp = $logicalOperator;
      	}

      	// Build the SQL && Bind-Var Array
      	$sql_where = "";
      	$bind_vars = array();
      	foreach($array as $col => $val)
      	{
      		$defaultOperator = "=";
      		
      		if(isset($operatorArray))
      		{
	      		if(isset($operatorArray[$col]))
	      		{
	      			$defaultOperator = $operatorArray[$col];	
	      		}
      		}
      		
      		
      		$bind_vars[":".$col] = $val;
      		$sql_where .= $col. $defaultOperator." :".$col." ". $logOp ." ";
      	}
      	$sql_where .= "1";

      	$sql = "SELECT * FROM ". $this->table_name." WHERE ".$sql_where;
		$this->select_query = $sql;
		$this->binding_array = $array;
		
      	$q = $con->prepare($sql);
      	$q->execute($bind_vars);
      	$q->setFetchMode(PDO::FETCH_CLASS , get_called_class());
      	$object = $q->fetch();

		$this->last_error_message = $q->errorInfo();

		

		return $object;
      }
      
      
      public function allSelect(array $selectArray)
      {
      	$this->connect();
      	$con = $this->db;
      	 
      	if(count( $selectArray) == 0) { return $this->all(); }
      	
      	// BUILD THE SQL
      	$sql = "SELECT ";
      	$arrayLength = count($selectArray);
      	$i = 1;
      	foreach($selectArray as $col => $val)
      	{
      		$sql .= $val . " AS " . $col;
      		
      		if($i != $arrayLength)
      		{
      			$sql .= ", ";
      		}	
      		$i++;
      	}
			
		$sql .=  " FROM ". $this->table_name. " ;";
		$this->select_query = $sql;
		
		$q = $con->prepare($sql);
      	$q->execute();
      	$q->setFetchMode(PDO::FETCH_OBJ);
      	$objects = $q->fetchAll();
      	
      	$this->last_error_message = $q->errorInfo();
      	
      	return $objects;
		
      }
      
      public function allSelectByArray(array $selectArray, array $whereArray, $operatorArray = null, $logicalOperator = null)
      {
      	$this->connect();
      	$con = $this->db;
      	 
      	if(count( $selectArray) == 0) { return $this->allByArray($whereArray, $operatorArray); }
      	if(count( $whereArray) == 0) { return $this-> allSelect($selectArray); }
      	
      	// BUILD THE SQL
      	$sql = "SELECT ";
      	$arrayLength = count($selectArray);
      	$i = 1;
      	foreach($selectArray as $col => $val)
      	{
      		$sql .= $val ." AS " .$col;
      		
      		if($i != $arrayLength)
      		{
      			$sql .= ", ";
      		}	
      		$i++;
      	}
			
		$sql .=  " FROM ". $this->table_name;
				
		$logOp = "AND";
      	if(isset($logicalOperator))
      	{
      		$logOp = $logicalOperator;
      	}
		$sql_where = " WHERE ";
      	$bind_vars = array();
      	foreach($whereArray as $col => $val)
      	{
      		$defaultOperator = "=";
      		
      		if(isset($operatorArray))
      		{
	      		if(isset($operatorArray[$col]))
	      		{
	      			$defaultOperator = $operatorArray[$col];	
	      		}
      		}
      		
      		$bind_vars[":".$col] = $val;
      		$sql_where .= $col. $defaultOperator . " :".$col." ". $logOp ." ";
      	}
      	$sql_where .= "1";
      	
      	$sql .= " ".$sql_where;
		
		$this->select_query = $sql;
		
		//echo "<BR> SELECT QUERY: " . $sql . "<BR>";
		
		$q = $con->prepare($sql);
      	$q->execute($bind_vars);
      	$q->setFetchMode(PDO::FETCH_OBJ);
      	$objects = $q->fetchAll();
      	
      	$this->last_error_message = $q->errorInfo();
      	
      	return $objects;
		
      }
      
      
      
      public function allByArray(array $array, $operatorArray = null, $logicalOperator = null)
      {
      	$this->connect();
      	$con = $this->db;
      	 
      	if(count($array) == 0) { return $this->all(); }
      	
      	// Build the SQL && Bind-Var Array
      	
      	$logOp = "AND";
      	if(isset($logicalOperator))
      	{
      		$logOp = $logicalOperator;
      	}
      	
      	$sql_where = "";
      	$bind_vars = array();
      	foreach($array as $col => $val)
      	{
      		$defaultOperator = "=";
      		
      		if(isset($operatorArray))
      		{
	      		if(isset($operatorArray[$col]))
	      		{
	      			$defaultOperator = $operatorArray[$col];	
	      		}
      		}
      		
      		$bind_vars[":".$col] = $val;
      		$sql_where .= $col. $defaultOperator . " :".$col." ". $logOp . " ";
      	}
      	$sql_where .= "1";
      	
      	$sql = "SELECT * FROM ". $this->table_name." WHERE ".$sql_where;
      	$this->select_query = $sql;
      	
      	//echo "SQL QUERY: " . $sql;
      	//print_r($bind_vars);
      	
      	$q = $con->prepare($sql);
      	$q->execute($bind_vars);
      	$q->setFetchMode(PDO::FETCH_CLASS , get_called_class());
      	$objects = $q->fetchAll();
      	
      	$this->last_error_message = $q->errorInfo();
      	
      	
      	
      	return $objects;
      	
      }
      
      public function allByArrayCount(array $array, $operatorArray = null)
      {
      	      	$this->connect();
      	$con = $this->db;
      	 
      	if(count($array) == 0) { return $this->all(); }
      	
      	// Build the SQL && Bind-Var Array
      	$sql_where = "";
      	$bind_vars = array();
      	foreach($array as $col => $val)
      	{
      		$defaultOperator = "=";
      		
      		if(isset($operatorArray))
      		{
	      		if(isset($operatorArray[$col]))
	      		{
	      			$defaultOperator = $operatorArray[$col];	
	      		}
      		}
      		
      		$bind_vars[":".$col] = $val;
      		$sql_where .= $col. $defaultOperator . " :".$col." AND ";
      	}
      	$sql_where .= "1";
      	
      	$sql = "SELECT COUNT(*) as ResultCount FROM ". $this->table_name." WHERE ".$sql_where;
      	$this->select_query = $sql;
      	//echo "SQL QUERY: " . $sql;
      	
      	$q = $con->prepare($sql);
      	$q->execute($bind_vars);
      	$q->setFetchMode(PDO::FETCH_OBJ);
      	$cnt = $q->fetch();
      	
      	$this->last_error_message = $q->errorInfo();
      	
      	return $cnt->ResultCount;
      }
      
      
      public function allByArrayPaged(array $array, $page, $pageSize, $operatorArray = null, $logicalOperator = null)
      {
       	$this->connect();
      	$con = $this->db;
      	 
      	if(count($array) == 0) { return $this->allPaged($page, $pageSize); }
      	
      	// Build the SQL && Bind-Var Array
      	$logOp = "AND";
      	if(isset($logicalOperator))
      	{
      		$logOp = $logicalOperator;
      	}
      	
      	$sql_where = "";
      	$bind_vars = array();
      	foreach($array as $col => $val)
      	{
      		$defaultOperator = "=";
      		
      		if(isset($operatorArray))
      		{
	      		if(isset($operatorArray[$col]))
	      		{
	      			$defaultOperator = $operatorArray[$col];	
	      		}
      		}
      		
      		$bind_vars[":".$col] = $val;
      		$sql_where .= $col. $defaultOperator . " :".$col." ". $logOp." ";
      	}
      	$sql_where .= "1";
      	

      	
      	$sqlCount = "SELECT Count(*) as Count FROM ".  $this->table_name . " WHERE " .$sql_where;
      	
      	//echo "SQL QUERY: " . $sqlCount;
      	
		$countQ = $con->prepare($sqlCount);
		$countQ->execute($bind_vars);
		$countQ->setFetchMode(PDO::FETCH_OBJ);
		$countQ->execute();
		 
		$itemCount = $countQ->fetch()->Count;
		 
		$start = ($page - 1) * $pageSize;
      	
      	$sql = "SELECT * FROM ". $this->table_name." WHERE ".$sql_where;
      	$sql = $sql." LIMIT $start,$pageSize";
      	$this->select_query = $sql;
      	//echo "SQL QUERY: " . $sql;
      	
      	$q = $con->prepare($sql);
      	$q->execute($bind_vars);
      	$q->setFetchMode(PDO::FETCH_CLASS , get_called_class());
      	$objects = $q->fetchAll();   
      	
      	$this->last_error_message = $q->errorInfo();
      	
      	$pagedResult = new PagedResult($objects, $page, $pageSize, $itemCount);  	
      	
      	return $pagedResult;
      	
      }
      
      public function findByQuery($query)
      {
      	 // Allows for view or joins of tables into objects
      	$this->connect();
      	$con = $this->db;
      	$this->select_query = $query;
      	$q = $con->prepare($query);
      	$q->execute();
      	$q->setFetchMode(PDO::FETCH_CLASS , get_called_class());
      	$objects = $q->fetch();
      	 
      	 $this->last_error_message = $q->errorInfo();
      	 
      	return $objects;
      }
      
      public function findAllByQuery($query)
      {
      	$this->connect();
      	$con = $this->db;
      	 
      	$q = $con->prepare($query);
      	$this->select_query = $query;
      	$q->execute();
      	$q->setFetchMode(PDO::FETCH_CLASS , get_called_class());
      	$objects = $q->fetchAll();
      	
      	$this->last_error_message = $q->errorInfo();
      	
      	if(count($objects) > 0)
      	{
      		//print_r($objects);
      	}
      	
      	return $objects;
      	
      }
      
      public function findAllByQueryWhere($query, array $array)
      {
      	$this->connect();
      	$con = $this->db;
      	 
      	$bind_vars = array();
      	foreach($array as $col => $val)
      	{
      		$bind_vars[$col] = $val;
      	} 
      	
      	$objects = array();
      	
      	
      	$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
 
      	$q = $con->prepare($query);
      	$q->execute($bind_vars);
      	$this->select_query = $query;
      	$q->setFetchMode(PDO::FETCH_CLASS , get_called_class());
      	$objects = $q->fetchAll();
      	
      	$this->last_error_message = $q->errorInfo();
      	
      	//print_r($this->last_error_message);
      	
      	
      	//echo "SQL: " .$query;
      	
      	return $objects;

      }
 
	############################################################
	### STATIC METHODS
	############################################################     
      public static function Query($query)
      {
      	$con = 	new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
      	$statment = $con->query($query);
      	$obj = $statment->fetchAll(PDO::FETCH_OBJ);
      	
      	// returns a list of standard objects
      	
      	return $obj;
      } 
      
      public static function QueryOne($query)
      {
      	$con = 	new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
      	$statment = $con->query($query);
      	$obj = $statment->fetch(PDO::FETCH_OBJ);
      
      	// returns first of standard objects
      
      	return $obj;
      }
           
      public static function QueryWithData($query, $valueArray)
      {
      	$con = 	new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
      	$sth = $con->prepare($query);
      	$sth->setFetchMode(PDO::FETCH_OBJ);
      	$sth->execute($valueArray);
      	$obj = $sth->fetchAll();
      	
      	return $obj;
      }
      
      public static function QueryOneWithData($query, $valueArray)
      {

      	$con = 	new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
      	$sth = $con->prepare($query);
      	$sth->setFetchMode(PDO::FETCH_OBJ);
      	$sth->execute($valueArray);
      	$obj = $sth->fetch();
      	

      	
      	return $obj;
      }
      

      public static function SelectAllByTable($tableName)
      {
      	$con = 	new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
      	$sth = $con->prepare("SELECT * FROM ". $tableName);
      	$sth->setFetchMode(PDO::FETCH_OBJ);
      	$sth->execute();
      	$obj = $sth->fetchAll();
      	 
      	return $obj;
      }
  
        public static function SelectByTableColumn($tableName, $columnName, $columnValue)
      {
      	$con = 	new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
      	$sth = $con->prepare("SELECT * FROM ". $tableName . " WHERE ". $columnName . " = :" . $columnName);
      	
      	$valueArray = array($columnName => $columnValue);
      	
      	$sth->setFetchMode(PDO::FETCH_OBJ);
      	$sth->execute($valueArray);
      	$obj = $sth->fetchAll();
      	
      	return $obj;
      }
      
      public static function SelectOneByTableColumn($tableName, $columnName, $columnValue)
      {
      	$con = 	new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
      	$sth = $con->prepare("SELECT * FROM ". $tableName . " WHERE ". $columnName . " = :" . $columnName);
      	
      	$valueArray = array($columnName => $columnValue);
      	
      	$sth->setFetchMode(PDO::FETCH_OBJ);
      	$sth->execute($valueArray);
      	$obj = $sth->fetch();
      	
      	return $obj;
      }
      
      public static function QueryExec($query)
      {
      	$con = 	new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
      	$statment = $con->query($query);
      	$statment->execute();
      	

      }
      
      public static function QueryExecWithData($query, $valueArray)
      {
      	
      	$con = 	new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
      	$sth = $con->prepare($query);
      	$sth->setFetchMode(PDO::FETCH_OBJ);
      	$sth->execute($valueArray);
      	
      }
      
      public static function QueryIntoClass($query, $className)
      {
      	$con = 	new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
      	$statment = $con->query($query);
      	$obj = $statment->fetchAll(PDO::FETCH_CLASS, $className);
      	
      	return obj;
      
      }
      
      public static function QueryOneIntoClass($query, $className)
      {
      	$con = 	new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
      	$statment = $con->query($query);
      	$obj = $statment->fetch(PDO::FETCH_CLASS, $className);
      	 
      	return obj;      	     	
      }

      public static function QueryIntoClassWithData($query, $className, $valueArray)
      {
      	$con = 	new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
      	$statment = $con->prepare($query);
      	$statment->execute($valueArray);
      	$obj = $statment->fetchAll(PDO::FETCH_CLASS, $className);
      	 
      	return obj;
      
      }
      
      public static function QueryOneIntoClassWithData($query, $className, $valueArray)
      {
      	$con = 	new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
      	$statment = $con->prepare($query);
      	$statment->execute($valueArray);
      	$obj = $statment->fetch(PDO::FETCH_CLASS, $className);
      
      	return obj;
      }



      ############################################################
      ### INSTANCE METHODS - Validation, Load, Save
      ############################################################

      # Placeholder; Override this within individual models!
      public function validate()
      {
      		return true;
      }
      
      public function validateMetadata()
      {
      	 $valid = true;
      	 
      	 if(count($this->metadata) > 0)
      	 {
      	 	$props = (new ReflectionObject($this))->getProperties(ReflectionProperty::IS_PUBLIC);  
        	foreach($props as $k)
        	{
        		$propName = $k->getName();
        		$propValue = $k->getValue($this);
        		
        		$meta = $this->metadata[$propName];
        		$extra = $meta->extra;
        		$default = $meta->default;
        		          	 			
        		if(isset($meta))
        		{
        			#Validate Nullable
        			
        			$nullable = $meta->nullable;
        			if(! $nullable )
        			{
        				if( (!isset($propValue) || $propValue == null) && $extra != 'auto_increment' && !isset($default))
        				{
        					$valid = false;
        					$this->validationErrors[$propName] = "The $propName value is required.  Value current set to: " . $propValue;
        				}
        			}
        			
          	 		#Validate Type
          	 		
          	 		$dbType = $meta->type; 		
          	 		if($dbType == "int" || $dbType == "INT")
          	 		{
          	 			
          	 			$propValue = (int)$propValue;
          	 			if(!is_Numeric($propValue))
          	 			{
          	 				if(!$nullable)
          	 				{
          	 					if($extra != 'auto_increment')
          	 					{
		          	 				$valid = false;
		          	 				$this->validationErrors[$propName] = "$propName must be of type int. Value is: " . $propValue;
          	 					}
          	 				}
          	 			}
          	 		}
          	 		else if($dbType == "binary"|| $dbType == "BINARY")
          	 		{
          	 			if(!is_bool($propValue) && !$nullable)
          	 			{
          	 				$valid = false;
          	 				$this->validationErrors[$propName] = "$propName must be of type boolean. Current value is ". $propValue;
          	 			}  
          	 		}
          	 		else if($dbType == "float"|| $dbType == "FLOAT")
          	 		{
          	 			if(!is_float($propValue) && !$nullable)
          	 			{
          	 				$valid = false;
          	 				$this->validationErrors[$propName] = "$propName must be of type float.";
          	 			}          	 			
          	 		}
          	 		else if($dbType == "double"|| $dbType == "DOUBLE")
          	 		{
          	 			if(!is_double($propValue) && !$nullable)
          	 			{
          	 				$valid = false;
          	 				$this->validationErrors[$propName] = "$propName must be of type double.";
          	 			}          	 			
          	 		}
          	 		else if($dbType == "datetime" || $dbType == "DATETIME")
          	 		{
          	 			if(strtotime($propValue) == false && !$nullable)
          	 			{
           	 				$valid = false;
          	 				$this->validationErrors[$propName] = "$propName must be a valid Date.";         	 				
          	 			}
          	 		}
          	 		else if($dbType == "char" || $dbType == "varchar")
          	 		{
          	 			#Validate String Length
          	 			
          	 			$maxLength = $meta->length;
          	 			$strLength = strlen($propValue);
          	 			
          	 			if($strLength > $maxLength)
          	 			{
          	 				$valid = false;
          	 				$this->validationErrors[$propName] = "$propName must less than or equal to $maxLength characters long.";
          	 			}
          	 		}
        		}

      	 	}
      	 }
      	 
      	 return $valid;
      }


      	protected function loadPropertiesFromDatabase()
      	{
      		
      		$con = 	new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
      		$keyName = $this->key_name;
      		
        	$sql = "SELECT * FROM ". $this->table_name ." WHERE " .$this->key_name . " = ? ";
        	$q = $con->prepare($sql);
        	$q->execute(array($this->$keyName));
     		$this->properties = $q->fetch(PDO::FETCH_ASSOC);
        }
        
        public function ToJson()
        {
        	$props = (new ReflectionObject($this))->getProperties(ReflectionProperty::IS_PUBLIC);  
        	
        	$resultArray = array();
        	
        	foreach($props as $k)
        	{
        		$propName = $k->getName();
        		$propValue = $k->getValue($this);
        		
        		$resultArray[$propName] = $propValue;
        	}
        	
        	// Get Additional data
        	foreach($this->additionalData as $k => $v)
        	{
        		$resultArray[$k] = $v;
        	}
        	
        	return json_encode($resultArray);
        	
        }
        
        public function ToStdObject()
        {
        	$props = (new ReflectionObject($this))->getProperties(ReflectionProperty::IS_PUBLIC);  
        	$resultClass = new stdClass();
        	
        	foreach($props as $k)
        	{
        		$propName = $k->getName();
        		$propValue = $k->getValue($this);
        		
        		$resultClass->$propName = $propValue;
        	}
        	
        	// Get Additional data
        	foreach($this->additionalData as $k => $v)
        	{
        		$resultClass->$k = $v;
        	}
        	
        	return $resultClass;
        }
        
        public function ToArray()
        {
        	$props = (new ReflectionObject($this))->getProperties(ReflectionProperty::IS_PUBLIC);  
        	
        	$resultArray = array();
        	
        	foreach($props as $k)
        	{
        		$propName = $k->getName();
        		$propValue = $k->getValue($this);
        		
        		$resultArray[$propName] = $propValue;
        	}
        	
        	// Get Additional data
        	foreach($this->additionalData as $k => $v)
        	{
        		$resultArray[$k] = $v;
        	}
        	
        	return $resultArray;        	
        }
        
        
        public function save()
       {      	
       		$con = 	new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
       		$con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
       		
        	# Validations MUST pass!
        	if($this->validate() === false) { return false; }

			# Validate Metadata
			if($this->validateMetadata() === false) 
			{
				
				$this->last_error_message = implode(" ",$this->validationErrors) ;
				
				echo "ERRORS: " . $this->last_error_message . "<BR>";
				
				return false; 
			}
			
			
        	# Table Name
        	$table_name = $this->table_name;
			$keyName = $this->key_name;
			$autoKey = $this->auto_key;
        	
        	# Create SQL Query
        	
        	$props = (new ReflectionObject($this))->getProperties(ReflectionProperty::IS_PUBLIC);       	
        	$total_properties_count = count($props);
        	
        	$insert = true;
        	
        	$sql_fields = "";
        	$sql_insert_values = "";
        	$sql_update = "";
        	$keyValue = 0;
        	
        	$valueArray = array();
        	
			//echo "UPDATE OR INSERT FOR: ". $table_name . "<BR>";
        	
        	foreach($props as $k)
        	{
        		$propName = $k->getName();
        		$propValue = $k->getValue($this);
        			
        		       		
        		//echo "Prop name: $propName Prop Value: $propValue<BR>";
        		
        		if($propName == $keyName && isset($propValue))
        		{
        			$keyValue = $propValue;
        			$insert = false;
        			$valueArray[$propName] = $propValue;    

        			// check if object exists
        			if($this->auto_key == false)
        			{
	        			$obj = $this->find($propValue);
	        			if(count($obj)== 0)
	        			{
	        				//echo "NO ITEM WITH KEY: $propValue found.  INSERTING ITEM <BR>";
	        				$insert = true;
	        				$sql_fields  = $sql_fields. ", " . $propName;
	        				$sql_insert_values = $sql_insert_values. ", :" . $propName;
	        			}
        			}
        		}
        		else
        		{
        			if($this->auto_key)
        			{
        				if($propName != $keyName)
        				{
        					$sql_fields  = $sql_fields. ", " . $propName;
        					$sql_insert_values = $sql_insert_values. ", :" . $propName;
        					$valueArray[$propName] = $propValue;
        				}
        			}
        			else
        			{
        				//echo "AUTO KEY FALSE <BR>";
        				
	        			$sql_fields  = $sql_fields. ", " . $propName;
	        			$sql_insert_values = $sql_insert_values. ", :" . $propName;
	        			$valueArray[$propName] = $propValue;
        			}
        			$sql_update =$sql_update. ", ".$propName . "= :" . $propName;	

        		}
        	}
        	$sql_fields = substr($sql_fields, 1);
        	$sql_insert_values = substr($sql_insert_values, 1);
        	$sql_update = substr($sql_update, 1);
        	$sql = "";
        	
        	
        	if($insert == true)
        	{
        		$sql = "INSERT INTO " . $this->table_name . "(" . $sql_fields . ") VALUES (" . $sql_insert_values . ");";
        		
        		//echo "SQL INSERT STATEMENT: $sql <BR>";
        	}
        	else 
        	{
        		$sql = "UPDATE " . $this->table_name . " SET " . $sql_update . " WHERE " . $this->key_name . " = :" . $keyName;
        		//echo "SQL UPDATE STATEMENT: $sql <BR>";
        	}
        	
        	//echo "SQL: " . $sql;
        	
        	
        	$sth = $con->prepare($sql);
        	
        	try
        	{
        		$run = $sth->execute($valueArray);
        		
        		
        	}
        	catch(Exception $er)
        	{
        		$this->last_error_message = $er;
        	}
        	
        	if($run)
        	{
        		if($insert == true)
        		{
        			return $con->lastInsertId();
        		}
        		else
        		{
        			$this->last_error_message = $sth->errorInfo();
        		}
        		//echo "INSERT OR UPDATE SUCCESS <BR>";
        	}
        	else
        	{
        		//echo "INSERT OR UPDATE FAILURE <BR>";
        		$this->last_error_message = $sth->errorInfo();
        	}
        	
        	      	
 		 }
 		 
 		 public function delete($id)
 		 {
 		 	$con = 	new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
 		 	$con->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
 		 	
 		 	$sql = "DELETE FROM " . $this->table_name . " WHERE " . $this->key_name . " = :" . $this->key_name;
 		 	
 		 	$q = $con->prepare($sql);
 		 	$q->execute(array($id));
 		 	$this->last_error_message = $q->errorInfo();
 		 }
}