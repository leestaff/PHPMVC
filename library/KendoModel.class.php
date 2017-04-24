<?php

class KendoModel {
    public function __call($key,$params){
        if(!isset($this->{$key})) throw new Exception("Call to undefined method ".get_class($this)."::".$key."()");
        $subject = $this->{$key};
        call_user_func_array($subject,$params);
    }
    
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
	
	public $id;
	public $fields = array(); 
	
	public function ToModelString()
	{
		$modelStr = "";
		
		$modelStr .=" \t  model: { \r\n";
		$modelStr .="\t\t id:\"" . $this->id . "\", \r\n";
		$modelStr .="\t\t fields: { \r\n";
		foreach($this->fields as $f)
		{
			$modelStr .="\t\t\t". $f->name . ":{";
			
			if(isset($f->type))
			{
				$modelStr .= "type: \"".$f->type."\", ";
			}
			if(isset($f->nullable))
			{
				$modelStr .= "nullable: ".$f->nullable.", ";
			}
			if(isset($f->editable))
			{
				$modelStr .= "editable: ".$f->editable.", ";
			}
			if(isset($f->validation))
			{
				$modelStr .= "validation: {".$f->editable."}, ";
			}	
			if(isset($f->defaultValue))
			{
				$modelStr .= "default: \"".$f->defaultValue."\", ";
			}		
					
			$modelStr .= "},\r\n";
			
		}
		
		$modelStr .= "\t\t } \r\n";
		$modelStr .= "\t } \r\n";
		
		return $modelStr;
	}
	
    public function AddField($name, $type, $editable, $nullable = null, $validation = null, $defaultValue = null)
    {
    	$newField = new KendoFieldModel();
    	$newField->name = $name;
    	$newField->editable = $editable;
    	if(isset($type))
    	{
    		$newField->type = $type;
    	}
    	if(isset($nullable))
    	{
    		$newField->nullable = $nullable;
    	}
    	if(isset($validation))
    	{
    		$newField->validation = $validation;
    	}
    	if(isset($defaultValue))
    	{
    		$newField->defaultValue = $defaultValue;
    	}
    	
    	array_push($this->fields, $newField);
    	
    }
}
