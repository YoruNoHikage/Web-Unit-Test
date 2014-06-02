<?php
    require_once '/Views/header.php';
?>
    <h2>Projet n°42 : La barre de fer à tout faire</h2>

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
                    foreach($participants as $participant)
                    {
                ?>
                <tr>
                    <td><?php echo $participant->getLastname(); ?></td>
                    <td><?php echo $participant->getFirstname(); ?></td>
                    <td>
                        <input type="checkbox" checked disabled />
                    </td>
                    <td>
                        <button type="submit" class="btn btn-success">Lancer les tests</button>
                        <button type="submit" class="btn btn-primary">Voir les résultats</button>
                    </td>
                    <td>
                        <a href="#" class="tip" data-toggle="tooltip" data-original-title="
                            <?php
                                $results = $participant->getResults();
                                foreach($results as $result)
                                {
                                    echo $result["subtest"]->getName() . ":" . ($result["result"]->getStatus() ? $result["subtest"]->getWeight() : "KO") . "<br/>";
                                }
                            ?>
                        " data-placement="right">
                            <?php echo $participant->getFinalMark($project); ?> / <?php echo $projectTotalWeight; ?>
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