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
                <tr>
                    <td>Goanvic</td>
                    <td>Maxime</td>
                    <td>
                        <input type="checkbox" checked disabled />
                    </td>
                    <td>
                        <button type="submit" class="btn btn-success">Lancer les tests</button>
                        <button type="submit" class="btn btn-primary">Voir les résultats</button>
                    </td>
                    <td>15/20</td>
                </tr>
                <tr>
                    <td>Tran</td>
                    <td>Yohann</td>
                    <td>
                        <input type="checkbox" disabled />
                    </td>
                    <td>
                        <button type="submit" class="btn btn-success">Lancer les tests</button>
                        <button type="submit" class="btn btn-primary">Voir les résultats</button>
                    </td>
                    <td>0/20</td>
                </tr>
            </tbody>
        </table>
    </div>
<?php
    require_once '/Views/footer.php';
?>