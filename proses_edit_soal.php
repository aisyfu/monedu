<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'GURU') {
    header("Location: login.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idSoal       = $_POST['idSoal'];
    $idMateri     = $_POST['idMateri'];
    $pertanyaan   = trim($_POST['pertanyaan']);
    $opsi_a       = trim($_POST['opsi_a']);
    $opsi_b       = trim($_POST['opsi_b']);
    $opsi_c       = trim($_POST['opsi_c']);
    $opsi_d       = trim($_POST['opsi_d']);
    $jawabanBenar = $_POST['jawabanBenar'];

    $stmt = mysqli_prepare($koneksi, "UPDATE soal SET idMateri=?, pertanyaan=?, opsi_a=?, opsi_b=?, opsi_c=?, opsi_d=?, jawabanBenar=? WHERE idSoal=?");
    mysqli_stmt_bind_param($stmt, "issssssi", $idMateri, $pertanyaan, $opsi_a, $opsi_b, $opsi_c, $opsi_d, $jawabanBenar, $idSoal);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: kelola-soal.php?status=sukses_edit");
    } else {
        header("Location: kelola-soal.php?status=gagal_edit");
    }
    exit();
}
?>