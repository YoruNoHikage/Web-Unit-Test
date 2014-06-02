<?php
    require_once '/Views/header.php';
?>
    <h2>Résultat de Mr Goanvic</h2>
    
    <div class="col-md-8 col-md-push-2">
        <table class="table table-striped text-center">
            <thead>
                <tr>
                    <th>Nom du test</th>
                    <th>Nom du test</th>
                    <th>Description</th>
                    <th>Erreurs</th>
                    <th>Poids</th>
                    <th>Résultat</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $results = $pupil->getResults();
                foreach($results as $result)
                {
            ?>
                    <tr class="<?php echo $result["result"]->getStatus() ? 'success' : 'danger' ?>">
                        <td><?php echo $result["subtest"]->getTest()->getName(); ?></td>
                        <td><?php echo $result["subtest"]->getName(); ?></td>
                        <td>Patate</td>
                        <td>Que dalle</td>
                        <td><?php echo $result["subtest"]->getWeight(); ?></td>
                        <td><?php echo $result["result"]->getStatus() ? "OK" : "KO"; ?></td>
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