<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'GURU') {
    header("Location: login.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id        = $_POST['id'];
    $judul     = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $kategori  = trim($_POST['kategori']);
    $idMapel   = !empty($_POST['idMapel']) ? $_POST['idMapel'] : null;
    $isiLama   = $_POST['isiLama'];
    $namaFile  = $isiLama; // default pakai file lama

    if (!empty($_FILES['filePdf']['name'])) {
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
            if (!empty($isiLama) && file_exists('uploads/' . $isiLama)) {
                unlink('uploads/' . $isiLama);
            }
        } else {
            header("Location: kelola-materi-guru.php?status=gagal"); exit();
        }
    }

    $stmt = mysqli_prepare($koneksi, "UPDATE materi SET judul=?, deskripsi=?, isi=?, kategori=?, idMapel=? WHERE idMateri=?");
    mysqli_stmt_bind_param($stmt, "ssssii", $judul, $deskripsi, $namaFile, $kategori, $idMapel, $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: kelola-materi-guru.php?status=sukses_edit");
    } else {
        header("Location: kelola-materi-guru.php?status=gagal");
    }
    exit();
}
