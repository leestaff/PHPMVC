<?php


class viewmodel{
	
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
    
    public function MapFromData($data)
    {
    	$dataProps = (new ReflectionObject($data))->getProperties(ReflectionProperty::IS_PUBLIC); 
    	
    	foreach($dataProps as $k)
        {
        		$propName = $k->getName();
        		$propValue = $k->getValue($data);
        		
        		if(property_exists($this, $propName))
        		{
        			$this->{$propName} = $propValue;
        		}
        }
    	
    }
    
    public function MapToData($data)
    {
     	$dataProps = (new ReflectionObject($this))->getProperties(ReflectionProperty::IS_PUBLIC); 
    	
    	foreach($dataProps as $k)
        {
        		$propName = $k->getName();
        		$propValue = $k->getValue($data);
        		
        		if(property_exists($data, $propName))
        		{
        			$data->{$propName} = $propValue;
        		}
        }   	
    }
	
}