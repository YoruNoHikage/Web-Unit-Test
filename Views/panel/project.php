<?php
    require_once '/Views/header.php';
?>
    <h2><?php echo $project->getName(); ?></h2>

    <div class="col-md-8 col-md-push-2">
        <table class="table table-striped text-center">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Envoyé</th>
                    <th>Actions</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach($users as $user)
                    {
                ?>
                <tr>
                    <td><?php echo $user->getLastname(); ?></td>
                    <td><?php echo $user->getFirstname(); ?></td>
                    <td>
                        <input type="checkbox" checked disabled />
                    </td>
                    <td>
                        <button type="submit" class="btn btn-success">Lancer les tests</button>
                        <a class="btn btn-primary" href="index.php?action=results&projectid=<?php echo $project->getId(); ?>&username=<?php echo $user->getUsername(); ?>">Voir les résultats</a>
                    </td>
                    <td>
                        <a href="#" class="tip" data-toggle="tooltip" data-original-title="
                            <?php
                                $results = $user->getResults();
                                foreach($results as $result)
                                {
                                    echo $result["subtest"]->getName() . ":" . ($result["result"]->getStatus() ? $result["subtest"]->getWeight() : "KO") . "<br/>";
                                }
                            ?>
                        " data-placement="right">
                            <?php echo $user->getFinalMark($project); ?> / <?php echo $projectTotalWeight; ?>
                        </a>
                    </td>
                </tr>
                <?php
                    }
                ?>
            </tbody>
        </table>
    </div>
<?php
    require_once '/Views/footer.php';
?>