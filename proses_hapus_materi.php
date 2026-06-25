<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'GURU') {
    header("Location: login.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    $cek = mysqli_query($koneksi, "SELECT isi FROM materi WHERE idMateri = $id");
    $row = mysqli_fetch_assoc($cek);

    $stmt = mysqli_prepare($koneksi, "DELETE FROM materi WHERE idMateri = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        if (!empty($row['isi']) && file_exists('uploads/' . $row['isi'])) {
            unlink('uploads/' . $row['isi']);
        }
        header("Location: kelola-materi-guru.php?status=sukses_hapus");
    } else {
        header("Location: kelola-materi-guru.php?status=gagal");
    }
    exit();
}
