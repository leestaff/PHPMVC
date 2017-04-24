<?php



class KendoDataSourceResult {


	public function __get($property) {
		if (property_exists($this, $property)) {
			return $this->$property;
		}
	}
	
	public function __set($property, $value) {
		if (property_exists($this, $property)) {
			$this->$property = $value;
		}
	}


	 protected $db;

    private $stringOperators = array(
        'neq' => 'NOT LIKE',
        'doesnotcontain' => 'NOT LIKE',
        'contains' => 'LIKE',
        'startswith' => 'LIKE',
        'endswith' => 'LIKE',
        'eq' => '=',
        'gt' => '>',
        'gte' => '>=',
        'lt' => '<',
        'lte' => '<=',
        'neq' => '!='
    );

    private $operators = array(
        'eq' => '=',
        'gt' => '>',
        'gte' => '>=',
        'lt' => '<',
        'lte' => '<=',
        'neq' => '!='
    );

    private $aggregateFunctions = array(
        'average' => 'AVG',
        'min' => 'MIN',
        'max' => 'MAX',
        'count' => 'COUNT',
        'sum' => 'SUM'
    );

	public $request;
	public $table;
	public $key;
	public $properties;

    function __construct($requestData = null, $table = null, $key = null, $properties = null) {
    	
        $this->db = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME , DB_USER, DB_PASSWORD);
        
        if(isset($table))
		{
			$this->table = $table;
		}
        if(isset($requestData))
		{
			$this->request = $requestData;
		}
		if(isset($key))
		{
			$this->key = $key;
		}
		else
		{
			if(isset($table) && is_object ($table))
			{
				if(is_subclass_of($table, 'SQLQuery'))
				{
					$this->key = $table->key_name;
				}
			}
		}

		if(isset($properties))
		{
			$this->properties = $properties;
		}
    }
    
    public function Crud($request = null)
    {
    	if(!isset($request))
    	{
    		$request = $this->request;
    	}
    	
    	if(isset($this->table) && isset($request))
    	{
    		switch($request->type)
    		{
    		 	case 'create':
            	$result = $this->create($this->table, $this->properties, $request->models, $this->key);
		            break;
		        case 'read':
		            $result = $this->read($this->table, $this->properties, $request);
		            break;
		        case 'update':
		            $result = $this->update($this->table, $this->properties, $request->models, $this->key);
		            break;
		        case 'destroy':
		            $result = $this->destroy($this->table, $request->models, $this->key);
		            break;
    		}
    		
    		return $result;
    		
    	}
    	else
    	{
    		throw "Table or model not set"; 
    	}
    }
    

    public function total($tableName, $properties, $request) {
        if (isset($request->filter)) {
            $where = $this->filter($properties, $request->filter);
            
            if(is_object($tableName))
            {
	            if(is_subclass_of($tableName, 'SQLModel'))
	        	{        
	        		$filterModelList = FilterModel::SQLToFilterModelList("SELECT COUNT(*) FROM DUMMYTABLE $where");
	        		$list = $tableName->ReadWhere($filterModelList);
	        				
	        		return $list->ListCount();
	        	}
	        	else if(get_class($tableName) == 'IList')
	        	{
	        		//List count 
	        		$whr = FilterModel::SQLToLINQWhere("SELECT COUNT(*) FROM DUMMYTABLE $where");
	        		$reduced = $tableName->where($whr);
	        		return $reduced->ListCount();
	        	}
            }
        	else
        	{
            
	            $statement = $this->db->prepare("SELECT COUNT(*) FROM $tableName $where");
	            $this->bindFilterValues($statement, $request->filter);
	            $statement->execute();
		        $total = $statement->fetch(PDO::FETCH_NUM);
		        return (int)($total[0]);
        	}
            
        } else {
        	
        	if(is_object ($tableName))
        	{
	        	if(is_subclass_of($tableName, 'SQLModel') && is_object ($tableName))
	        	{        	
	        		$queryResult = 	$tableName->Read();
	        		
					return $queryResult->ListCount();
	        	}
	        	else if(get_class($tableName) == 'IList')
	        	{
	        		//List count 
	        		
	        		return $tableName->ListCount();
	        	}
        	}
        	else
        	{
        		// Default run query on whole table
        		
            	$statement = $this->db->prepare("SELECT COUNT(*) FROM $tableName");
	            $statement->execute();
	        	$total = $statement->fetch(PDO::FETCH_NUM);
	        	return (int)($total[0]);
        	}
        }


    }

    private function page() {
        return ' LIMIT :skip,:take';
    }

    private function group($data, $groups, $table, $request, $propertyNames) {
        if (count($data) > 0) {
            return $this->groupBy($data, $groups, $table, $request, $propertyNames);
        }
        return array();
    }

    private function mergeSortDescriptors($request) {
        $sort = isset($request->sort) && count($request->sort) ? $request->sort : array();
        $groups = isset($request->group) && count($request->group) ? $request->group : array();

        return array_merge($sort, $groups);
    }

    private function groupBy($data, $groups, $table, $request, $propertyNames) {
        if (count($groups) > 0) {
            $field = $groups[0]->field;
            $count = count($data);
            $result = array();
            $value = $data[0][$field];
            $aggregates = isset($groups[0]->aggregates) ? $groups[0]->aggregates : array();

            $hasSubgroups = count($groups) > 1;
            $groupItem = $this->createGroup($field, $value, $hasSubgroups, $aggregates, $table, $request, $propertyNames);

            for ($index = 0; $index < $count; $index++) {
                $item = $data[$index];
                if ($item[$field] != $value) {
                    if (count($groups) > 1) {
                        $groupItem["items"] = $this->groupBy($groupItem["items"], array_slice($groups, 1), $table, $request, $propertyNames);
                    }

                    $result[] = $groupItem;

                    $groupItem = $this->createGroup($field, $data[$index][$field], $hasSubgroups, $aggregates, $table, $request, $propertyNames);
                    $value = $item[$field];
                }
                $groupItem["items"][] = $item;
            }

            if (count($groups) > 1) {
                $groupItem["items"] = $this->groupBy($groupItem["items"], array_slice($groups, 1), $table, $request, $propertyNames);
            }

            $result[] = $groupItem;

            return $result;
        }
        return array();
    }

    private function addFilterToRequest($field, $value, $request) {
        $filter = array(
            'logic' => 'and',
            'filters' => array(
                array(
                    'field' => $field,
                    'operator' => 'eq',
                    'value' => $value
                ))
            );

        if (isset($request->filter)) {
            $filter['filters'] = $request->filter;
        }

        return  array('filter' => $filter);
    }

    private function addFieldToProperties($field, $propertyNames) {
        if (!in_array($field, $propertyNames)) {
            $propertyNames[] = $field;
        }
        return $propertyNames;
    }

    private function createGroup($field, $value, $hasSubgroups, $aggregates, $table, $request, $propertyNames) {
        if (count($aggregates) > 0) {
            $request = $this->addFilterToRequest($field, $value, $request);
            $propertyNames = $this->addFieldToProperties($field, $propertyNames);
        }

        $groupItem = array(
            'field' => $field,
            'aggregates' => $this->calculateAggregates($table, $aggregates, $request, $propertyNames),
            'hasSubgroups' => $hasSubgroups,
            'value' => $value,
            'items' => array()
        );

        return $groupItem;
    }

    private function calculateAggregates($table, $aggregates, $request, $propertyNames) {
        $count = count($aggregates);

        if (count($aggregates) > 0) {
            $functions = array();

            for ($index = 0; $index < $count; $index++) {
                $aggregate = $aggregates[$index];
                $name = $this->aggregateFunctions[$aggregate->aggregate];
                $functions[] = $name.'('.$aggregate->field.') as '.$aggregate->field.'_'.$aggregate->aggregate;
            }

            $sql = sprintf('SELECT %s FROM %s', implode(', ', $functions), $table);

		    if(is_subclass_of($table, 'SQLModel'))
        	{        	
        		$sql = sprintf('SELECT %s FROM %s', implode(', ', $functions), $table->table_name);
        	}
        	else if(get_class($table) == 'IList')
        	{
        		throw "Not implimented for ILists";
        	}


            if (isset($request->filter)) {
                $sql .= $this->filter($propertyNames, $request->filter);
            }

            $statement = $this->db->prepare($sql);

            if (isset($request->filter)) {
                $this->bindFilterValues($statement, $request->filter);
            }

            $statement->execute();

            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

            return $this->convertAggregateResult($result[0]);
        	
        }
        return (object)array();
    }

    private function convertAggregateResult($propertyNames) {
        $result = array();

        foreach($propertyNames as $property => $value) {
            $item = array();
            $split = explode('_', $property);
            $field = $split[0];
            $function = $split[1];
            if (array_key_exists($field, $result)) {
                $result[$field][$function] = $value;
            } else {
                $result[$field] = array($function => $value);
            }
        }

        return $result;
    }

    private function sort($propertyNames, $sort) {
        $count = count($sort);

        $sql = '';

        if ($count > 0) {
            $sql = ' ORDER BY ';

            $order = array();

            for ($index = 0; $index < $count; $index ++)
             {
            	
                $field = $sort[$index]['field'];

                $dir = 'ASC';

                if ($sort[$index]['dir'] == 'desc' ) {
                    $dir = 'DESC';
                }

                $order[] = "$field $dir";
	                
            }
			//print_r($order);
            $sql .= implode(',', $order);
        }
		//echo "SORT SQL: " . $sql;

        return $sql;
    }

    private function where($properties, $filter, $all) {
        if (isset($filter['filters'])) {
            $logic = ' AND ';

            if ($filter['logic'] == 'or') {
                $logic = ' OR ';
            }

            $filters = $filter['filters'];

            $where = array();

            for ($index = 0; $index < count($filters); $index++) {
                $where[] = $this->where($properties, $filters[$index], $all);
            }

            $where = implode($logic, $where);

            return "($where)";
        }

        $field = $filter['field'];

        $propertyNames = $this->propertyNames($properties);


        $type = "string";

        $index = array_search($filter, $all);

        $value = ":filter$index";

        if (isset($properties[$field])) {
            $type = $properties[$field]['type'];
        } else if ($this->isDate($filter['value'])) {
            $type = "date";
        } else if (array_key_exists($filter['operator'], $this->operators) && !$this->isString($filter['value'])) {
            $type = "number";
        }

        if ($type == "date") {
            $field = "date($field)";
            $value = "date($value)";
        }

        if ($type == "string")
        {	
            $operator = $this->stringOperators[$filter['operator']];
        } else {
            $operator = $this->operators[$filter['operator']];
        }

        return "$field $operator $value";
        
    }

    private function flatten(&$all, $filter) {
        if (isset($filter['filters'])) {
            $filters = $filter['filters'];

            for ($index = 0; $index < count($filters); $index++) {
                $this->flatten($all, $filters[$index]);
            }
        } else {
            $all[] = $filter;
        }
    }

    private function filter($properties, $filter) {
        $all = array();

        $this->flatten($all, $filter);

        $where = $this->where($properties, $filter, $all);

        return " WHERE $where";
    }

    private function isDate($value) {
        $result = date_parse($value);
        return $result["error_count"] < 1 && checkdate($result['month'], $result['day'], $result['year']);
    }

    private function isString($value) {
        return !is_bool($value) && !is_numeric($value) && !$this->isDate($value);
    }

    protected function propertyNames($properties) {
        $names = array();

		if(isset($properties))
		{
	        foreach ($properties as $key => $value) {
	            if (is_string($value)) {
	                $names[] = $value;
	            } else {
	                $names[] = $key;
	            }
	        }
		}
		else if(isset($this->table) && is_object ($this->table)) 
		{
			if(is_subclass_of($this->table, 'SQLModel'))
			{
				$props = (new ReflectionObject($this->table))->getProperties(ReflectionProperty::IS_PUBLIC);  
	        	foreach($props as $k)
	        	{
	        		$propName = $k->getName();
	        		$names[] = $propName;
	        	}
			}
		}
        return $names;
    }

    private function bindFilterValues($statement, $filter) {
        $filters = array();
        $this->flatten($filters, $filter);

        for ($index = 0; $index < count($filters); $index++) {
            $value = $filters[$index]['value'];
            $operator = $filters[$index]['operator'];
            $date = date_parse($value);

            if ($operator == 'contains' || $operator == 'doesnotcontain') {
                $value = "%$value%";
            } else if ($operator == 'startswith') {
                $value = "$value%";
            } else if ($operator == 'endswith') {
                $value = "%$value";
            }

            $statement->bindValue(":filter$index", $value);
        }
    }
    
    private function bindFilterValuesToArray($filter)
    {
    	$whereArray = array();
    	$filters = array();
    	$this->flatten($filters, $filter);

        for ($index = 0; $index < count($filters); $index++) {
            $value = $filters[$index]['value'];
            $operator = $filters[$index]['operator'];
            $date = date_parse($value);

            if ($operator == 'contains' || $operator == 'doesnotcontain') {
                $value = "%$value%";
            } else if ($operator == 'startswith') {
                $value = "$value%";
            } else if ($operator == 'endswith') {
                $value = "%$value";
            }

            $whereArray[":filter$index"] =  $value;
        }
        
        return $whereArray;
    }

    public function create($table, $properties, $models, $key) {
        $result = array();
        $data = array();
        $propertyNames = $this->propertyNames($properties);

        if (!is_array($models)) {
            $models = array($models);
        }

        $errors = array();

		if(is_object ($table))
        {        	
			if(is_subclass_of($table, 'SQLModel'))
	    	{
	    		// ****** Data  SQL Model ****	    		
		        foreach ($models as $model) {
		        	
		        	$newItem = new $table();
		        	
		            foreach ($propertyNames as $property) {
		            	$newItem->$property = $model[$property];
		            }
		            
		            $model[$key] = $newItem->save();
		            $data[] = $model;
		        }
	    		
	    	}
	    	else if(get_class($table) == 'IList')
	    	{
	    		foreach ($models as $model) {
	    			$table->add($model);
	    			 $data[] = $model;
	    		}
	    	}
        }    
        else
        {
			// DEFAULT USE PDO
			
	        foreach ($models as $model) {
	            $columns = array();
	            $values = array();
	            $input_parameters = array();
	
	            foreach ($propertyNames as $property) {
	                if ($property != $key) {
	                    $columns[] = $property;
	                    $values[] = '?';
	                    
	                    $input_parameters[] = $model[$property];
	                }
	            }
	
	            $columns = implode(', ', $columns);
	            $values = implode(', ', $values);
	
	            $sql = "INSERT INTO $table ($columns) VALUES ($values)";
	
	            $statement = $this->db->prepare($sql);
	
	            $statement->execute($input_parameters);
	
	            $status = $statement->errorInfo();
	
	            if ($status[1] > 0) {
	                $errors[] = $status[2];
	            } else {
	                $model[$key] = $this->db->lastInsertId();
	                $data[] = $model;
	            }
	        }
        }
        

        if (count($errors) > 0) {
            $result['errors'] = $errors;
        } else {
            $result['data'] = $data;
        }

        return $result;
    }

    public function destroy($table, $models, $key) {
        $result = array();

        if (!is_array($models)) {
            $models = array($models);
        }

        $errors = array();


		if(is_object ($table))
        {        	
			if(is_subclass_of($table, 'SQLModel'))
	    	{
	    		// ****** Data  SQL Model ****	    		
		        foreach ($models as $model) {
		        	$item = new $table();
		        	$item->delete($model[$key]);
		        }
	    		
	    	}
	    	else if(get_class($table) == 'IList')
	    	{
	    		
	    		/// **** ILIST ***
	    		
	    		foreach ($models as $model) {
					unset($table->data[$key]);
	    		}
	    	}
        }    
        else
        {

	        foreach ($models as $model) {
	            $sql = "DELETE FROM $table WHERE $key=?";
	
	            $statement = $this->db->prepare($sql);
	
	            $statement->execute(array($model->$key));
	
	            $status = $statement->errorInfo();
	
	            if ($status[1] > 0) {
	                $errors[] = $status[2];
	            }
	        }
	
	        if (count($errors) > 0) {
	            $result['errors'] = $errors;
	        }
        }

        return $result;
    }

    public function update($table, $properties, $models, $key) {
        $result = array();

        $propertyNames = $this->propertyNames($properties);
       	$errors = array();
       	
        if (in_array($key, $propertyNames)) {

            if (!is_array($models)) {
                $models = array($models);
            }

			if(is_object ($table))
	        {        	
				if(is_subclass_of($table, 'SQLModel'))
		    	{
		    		// ****** Data  SQL Model ****	    		
			        foreach ($models as $model) {
			        	
			        	$newItem = new $table();
			        	
			        	 //print_r($model);
			        	
			            foreach ($propertyNames as $property) {
			            	$newItem->$property = $model[$property];
			            				            	
			            }
			            
			            $model[$key] = $newItem->save();
			            $data[] = $model;		 
			            
			            if(isset($newItem->last_error_message) > 0)
			            {
			            	$errors[] = $newItem->last_error_message;
			            }         		           
			            
			        }
		    		
		    	} // END OF IF SUBCLASS
		    	else if(get_class($table) == 'IList')
		    	{
		    		
		    		/// **** ILIST ***
		    		
		    		foreach ($models as $model) {
	
						unset($table->data[$key]);
						$table->add($model);
	
		    		}
		    	} // END OF ELSE IF
	        }    // END OF IF OBJECT
	        else
	        {
	
	            foreach ($models as $model) 
	            {
	                $set = array();
	
	                $input_parameters = array();
	
	                foreach ($propertyNames as $property)
	                 {
	                    if ($property != $key)
	                     {
	                        $set[] = "$property=?";
	                        $input_parameters[] = $model->$property;
	                    }
	                }
	
	                $input_parameters[] = $model->$key;
	
	                $set = implode(', ', $set);
	
	                $sql = "UPDATE $table SET $set WHERE $key=?";
	
	                $statement = $this->db->prepare($sql);
	
	                $statement->execute($input_parameters);
	
	                $status = $statement->errorInfo();
	
	                if ($status[1] > 0) 
	                {
	                    $errors[] = $status[2];
	                }
	            }	// END OF FOREACH
		    } // END OF ELSE
        } // END OF IF 


        if (count($errors) > 0) {
            $result['errors'] = $errors;
        }

        if (count($result) == 0) {
            $result = "";
        }

        return $result;
    }

    public function read($table, $properties, $request = null) {
    	
    	if(!isset($request))
    	{
    		$request = $this->request;
    	}
    	
        $result = array();

        $propertyNames = $this->propertyNames($properties);

        $result['total'] = $this->total($table, $properties, $request);

		$tableName = $table;
		if(is_object ($tableName))
        {        	
			if(is_subclass_of($table, 'SQLModel'))
	    	{        	
	    		$tableName =  $table->table_name;
	    		$sql = sprintf('SELECT * FROM %s', $tableName);
	    		if (isset($request->filter)) {
	            	$sql .= $this->filter($properties, $request->filter);
	        	}
	    		$sort = $this->mergeSortDescriptors($request);
		        if (count($sort) > 0) {
		            $sql .= $this->sort($propertyNames, $sort);
		        }
		        if (isset($request->skip) && isset($request->take)) {
		            $sql .= $this->page();
		        }
		        // Bind filters
		        $whereArray = array();
		        if (isset($request->filter)) {
		            $whereArray = $this->bindFilterValuesToArray($request->filter);
		        }
		        //Bind take and skip
	        	if ( isset($request->skip)  && isset($request->take) ) {
	    			
	    			$whereArray[':skip'] = (int)$request->skip;
	    			$whereArray[':take'] = (int)$request->take;
		        }
		        
		        echo "SQL: " . $sql;
		        		        		        
		        $dataset = $table->findAllByQueryWhere($sql, $whereArray);
		        
		        echo "WHERE: ";
		        print_r($whereArray);
		           
		       	print_r($dataset);
		        		        
		        if (isset($request->group) && count($request->group) > 0) {
		            $data = $this->group($dataset, $request->group, $table, $request, $propertyNames);
		            $result['groups'] = $data;
		        } else {
		            $result['data'] = $dataset;
		        }
		
		        if (isset($request->aggregate)) {
		            $result["aggregates"] = $this->calculateAggregates($table, $request->aggregate, $request, $propertyNames);
		        }
		
		        return $result;
	    	}
	    	else if(get_class($table) == 'IList')
	    	{	    			    		
	    		// ****************** READ ILIST ********************************
	    		
	    		//Select
	    		if(isset($properties))
	    		{
	    			$selectLinq = FilterModel::ArrayToLinqSelect($properties);
	    			    			
	    			$table = $table->select($selectLinq);
	    			

	    		}
	    		//Where
	    		$sql = 'SELECT * FROM List ';
	    		if (isset($request->filter)) {
	            	$sql .= $this->filter($properties, $request->filter);
	            	
	            	$whereLinq = FilterModel::SQLToLINQWhere($sql);
	            	$table = $table->where($whereLinq);
	            	
	            						
	        	}
	        	//Sort
	        	if (count($request->sort) > 0) {
	        		
		            $sort = reset($request->sort);
		           $orderLinq = '$x => $x->' . $sort['field'];
		           $orderDir = $sort['dir'];
		           
		           if($orderDir == "DESC" || $orderDir == "desc")
		           {
		           		$table = $table->orderByDescending($orderLinq);
		           }
		           else
		           {
		           		$table = $table->orderBy($orderLinq); 
		           }
		           
		           
		       
		        } // END OF SORT
		        
		        // ---PAGE ---
		        
		        if (isset($request->skip) && isset($request->take)) {
		           
		           $table = $table->skip((int)$request->skip);		           
		           $table = $table->take((int)$request->take);		          
		        }
		        
		        $dataset = $table->data;
		         		        
		        $result['data'] =  $dataset;
		        return $result;	        
	    		
	    	}   // END OF IF ILIST
        }
    	else
    	{ 	
    		
    		// ************* DEFAULT *****************************************
    		
	        $sql = sprintf('SELECT %s FROM %s', implode(', ', $propertyNames), $tableName);
	
	        if (isset($request->filter)) {
	            $sql .= $this->filter($properties, $request->filter);
	        }
	
	        $sort = $this->mergeSortDescriptors($request);
	
	        if (count($sort) > 0) {
	            $sql .= $this->sort($propertyNames, $sort);
	        }
	
	        if (isset($request->skip) && isset($request->take)) {
	            $sql .= $this->page();
	        }
	
	        $statement = $this->db->prepare($sql);
	
	        if (isset($request->filter)) {
	            $this->bindFilterValues($statement, $request->filter);
	        }
	
	        if (isset($request->skip) && isset($request->take)) {
	            $statement->bindValue(':skip', (int)$request->skip, PDO::PARAM_INT);
	            $statement->bindValue(':take', (int)$request->take, PDO::PARAM_INT);
	        }

	        $statement->execute();
	
	        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
	
	        if (isset($request->group) && count($request->group) > 0) {
	            $data = $this->group($data, $request->group, $table, $request, $propertyNames);
	            $result['groups'] = $data;
	        } else {
	            $result['data'] = $data;
	        }
	
	        if (isset($request->aggregate)) {
	            $result["aggregates"] = $this->calculateAggregates($table, $request->aggregate, $request, $propertyNames);
	        }
	
	        return $result;
    	}
    }

    public function readJoin($table, $joinTable, $properties, $key, $column, $request = null) {
        $result = $this->read($table, $properties, $request);

        for ($index = 0, $count = count($result['data']); $index < $count; $index++) {
            $sql = sprintf('SELECT %s FROM %s WHERE %s = %s', $column, $joinTable, $key, $result['data'][$index][$key]);

            $statement = $this->db->prepare($sql);
            $statement->execute();
            $data = $statement->fetchAll(PDO::FETCH_NUM);
            $result['data'][$index]['Attendees'] = $data;
        }

        return $result;
    }
	

}
