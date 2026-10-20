<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("location:login.php");
    exit;
}

require 'koneksi.php';

$user_id = $_SESSION['user_id'];

//diambil dari formulir
$id = $_POST["id"];
$penalty = $_POST["penalty"];

//ambil data solves untuk raw_time & scramble jika tidak dikirim dari form
$ambil = mysqli_query($koneksi, "SELECT * FROM solves WHERE id=$id AND user_id=$user_id");
$data = mysqli_fetch_array($ambil);

if (!$data) {
    header("location:index.php");
    exit;
}

$raw_time = isset($_POST["raw_time"]) ? $_POST["raw_time"] : $data["raw_time"];
$scramble = isset($_POST["scramble"]) ? addslashes($_POST["scramble"]) : addslashes($data["scramble"]);

//hitung final_time sesuai penalty csTimer
$final_time = "'$raw_time'";
if ($penalty == "+2") {
    $final_time = "'" . ($raw_time + 2) . "'";
} else if ($penalty == "DNF") {
    $final_time = "NULL";
}

//samakan kolom solves dengan input formulir
$updatedata = mysqli_query($koneksi, "UPDATE solves SET 
raw_time='$raw_time', penalty='$penalty', final_time=$final_time, scramble='$scramble' 
WHERE id=$id AND user_id=$user_id");

if ($updatedata == TRUE) {
    header("location:index.php");
}
?>
