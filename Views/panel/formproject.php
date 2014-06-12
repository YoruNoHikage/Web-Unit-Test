<div class="col-md-8 col-md-push-2">

    <form class="form-horizontal" 
          action="index.php?action=<?php echo $_GET['action']; if(isset($_GET['id'])) { echo '&id=' . $_GET['id']; } ?>"
          method="post" role="form">
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
            <label for="group" class="col-sm-3 control-label">Groupe cible</label>
            <div class="col-sm-9">
                <select name="group" id="group" class="form-control">
                    <?php foreach($groups as $group) { ?>
                        <option 
                           value="<?php echo $group->getName(); ?>"
                           <?php 
                                if($_GET['action'] == 'editproject' 
                                   && $project->getTargetGroup()->getName() === $group->getName()) { 
                                    echo 'selected'; } ?>>
                            <?php echo $group->getName(); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <div class="jumbotron">

                <?php require_once 'formuploadarea.php' ?>

                <div id="upload-panelzone">
                <?php 
                    if(isset($project))
                    {
                        foreach($project->getTests() as $test)
                        {
                ?>
                <div class="col-sm-4">
                    <button type="button" class="close" data-dismiss="alert" 
                            id="<?php echo $test->getName(); ?>">&times;</button>
                    <div class="panel panel-default">
                        <div class="panel-heading"><?php echo $test->getName(); ?></div>
                        <div class="panel-body">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" 
                                     aria-valuenow="100" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100" style="width: 100%;">
                                    <span class="sr-only">100%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                        }
                ?>
                    <input type="hidden" id="projectid" name="projectid" value="<?php echo $project->getId(); ?>">
                <?php
                    }
                ?>
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