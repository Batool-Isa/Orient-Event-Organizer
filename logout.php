<?php
session_start();

include 'header.php';
include 'Login.php';

$lgnObject = new Login();
$lgnObject->logout();

header('Location: index.php');
?>
