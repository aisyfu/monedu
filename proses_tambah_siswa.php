<?php
session_start();
include 'koneksi.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // tambahkan ini

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama     = $_POST['nama'];
    $nis      = $_POST['nis'];
    $kelas    = $_POST['kelas'];
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $idRole   = 3; // role siswa
    $status   = 'Aktif';
    date_default_timezone_set('Asia/Jakarta');
    $createdAt = date('Y-m-d H:i:s');

    mysqli_begin_transaction($koneksi);
    try {
        $queryUser = "INSERT INTO user (idRole, nama, username, password, email, status, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtUser  = mysqli_prepare($koneksi, $queryUser);
        mysqli_stmt_bind_param($stmtUser, "issssss", $idRole, $nama, $username, $password, $email, $status, $createdAt);
        mysqli_stmt_execute($stmtUser);

        $idUserBaru = mysqli_insert_id($koneksi);

        $querySiswa = "INSERT INTO siswa (idUser, nis, kelas) VALUES (?, ?, ?)";
        $stmtSiswa  = mysqli_prepare($koneksi, $querySiswa);
        mysqli_stmt_bind_param($stmtSiswa, "iss", $idUserBaru, $nis, $kelas);
        mysqli_stmt_execute($stmtSiswa);

        mysqli_commit($koneksi);
        header("Location: kelola_siswa.php?status=sukses_tambah");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "Gagal menambahkan data: " . $e->getMessage();
    }
}
?>