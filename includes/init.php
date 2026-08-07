<?php include 'session.php';
      include 'functions.php';
if (empty($_SESSION['userId']) || empty($_SESSION['isLogin'])) {
    header("Location: login.php");
    exit;
}
include 'header.php';
include 'sidebar.php';
include 'topbar.php';
