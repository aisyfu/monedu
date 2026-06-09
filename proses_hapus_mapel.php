<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    // Cek dulu apakah mapel masih dipakai oleh guru
    $cek = mysqli_query($koneksi, "SELECT idUser FROM guru WHERE idMapel = $id");
    if (mysqli_num_rows($cek) > 0) {
        header("Location: kelola_mapel.php?status=gagal_hapus_dipakai");
        exit();
    }

    $query = "DELETE FROM mata_pelajaran WHERE idMapel = ?";
    $stmt  = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: kelola_mapel.php?status=sukses_hapus");
    } else {
        header("Location: kelola_mapel.php?status=gagal");
    }
    exit();
}
?>