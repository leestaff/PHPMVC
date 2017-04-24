<?php
class RelationshipModel
{
	public $ModelProperty;  // Name of the property variable in the model that will be populated with data
	public $ClassName;  // Model class of foreign object
	public $RelationshipType;   //  1 - One to One, 0 - One to Many
	public $ModelKey; // Model key - example the model includes a property called ColorId that links to a the "Color" table and it's primary key
	public $ForeignKey; // Foreign key
	
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
}