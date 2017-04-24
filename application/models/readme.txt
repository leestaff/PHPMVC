Use this folder to place your models

Use the Admin Controller GenerateModelClasses page to create the php code used for the models 


Here are some example model classes:


<?php
class venue_promo_template_table extends SQLModel
{
	public $templateId;
	public $templateName;
	public $venueId;
  
	protected $Elements;
		
	function __construct()
	 {
	 	$this->auto_key = true;
		$this->table_name = "venue_promo_template_table";	
		$this->key_name = "templateId";

		$ElementRelationship = new RelationshipModel();
		$ElementRelationship->ModelProperty = "Elements";
		$ElementRelationship->ClassName = "venue_promo_template_elements_table";
		$ElementRelationship->RelationshipType = 0; // ONE TO MANY
		$ElementRelationship->ModelKey = "templateId";
		$ElementRelationship->ForeignKey = "templateId";	
		
		$this->relationships = array($ElementRelationship);		
	}
}
?>


<?php
class venue_promo_elements_table extends SQLModel
{
	public $promo_elementId;
	public $promoId;
	public $templateId;
	public $elementId;
	public $text;
	
	// Relationships
	protected $Template;
	protected $Element;

		
	function __construct()
	 {
	 	$this->auto_key = true;
		$this->table_name = "venue_promo_elements_table";	
		$this->key_name = "promo_elementId";

		$TemplateRelationship = new RelationshipModel();
		$TemplateRelationship->ModelProperty = "Template";
		$TemplateRelationship->ClassName = "venue_promo_template_table";
		$TemplateRelationship->RelationshipType = 1; // INVERSE RELATIONSHIP
		$TemplateRelationship->ModelKey = "templateId";
		$TemplateRelationship->ForeignKey = "templateId";
		
		$ElementRelationship = new RelationshipModel();
		$ElementRelationship->ModelProperty = "Element";
		$ElementRelationship->ClassName = "venue_promo_template_elements_table";
		$ElementRelationship->RelationshipType = 1; // ONE TO ONE
		$ElementRelationship->ModelKey = "elementId";
		$ElementRelationship->ForeignKey = "elementId";		
			
		$this->relationships = array($TemplateRelationship, $ElementRelationship);	
		
	}
}
?>
