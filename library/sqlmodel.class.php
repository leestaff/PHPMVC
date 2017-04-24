<?php
class SQLModel extends SQLQuery {
  
    function __construct() {

        $this->table_name = get_called_class().'s';	
        $this->key_name = 'id';
        $this->auto_key = true;
    }
 
    
    function __destruct() {
    }
    
    /**
	 * Description
	 * returns an IList object.
	 */
    public function Read()
    {
    	return new IList($this->all());
    }

	/**
	 * Description
	 * Requires a FilterModel array as input
	 * returns an IList object.
	 */
	public function ReadWhere(array $filterModelList)
	{
		$whereArray = array();
		$operatorArray = array();
		$logicalOperator = "AND";
		
		foreach($filterModelList as $col => $val)
		{
			$whereArray[$val->filter] = $val->value;
			$operatorArray[$val->filter] = $val->operator;
			$logicalOperator = $val->logicalOperator;
		}
		
		return new IList($this->allByArray($whereArray, $operatorArray, $logicalOperator));
	}
	
		/**
	 * Description
	 * Requires a select array that lists the properties to be selected
	 * return an IList object
	 */
	public function ReadSelect(array $selectArray)
	{
		return new IList($this->allSelect($selectArray));
	}
	
		/**
	 * Description
	 * Requires a select array that lists the properties to be selected
	 * Requires a FilterModel array as input
	 * return an IList object
	 */
	public function ReadSelectWhere(array $selectArray, array $filterModelList)
	{
		$whereArray = array();
		$operatorArray = array();
		$logicalOperator = "AND";
		
		foreach($filterModelList as $col => $val)
		{
			$whereArray[$val->filter] = $val->value;
			$operatorArray[$val->filter] = $val->operator;
			$logicalOperator = $val->logicalOperator;
		}
		
		$result = $this->allSelectByArray($selectArray, $whereArray, $operatorArray, $logicalOperator);
				
		return new IList($result);
	}
	
	/**
	 * Description
	 * Requires a FilterModel array as input
	 * Returns a single object
	 */
	public function SingleWhere(array $filterModelList)
	{
		$whereArray = array();
		$operatorArray = array();
		$logicalOperator = "AND";
		
		foreach($filterModelList as $col => $val)
		{
			$whereArray[$val->filter] = $val->value;
			$operatorArray[$val->filter] = $val->operator;
			$logicalOperator = $val->logicalOperator;
		}
		
		return $this->findByArray($whereArray, $operatorArray, $logicalOperator);
		
	}
	
	public function GetById($id)
	{
		return $this->find($id);
	}
    
    
    public function Bind()
    {
    	$postVars = $_REQUEST;
    	
    	$binded = false;
    	
    	foreach($postVars as $k => $v)
    	{
    		if(property_exists($this, $k))
    		{
       			$this->$k = $v;
       			
       			$binded = true; 			
    		}
    	}
    	
    	return $binded;
    }
    
    public function BindToArray(array $postVars)
    {	
    	
    	$binded = false;
    	
    	foreach($postVars as $k => $v)
    	{
    		//echo "param Name: " . $k . " Key name: " . $this->key_name;
    		if($k === $this->key_name)
    		{
    			// Get Model Data
    			//echo "FOUND KEY NAME ". $k ." and key: ".$v . "<BR>";
    			$dbObj = $this->find($v);
    			
    			//print_r($dbObj);
    			
    			// Now Update Properties with POSTED Data
    			
    			
    			foreach($postVars as $key => $val)
		    	{
		    		if(property_exists($dbObj, $key))
		    		{
		       			$dbObj->$key = $val;
		       			
		       			$binded = true; 			
		    		}
		    	}
		    	
		    	return $dbObj;
		    	
    		}
    	}
    	
    	return $binded;  	
    }
    
    
    public function BindToData()
    {
    	$postVars = $_REQUEST;
    	
    	$binded = false;
    	
    	foreach($postVars as $k => $v)
    	{
    		//echo "File Name: " . $k . " Key name: " . $this->key_name;
    		
    		if($k === $this->key_name)
    		{
    			// Get Model Data
    			//echo "FOUND KEY NAME and key: ".$v . "<BR>";
    			$dbObj = $this->find($v);
    			
    			//print_r($dbObj);
    			
    			// Now Update Properties with POSTED Data
    			if(isset($dbObj))
    			{
	    			foreach($postVars as $key => $val)
			    	{
			    		
			    		
			    		if(property_exists($dbObj, $key))
			    		{
			       			$dbObj->$key = $val;
			       			
			       			$binded = true; 			
			    		}
			    		
			    	}
			    	
			    	return $dbObj;
    			}
    		}
    	}
    	
    	return $binded;
    }
    
}