<div class="col-md-8 col-md-push-2">

    <?php
        if($_GET['action'] == 'editproject')
            
    ?>

    <form class="form-horizontal" action="index.php?action=<?php echo $_GET['action'] ;?>" method="post" role="form">
        <div class="form-group">
            <label for="name" class="col-sm-3 control-label">Nom du projet</label>
            <div class="col-sm-9">
                <input type="text" class="form-control" id="name" name="name" placeholder="Nom du projet" value="<?php
                    if($_GET['action'] == 'editproject')
                        echo $project->getName();
                ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="duedate" class="col-sm-3 control-label">Date</label>
            <div class="col-sm-9">
                <div class='input-group date'>
                    <input type='text' class="form-control" id="duedate" name="duedate" value="<?php
                        if($_GET['action'] == 'editproject')
                        echo $project->getDue_date()->format("d/m/Y H:i");
                    ?>" required />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="jumbotron">

                <?php require_once 'formuploadarea.php' ?>

                <div id="upload-panelzone">
                    <!--<div class="col-sm-4"> exemple 
                        <div class="panel panel-default">
                            <button type="button" class="close">&times;</button>
                            <div class="panel-heading">Test 3</div>
                            <div class="panel-body">
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" 
                                         aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" 
                                         style="width: 60%;">
                                        <span class="sr-only">60%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>-->
                </div>
                <div class="clearfix"></div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button type="submit" class="btn btn-default">Valider</button>
            </div>
        </div>
    </form>

</div>