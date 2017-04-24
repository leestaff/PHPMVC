<?php
class HomeController extends Controller
{	
	public function Index()
	{
		//$this->set('featured', "Sample Featured Content");
		
		$this->RenderView();
	}
	
	public function Menu()
	{
		$this->RenderPartialView();
	}
}
