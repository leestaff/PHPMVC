<?php

      
class KendoDataSource{

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
	
	function __construct($controller, $readAction, $additionalData = null, $updateAction = null, $createAction = null, $deleteAction = null, $pageSize = null)
	{
		
		$readUrl = HTML::UrlVar( $readAction, $controller, $additionalData);
		
		$this->readUrl = $readUrl;
		
		if(isset($updateAction))
		{
			$updateUrl = HTML::UrlVar($updateAction, $controller, $additionalData);
			$this->updateUrl = $updateUrl;
		}
		if(isset($createAction))
		{
			$createUrl = HTML::UrlVar( $createAction, $controller, $additionalData);
			$this->createUrl = $createUrl;
		}
		if(isset($deleteAction))
		{
			$deleteUrl = HTML::UrlVar( $deleteAction, $controller, $additionalData);
			$this->deleteUrl = $deleteUrl;
		}
		$this->pageSize = 25;
		
		if(isset($pageSize))
		{
			$this->pageSize = $pageSize;
		}
		
		$this->model = new KendoModel();	
	}
	
	public $readUrl;
	public $updateUrl;
	public $createUrl;
	public $deleteUrl;
	public $model;	
	public $pageSize;
	
	public function SetId($key)
	{
		$this->model->id = $key;
	}
	
	public function AddField($name, $type, $editable, $nullable = null, $validation = null, $defaultValue = null)
	{
		$this->model->AddField($name, $type, $editable, $nullable, $validation, $defaultValue);
	}
	
	public function DataSourceString()
	{
		$dsString = "";
		
		$dsString .= " dataSource: { \r\n";
		$dsString .= "\t transport: { \r\n";
		$dsString .= "\t\t read: {\r\n";
		$dsString .= "\t\t\t url: \"" . $this->readUrl . "\",  \r\n";
		$dsString .= "\t\t\t type: \"get\", \r\n";
		$dsString .= "\t\t\t dataType: \"json\", \r\n";
		$dsString .= "\t\t }, \r\n";
		
		if(isset($this->updateUrl))
		{
			$dsString .= "\t\t update: \"".  $this->updateUrl. "\", \r\n";
		}
		if(isset($this->createUrl))
		{
			$dsString .= "\t\t create: \"".  $this->createUrl. "\", \r\n";
		}		
		if(isset($this->deleteUrl))
		{
			$dsString .= "\t\t destroy: {\r\n";
			$dsString .= "\t\t\t url: \"".  $this->deleteUrl. "\", \r\n";
			$dsString .= "\t\t\t type: \"get\", \r\n";
			$dsString .= "\t\t\t dataType: \"json\", \r\n";
			$dsString .= "\t\t }, \r\n";
		}
		$dsString .= "\t\t parameterMap: function(options, operation) {   \r\n";
		$dsString .= "\t\t\t if (operation != \"read\" && options.models) { \r\n";
		$dsString .= "\t\t\t\t  alert(operation); return { models: kendo.stringify(options.models), operation: operation}; \r\n";
		$dsString .= "\t\t\t } else { options.type = operation;  return options; }\r\n";
		$dsString .= "\t\t }, \r\n";
		$dsString .= "\t }, \r\n";
		
		
		$dsString .= "\t dataType: \"json\", \r\n";
		$dsString .= "\t type: 'get', \r\n";
		$dsString .= "\t schema: { \r\n";
		$dsString .= "\t\t data: 'data', \r\n";
		$dsString .= "\t\t total: 'total', \r\n";
		
		$dsString .= $this->model->ToModelString();
		
		$dsString .= "\t }, \r\n";
		$dsString .= "\t pageSize: ". $this->pageSize .", \r\n";
		$dsString .= "\t serverPaging: true, \r\n";
		$dsString .= "\t serverFiltering: true, \r\n";
		$dsString .= "\t serverSorting: true, \r\n";
		$dsString .= "} \r\n";
		return $dsString;
	}
	
	
	
}