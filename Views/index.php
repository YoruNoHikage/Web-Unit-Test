<?php
    require_once 'Views/header.php';
?>
<div class="jumbotron">
    <h2>Viens tester ton projet !</h2>

    <div class="row">
        <div class="col-md-4">
            <img data-src="holder.js/100%x100" alt="..." src="Views/images/users.png" class="img-circle">
            <h3>
                <i class="fa fa-sign-in"></i><br/>
                Commence par te connecter
            </h3>
            <p>Utilise tes identifiants de l'ENT</p>
        </div>
        <div class="col-md-4">
            <img data-src="holder.js/100%x100" alt="..." src="Views/images/upload.png" class="img-circle">
            <h3>
                <i class="fa fa-sign-in"></i><br/>
                Ensuite, upload ton projet !
            </h3>
            <p>La correction sera automatique !</p>
        </div>
        <div class="col-md-4">
            <img data-src="holder.js/100%x100" alt="..." src="Views/images/doge.jpg" class="img-circle">
            <h3>
                <i class="fa fa-sign-in"></i><br/>
                Reçois tes résultats et corrige tes erreurs !
            </h3>
            <p>Une fois fini, tu seras noté automatiquement.</p>
        </div>
    </div>
</div>
<?php
    require_once 'Views/footer.php';
?>