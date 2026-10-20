<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("location:login.php");
    exit;
}
require 'koneksi.php';

$user_id = (int)$_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$query = mysqli_query($koneksi, "SELECT * FROM solves WHERE id='$id' AND user_id='$user_id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("location:index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Putaran Solve #<?= $data['id'] ?> - Rubik Timer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="auth-wrapper">
        <div class="auth-card" style="max-width: 500px;">
            <div class="auth-header">
                <div class="brand-icon" style="margin: 0 auto; width: 44px; height: 44px; font-size: 22px;">✏️</div>
                <h1 class="auth-title">Edit Putaran Solve #<?= $data['id'] ?></h1>
                <p class="auth-subtitle">Perbarui rincian waktu, status penalti, atau scramble</p>
            </div>

            <form action="update.php" method="POST">
                <input type="hidden" name="id" value="<?= $data['id'] ?>">

                <div class="form-field-group">
                    <label for="raw_time">Waktu Catatan (Detik)</label>
                    <input type="text" id="raw_time" name="raw_time" class="form-input-control" value="<?= htmlspecialchars($data['raw_time']) ?>" required>
                </div>

                <div class="form-field-group">
                    <label>Status Penalti</label>
                    <div class="penalty-selector">
                        <label class="penalty-radio-label <?= $data['penalty'] === 'OK' ? 'selected' : '' ?>">
                            <input type="radio" name="penalty" value="OK" <?= $data['penalty'] === 'OK' ? 'checked' : '' ?>> OK
                        </label>
                        <label class="penalty-radio-label <?= $data['penalty'] === '+2' ? 'selected' : '' ?>">
                            <input type="radio" name="penalty" value="+2" <?= $data['penalty'] === '+2' ? 'checked' : '' ?>> +2
                        </label>
                        <label class="penalty-radio-label <?= $data['penalty'] === 'DNF' ? 'selected' : '' ?>">
                            <input type="radio" name="penalty" value="DNF" <?= $data['penalty'] === 'DNF' ? 'checked' : '' ?>> DNF
                        </label>
                    </div>
                </div>

                <div class="form-field-group">
                    <label for="scramble">Scramble Gerakan</label>
                    <input type="text" id="scramble" name="scramble" class="form-input-control" value="<?= htmlspecialchars($data['scramble']) ?>">
                </div>

                <div class="modal-actions" style="margin-top: 26px;">
                    <button type="submit" class="btn-primary">Perbarui Data</button>
                    <a href="index.php" class="btn-secondary" style="display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">Batal</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
