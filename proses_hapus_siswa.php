<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idUser = $_POST['id'];

    mysqli_begin_transaction($koneksi);
    try {
        $querySiswa = "DELETE FROM siswa WHERE idUser = ?";
        $stmtSiswa  = mysqli_prepare($koneksi, $querySiswa);
        mysqli_stmt_bind_param($stmtSiswa, "i", $idUser);
        mysqli_stmt_execute($stmtSiswa);

        $queryUser = "DELETE FROM user WHERE idUser = ?";
        $stmtUser  = mysqli_prepare($koneksi, $queryUser);
        mysqli_stmt_bind_param($stmtUser, "i", $idUser);
        mysqli_stmt_execute($stmtUser);

        mysqli_commit($koneksi);
        header("Location: kelola_siswa.php?status=sukses_hapus");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "Gagal menghapus data: " . $e->getMessage();
    }
}
?>