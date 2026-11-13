<?php
require_once __DIR__ . '/../config/koneksi.php';
wajibLogin('../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pegawai/index.php');
    exit;
}

$pesanError = validasiInputPegawai($_POST);
if ($pesanError !== null) {
    $_SESSION['flash_error'] = $pesanError;
    $_SESSION['old_input']   = $_POST;
    header('Location: ../pegawai/tambah.php');
    exit;
}

$nip       = trim($_POST['nip']);
$nama      = trim($_POST['nama']);
$email     = trim($_POST['email']);
$jabatan   = trim($_POST['jabatan']);
$noTelepon = trim($_POST['no_telepon']);

// Cek keunikan NIP
$cekStmt = $pdo->prepare('SELECT id FROM employees WHERE nip = :nip LIMIT 1');
$cekStmt->execute(['nip' => $nip]);
if ($cekStmt->fetch()) {
    $_SESSION['flash_error'] = 'NIP tersebut sudah terdaftar pada pegawai lain.';
    $_SESSION['old_input']   = $_POST;
    header('Location: ../pegawai/tambah.php');
    exit;
}

$errorFoto = null;
$namaFoto  = unggahFotoPegawai($_FILES['foto'] ?? null, $errorFoto);
if ($errorFoto !== null) {
    $_SESSION['flash_error'] = $errorFoto;
    $_SESSION['old_input']   = $_POST;
    header('Location: ../pegawai/tambah.php');
    exit;
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO employees (nip, nama, email, jabatan, no_telepon, foto)
         VALUES (:nip, :nama, :email, :jabatan, :no_telepon, :foto)'
    );
    $stmt->execute([
        'nip'        => $nip,
        'nama'       => $nama,
        'email'      => $email,
        'jabatan'    => $jabatan,
        'no_telepon' => $noTelepon,
        'foto'       => $namaFoto,
    ]);

    unset($_SESSION['old_input']);
    $_SESSION['flash_sukses'] = 'Data pegawai berhasil ditambahkan.';
    header('Location: ../pegawai/index.php');
    exit;
} catch (PDOException $e) {
    hapusFileFoto($namaFoto);
    $_SESSION['flash_error'] = 'Terjadi kesalahan sistem.';
    $_SESSION['old_input']   = $_POST;
    header('Location: ../pegawai/tambah.php');
    exit;
}
