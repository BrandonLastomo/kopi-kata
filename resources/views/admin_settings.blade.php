<?php
require_once 'admin_auth_check.php';
require_once 'config.php';

$admin_name = $_SESSION['admin_name'];
$error = '';
$success = '';

// Ambil pesan sukses atau error dari session
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary: #4a2c2a;
            --secondary: #f4b95a;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: var(--primary);
            color: white;
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 10;
        }

        .sidebar-header {
            margin-top: 80px;
            text-align: center;
            padding: 0 20px 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h2 {
            font-family: 'Clicker Script', cursive;
            font-size: 2.5rem;
            margin: 0;
            color: var(--secondary);
        }

        .sidebar-nav {
            padding: 0 10px;
        }

        .nav-item {
            padding: 10px 20px;
            margin-bottom: 5px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .nav-item:hover,
        .nav-item.active {
            background: rgba(244, 185, 90, 0.2);
        }

        .nav-item i {
            margin-right: 10px;
            color: var(--secondary);
        }

        .nav-item a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            margin-top: 80px;
            background: #f9f9f9;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 10px 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }

        .header h1 {
            color: var(--primary);
            margin: 0;
            font-size: 1.8rem;
        }

        .admin-info {
            display: flex;
            align-items: center;
        }

        .admin-info span {
            margin-right: 15px;
            color: #666;
        }

        .logout-btn {
            background: #f44336;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: #d32f2f;
        }

        .content-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .coming-soon {
            text-align: center;
            padding: 80px 20px;
        }

        .coming-soon i {
            font-size: 5rem;
            color: var(--secondary);
            margin-bottom: 20px;
        }

        .coming-soon h2 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .coming-soon p {
            font-size: 1.2rem;
            color: #555;
            max-width: 600px;
            margin: 0 auto 30px;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 10px;
            }

            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }

            .sidebar.active {
                transform: translateX(0);
            }
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Kopi & Kata</h2>
                <p>Admin Panel</p>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <a href="admin_dashboard.php">Dashboard</a>
                </div>
                <div class="nav-item">
                    <i class="fas fa-calendar-alt"></i>
                    <a href="bookings.php">Kelola Booking</a>
                </div>
                <div class="nav-item">
                    <i class="fas fa-chair"></i>
                    <a href="admin_tables.php">Kelola Meja</a>
                </div>
                <div class="nav-item">
                    <i class="fas fa-users"></i>
                    <a href="admin_users.php">Kelola Pengguna</a>
                </div>
                <div class="nav-item active">
                    <i class="fas fa-cog"></i>
                    <a href="admin_settings.php">Pengaturan</a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>Pengaturan Sistem</h1>
                <div class="admin-info">
                    <span>Selamat datang, <?= htmlspecialchars($admin_name) ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <div class="content-card">
                <div class="coming-soon">
                    <i class="fas fa-cogs"></i>
                    <h2>Coming Soon!</h2>
                    <p>Halaman pengaturan sistem sedang dalam pengembangan. Fitur ini akan segera tersedia pada
                        pembaruan berikutnya.</p>
                    <p>Silahkan kembali ke <a href="admin_dashboard.php"
                            style="color: var(--primary); font-weight: bold;">Dashboard</a> untuk mengakses fitur
                        lainnya.</p>
                </div>
            </div>
        </main>
    </div>
</body>

</html>