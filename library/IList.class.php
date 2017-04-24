<?php

require_once(ROOT . DS . 'extensions' . DS . 'PHPLinq' . DS . 'LinqToObjects.php');
require_once(ROOT . DS . 'extensions' . DS . 'PHPLinq.php');

class IList {


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
	
	public $data = array();
	
	function __construct($dataArray = null) {
		
		if(isset($dataArray))
		{
			foreach($dataArray as $k => $v)
			{
				array_push($this->data, $v);
			}
		}
		
	}
	
	public function Add($dataItem)
	{
		array_push($this->data, $dataItem);
	}
	
	public function where($linqQuery)
	{
		return new IList(from('$x')->in($this->data)->where($linqQuery)->select('$x')); 	
	}
	
	public function select($linqQuery)
	{
		return new IList(from('$x')->in($this->data)->select($linqQuery)); 
	}
	
	public function orderBy($linqQuery)
	{
		return new IList(from('$x')->in($this->data)->orderBy($linqQuery)->select('$x')); 
	}
	
	public function orderByDescending($linqQuery)
	{
		return new IList(from('$x')->in($this->data)->orderByDescending($linqQuery)->select('$x')); 
	}
	
	public function skip($n)
	{
		return new IList( from('$x')->in($this->data)->skip($n)->select('$x'));
	}
	
	public function take($n)
	{
		return new IList(from('$x')->in($this->data)->take($n)->select('$x'));
	}
	
	public function sum($linqQuery)
	{
		return from('$x')->in($this->data)->sum($linqQuery);
	}
	
	public function firstOrDefault($linqQuery)
	{
		return  from('$x')->in($this->data)->firstOrDefault($linqQuery);
	}
	
	public function ListCount()
	{
		return count($this->data);
	}
	
	public function ListCountQuery($linqQuery)
	{
		return count($this->where($linqQuery));
	}
	
	public function any($linqQuery)
	{
		return from('$x')->in($this->data)->any($linqQuery);
	}
	
	public function all($linqQuery)
	{
		return from('$x')->in($this->data)->all($linqQuery);
	}
	
	public function contains($element)
	{
		return from('$x')->in($this->data)->contains($element);
	}
	
	public function elementAtOrDefault($i)
	{
		return from('$x')->in($this->data)->elementAtOrDefault($i);
	}
	
	
	

}
