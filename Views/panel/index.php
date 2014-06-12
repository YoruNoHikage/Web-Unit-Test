<?php
    require_once 'Views/header.php';
    
    if(isset($_SESSION['user'])) // If the user is logged in
    {
        $user = unserialize($_SESSION['user']);
        if($user->getRole() == 'teacher')
        {
?>
    <div class="row">
        <div class="col-md-12 text-center">
            <a href="index.php?action=newproject" class="btn btn-primary">
                <span class="glyphicon glyphicon-plus"></span>
                Ajouter un projet
            </a>
        </div>
    </div>
    <?php
        }
    ?>
    
    <div class="row">
        <div class="col-md-12">
            <h2>Derniers projets</h2>
            <ul class="list-group">
                <?php
                    $now = new DateTime('now');
                    foreach($projects as $project)
                    { 
                ?>
                    <li class="list-group-item">
                        <a href="index.php?action=project&id=<?php echo $project->getId(); ?>">
                           <?php if(!in_array($project->getId(), $projectIds)) { ?><span class="badge">Aucun dépôt</span><?php } ?>
                            <?php echo $project->getName(); ?>
                        </a>
                        <span class="pull-right">
                    <?php
                        if($user->getRole() == 'student')
                        {
                            if($project->getDue_date() > $now)
                            {
                        ?>
                            <a href="index.php?action=uploadsources&id=<?php echo $project->getId(); ?>" class="btn btn-primary btn-xs">
                                <span class="glyphicon glyphicon-upload"></span>
                                Envoyer des sources
                            </a>
                    <?php
                            }
                        }
                        if($user->getRole() == 'teacher')
                        {
                    ?>
                        <a href="index.php?action=editproject&id=<?php echo $project->getId(); ?>" class="btn btn-primary btn-xs">
                            <span class="glyphicon glyphicon-pencil"></span>
                            Éditer
                        </a>
                        <a href="index.php?action=deleteproject&id=<?php echo $project->getId(); ?>" class="btn btn-danger btn-xs">
                            <span class="glyphicon glyphicon-remove"></span>
                            Supprimer
                        </a>
                    <?php
                        }
                    ?>
                       </span>
                    <?php
                        if($project->getDue_date() > $now)
                            echo ' - Date limite : le ' . $project->getDue_date()->format('d/m/Y à H:i');
                        else
                            echo ' - Projet clos';
                    ?>
                    </li>
                <?php
                    }
                ?>
            </ul>
        </div>
    </div>
<?php
    }
    
    require_once 'Views/footer.php';
?>