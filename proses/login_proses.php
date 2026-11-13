<?php
require_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['flash_error'] = 'Username dan password wajib diisi.';
    header('Location: ../login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, username, password, nama, foto FROM users WHERE username = :username LIMIT 1');
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['nama_user'] = $user['nama'];
    $_SESSION['foto_user'] = $user['foto'] ?? null;
    header('Location: ../dashboard.php');
    exit;
}

$_SESSION['flash_error'] = 'Username atau password yang Anda masukkan salah.';
header('Location: ../login.php');
exit;
