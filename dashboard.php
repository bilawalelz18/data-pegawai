<?php
require_once __DIR__ . '/config/koneksi.php';
wajibLogin('login.php');

$totalPegawai = (int) $pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();
$totalJabatan = (int) $pdo->query('SELECT COUNT(DISTINCT jabatan) FROM employees')->fetchColumn();

$batasPerHalaman = 5;
$totalHalaman    = max(1, (int) ceil($totalPegawai / $batasPerHalaman));
$halamanAktif    = filter_input(INPUT_GET, 'halaman', FILTER_VALIDATE_INT) ?: 1;
$halamanAktif    = max(1, min($halamanAktif, $totalHalaman));
$offset          = ($halamanAktif - 1) * $batasPerHalaman;

$stmtPegawai = $pdo->prepare('SELECT id, nip, nama, jabatan, email, foto FROM employees ORDER BY id DESC LIMIT :limit OFFSET :offset');
$stmtPegawai->bindValue(':limit', $batasPerHalaman, PDO::PARAM_INT);
$stmtPegawai->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtPegawai->execute();
$pegawaiTerbaru = $stmtPegawai->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIMPEG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<?php renderNavbar('dashboard', ''); ?>

<main class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 fade-in">
        <div>
            <h1 class="h3 fw-bold mb-1">Dashboard Kepegawaian</h1>
            <p class="text-secondary mb-0">Ringkasan data pegawai dan akses cepat pengelolaan.</p>
        </div>
        <a href="pegawai/tambah.php" class="btn btn-primary d-inline-flex align-items-center gap-2 fw-semibold">
            <i class="bi bi-person-plus-fill"></i> Tambah Pegawai
        </a>
    </div>

    <!-- Kartu Ringkasan -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 fade-in">
            <div class="card-custom card-hover p-4 h-100 d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div>
                    <div class="text-secondary small fw-semibold">Total Pegawai</div>
                    <div class="h2 fw-bold mb-0"><?= $totalPegawai ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 fade-in fade-in-delay-1">
            <div class="card-custom card-hover p-4 h-100 d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-briefcase-fill"></i>
                </div>
                <div>
                    <div class="text-secondary small fw-semibold">Total Jabatan Aktif</div>
                    <div class="h2 fw-bold mb-0"><?= $totalJabatan ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Pegawai -->
    <div class="card-custom p-4 fade-in fade-in-delay-2">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h5 fw-bold mb-0"><i class="bi bi-person-fill me-2 text-primary"></i>Tabel Pegawai</h2>
            <a href="pegawai/index.php" class="btn btn-sm btn-outline-primary">
                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Pegawai</th>
                        <th>NIP</th>
                        <th>Jabatan</th>
                        <th>Email</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pegawaiTerbaru)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">Belum ada data pegawai.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pegawaiTerbaru as $pegawai): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($pegawai['foto']) && is_file(__DIR__ . '/assets/images/pegawai/' . $pegawai['foto'])): ?>
                                            <img src="assets/images/pegawai/<?= e($pegawai['foto']) ?>" alt="<?= e($pegawai['nama']) ?>" class="avatar-pegawai">
                                        <?php else: ?>
                                            <span class="avatar-pegawai text-primary"><i class="bi bi-person-fill"></i></span>
                                        <?php endif; ?>
                                        <span class="fw-semibold"><?= e($pegawai['nama']) ?></span>
                                    </div>
                                </td>
                                <td><?= e($pegawai['nip']) ?></td>
                                <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= e($pegawai['jabatan']) ?></span></td>
                                <td><?= e($pegawai['email']) ?></td>
                                <td class="text-end">
                                    <a href="pegawai/detail.php?id=<?= (int) $pegawai['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPegawai > 0): ?>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 pt-2 border-top">
                <span class="text-secondary small">
                    Menampilkan <?= $offset + 1 ?>&ndash;<?= min($offset + count($pegawaiTerbaru), $totalPegawai) ?> dari <?= $totalPegawai ?> pegawai (Halaman <?= $halamanAktif ?> / <?= $totalHalaman ?>)
                </span>
                <nav aria-label="Navigasi halaman dashboard">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= $halamanAktif <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?halaman=<?= $halamanAktif - 1 ?>">Sebelumnya</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                            <li class="page-item <?= $i === $halamanAktif ? 'active' : '' ?>">
                                <a class="page-link" href="?halaman=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $halamanAktif >= $totalHalaman ? 'disabled' : '' ?>">
                            <a class="page-link" href="?halaman=<?= $halamanAktif + 1 ?>">Berikutnya</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
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
