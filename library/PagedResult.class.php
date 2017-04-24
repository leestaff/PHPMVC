<?php

class PagedResult {

	public $CurrentPage;
	public $PageSize;
	public $PageCount;
	public $hasNext;
	public $hasPrev;
	public $ItemCount;
	public $DataSet;
	
	public function __construct($Data, $p, $ps, $ic)
	{
		$this->DataSet = $Data;
		$this->CurrentPage = $p;
		$this->PageSize = $ps;
		$this->ItemCount = $ic;
		
		$this->PageCount = ceil($ic / $ps);
		
		if($p <= 1)
		{
			$this->hasPrev = false;
		}
		else
		{
			$this->hasPrev = true;
		}
		
		if($p >= $this->PageCount)
		{
			$this->hasNext = false;
		}
		else
		{
			$this->hasNext = true;
		}
		
	}
  	
	
}
?>