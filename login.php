<?php
session_start();
include 'koneksi.php';

// Jika sudah login, langsung alihkan ke index.php
if (isset($_SESSION['user_id'])) {
    header("location:index.php");
    exit;
}

$pesan = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username' AND password='$password'");

    if (mysqli_num_rows($cek) > 0) {
        $user = mysqli_fetch_array($cek);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("location:index.php");
        exit;
    } else {
        $pesan = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rubik Timer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <div class="brand-icon" style="margin: 0 auto; width: 48px; height: 48px; font-size: 24px;">⏱️</div>
                <h1 class="auth-title">Rubik Timer</h1>
                <p class="auth-subtitle">Masuk untuk mencatat dan melacak waktu solve Anda</p>
            </div>

            <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'berhasil'): ?>
                <div class="alert-box alert-success">
                    Registrasi akun berhasil! Silakan masuk dengan akun baru Anda.
                </div>
            <?php endif; ?>

            <?php if ($pesan != ""): ?>
                <div class="alert-box alert-danger">
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-field-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-input-control" placeholder="Masukkan username" required autofocus>
                </div>

                <div class="form-field-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-input-control" placeholder="Masukkan password" required>
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" name="login" class="btn-primary" style="width: 100%;">Masuk ke Rubik Timer</button>
                </div>
            </form>

            <div class="auth-footer">
                Belum memiliki akun? <a href="register.php">Daftar sekarang &rarr;</a>
            </div>
        </div>
    </div>

</body>
</html>
