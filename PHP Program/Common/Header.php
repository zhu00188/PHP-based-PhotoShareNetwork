<?php

include_once './common/Functions.php';
include_once './common/EntityClassLib.php';
session_start();

?>
<!DOCTYPE html>
<html lang="en" style="position: relative; min-height: 100%;">
<head>
	<title>Algonquin Social Media</title>
        <meta charset="utf-8"> 
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="./common/css/Site.css">
</head>
<body style="margin-bottom: 100px;">
    <nav class="navbar navbar-expand-lg bg-dark mb-4" data-bs-theme="dark">
      <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggler collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" style="padding: 10px" href="http://www.algonquincollege.com">
              <img src="Common/img/AC.png" 
                   alt="Algonquin College" style="max-width:100%; max-height:100%;"/>
          </a>    
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item"><a class="nav-link <?= ($currentPage == 'home') ? 'active' : ''; ?>" href="Index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link <?= ($currentPage == 'myFriends') ? 'active' : ''; ?>" href="MyFriends.php">My Friends</a></li>
            <li class="nav-item"><a class="nav-link <?= ($currentPage == 'myAlbums') ? 'active' : ''; ?>" href="MyAlbums.php">My Albums</a></li>
            <li class="nav-item"><a class="nav-link <?= ($currentPage == 'myPictures') ? 'active' : ''; ?>" href="MyPictures.php">My Pictures</a></li>
            <li class="nav-item"><a class="nav-link <?= ($currentPage == 'uploadPictures') ? 'active' : ''; ?>" href="UploadPictures.php">Upload Pictures</a></li>
            <li class="nav-item">
                <a class="nav-link <?= ($currentPage == 'logIn' || $currentPage == 'logOut') ? 'active' : ''; ?>" 
                   href="<?= isset($_SESSION['isValid']) && $_SESSION['isValid'] ? 'Logout.php' : 'Login.php'; ?>">
                    <?= isset($_SESSION['isValid']) && $_SESSION['isValid'] ? 'Log Out' : 'Log In'; ?>
                </a>
            </li>
          </ul>
        </div>
      </div>  
    </nav>
