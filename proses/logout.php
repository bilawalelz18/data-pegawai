<?php
require_once __DIR__ . '/../config/koneksi.php';

$_SESSION = [];
session_destroy();

header('Location: ../login.php');
exit;

