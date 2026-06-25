<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'GURU') {
    header("Location: login.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idSoal = $_POST['id'];

    $stmt = mysqli_prepare($koneksi, "DELETE FROM soal WHERE idSoal = ?");
    mysqli_stmt_bind_param($stmt, "i", $idSoal);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: kelola-soal.php?status=sukses_hapus");
    } else {
        header("Location: kelola-soal.php?status=gagal_hapus");
    }
    exit();
}
?>