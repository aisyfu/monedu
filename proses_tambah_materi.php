<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'GURU') {
    header("Location: login.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idGuru    = $_SESSION['idUser'];
    $judul     = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $kategori  = trim($_POST['kategori']);
    $idMapel   = !empty($_POST['idMapel']) ? $_POST['idMapel'] : null;

    $file    = $_FILES['filePdf'];
    $maxSize = 30 * 1024 * 1024;

    if ($file['size'] > $maxSize) {
        header("Location: kelola-materi-guru.php?status=file_besar"); exit();
    }

    $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ekstensi !== 'pdf') {
        header("Location: kelola-materi-guru.php?status=bukan_pdf"); exit();
    }

    $namaFile = time() . '_' . preg_replace('/\s+/', '_', basename($file['name']));
    $tujuan   = 'uploads/' . $namaFile;

    if (!is_dir('uploads')) mkdir('uploads', 0755, true);

    if (move_uploaded_file($file['tmp_name'], $tujuan)) {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO materi (idGuru, judul, deskripsi, isi, kategori, statusValidasi, idMapel) VALUES (?, ?, ?, ?, ?, 'pending', ?)");
        mysqli_stmt_bind_param($stmt, "issssi", $idGuru, $judul, $deskripsi, $namaFile, $kategori, $idMapel);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: kelola-materi-guru.php?status=sukses_tambah");
        } else {
            header("Location: kelola-materi-guru.php?status=gagal");
        }
    } else {
        header("Location: kelola-materi-guru.php?status=gagal");
    }
    exit();
}
