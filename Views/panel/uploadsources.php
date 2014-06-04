<?php
    require_once 'Views/header.php';
?>
    <h2>Envoyer un projet</h2>
     
    <form class="form-horizontal" action="index.php?action=uploadsources" method="post" role="form">
        <div class="form-group">
            <div class="jumbotron">
                
                <?php require_once 'Views/panel/formuploadarea.php' ?>
                
                <div id="upload-uniquefile" class="panel panel-default">
                    <div class="panel-heading">Filename</div>
                    <div class="panel-body">
                        <div class="progress">
                            <div id="uploadprogress" class="progress-bar" role="progressbar" 
                                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" 
                                 style="width: 0%;">
                                <span class="sr-only">0%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-sm-12 text-center">
                <button type="submit" class="btn btn-default">Valider</button>
            </div>
        </div>
    </form>
<?php
    require_once 'Views/footer.php';
?>