<?php
    require_once '/Views/header.php';
    
    if(isset($_SESSION['username'])) // If the user is logged in
    {
        if($_SESSION['role'] == 'teacher')
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
        <div class="col-md-6">
            <h2>Derniers projets</h2>
            <ul>
                <li>
                    <a href="index.php?action=project&id=1">Projet 1</a>
                <?php
                    if($_SESSION['role'] == 'student')
                    {
                ?>
                    <a href="index.php?action=uploadsources&id=1" class="btn btn-primary">
                        <span class="glyphicon glyphicon-upload"></span>
                        Envoyer des sources
                    </a>
                <?php
                    }
                    
                    if($_SESSION['role'] == 'teacher')
                    {
                ?>
                    <a href="index.php?action=editproject&id=1" class="btn btn-primary">
                        <span class="glyphicon glyphicon-pencil"></span>
                        Éditer
                    </a>
                    <a href="index.php?action=deleteproject&id=1" class="btn btn-danger">
                        <span class="glyphicon glyphicon-remove"></span>
                        Supprimer
                    </a>
                <?php
                    }
                ?>
                </li>
            </ul>
        </div>
        <?php
            if($_SESSION['role'] == 'teacher')
            {
        ?>
        <div class="col-md-6">
            <p>
                Purgator meur a gwech e ar gwellañ. Aozañ tamm-ha-tamm skeudenn dleout c'hwec'h 
                mat mor darev divalav kilometrad, gwelloc'h kloued chase nag karr niz etrezek 
                c'hoarvezout kig gwirionez aod te gwern saout ma gwalenn aet moc'h ruilhañ 
                pegement, dleout torgenn nec'hin tal re tri bag-dre-dan. Giz bruzun respont. 
                Louet beg arrebeuri war tremen mui degas kof hantereur liv Europa bloavezh eñ 
                gourc'hemennoù kas disheol taer seiz ael dindan, skuizhañ giz gwez, kerc'hat 
                brozh mat pevarzek dilhad-gwele, kreskiñ eo ifern Menez Arre bez niz amzer 
                sav-heol dreist-holl. Penn tachenn-c'hoari marteze korn gwelout bod merenn 
                kelenner sec'hed. Kar regiñ armel-levrioù, eil bev Breizh-Veur.
            </p>
        </div>
        <?php
            }
        ?>
    </div>
<?php
    }
    
    require_once '/Views/footer.php';
?>