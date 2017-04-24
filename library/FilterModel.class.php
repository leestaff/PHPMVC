<?php
require_once(ROOT . DS . 'extensions' .DS .  'PHP-SQL-Parser' . DS . 'src' . DS . 'PHPSQLParser.php');


class FilterModel {

	public function FilterModel($filterName, $operator, $value, $logOp = null)
	{
		$this->filter = $filterName;
		$this->operator = $operator;
		$this->value = $value;
		
		if(isset($logOp))
		{
			$this->logicalOperator = $logOp;
		}
		else
		{
			$this->logicalOperator = "AND";
		}
	}
	
	public static function ArrayToLinqSelect(array $parameters)
	{
		$linq = '$x => new {';
		$parmCount = count($parameters);
		$i = 1;
		foreach($parameters as $col => $val)
		{
			if(is_numeric($col))
			{
				$linq .= '" '. $val .'"=> $x->'.$val.' ';
				if($i != $parmCount)
				{
					$linq .= ',';
				}
			}
			else
			{
				$linq .= '" '. $col .'"=> $x->'.$val.' ';
				if($i != $parmCount)
				{
					$linq .= ',';
				}
			}
			$i++;
		}
		$linq .= " }";
		
		return $linq;
	}
	
	public static function SQLToLINQWhere($sql)
	{
		$filterList = FilterModel::SQLToFilterModelList($sql);
		
		$linq = '$x =>';
		
		$filterLength = count($filterList);
		$i = 1;
		
		foreach($filterList as $filter)
		{
			$linq .= ' $x->' . $filter->filter;
			
			$op = $filter->operator;
			if($op == '=')
			{
				$op = "==";
			}
			if($op == '<>')
			{
				$op = "!=";
			}
			if($op == 'IS')
			{
				$op = "==";
			}
			if($op == 'IS NOT')
			{
				$op = "!=";
			}
			
			
			$linq .= ' ' . $op;
			$linq .= ' ' . $filter->value;
				
			if($i < $filterLength)
			{
				$logOp = $filter->logicalOperator;
				
				if($logOp == 'AND' || $logOp == 'and')
				{
					$logOp = "&&";
				}
				if($logOp == 'OR' || $logOp == 'or')
				{
					$logOp = "||";
				}
				
				$linq .= ' '.$logOp ;
			}
				
			$i++;
				
		}
		
		return $linq;
		
	}
	
	public static function SQLToFilterModelList($sql)
	{
		$parser=new PHPSQLParser($sql, true);
	
		$filterList = array();
		
		$filName = '0';
		$op = '=';
		$valu = 0;
		$logOp = 'AND';	
		
		$colFound = false;
		$opFound = false;
		$valFond = false;	
		
		$whereArray = $parser->parsed['WHERE'];
		
		foreach($whereArray as $col => $val)
		{

			if($val['expr_type'] == 'colref')	
			{
				if($colFound == false)
				{
					$filName = $val['base_expr'];
					$colFound = true;
				}
				else
				{					
					$oneFil = new FilterModel($filName, $op, $valu, $logOp);
					array_push($filterList, $oneFil);
					
					$filName = $val['base_expr'];
					$colFound = true;
					$opFound = false;
					$valFond = false;
					$logOp = 'AND';
				}				
			}
			
			if($val['expr_type'] == 'operator')
			{
				if($opFound == false)
				{
					$op =  $val['base_expr'];
					$opFound = true;
				}
				else
				{
					if($val['base_expr'] == 'NOT')
					{
						$op .= ' NOT';
					}
					else
					{
						$logOp = $val['base_expr'];
					}
				}
			}
			if($val['expr_type'] == 'const')
			{
				$valu = $val['base_expr'];
				$valFond = true;
				
			}

			
		} // END OF FOREACH
		
		// *** ADD LAST FILTER
		
		$lastFil = new FilterModel($filName, $op, $valu, $logOp);
		array_push($filterList, $lastFil);
		
		return $filterList;
		
	}
	
	public function ToFilterList()
	{
		$filterArray = array();
		array_push($filterArray, $this);
		
		return $filterArray;
	}
	
	public function AddToFilterList(array $filterList)
	{
		array_push($filterList, $this);
		return $filterList;
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
	
	public $filter;
	public $operator;
	public $value;
	public $logicalOperator;
	

}
