<?php
    require_once 'Views/header.php';
?>
    <h2>Créer un nouveau projet</h2>
                
<?php 
    if($_SERVER['REQUEST_METHOD'] != 'POST')
        require_once 'Views/panel/formproject.php';
    else
        require_once 'Views/panel/formmarks.php';
    
    require_once 'Views/footer.php';
?>