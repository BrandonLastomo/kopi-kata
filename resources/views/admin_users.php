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

// Proses form untuk menambah/mengupdate user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        try {
            // Tambah user baru
            if ($_POST['action'] == 'add') {
                $username = trim($_POST['username']);
                $password = $_POST['password'];
                $email = trim($_POST['email']);
                $status = isset($_POST['status']) ? $_POST['status'] : 'active';

                // Validasi data
                if (empty($username) || empty($password) || empty($email)) {
                    $error = "Username, password, dan email wajib diisi";
                } else {
                    // Cek apakah username sudah ada
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if ($stmt->fetchColumn() > 0) {
                        $error = "Username sudah digunakan";
                    } else {
                        // Cek apakah email sudah ada
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                        $stmt->execute([$email]);
                        if ($stmt->fetchColumn() > 0) {
                            $error = "Email sudah terdaftar";
                        } else {
                            // Hash password
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                            // Tambahkan user baru
                            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) 
                                                VALUES (?, ?, ?, NOW())");
                            $stmt->execute([$username, $email, $hashed_password]);
                            $success = "User baru berhasil ditambahkan";
                        }
                    }
                }
            }
            // Update user
            elseif ($_POST['action'] == 'edit') {
                $id = $_POST['id'];
                $username = trim($_POST['username']);
                $email = trim($_POST['email']);
                $password = $_POST['password'];

                // Validasi data
                if (empty($username) || empty($email)) {
                    $error = "Username dan email wajib diisi";
                } else {
                    // Cek apakah username sudah ada (selain user ini)
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
                    $stmt->execute([$username, $id]);
                    if ($stmt->fetchColumn() > 0) {
                        $error = "Username sudah digunakan";
                    } else {
                        // Cek apakah email sudah ada (selain user ini)
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
                        $stmt->execute([$email, $id]);
                        if ($stmt->fetchColumn() > 0) {
                            $error = "Email sudah terdaftar";
                        } else {
                            if (empty($password)) {
                                // Update tanpa mengubah password
                                $sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$username, $email, $id]);
                            } else {
                                // Update dengan password baru
                                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                $sql = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$username, $email, $hashed_password, $id]);
                            }
                            $success = "Data user berhasil diperbarui";
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Filter dan pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Base query
$query = "SELECT * FROM users WHERE 1=1";
$params = [];

// Terapkan filter pencarian
if (!empty($search)) {
    $query .= " AND (username LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
}

// Sorting
$query .= " ORDER BY created_at DESC";

// Execute query
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $users = [];
}

// Mode edit jika ada parameter GET id
$editMode = false;
$editUser = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($editUser) {
            $editMode = true;
        }
    } catch (PDOException $e) {
        $error = "Error retrieving user data: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna | Admin Panel</title>
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

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }

        .form-group {
            padding: 0 10px;
            margin-bottom: 20px;
            flex: 1;
            min-width: 200px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #3a2320;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .search-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-box {
            flex: 2;
            min-width: 200px;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 38px 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .search-box button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #555;
            cursor: pointer;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }

        td {
            border-bottom: 1px solid #dee2e6;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-sm i {
            margin-right: 5px;
        }

        .btn-edit {
            background-color: #007bff;
            color: white;
        }

        .btn-edit:hover {
            background-color: #0069d9;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        .tab-container {
            margin-bottom: 20px;
        }

        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
        }

        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s;
        }

        .tab.active {
            border-bottom-color: var(--primary);
            color: var(--primary);
            font-weight: 500;
        }

        .tab-content {
            display: none;
            padding: 20px 0;
        }

        .tab-content.active {
            display: block;
        }

        .password-field {
            position: relative;
        }

        .password-field .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #555;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }

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
                    <a href="admin_bookings.php">Kelola Booking</a>
                </div>
                <div class="nav-item">
                    <i class="fas fa-chair"></i>
                    <a href="admin_tables.php">Kelola Meja</a>
                </div>
                <div class="nav-item active">
                    <i class="fas fa-users"></i>
                    <a href="admin_users.php">Kelola Pengguna</a>
                </div>
                <div class="nav-item">
                    <i class="fas fa-cog"></i>
                    <a href="admin_settings.php">Pengaturan</a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>Kelola Pengguna</h1>
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

            <div class="tab-container">
                <div class="tabs">
                    <div class="tab <?= (!isset($_GET['tab']) || $_GET['tab'] == 'list') ? 'active' : '' ?>"
                        onclick="switchTab('list')">Daftar Pengguna</div>
                    <div class="tab <?= (isset($_GET['tab']) && $_GET['tab'] == 'add') || $editMode ? 'active' : '' ?>"
                        onclick="switchTab('add')">
                        <?= $editMode ? 'Edit Pengguna' : 'Tambah Pengguna' ?>
                    </div>
                </div>

                <!-- Tab Content: List Users -->
                <div id="tab-list"
                    class="tab-content <?= (!isset($_GET['tab']) || $_GET['tab'] == 'list') ? 'active' : '' ?>">
                    <div class="content-card">
                        <form action="" method="GET">
                            <div class="search-filters">
                                <div class="search-box">
                                    <input type="text" name="search" placeholder="Cari username atau email..."
                                        value="<?= htmlspecialchars($search ?? '') ?>">
                                    <button type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                            <input type="hidden" name="tab" value="list">
                        </form>

                        <div class="table-container">
                            <?php if (count($users) > 0): ?>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Tanggal Registrasi</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?= $user['id'] ?></td>
                                                <td><?= htmlspecialchars($user['username']) ?></td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td><?= date('d M Y H:i', strtotime($user['created_at'])) ?></td>
                                                <td>
                                                    <div class="action-btns">
                                                        <a href="?tab=add&edit=<?= $user['id'] ?>" class="btn-sm btn-edit">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <a href="admin_delete_user.php?id=<?= $user['id'] ?>"
                                                            class="btn-sm btn-delete"
                                                            onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>Tidak ada data pengguna yang ditemukan.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Add/Edit User -->
                <div id="tab-add"
                    class="tab-content <?= (isset($_GET['tab']) && $_GET['tab'] == 'add') || $editMode ? 'active' : '' ?>">
                    <div class="content-card">
                        <h2><?= $editMode ? 'Edit Pengguna' : 'Tambah Pengguna Baru' ?></h2>
                        <form method="post" action="">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username" required
                                        value="<?= $editMode ? htmlspecialchars($editUser['username'] ?? '') : '' ?>">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" required
                                        value="<?= $editMode ? htmlspecialchars($editUser['email'] ?? '') : '' ?>">
                                </div>
                            </div>
                            <div class="form-group password-field">
                                <label
                                    for="password"><?= $editMode ? 'Password (biarkan kosong jika tidak diubah)' : 'Password' ?></label>
                                <input type="password" id="password" name="password" <?= $editMode ? '' : 'required' ?>>
                                <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>

                            <input type="hidden" name="action" value="<?= $editMode ? 'edit' : 'add' ?>">
                            <?php if ($editMode): ?>
                                <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
                            <?php endif; ?>

                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> <?= $editMode ? 'Update Pengguna' : 'Tambah Pengguna' ?>
                            </button>
                            <a href="admin_users.php" class="btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function switchTab(tabId) {
            window.location.href = 'admin_users.php?tab=' + tabId;
        }

        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const toggleButton = document.querySelector('.toggle-password i');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleButton.className = 'far fa-eye-slash';
            } else {
                passwordField.type = 'password';
                toggleButton.className = 'far fa-eye';
            }
        }
    </script>
</body>

</html>