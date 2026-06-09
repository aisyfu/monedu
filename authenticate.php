<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT u.idUser, u.nama, u.password, r.namaRole 
              FROM user u 
              JOIN role r ON u.idRole = r.idRole 
              WHERE u.email = ?";
              
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            
            $_SESSION['login'] = true;
            $_SESSION['idUser'] = $row['idUser'];
            $_SESSION['nama'] = $row['nama'];
            $_SESSION['role'] = strtoupper($row['namaRole']);
            
            if ($_SESSION['role'] == 'ADMIN') {
                header("Location: dashboard_admin.php");
            } else if ($_SESSION['role'] == 'GURU') {
                header("Location: dashboard_guru.php");
            } else if ($_SESSION['role'] == 'SISWA') {
                header("Location: dashboard_siswa.php");
            } else {
                header("Location: login.php?error=role_tidak_dikenali");
            }
            exit();
            
        } else {
            header("Location: login.php?error=password_salah");
            exit();
        }
    } else {
        header("Location: login.php?error=email_tidak_ditemukan");
        exit();
    }
}
?>