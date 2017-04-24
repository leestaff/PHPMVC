<?php

class KendoFieldModel {
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
	
	public $name;
	public $editable;
	public $type;
	public $validation;
	public $defaultValue;
	public $nullable;
	

}
?>