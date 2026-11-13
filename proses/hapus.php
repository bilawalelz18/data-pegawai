<?php
require_once __DIR__ . '/../config/koneksi.php';
wajibLogin('../login.php');

$isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        http_response_code(405);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'pesan' => 'Metode request tidak diizinkan.']);
        exit;
    }
    $_SESSION['flash_error'] = 'Metode request tidak diizinkan.';
    header('Location: ../pegawai/index.php');
    exit;
}

$idPegawai = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$idPegawai) {
    if ($isAjax) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'pesan' => 'ID pegawai tidak valid untuk dihapus.']);
        exit;
    }
    $_SESSION['flash_error'] = 'ID pegawai tidak valid untuk dihapus.';
    header('Location: ../pegawai/index.php');
    exit;
}

try {
    $stmtFoto = $pdo->prepare('SELECT foto FROM employees WHERE id = :id LIMIT 1');
    $stmtFoto->execute(['id' => $idPegawai]);
    $dataFoto = $stmtFoto->fetch();

    $stmt = $pdo->prepare('DELETE FROM employees WHERE id = :id');
    $stmt->execute(['id' => $idPegawai]);

    if ($dataFoto && !empty($dataFoto['foto'])) {
        hapusFileFoto($dataFoto['foto']);
    }

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'sukses', 'pesan' => 'Data pegawai berhasil dihapus.']);
        exit;
    }

    $_SESSION['flash_sukses'] = 'Data pegawai berhasil dihapus.';
} catch (PDOException $e) {
    if ($isAjax) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'pesan' => 'Terjadi kesalahan sistem.']);
        exit;
    }
    $_SESSION['flash_error'] = 'Terjadi kesalahan sistem.';
}

header('Location: ../pegawai/index.php');
exit;
