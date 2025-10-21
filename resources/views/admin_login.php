<?php
session_start();
require_once 'config.php';

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Cek apakah username ada
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        // Verifikasi password jika username ditemukan
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            header('Location: admin_dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah!';
        }
    } catch (PDOException $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Kopi & Kata</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-login-container {
            max-width: 400px;
            margin: 150px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .admin-login-container h2 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-family: 'Clicker Script', cursive;
            font-size: 3rem;
        }

        .admin-login-container .box {
            width: 100%;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .admin-login-container .btn {
            width: 100%;
            margin-top: 10px;
        }

        .error-message {
            background-color: rgba(244, 67, 54, 0.8);
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }

        .coffee-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('src/images/background.jpg');
            background-size: cover;
            background-position: center;
            filter: brightness(0.3);
            z-index: -1;
        }
    </style>
</head>

<body>
    <div class="coffee-bg"></div>
    <div class="admin-login-container">
        <h2>Admin Login</h2>

        <?php if ($error): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="text" name="username" placeholder="Username" class="box" required>
            <input type="password" name="password" placeholder="Password" class="box" required>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>

</html>