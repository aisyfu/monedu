<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id        = $_POST['id'];
    $kodeMapel = trim($_POST['kodeMapel']);
    $namaMapel = trim($_POST['namaMapel']);

    $query = "UPDATE mata_pelajaran SET kodeMapel=?, namaMapel=? WHERE idMapel=?";
    $stmt  = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ssi", $kodeMapel, $namaMapel, $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: kelola_mapel.php?status=sukses_edit");
    } else {
        header("Location: kelola_mapel.php?status=gagal");
    }
    exit();
}
?>