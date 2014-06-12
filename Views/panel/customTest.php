<?php
    require_once 'Views/header.php';
?>
    <h2>Ajouter un test</h2>

    <form class="form-horizontal" action="index.php?action=addcustomtest&id=<?php echo $projectId; ?>" method="post" role="form">
    	<div class="form-group">
	    	<label for="testName" class="col-sm-3 control-label">Nom du test</label>
	    	<div class="col-sm-9">
	    		<input type="text" name="testName" id="nameTest" class="form-control" value="CustomTest" required/>
	    	</div>
	    </div>
	    <div class="form-group">
	    	<label for="declarations" class="col-sm-3 control-label">Déclaration des variables necessaires aux tests</label>
	    	<div class="col-sm-9">
	    		<textarea name="declarations" id="declarations" class="form-control" required/>protected Money money1, money2;</textarea>
	    	</div>
	    </div>
    	<div class="form-group">
	    	<label for="beforeTestContent" class="col-sm-3 control-label">@Before</label>
	    	<div class="col-sm-9">
	    		<textarea name="beforeTestContent" id="beforeTestContent" class="form-control" required/>money1 = new Money(); money2 = new Money(2, "KIL");</textarea>
	    	</div>
	    </div>
	    <div class="form-group">
	    	<label for="subtestName" class="col-sm-3 control-label">Nom du sous-test</label>
	    	<div class="col-sm-7">
	    		<input type="text" name="subtestName" id="subtestName" class="form-control" value="customSubTest" required/>
	    	</div>
	    	<div class="col-sm-2">
            	<select name="weight" id="weight" class="form-control">
                	<?php 
                      	for($i = 1 ; $i <= 5 ; $i++)
                      	{
                   	?>
                   	<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php
                    	}
                    ?>
               	</select>
            </div>
	    	<label for="subtestContent" class="col-sm-3 control-label">Contenu du sous-test</label>
	    	<div class="col-sm-9">
	    		<textarea name="subtestContent" id="subtestContent" class="form-control" required/>assertEquals("0.0 EUR", money1.toString());</textarea>
	    	</div>
	    </div>
	    <div class="form-group">
	    	<label for="afterTestContent" class="col-sm-3 control-label">@After</label>
	    	<div class="col-sm-9">
	    		<textarea name="afterTestContent" id="afterTestContent" class="form-control"/></textarea>
	    	</div>
	    </div>
	    <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button type="submit" class="btn btn-default">Valider</button>
            </div>
        </div>
    </form>
                
<?php    
    require_once 'Views/footer.php';
?>