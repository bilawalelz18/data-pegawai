<?php
require_once __DIR__ . '/../config/koneksi.php';
wajibLogin('../login.php');

$daftarPegawai = $pdo->query('SELECT id, nip, nama, email, jabatan, no_telepon, foto FROM employees ORDER BY id DESC')->fetchAll();

$pesanSukses = $_SESSION['flash_sukses'] ?? null;
$pesanError  = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_sukses'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pegawai - SIMPEG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<?php renderNavbar('pegawai', '../'); ?>

<main class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 fade-in">
        <div>
            <h1 class="h3 fw-bold mb-1">Daftar Pegawai</h1>
            <p class="text-secondary mb-0">Kelola data pegawai: tambah, lihat detail, perbarui, atau hapus data.</p>
        </div>
        <a href="tambah.php" class="btn btn-primary d-inline-flex align-items-center gap-2 fw-semibold">
            <i class="bi bi-person-plus-fill"></i> Tambah Pegawai
        </a>
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

    <div id="ajaxAlertContainer"></div>

    <div class="card-custom p-4 fade-in fade-in-delay-1">
        <div class="row g-3 align-items-center justify-content-between mb-3">
            <div class="col-md-5">
                <label for="cariPegawai" class="visually-hidden">Cari Pegawai</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="search" id="cariPegawai" class="form-control" placeholder="Cari NIP, nama, atau email...">
                </div>
            </div>
            <div class="col-md-4">
                <label for="filterJabatan" class="visually-hidden">Filter Jabatan</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-funnel"></i></span>
                    <select id="filterJabatan" class="form-select">
                        <option value="">Semua Jabatan</option>
                        <?php foreach (DAFTAR_JABATAN as $itemJabatan): ?>
                            <option value="<?= e($itemJabatan) ?>"><?= e($itemJabatan) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-auto text-secondary small">
                Total: <strong id="totalPegawaiCount"><?= count($daftarPegawai) ?></strong> pegawai terdaftar
            </div>
        </div>

        <div class="table-responsive">
            <table id="tabelPegawai" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Pegawai</th>
                        <th>NIP</th>
                        <th>Email</th>
                        <th>Jabatan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarPegawai)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">Belum ada data pegawai tersimpan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftarPegawai as $indeks => $pegawai): ?>
                            <tr class="baris-data" data-jabatan="<?= e($pegawai['jabatan']) ?>" style="<?= $indeks >= 5 ? 'display: none;' : '' ?>">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($pegawai['foto']) && is_file(__DIR__ . '/../assets/images/pegawai/' . $pegawai['foto'])): ?>
                                            <img src="../assets/images/pegawai/<?= e($pegawai['foto']) ?>" alt="<?= e($pegawai['nama']) ?>" class="avatar-pegawai">
                                        <?php else: ?>
                                            <span class="avatar-pegawai text-primary"><i class="bi bi-person-fill"></i></span>
                                        <?php endif; ?>
                                        <span class="fw-semibold"><?= e($pegawai['nama']) ?></span>
                                    </div>
                                </td>
                                <td><?= e($pegawai['nip']) ?></td>
                                <td><?= e($pegawai['email']) ?></td>
                                <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= e($pegawai['jabatan']) ?></span></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="detail.php?id=<?= (int) $pegawai['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Lihat Detail">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                        <a href="edit.php?id=<?= (int) $pegawai['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit Data">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger btn-hapus"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalHapus"
                                                data-id="<?= (int) $pegawai['id'] ?>"
                                                data-nama="<?= e($pegawai['nama']) ?>">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="barisKosongFilter" class="d-none">
                            <td colspan="5" class="text-center text-secondary py-4">Tidak ada pegawai yang cocok dengan filter pencarian.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="wadahNavigasiHalaman" class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 pt-2 border-top <?= empty($daftarPegawai) ? 'd-none' : '' ?>">
            <span id="infoHalamanPegawai" class="text-secondary small"></span>
            <nav aria-label="Navigasi halaman pegawai">
                <ul id="paginasiPegawai" class="pagination pagination-sm mb-0"></ul>
            </nav>
        </div>
    </div>
</main>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="judulModalHapus" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5 fw-bold" id="judulModalHapus">Konfirmasi Hapus Pegawai</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Batal"></button>
            </div>
            <form id="formHapusPegawai" action="../proses/hapus.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="hapusId">
                    <p class="mb-0">Apakah Anda yakin ingin menghapus data pegawai <strong id="hapusNama"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i> Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="py-3 border-top bg-white text-center text-secondary small">
    <div class="container">SIMPEG &mdash; Sistem Informasi Manajemen Kepegawaian</div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js?v=<?= filemtime(__DIR__ . '/../assets/js/script.js') ?>"></script>
</body>
</html>
