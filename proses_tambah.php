<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $nip = $_POST['nip'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $idRole = 2; 
    $status = 'Aktif';
    $mataPelajaran = 'Belum ditentukan'; 
    $createdAt = date('Y-m-d H:i:s');

    mysqli_begin_transaction($koneksi);

    try {
        $queryUser = "INSERT INTO user (idRole, nama, username, password, email, status, createdAt) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtUser = mysqli_prepare($koneksi, $queryUser);
        mysqli_stmt_bind_param($stmtUser, "issssss", $idRole, $nama, $username, $password, $email, $status, $createdAt);
        mysqli_stmt_execute($stmtUser);
        
        $idUserBaru = mysqli_insert_id($koneksi);

        $queryGuru = "INSERT INTO guru (idUser, nip, mataPelajaran) VALUES (?, ?, ?)";
        $stmtGuru = mysqli_prepare($koneksi, $queryGuru);
        mysqli_stmt_bind_param($stmtGuru, "iss", $idUserBaru, $nip, $mataPelajaran);
        mysqli_stmt_execute($stmtGuru);

        mysqli_commit($koneksi);
        header("Location: kelola_guru.php?status=sukses_tambah");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo "Gagal menambahkan data: " . $e->getMessage();
    }
}
?>