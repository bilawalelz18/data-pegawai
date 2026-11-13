<?php
require_once __DIR__ . '/../config/koneksi.php';
wajibLogin('../login.php');

$idPegawai = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idPegawai) {
    $_SESSION['flash_error'] = 'ID pegawai tidak valid.';
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, nip, nama, email, jabatan, no_telepon, foto FROM employees WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $idPegawai]);
$pegawai = $stmt->fetch();

if (!$pegawai) {
    $_SESSION['flash_error'] = 'Data pegawai tidak ditemukan.';
    header('Location: index.php');
    exit;
}

$fotoAda = !empty($pegawai['foto']) && is_file(__DIR__ . '/../assets/images/pegawai/' . $pegawai['foto']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pegawai - <?= e($pegawai['nama']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php renderNavbar('pegawai', '../'); ?>

<main class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-center mb-3 fade-in">
                <h1 class="h3 fw-bold mb-0">Detail Informasi Pegawai</h1>
                <a href="index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>

            <div class="card-custom p-4 fade-in fade-in-delay-1">
                <div class="d-flex align-items-center gap-3 pb-3 mb-3 border-bottom">
                    <?php if ($fotoAda): ?>
                        <img src="../assets/images/pegawai/<?= e($pegawai['foto']) ?>" alt="<?= e($pegawai['nama']) ?>" class="avatar-detail">
                    <?php else: ?>
                        <div class="avatar-detail text-primary">
                            <i class="bi bi-person-fill"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h2 class="h4 fw-bold mb-1"><?= e($pegawai['nama']) ?></h2>
                        <span class="badge bg-primary bg-opacity-10 text-primary fs-6 fw-normal"><?= e($pegawai['jabatan']) ?></span>
                    </div>
                </div>

                <dl class="row mb-4">
                    <dt class="col-sm-4 text-secondary">NIP</dt>
                    <dd class="col-sm-8 fw-semibold"><?= e($pegawai['nip']) ?></dd>

                    <dt class="col-sm-4 text-secondary">Nama Lengkap</dt>
                    <dd class="col-sm-8"><?= e($pegawai['nama']) ?></dd>

                    <dt class="col-sm-4 text-secondary">Alamat Email</dt>
                    <dd class="col-sm-8"><a href="mailto:<?= e($pegawai['email']) ?>"><?= e($pegawai['email']) ?></a></dd>

                    <dt class="col-sm-4 text-secondary">Jabatan</dt>
                    <dd class="col-sm-8"><?= e($pegawai['jabatan']) ?></dd>

                    <dt class="col-sm-4 text-secondary">Nomor HP</dt>
                    <dd class="col-sm-8"><?= e($pegawai['no_telepon']) ?></dd>
                </dl>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="index.php" class="btn btn-light border px-4">Kembali ke Daftar</a>
                    <a href="edit.php?id=<?= (int) $pegawai['id'] ?>" class="btn btn-primary px-4 fw-semibold">
                        <i class="bi bi-pencil-square me-1"></i> Edit Data
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="py-3 border-top bg-white text-center text-secondary small">
    <div class="container">SIMPEG &mdash; Sistem Informasi Manajemen Kepegawaian</div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
</body>
</html>
