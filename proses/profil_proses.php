<?php
require_once __DIR__ . '/../config/koneksi.php';
wajibLogin('../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin.php');
    exit;
}

$idAdmin  = (int) $_SESSION['user_id'];
$nama     = trim($_POST['nama'] ?? '');
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$jabatan  = 'Administrator';

if ($nama === '') {
    $_SESSION['flash_error'] = 'Nama lengkap wajib diisi.';
    header('Location: ../admin.php');
    exit;
}

if ($username === '') {
    $_SESSION['flash_error'] = 'Username wajib diisi.';
    header('Location: ../admin.php');
    exit;
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash_error'] = 'Format email administrator tidak valid.';
    header('Location: ../admin.php');
    exit;
}

$stmtLama = $pdo->prepare('SELECT foto FROM users WHERE id = :id LIMIT 1');
$stmtLama->execute(['id' => $idAdmin]);
$dataLama = $stmtLama->fetch();
if (!$dataLama) {
    header('Location: logout.php');
    exit;
}

// Cek keunikan username jika diubah
$cekUser = $pdo->prepare('SELECT id FROM users WHERE username = :username AND id != :id LIMIT 1');
$cekUser->execute(['username' => $username, 'id' => $idAdmin]);
if ($cekUser->fetch()) {
    $_SESSION['flash_error'] = 'Username tersebut sudah digunakan.';
    header('Location: ../admin.php');
    exit;
}

$errorFoto = null;
$fotoBaru  = unggahFotoPegawai($_FILES['foto'] ?? null, $errorFoto);
if ($errorFoto !== null) {
    $_SESSION['flash_error'] = $errorFoto;
    header('Location: ../admin.php');
    exit;
}

$fotoSimpan = $fotoBaru !== null ? $fotoBaru : $dataLama['foto'];

try {
    $stmt = $pdo->prepare(
        'UPDATE users
         SET username = :username, nama = :nama, email = :email, jabatan = :jabatan, foto = :foto
         WHERE id = :id'
    );
    $stmt->execute([
        'username' => $username,
        'nama'     => $nama,
        'email'    => $email,
        'jabatan'  => $jabatan,
        'foto'     => $fotoSimpan,
        'id'       => $idAdmin,
    ]);

    if ($fotoBaru !== null) {
        hapusFileFoto($dataLama['foto']);
    }

    $_SESSION['username']     = $username;
    $_SESSION['nama_user']    = $nama;
    $_SESSION['foto_user']    = $fotoSimpan;
    $_SESSION['flash_sukses'] = 'Profil administrator berhasil diperbarui.';
} catch (PDOException $e) {
    if ($fotoBaru !== null) {
        hapusFileFoto($fotoBaru);
    }
    $_SESSION['flash_error'] = 'Terjadi kesalahan sistem.';
}

header('Location: ../admin.php');
exit;
