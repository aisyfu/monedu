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
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: #ffffff;
            padding: 45px 40px;
            border-radius: 24px; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 520px ; 
            text-align: center;
        }

        .login-container h2 {
            margin-bottom: 8px;
            font-size: 32px;   
            font-weight: 700;
            color: #577883;
        }

        .login-container p {
            margin-bottom: 36px;
            color: #8a8a8a;
            font-size: 14px;
            font-weight: 400;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #333333;
        }

        .input-field {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-field i {
            position: absolute;
            left: 16px;
            color: #b0bac3;
            font-size: 16px;
        }

        .form-group input {
            width: 100%;
            height: 48px;
            padding: 10px 16px 10px 44px;
            background-color: #f1f3f5;
            border: 1px solid transparent;
            border-radius: 12px; 
            font-size: 14px;
            color: #333;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            box-shadow: 0 0 0 3px rgba(87, 120, 131, 0.1);
        }

        .form-group input::placeholder {
            color: #b0bac3;
        }

        .forgot-password {
            text-align: right;
            margin-top: -8px;
            margin-bottom: 28px;
        }

        .forgot-password a {
            font-size: 12px;
            color: #8a8a8a;
            text-decoration: none;
            transition: color 0.2s;
        }

        .forgot-password a:hover {
            color: #577883;
            text-decoration: underline;
        }

        .login-container button {
            width: 100%;
            height: 48px;
            background-color: #577883;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.5px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(87, 120, 131, 0.25);
            transition: all 0.3s ease;
        }

        .login-container button:hover {
            background-color: #456069; 
            transform: translateY(-1px);
        }
        
        .login-container button:active {
            transform: translateY(0);
        }
    </style>
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
