  <div id="topbar">
    <div id="topnav">
      <ul>
      
      <?php 
      
      $controllerName = $ThisController;
      
      function selectActiveMenuItem($controllerName, $MenuItem)
      {
      	if(strtolower($controllerName) == strtolower($MenuItem) )
      	{
      		echo "class='active'";
      	}
      }
      ?>
      
        <li <?php selectActiveMenuItem($controllerName, "Home")?>><?php $HTML::ActionLink("Home", "Index", "Home"); ?></li>
        <li <?php selectActiveMenuItem($controllerName, "Content")?>><a href="#">Contact</a></li>
        <li <?php selectActiveMenuItem($controllerName, "Content2")?>><a href="#">Projects</a>
          <ul>
            <li>Project 1</li>
            <li>Project 2</li>
          </ul>
        </li>
      </ul>
    </div>
    <div id="login">
		<?php $HTML::Action("LoginDisplay", "Account"); ?>
    </div>
    <br class="clear" />
  </div>
