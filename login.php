<?php
session_start();

if (isset($_SESSION['login'])) {
    if ($_SESSION['role'] == 'ADMIN') {
        header("Location: dashboard_admin.php");
    } else if ($_SESSION['role'] == 'GURU') {
        header("Location: dashboard_guru.php");
    } else if ($_SESSION['role'] == 'SISWA') {
        header("Location: dashboard_siswa.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MonEdu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>

    <div class="login-container">
        <h2>MonEdu</h2>
        <p>Silakan masuk ke akun Anda</p>
        
        <form action="authenticate.php" method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-field">
                    <i class="fa-regular fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="nama@sch.id" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-field">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <div class="forgot-password">
                <a href="#">Lupa Password?</a>
            </div>

            <button type="submit">LOGIN</button>
        </form>
    </div>
</body>
</html>
