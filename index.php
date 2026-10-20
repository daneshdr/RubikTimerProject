<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("location:login.php");
    exit;
}

require_once 'koneksi.php';
require_once 'functions.php';

$user_id = $_SESSION['user_id'];
$res = mysqli_query($koneksi, "SELECT * FROM solves WHERE user_id='$user_id' ORDER BY id ASC");
$solves = [];
while ($row = mysqli_fetch_assoc($res)) {
    $solves[] = $row;
}

$data    = hitungStatistik($solves);
$list    = $data['list'];
$summary = $data['summary'];
$last    = count($list) > 0 ? $list[count($list) - 1] : null;
$history = array_reverse($list);

$timerText = $last ? $last['display_time'] : "0.00";
$diffText  = "";
if ($last && $last['diff'] !== null) {
    $sign = $last['diff'] >= 0 ? "+" : "";
    $diffText = "(" . $sign . number_format($last['diff'], 2, '.', '') . ")";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rubik Timer - Speedcubing Timer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Header & Navigation Bar -->
    <header class="app-header">
        <div class="brand-area">
            <div class="brand-icon">⏱️</div>
            <div>
                <span class="brand-title">Rubik Timer</span>
                <span class="brand-badge">WCA 3x3x3</span>
            </div>
        </div>
        <div class="user-area">
            <div class="user-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span><strong><?= htmlspecialchars($_SESSION['username']) ?></strong></span>
            </div>
            <a href="logout.php" class="btn-logout" title="Keluar dari akun">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span>Logout</span>
            </a>
        </div>
    </header>

    <div class="main-wrapper">

        <!-- Scramble Bar -->
        <section class="scramble-card" aria-label="WCA Scramble">
            <div class="scramble-content">
                <div class="scramble-title-label">WCA Official 3x3 Scramble</div>
                <div class="scramble-text" id="scramble-display">Memuat Scramble...</div>
            </div>
            <button type="button" id="btn-new-scramble" class="btn-scramble" title="Dapatkan Scramble Baru">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
                <span>New Scramble</span>
            </button>
        </section>

        <!-- Main Stopwatch Timer Display -->
        <section class="timer-section" id="timer-section" title="Tekan atau tahan Spacebar untuk memulai">
            <div class="timer-status-pill" id="timer-status">Tahan Spacebar lalu lepas untuk mulai stopwatch</div>

            <div class="timer-container" id="timer-container">
                <div class="timer-digits-wrapper">
                    <span class="timer-digits" id="timer-display"><?= htmlspecialchars($timerText) ?></span>
                    <span class="timer-diff-badge" id="timer-diff"><?= htmlspecialchars($diffText) ?></span>
                </div>
            </div>

            <div class="timer-quick-stats">
                <div class="quick-stat-badge">ao5: <strong><?= formatStat($summary['current']['ao5']) ?></strong></div>
                <div class="quick-stat-badge">ao12: <strong><?= formatStat($summary['current']['ao12']) ?></strong></div>
            </div>

            <!-- Hidden Auto-Submit Form -->
            <form id="form-create-solve" method="POST" action="create.php" style="display:none;">
                <input type="hidden" name="raw_time" id="input-raw-time">
                <input type="hidden" name="scramble" id="input-scramble">
            </form>
        </section>

        <!-- Statistics Section -->
        <div class="stats-wrapper">

            <!-- Summary Cards -->
            <div class="stats-card">
                <div class="card-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                    Ringkasan Sesi
                </div>
                <div class="summary-badges-grid">
                    <div class="summary-badge-item">
                        <div class="badge-label">Total Solves</div>
                        <div class="badge-val"><?= $summary['valid'] ?> <small style="font-size:0.8rem; color:var(--text-muted);">/ <?= $summary['total'] ?></small></div>
                    </div>
                    <div class="summary-badge-item">
                        <div class="badge-label">Session Mean</div>
                        <div class="badge-val" style="color:var(--accent-blue);"><?= formatStat($summary['mean']) ?></div>
                    </div>
                    <div class="summary-badge-item">
                        <div class="badge-label">Current Single</div>
                        <div class="badge-val"><?= htmlspecialchars($summary['current']['time']) ?></div>
                    </div>
                    <div class="summary-badge-item">
                        <div class="badge-label">Best Single</div>
                        <div class="badge-val" style="color:var(--accent-green);"><?= formatStat($summary['best']['time']) ?></div>
                    </div>
                </div>
            </div>

            <!-- WCA Average Comparison Table -->
            <div class="stats-card">
                <div class="card-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    Statistik Rata-rata
                </div>
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Tipe</th>
                            <th>Current</th>
                            <th>Best</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="stat-name">Single Time</td>
                            <td class="stat-current"><?= htmlspecialchars($summary['current']['time']) ?></td>
                            <td class="stat-best"><?= formatStat($summary['best']['time']) ?></td>
                        </tr>
                        <tr>
                            <td class="stat-name">Mean of 3 (Mo3)</td>
                            <td class="stat-current"><?= formatStat($summary['current']['mo3']) ?></td>
                            <td class="stat-best"><?= formatStat($summary['best']['mo3']) ?></td>
                        </tr>
                        <tr>
                            <td class="stat-name">Average of 5 (Ao5)</td>
                            <td class="stat-current"><?= formatStat($summary['current']['ao5']) ?></td>
                            <td class="stat-best"><?= formatStat($summary['best']['ao5']) ?></td>
                        </tr>
                        <tr>
                            <td class="stat-name">Average of 12 (Ao12)</td>
                            <td class="stat-current"><?= formatStat($summary['current']['ao12']) ?></td>
                            <td class="stat-best"><?= formatStat($summary['best']['ao12']) ?></td>
                        </tr>
                        <tr>
                            <td class="stat-name">Average of 100 (Ao100)</td>
                            <td class="stat-current"><?= formatStat($summary['current']['ao100']) ?></td>
                            <td class="stat-best"><?= formatStat($summary['best']['ao100']) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- History Solves Table (Full CRUD) -->
        <section class="history-card">
            <div class="history-header">
                <div class="card-title" style="margin-bottom:0;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    Riwayat Putaran (Solves)
                </div>
                <span class="brand-badge"><?= count($list) ?> total solves</span>
            </div>

            <div class="table-responsive">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th class="col-no">#</th>
                            <th>Time</th>
                            <th>Ao5</th>
                            <th>Ao12</th>
                            <th>Mo3</th>
                            <th>Ao100</th>
                            <th>Scramble</th>
                            <th>Date</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="9" style="text-align:center; padding: 40px 20px; color: var(--text-muted);">
                                Belum ada catatan waktu. Selesaikan putaran rubik pertamamu menggunakan stopwatch di atas!
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($history as $item): ?>
                            <tr>
                                <td class="col-no"><?= $item['no'] ?></td>
                                <td class="col-time">
                                    <?= htmlspecialchars($item['display_time']) ?>
                                    <?php if ($item['penalty'] === '+2'): ?>
                                        <span class="badge-penalty plus2">+2</span>
                                    <?php elseif ($item['penalty'] === 'DNF'): ?>
                                        <span class="badge-penalty dnf">DNF</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatStat($item['ao5']) ?></td>
                                <td><?= formatStat($item['ao12']) ?></td>
                                <td><?= formatStat($item['mo3']) ?></td>
                                <td><?= formatStat($item['ao100']) ?></td>
                                <td class="col-scramble" title="<?= htmlspecialchars($item['scramble']) ?>"><?= htmlspecialchars($item['scramble']) ?></td>
                                <td class="col-date"><?= htmlspecialchars($item['created_at']) ?></td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <button type="button" class="btn-action-edit" onclick="bukaEdit(
                                        <?= (int)$item['id'] ?>,
                                        <?= (int)$item['no'] ?>,
                                        <?= htmlspecialchars(json_encode($item['display_time']), ENT_QUOTES, 'UTF-8') ?>,
                                        <?= htmlspecialchars(json_encode($item['penalty']), ENT_QUOTES, 'UTF-8') ?>,
                                        <?= htmlspecialchars(json_encode($item['scramble']), ENT_QUOTES, 'UTF-8') ?>,
                                        <?= htmlspecialchars(json_encode($item['created_at']), ENT_QUOTES, 'UTF-8') ?>
                                    )">Edit</button>
                                    <button type="button" class="btn-action-del" title="Hapus solve ini" onclick="hapusSolve(<?= (int)$item['id'] ?>, <?= (int)$item['no'] ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>

    <!-- Quick Edit Modal Dialog -->
    <dialog id="modal-edit" class="custom-modal">
        <div class="modal-header">
            <div class="modal-title-text" id="modal-title">Solves Detail</div>
            <div class="modal-time-large" id="modal-time">0.00</div>
        </div>

        <form method="POST" action="update.php">
            <input type="hidden" name="id" id="modal-id">

            <label style="font-size: 0.78rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Penalti</label>
            <div class="penalty-selector">
                <label class="penalty-radio-label">
                    <input type="radio" name="penalty" value="OK" id="p-ok"> OK
                </label>
                <label class="penalty-radio-label">
                    <input type="radio" name="penalty" value="+2" id="p-plus2"> +2
                </label>
                <label class="penalty-radio-label">
                    <input type="radio" name="penalty" value="DNF" id="p-dnf"> DNF
                </label>
            </div>

            <div class="form-field-group">
                <label for="modal-scramble">Scramble</label>
                <input type="text" class="form-input-control" id="modal-scramble" readonly>
            </div>

            <div class="form-field-group">
                <label for="modal-date">Tanggal &amp; Waktu</label>
                <input type="text" class="form-input-control" id="modal-date" readonly>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn-primary">Simpan</button>
                <button type="button" id="modal-delete" class="btn-danger" title="Hapus Solve">Hapus</button>
                <button type="button" class="btn-secondary" onclick="document.getElementById('modal-edit').close()">Batal</button>
            </div>

            <a id="modal-edit-page" href="#" style="font-size: 0.8rem; text-align: center; display: block; margin-top: 16px;">Buka Halaman Edit Lengkap &rarr;</a>
        </form>
    </dialog>

    <footer class="app-footer">
        Rubik Timer &bull; Speedcubing Timer CRUD &bull; Laragon Edition
    </footer>

    <script src="script.js"></script>
</body>
</html>
