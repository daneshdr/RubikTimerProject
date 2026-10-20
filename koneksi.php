<?php

    $host = "localhost";
    $user = "root";
    $pw = "";
    $db = "db_cstimer";

    // Membuat koneksi
    $koneksi = mysqli_connect($host, $user, $pw, $db);

    // Periksa koneksi
    if (!$koneksi) {
        die("Koneksi gagal: " . mysqli_connect_error());
    }
    // echo "Koneksi berhasil!";

    // Pastikan tabel users dan kolom user_id tersedia
    mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS users (
        id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL UNIQUE
    )");

    $cek_kolom = mysqli_query($koneksi, "SHOW COLUMNS FROM solves LIKE 'user_id'");
    if (mysqli_num_rows($cek_kolom) == 0) {
        mysqli_query($koneksi, "ALTER TABLE solves ADD COLUMN user_id INT(11) NULL AFTER id");
    }

    $conn = $koneksi;

?>
