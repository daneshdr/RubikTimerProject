<?php
session_start();
include 'koneksi.php';

// Jika sudah login, langsung alihkan ke index.php
if (isset($_SESSION['user_id'])) {
    header("location:index.php");
    exit;
}

$pesan = "";

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Cek apakah username atau password sudah pernah terpakai
    $cek_user = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
    $cek_pass = mysqli_query($koneksi, "SELECT * FROM users WHERE password='$password'");

    if (mysqli_num_rows($cek_user) > 0 && mysqli_num_rows($cek_pass) > 0) {
        $pesan = "Username dan password telah terpakai!";
    } else if (mysqli_num_rows($cek_user) > 0) {
        $pesan = "Username telah terpakai! Silakan gunakan username lain.";
    } else if (mysqli_num_rows($cek_pass) > 0) {
        $pesan = "Password telah terpakai! Silakan gunakan password lain.";
    } else {
        $input = mysqli_query($koneksi, "INSERT INTO users (username, password) VALUES ('$username', '$password')");
        if ($input == TRUE) {
            header("location:login.php?pesan=berhasil");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Rubik Timer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <div class="brand-icon" style="margin: 0 auto; width: 48px; height: 48px; font-size: 24px;">⏱️</div>
                <h1 class="auth-title">Buat Akun Baru</h1>
                <p class="auth-subtitle">Daftar untuk mulai mencatat rekor solve rubik Anda</p>
            </div>

            <?php if ($pesan != ""): ?>
                <div class="alert-box alert-danger">
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="form-field-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-input-control" placeholder="Pilih username" required autofocus>
                </div>

                <div class="form-field-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-input-control" placeholder="Buat password" required>
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" name="register" class="btn-primary" style="width: 100%;">Daftar Akun</button>
                </div>
            </form>

            <div class="auth-footer">
                Sudah memiliki akun? <a href="login.php">Masuk di sini &rarr;</a>
            </div>
        </div>
    </div>

</body>
</html>
