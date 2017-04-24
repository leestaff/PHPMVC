<div class="data_input">
    <form action="<?php HTML::Url("GenerateModelClassesSubmit", "Admin");?>" method="post">
        <fieldset class="top">
            <legend>Model Code Generator</legend>
            <div><b>Select Table</b></div>
			<div>
				<select name='tablename' id='tablename'>
					<?php
					foreach($TableNames as $t)
					{
						echo "<option value='$t->TABLE_NAME'>$t->TABLE_NAME</option>"	;					
					}
					?>
				</select>
			</div>
			<div>
				<input type='submit'> 
			</div>
		</fieldset>
	</form>
</div>
