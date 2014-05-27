<?php
    require_once '/Views/header.php';
?>
    <div class="col-md-12 text-center">
        <p>Voulez-vous vraiment supprimer ce projet ?</p>
        <form method="post" action="index.php?action=deleteproject">
            <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>" />
            <button type="submit" class="btn btn-danger text-center">
                <span class="glyphicon glyphicon-remove"></span>
                Supprimer
            </button>
        </form>
    </div>
    
<?php
    require_once '/Views/footer.php';
?>