<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="EN" lang="EN" dir="ltr">
<head profile="http://gmpg.org/xfn/11">
<title><?php if(isset($title)) {echo $title; } else {echo "My WebSite";}?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php $HTML::includeCSS("site"); ?>
<!--<?php $HTML::includeJs("jquery-2.0.3.min") ?>   INCLUDE YOUR JS FILES HERE -->
</head>
<body>

<div id="MenuDiv">
	<?php 
	$data = array("ThisController" => $ThisController, "HTML"=> $HTML);
	$HTML::Partial("Home/Menu", $data);
	
	?>
</div>
