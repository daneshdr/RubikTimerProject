<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("location:login.php");
    exit;
}

include 'koneksi.php';

$user_id = $_SESSION['user_id'];
$id = $_GET['id'];
$hapusdata = mysqli_query($koneksi, "DELETE FROM solves WHERE id=$id AND user_id=$user_id");

if ($hapusdata == TRUE) {
    header("location:index.php");
}
?>
