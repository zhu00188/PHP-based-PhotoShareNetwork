<?php
$currentPage = 'logOut'; 
include("./common/header.php"); 

session_unset();
session_destroy();
header("Location: Index.php");


?>

<?php include('./common/footer.php'); ?>