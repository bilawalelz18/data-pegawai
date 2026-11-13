<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db   = 'db_pegawai';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi database gagal.");
}

const DAFTAR_JABATAN = [
    'Manager',
    'Supervisor',
    'Staff IT',
    'Staff HRD',
    'Staff Keuangan',
    'Staff Administrasi',
];

function wajibLogin(string $loginPath = 'login.php'): void {
    if (empty($_SESSION['user_id'])) {
        header("Location: $loginPath");
        exit;
    }
}

function e(?string $teks): string {
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

function renderNavbar(string $menuAktif = 'dashboard', string $basePath = ''): void {
    global $pdo;
    $username = $_SESSION['username'] ?? 'admin';
    $namaUser = $_SESSION['nama_user'] ?? null;
    $fotoUser = $_SESSION['foto_user'] ?? null;

    if (!empty($_SESSION['user_id']) && (!isset($_SESSION['foto_user']) || empty($namaUser))) {
        $stmtU = $pdo->prepare('SELECT username, nama, foto FROM users WHERE id = :id LIMIT 1');
        $stmtU->execute(['id' => $_SESSION['user_id']]);
        if ($rowU = $stmtU->fetch()) {
            $username = $_SESSION['username']  = $rowU['username'];
            $namaUser = $_SESSION['nama_user'] = $rowU['nama'] ?: $rowU['username'];
            $fotoUser = $_SESSION['foto_user'] = $rowU['foto'];
        }
    }
    $namaTampil = $namaUser ?: $username;

    $fotoValid = !empty($fotoUser) && is_file(__DIR__ . '/../assets/images/pegawai/' . $fotoUser);
    $aktifDashboard = $menuAktif === 'dashboard' ? 'active' : '';
    $aktifPegawai   = $menuAktif === 'pegawai' ? 'active' : '';
    $aktifAdmin     = $menuAktif === 'admin' ? 'active' : '';
    ?>
    <!-- Topbar Mobile -->
    <header class="mobile-topbar d-lg-none bg-brand text-white px-3 py-2 d-flex align-items-center justify-content-between shadow-sm sticky-top">
        <a class="d-flex align-items-center gap-2 text-white text-decoration-none fw-bold" href="<?= $basePath ?>dashboard.php">
            <img src="<?= $basePath ?>assets/images/logo.svg" alt="Logo SIMPEG" width="32" height="32">
            <span>SIMPEG</span>
        </a>
        <button class="btn btn-sm btn-light text-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-label="Buka menu">
            <i class="bi bi-list fs-5"></i>
        </button>
    </header>

    <!-- Sidebar Kiri -->
    <aside class="offcanvas-lg offcanvas-start app-sidebar bg-brand text-white d-flex flex-column" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
        <div class="sidebar-header p-3 d-flex align-items-center justify-content-between border-bottom border-white border-opacity-10">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-white text-decoration-none" href="<?= $basePath ?>dashboard.php" id="sidebarMenuLabel">
                <img src="<?= $basePath ?>assets/images/logo.svg" alt="Logo SIMPEG" width="38" height="38">
                <div>
                    <div class="lh-1 fs-5">SIMPEG</div>
                    <small class="text-white-50 fw-normal" style="font-size: 0.72rem;">Manajemen Pegawai</small>
                </div>
            </a>
            <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Tutup"></button>
        </div>

        <div class="p-3 flex-grow-1 d-flex flex-column justify-content-between">
            <ul class="nav nav-pills flex-column gap-1 sidebar-nav">
                <li class="nav-item">
                    <a class="nav-link <?= $aktifDashboard ?>" href="<?= $basePath ?>dashboard.php">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $aktifPegawai ?>" href="<?= $basePath ?>pegawai/index.php">
                        <i class="bi bi-people me-2"></i> Daftar Pegawai
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $aktifAdmin ?>" href="<?= $basePath ?>admin.php">
                        <i class="bi bi-person-gear me-2"></i> Profil Admin
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer pt-3 border-top border-white border-opacity-10">
                <a href="<?= $basePath ?>admin.php" class="d-flex align-items-center gap-2 text-white text-decoration-none p-2 rounded-3 sidebar-user-link mb-2">
                    <?php if ($fotoValid): ?>
                        <img src="<?= $basePath ?>assets/images/pegawai/<?= e($fotoUser) ?>" alt="<?= e($namaTampil) ?>" class="avatar-pegawai border-white">
                    <?php else: ?>
                        <span class="avatar-pegawai bg-white text-primary"><i class="bi bi-person-fill"></i></span>
                    <?php endif; ?>
                    <div class="overflow-hidden">
                        <div class="fw-semibold text-truncate"><?= e($namaTampil) ?></div>
                        <div class="small text-white-50 text-truncate">Administrator</div>
                    </div>
                </a>
                <a href="<?= $basePath ?>proses/logout.php" class="btn btn-light text-primary fw-semibold w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </aside>
    <?php
}

function validasiInputPegawai(array $data): ?string {
    $nip       = trim($data['nip'] ?? '');
    $nama      = trim($data['nama'] ?? '');
    $email     = trim($data['email'] ?? '');
    $jabatan   = trim($data['jabatan'] ?? '');
    $noTelepon = trim($data['no_telepon'] ?? '');

    if ($nip === '' || !ctype_digit($nip)) {
        return 'NIP wajib diisi dan hanya boleh berisi angka.';
    }
    if ($nama === '') {
        return 'Nama pegawai wajib diisi dan tidak boleh hanya spasi.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Format email pegawai tidak valid.';
    }
    if (!in_array($jabatan, DAFTAR_JABATAN, true)) {
        return 'Silakan pilih jabatan pegawai yang tersedia.';
    }
    if ($noTelepon === '' || !ctype_digit($noTelepon) || strlen($noTelepon) < 12) {
        return 'Nomor HP wajib diisi berupa angka minimal 12 digit.';
    }
    return null;
}

function unggahFotoPegawai(?array $file, ?string &$pesanError = null): ?string {
    if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $pesanError = 'Gagal mengunggah file foto.';
        return null;
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        $pesanError = 'Ukuran foto maksimal 2 MB.';
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $ekstensiValid = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($ekstensiValid[$mime])) {
        $pesanError = 'Format foto harus berupa JPG, PNG, atau WEBP.';
        return null;
    }

    $folderTujuan = __DIR__ . '/../assets/images/pegawai';
    if (!is_dir($folderTujuan)) {
        mkdir($folderTujuan, 0755, true);
    }

    $namaFile = 'pegawai_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ekstensiValid[$mime];
    if (!move_uploaded_file($file['tmp_name'], $folderTujuan . '/' . $namaFile)) {
        $pesanError = 'Gagal menyimpan file foto ke server.';
        return null;
    }

    return $namaFile;
}

function hapusFileFoto(?string $namaFile): void {
    if (!$namaFile) {
        return;
    }
    $lokasiFile = __DIR__ . '/../assets/images/pegawai/' . basename($namaFile);
    if (is_file($lokasiFile)) {
        unlink($lokasiFile);
    }
}
