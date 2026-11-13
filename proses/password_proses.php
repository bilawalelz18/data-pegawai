<?php
require_once __DIR__ . '/../config/koneksi.php';
wajibLogin('../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin.php');
    exit;
}

$passwordLama       = $_POST['password_lama'] ?? '';
$passwordBaru       = $_POST['password_baru'] ?? '';
$konfirmasiPassword = $_POST['konfirmasi_password'] ?? '';

if ($passwordLama === '' || $passwordBaru === '' || $konfirmasiPassword === '') {
    $_SESSION['flash_error'] = 'Semua kolom password wajib diisi.';
    header('Location: ../admin.php');
    exit;
}

if (strlen($passwordBaru) < 6) {
    $_SESSION['flash_error'] = 'Password baru minimal harus 6 karakter.';
    header('Location: ../admin.php');
    exit;
}

if ($passwordBaru !== $konfirmasiPassword) {
    $_SESSION['flash_error'] = 'Konfirmasi password baru tidak sesuai.';
    header('Location: ../admin.php');
    exit;
}

$stmt = $pdo->prepare('SELECT password FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || !password_verify($passwordLama, $user['password'])) {
    $_SESSION['flash_error'] = 'Password saat ini yang Anda masukkan salah.';
    header('Location: ../admin.php');
    exit;
}

$hashBaru = password_hash($passwordBaru, PASSWORD_DEFAULT);

try {
    $upd = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
    $upd->execute([
        'password' => $hashBaru,
        'id'       => $_SESSION['user_id'],
    ]);

    $_SESSION['flash_sukses'] = 'Password berhasil diperbarui.';
} catch (PDOException $e) {
    $_SESSION['flash_error'] = 'Terjadi kesalahan sistem.';
}

header('Location: ../admin.php');
exit;

