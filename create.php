<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("location:login.php");
    exit;
}

include 'koneksi.php';

$user_id = $_SESSION['user_id'];
$raw_time = $_POST["raw_time"];
$scramble = addslashes($_POST["scramble"]);

$input = mysqli_query($koneksi, "INSERT INTO solves (user_id, raw_time, penalty, final_time, scramble) 
VALUES('$user_id', '$raw_time', 'OK', '$raw_time', '$scramble')");

if ($input == TRUE) {
    // echo "Nailed it";
    header("location:index.php");
}
?>
