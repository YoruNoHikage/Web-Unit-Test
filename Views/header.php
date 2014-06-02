<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        
        <title>Mon super site</title>
        
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
        <link rel="stylesheet" href="Views/styles/upload.css">
        <style>
            header .row {
                display: table;
            }
            header [class*="col-"] {
                display: table-cell;
                float: none;
                vertical-align: middle;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <header class="row">
                <div class="col-md-6">
                    <h1><a href="index.php">Tests & Co - Envoyez, testez</a></h1>
                </div>
                <div class="col-md-6 bg-primary text-center logbox">
                <?php
                    if(isset($_SESSION['user'])) // If the user is logged in
                    {
                        $user = unserialize($_SESSION['user']);
                ?>
                        <p class="col-md-6"><?php echo $user->getUsername() ?></p>
                        <div class="col-md-3">
                            <a href="index.php?action=userpanel" class="btn btn-primary">
                                <span class="glyphicon glyphicon-user"></span>
                                <?php echo $user->getRole() == 'teacher' ? 'Administration' : 'Panel'; ?>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="index.php?action=signout" class="btn btn-danger">
                                <span class="glyphicon glyphicon-off"></span>
                                Se déconnecter
                            </a>
                        </div>
                <?php
                    }
                    else
                    {
                ?>
                    <form class="form-inline" action="index.php?action=signin" method="post" role="form">
                        <div class="form-group">
                            <label class="sr-only" for="username">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" name="username" placeholder="Nom d'utilisateur">
                        </div>
                        <div class="form-group">
                            <label class="sr-only" for="password">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe">
                        </div>
                        <button type="submit" class="btn btn-default">Connexion</button>
                    </form>
                <?php
                    }
                ?>
                </div>
            </header>
            <hr/>
            
            <?php 
                if(isset($_SESSION['flash']))
                { 
            ?>
            <p class="alert 
                      alert-<?php echo $_SESSION['flashType']; ?>
                      alert-dismissable text-center">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <?php echo $_SESSION['flash']; ?>
            </p>
            <?php
                }
            ?>