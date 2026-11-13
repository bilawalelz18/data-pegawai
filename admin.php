<?php
require_once __DIR__ . '/config/koneksi.php';
wajibLogin('login.php');

$stmt = $pdo->prepare('SELECT id, username, nama, email, jabatan, foto FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $_SESSION['user_id']]);
$admin = $stmt->fetch();

if (!$admin) {
    header('Location: proses/logout.php');
    exit;
}

$fotoAda = !empty($admin['foto']) && is_file(__DIR__ . '/assets/images/pegawai/' . $admin['foto']);

$pesanSukses = $_SESSION['flash_sukses'] ?? null;
$pesanError  = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_sukses'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Administrator - SIMPEG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<?php renderNavbar('admin', ''); ?>

<main class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 fade-in">
        <div>
            <h1 class="h3 fw-bold mb-1">Profil &amp; Pengaturan Akun</h1>
            <p class="text-secondary mb-0">Kelola foto profil, username, email, dan keamanan kata sandi Anda.</p>
        </div>
    </div>

    <?php if ($pesanSukses): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center justify-content-between" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div><?= e($pesanSukses) ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup" onclick="this.closest('.alert').remove()"></button>
        </div>
    <?php endif; ?>

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

    <!-- Kartu Ringkasan Profil Admin -->
    <div class="card-custom p-4 mb-4 fade-in">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <img id="previewFotoAdmin"
                 src="<?= $fotoAda ? 'assets/images/pegawai/' . e($admin['foto']) : '' ?>"
                 alt="<?= e($admin['username']) ?>"
                 class="avatar-detail <?= $fotoAda ? '' : 'd-none' ?>">
            <div id="avatarIconAdmin" class="avatar-detail bg-primary bg-opacity-10 text-primary <?= $fotoAda ? 'd-none' : '' ?>">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <div>
                <h2 class="h4 fw-bold mb-1"><?= e($admin['username']) ?></h2>
                <div class="d-flex flex-wrap align-items-center gap-2 text-secondary small">
                    <span class="badge bg-primary bg-opacity-10 text-primary fs-6 fw-normal"><?= e($admin['jabatan']) ?></span>
                    <span>&bull;</span>
                    <span><i class="bi bi-envelope me-1"></i><?= e($admin['email']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Form Ubah Profil -->
        <div class="col-lg-6 fade-in fade-in-delay-1">
            <div class="card-custom p-4 h-100">
                <h2 class="h5 fw-bold mb-3 pb-2 border-bottom">
                    <i class="bi bi-person-gear me-2 text-primary"></i>Ubah Profil Administrator
                </h2>
                <form action="proses/profil_proses.php" method="POST" enctype="multipart/form-data" onsubmit="return validasiFormProfil(event)" novalidate>
                    <div class="mb-3">
                        <label for="foto_admin" class="form-label fw-semibold">Foto Profil</label>
                        <input type="file" id="foto_admin" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">Format JPG, PNG, atau WEBP (maks. 2 MB). Kosongkan jika tidak ingin mengganti foto.</div>
                    </div>

                    <div class="mb-3">
                        <label for="username_admin" class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                        <input type="text" id="username_admin" name="username" class="form-control" value="<?= e($admin['username']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email_admin" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" id="email_admin" name="email" class="form-control" value="<?= e($admin['email']) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label for="jabatan_admin" class="form-label fw-semibold">Jabatan <span class="text-danger">*</span></label>
                        <input type="text" id="jabatan_admin" name="jabatan" class="form-control bg-light" value="<?= e($admin['jabatan']) ?>" readonly>
                        <div class="form-text">Peran sistem ditetapkan sebagai Administrator.</div>
                    </div>

                    <button type="submit" class="btn btn-primary fw-semibold w-100">
                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan Profil
                    </button>
                </form>
            </div>
        </div>

        <!-- Form Ubah Password -->
        <div class="col-lg-6 fade-in fade-in-delay-2">
            <div class="card-custom p-4 h-100">
                <h2 class="h5 fw-bold mb-3 pb-2 border-bottom">
                    <i class="bi bi-shield-lock me-2 text-primary"></i>Ubah Password
                </h2>
                <form action="proses/password_proses.php" method="POST" onsubmit="return validasiFormPassword(event)" novalidate>
                    <div class="mb-3">
                        <label for="password_lama" class="form-label fw-semibold">Password Saat Ini <span class="text-danger">*</span></label>
                        <input type="password" id="password_lama" name="password_lama" class="form-control" placeholder="Masukkan password saat ini" required>
                    </div>

                    <div class="mb-3">
                        <label for="password_baru" class="form-label fw-semibold">Password Baru <span class="text-danger">*</span></label>
                        <input type="password" id="password_baru" name="password_baru" class="form-control" placeholder="Minimal 6 karakter" minlength="6" required>
                    </div>

                    <div class="mb-4">
                        <label for="konfirmasi_password" class="form-label fw-semibold">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                        <input type="password" id="konfirmasi_password" name="konfirmasi_password" class="form-control" placeholder="Ulangi password baru" minlength="6" required>
                    </div>

                    <button type="submit" class="btn btn-outline-primary fw-semibold w-100">
                        <i class="bi bi-key me-1"></i> Perbarui Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<footer class="py-3 border-top bg-white text-center text-secondary small">
    <div class="container">SIMPEG &mdash; Sistem Informasi Manajemen Kepegawaian</div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js?v=<?= filemtime(__DIR__ . '/assets/js/script.js') ?>"></script>
</body>
</html>
