<?php
require_once __DIR__ . '/config/koneksi.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$pesanError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMPEG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<main class="login-wrapper p-3">
    <div class="card-custom p-4 p-md-5 fade-in" style="width: 100%; max-width: 440px;">
        <div class="text-center mb-4">
            <img src="assets/images/logo.svg" alt="Logo SIMPEG" width="64" height="64" class="mb-3">
            <h1 class="h4 fw-bold mb-1">SIMPEG</h1>
            <p class="text-secondary small mb-0">Sistem Informasi Manajemen Kepegawaian</p>
        </div>

        <?php if ($pesanError): ?>
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center justify-content-between" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= e($pesanError) ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup" onclick="this.closest('.alert').remove()"></button>
            </div>
        <?php endif; ?>

        <div id="jsAlertBox" class="alert alert-warning alert-dismissible d-flex align-items-center justify-content-between d-none" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                <div id="jsAlertText"></div>
            </div>
            <button type="button" class="btn-close btn-tutup-js-alert" aria-label="Tutup" onclick="this.closest('#jsAlertBox').classList.add('d-none')"></button>
        </div>

        <form action="proses/login_proses.php" method="POST" onsubmit="return validasiFormLogin(event)" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username" autocomplete="username" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" autocomplete="current-password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>
        </form>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js?v=<?= filemtime(__DIR__ . '/assets/js/script.js') ?>"></script>
</body>
</html>
