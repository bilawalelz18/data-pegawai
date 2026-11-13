<?php
require_once __DIR__ . '/../config/koneksi.php';
wajibLogin('../login.php');

$idPegawai = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$modeEdit  = !empty($modeEdit) || !empty($idPegawai);

$dataPegawai = [
    'id'         => '',
    'nip'        => $_SESSION['old_input']['nip'] ?? '',
    'nama'       => $_SESSION['old_input']['nama'] ?? '',
    'email'      => $_SESSION['old_input']['email'] ?? '',
    'jabatan'    => $_SESSION['old_input']['jabatan'] ?? '',
    'no_telepon' => $_SESSION['old_input']['no_telepon'] ?? '',
    'foto'       => '',
];
unset($_SESSION['old_input']);

if ($modeEdit) {
    if (!$idPegawai) {
        $_SESSION['flash_error'] = 'ID pegawai tidak ditemukan.';
        header('Location: index.php');
        exit;
    }
    $stmt = $pdo->prepare('SELECT id, nip, nama, email, jabatan, no_telepon, foto FROM employees WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $idPegawai]);
    $baris = $stmt->fetch();

    if (!$baris) {
        $_SESSION['flash_error'] = 'Data pegawai tidak ditemukan.';
        header('Location: index.php');
        exit;
    }
    $dataPegawai = $baris;
}

$judulHalaman = $modeEdit ? 'Edit Data Pegawai' : 'Tambah Data Pegawai';
$actionForm   = $modeEdit ? '../proses/update.php' : '../proses/simpan.php';

$pesanError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$fotoAda = !empty($dataPegawai['foto']) && is_file(__DIR__ . '/../assets/images/pegawai/' . $dataPegawai['foto']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($judulHalaman) ?> - SIMPEG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<?php renderNavbar('pegawai', '../'); ?>

<main class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-center mb-3 fade-in">
                <h1 class="h3 fw-bold mb-0"><?= e($judulHalaman) ?></h1>
                <a href="index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
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

            <div class="card-custom p-4 fade-in fade-in-delay-1">
                <form action="<?= e($actionForm) ?>" method="POST" enctype="multipart/form-data" onsubmit="return validasiFormPegawai(event)" novalidate>
                    <?php if ($modeEdit): ?>
                        <input type="hidden" name="id" value="<?= (int) $dataPegawai['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nip" class="form-label fw-semibold">NIP <span class="text-danger">*</span></label>
                        <input type="text" id="nip" name="nip" class="form-control" inputmode="numeric"
                               placeholder="Contoh: 199001012020121001" value="<?= e($dataPegawai['nip']) ?>" required>
                        <div class="form-text">Hanya berisi angka dan bersifat unik untuk setiap pegawai.</div>
                    </div>

                    <div class="mb-3">
                        <label for="nama" class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" id="nama" name="nama" class="form-control"
                               placeholder="Masukkan nama lengkap pegawai" value="<?= e($dataPegawai['nama']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control"
                               placeholder="nama@instansi.go.id" value="<?= e($dataPegawai['email']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="jabatan" class="form-label fw-semibold">Jabatan <span class="text-danger">*</span></label>
                        <select id="jabatan" name="jabatan" class="form-select" required>
                            <option value="">-- Pilih Jabatan --</option>
                            <?php foreach (DAFTAR_JABATAN as $itemJabatan): ?>
                                <option value="<?= e($itemJabatan) ?>" <?= $dataPegawai['jabatan'] === $itemJabatan ? 'selected' : '' ?>>
                                    <?= e($itemJabatan) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="no_telepon" class="form-label fw-semibold">Nomor HP <span class="text-danger">*</span></label>
                        <input type="text" id="no_telepon" name="no_telepon" class="form-control" inputmode="tel" minlength="12"
                               placeholder="Contoh: 081234567890" value="<?= e($dataPegawai['no_telepon']) ?>" required>
                        <div class="form-text">Minimal 12 digit angka.</div>
                    </div>

                    <div class="mb-4">
                        <label for="foto" class="form-label fw-semibold">Foto Pegawai</label>
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <img id="previewFoto"
                                 src="<?= $fotoAda ? '../assets/images/pegawai/' . e($dataPegawai['foto']) : '' ?>"
                                 alt="Pratinjau Foto Pegawai"
                                 class="avatar-detail <?= $fotoAda ? '' : 'd-none' ?>">
                            <div class="flex-grow-1">
                                <input type="file" id="foto" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                                <div class="form-text">
                                    Format JPG, PNG, atau WEBP (maks. 2 MB). <?= $modeEdit ? 'Kosongkan jika tidak ingin mengubah foto.' : '' ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end pt-2 border-top">
                        <a href="index.php" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-check-lg me-1"></i> Simpan
                        </button>
                    </div>
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
<script src="../assets/js/script.js?v=<?= filemtime(__DIR__ . '/../assets/js/script.js') ?>"></script>
</body>
</html>
