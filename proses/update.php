<?php
require_once __DIR__ . '/../config/koneksi.php';
wajibLogin('../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pegawai/index.php');
    exit;
}

$idPegawai = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$idPegawai) {
    $_SESSION['flash_error'] = 'ID pegawai tidak valid.';
    header('Location: ../pegawai/index.php');
    exit;
}

$stmtLama = $pdo->prepare('SELECT id, foto FROM employees WHERE id = :id LIMIT 1');
$stmtLama->execute(['id' => $idPegawai]);
$dataLama = $stmtLama->fetch();
if (!$dataLama) {
    $_SESSION['flash_error'] = 'Data pegawai tidak ditemukan.';
    header('Location: ../pegawai/index.php');
    exit;
}

$pesanError = validasiInputPegawai($_POST);
if ($pesanError !== null) {
    $_SESSION['flash_error'] = $pesanError;
    header('Location: ../pegawai/edit.php?id=' . $idPegawai);
    exit;
}

$nip       = trim($_POST['nip']);
$nama      = trim($_POST['nama']);
$email     = trim($_POST['email']);
$jabatan   = trim($_POST['jabatan']);
$noTelepon = trim($_POST['no_telepon']);

// Cek keunikan NIP selain milik dirinya sendiri
$cekStmt = $pdo->prepare('SELECT id FROM employees WHERE nip = :nip AND id != :id LIMIT 1');
$cekStmt->execute(['nip' => $nip, 'id' => $idPegawai]);
if ($cekStmt->fetch()) {
    $_SESSION['flash_error'] = 'NIP tersebut sudah digunakan oleh pegawai lain.';
    header('Location: ../pegawai/edit.php?id=' . $idPegawai);
    exit;
}

$errorFoto = null;
$fotoBaru  = unggahFotoPegawai($_FILES['foto'] ?? null, $errorFoto);
if ($errorFoto !== null) {
    $_SESSION['flash_error'] = $errorFoto;
    header('Location: ../pegawai/edit.php?id=' . $idPegawai);
    exit;
}

$fotoSimpan = $fotoBaru !== null ? $fotoBaru : $dataLama['foto'];

try {
    $stmt = $pdo->prepare(
        'UPDATE employees
         SET nip = :nip, nama = :nama, email = :email, jabatan = :jabatan, no_telepon = :no_telepon, foto = :foto
         WHERE id = :id'
    );
    $stmt->execute([
        'nip'        => $nip,
        'nama'       => $nama,
        'email'      => $email,
        'jabatan'    => $jabatan,
        'no_telepon' => $noTelepon,
        'foto'       => $fotoSimpan,
        'id'         => $idPegawai,
    ]);

    if ($fotoBaru !== null) {
        hapusFileFoto($dataLama['foto']);
    }

    $_SESSION['flash_sukses'] = 'Data pegawai berhasil diperbarui.';
    header('Location: ../pegawai/index.php');
    exit;
} catch (PDOException $e) {
    if ($fotoBaru !== null) {
        hapusFileFoto($fotoBaru);
    }
    $_SESSION['flash_error'] = 'Terjadi kesalahan sistem.';
    header('Location: ../pegawai/edit.php?id=' . $idPegawai);
    exit;
}
