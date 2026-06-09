<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idUser   = $_POST['id'];
    $nama     = $_POST['nama'];
    $nis      = $_POST['nis'];
    $kelas    = $_POST['kelas'];
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password_input = $_POST['password'];

    mysqli_begin_transaction($koneksi);
    try {
        if (!empty($password_input)) {
            $password_hash = password_hash($password_input, PASSWORD_DEFAULT);
            $queryUser = "UPDATE user SET nama=?, username=?, email=?, password=? WHERE idUser=?";
            $stmtUser  = mysqli_prepare($koneksi, $queryUser);
            mysqli_stmt_bind_param($stmtUser, "ssssi", $nama, $username, $email, $password_hash, $idUser);
        } else {
            $queryUser = "UPDATE user SET nama=?, username=?, email=? WHERE idUser=?";
            $stmtUser  = mysqli_prepare($koneksi, $queryUser);
            mysqli_stmt_bind_param($stmtUser, "sssi", $nama, $username, $email, $idUser);
        }
        mysqli_stmt_execute($stmtUser);

        $querySiswa = "UPDATE siswa SET nis=?, kelas=? WHERE idUser=?";
        $stmtSiswa  = mysqli_prepare($koneksi, $querySiswa);
        mysqli_stmt_bind_param($stmtSiswa, "ssi", $nis, $kelas, $idUser);
        mysqli_stmt_execute($stmtSiswa);

        mysqli_commit($koneksi);
        header("Location: kelola_siswa.php?status=sukses_edit");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "Gagal mengupdate data: " . $e->getMessage();
    }
}
?>