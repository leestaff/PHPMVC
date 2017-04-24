<?php


class KendoDataSourceRequest{
	
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
	
	public $take;
	public $skip;
	public $filter;
	public $group;
	public $aggregate;
	public $page;
	public $pageSize;
	public $sort;
	public $type;
	public $models = array();
	
	public static function CreateJSReadDataSource($readUrl, $pageSize)
	{
		$dsString = "";
		
		$dsString .= " dataSource: { \r\n";
		$dsString .= "\t transport: { \r\n";
		$dsString .= "\t\t read: \"".  $readUrl. "\" \r\n";
		$dsString .= "\t }, \r\n";
		$dsString .= "\t dataType: \"json\", \r\n";
		$dsString .= "\t type: 'get', \r\n";
		$dsString .= "\t schema: { \r\n";
		$dsString .= "\t\t data: 'data', \r\n";
		$dsString .= "\t\t total: 'total', \r\n";
		$dsString .= "\t }, \r\n";
		$dsString .= "\t pageSize: ". $pageSize .", \r\n";
		$dsString .= "\t serverPaging: true, \r\n";
		$dsString .= "\t serverFiltering: true, \r\n";
		$dsString .= "\t serverSorting: true, \r\n";
		$dsString .= "} \r\n";
		return $dsString;
	}
	
	
	public static function FromQueryParameters(array $queryParameters)
	{
		$r = new KendoDataSourceRequest();
		if(isset($queryParameters['take']))
		{
			 $r->take = $queryParameters['take'];
		}
		if(isset($queryParameters['skip']))
		{
			 $r->skip = $queryParameters['skip'];
		}
		if(isset($queryParameters['page']))
		{ 
			$r->page = $queryParameters['page'];
		}		
		if(isset($queryParameters['filter']))
		{
			$fil = $queryParameters['filter'];
			
			if(is_array($fil))
			{
				$r->filter = $queryParameters['filter'];
			}
			else if(strlen(trim($fil) > 0))
			{
			 	$r->filter = $queryParameters['filter'];
			}
		}
		if(isset($queryParameters['group']))
		{
			 $r->group = $queryParameters['group'];
		}
		if(isset($queryParameters['aggregate']))
		{
			 $r->aggregate = $queryParameters['aggregate'];
		}
		if(isset($queryParameters['page']))
		{
			 $r->page = $queryParameters['page'];
		}
		if(isset($queryParameters['pageSize']))
		{
			 $r->pageSize = $queryParameters['pageSize'];
		}
		if(isset($queryParameters['sort']))
		{
			 $r->sort = $queryParameters['sort'];
		}
		if(isset($queryParameters['type']))
		{
			 $r->type = $queryParameters['type'];
		}
		if(isset($queryParameters['models']))
		{
			 $r->models = $queryParameters['models'];
		}
		else
		{
			array_push($r->models, $queryParameters);
		}
		
		return $r;
	}
	
}