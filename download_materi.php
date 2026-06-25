<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'SISWA') {
    header("Location: login.php?error=akses_ditolak");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: materi_siswa.php");
    exit();
}

$idMateri = (int)$_GET['id'];

// Ambil data materi
$stmt = mysqli_prepare($koneksi, "
    SELECT isi, judul FROM materi
    WHERE idMateri = ?
");
mysqli_stmt_bind_param($stmt, "i", $idMateri);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$materi = mysqli_fetch_assoc($result);

if (!$materi) {
    header("Location: materi_siswa.php");
    exit();
}

$namaFile = $materi['isi'];
$filePath = 'uploads/' . $namaFile;

if (!file_exists($filePath)) {
    // File belum ada di server — redirect kembali dengan pesan error
    header("Location: detail_materi.php?id=$idMateri&error=file_tidak_ditemukan");
    exit();
}

// Serve file sebagai download
$mime = mime_content_type($filePath) ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $namaFile . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache');
readfile($filePath);
exit();
?>
