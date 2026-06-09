<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idUser = $_POST['id'];

    mysqli_begin_transaction($koneksi);

    try {
        $queryGuru = "DELETE FROM guru WHERE idUser = ?";
        $stmtGuru = mysqli_prepare($koneksi, $queryGuru);
        mysqli_stmt_bind_param($stmtGuru, "i", $idUser);
        mysqli_stmt_execute($stmtGuru);

        $queryUser = "DELETE FROM user WHERE idUser = ?";
        $stmtUser = mysqli_prepare($koneksi, $queryUser);
        mysqli_stmt_bind_param($stmtUser, "i", $idUser);
        mysqli_stmt_execute($stmtUser);

        mysqli_commit($koneksi);
        header("Location: kelola_guru.php?status=sukses_hapus");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "Gagal menghapus data: " . $e->getMessage();
    }
}
?>