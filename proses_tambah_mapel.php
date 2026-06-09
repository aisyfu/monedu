<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kodeMapel = trim($_POST['kodeMapel']);
    $namaMapel = trim($_POST['namaMapel']);

    $query = "INSERT INTO mata_pelajaran (kodeMapel, namaMapel) VALUES (?, ?)";
    $stmt  = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ss", $kodeMapel, $namaMapel);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: kelola_mapel.php?status=sukses_tambah");
    } else {
        header("Location: kelola_mapel.php?status=gagal");
    }
    exit();
}
?>