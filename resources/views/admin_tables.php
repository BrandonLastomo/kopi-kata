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

// Proses form untuk menambah/mengedit meja
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        try {
            // Tambah meja baru
            if ($_POST['action'] == 'add') {
                $table_number = $_POST['table_number'];
                $capacity = $_POST['capacity'];
                $location = $_POST['location'];
                $status = $_POST['status'];
                $description = $_POST['description'];

                // Cek apakah nomor meja sudah ada
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM tables WHERE table_number = ?");
                $stmt->execute([$table_number]);
                if ($stmt->fetchColumn() > 0) {
                    $error = "Nomor meja $table_number sudah ada.";
                } else {
                    // Tambahkan meja baru
                    $stmt = $pdo->prepare("INSERT INTO tables (table_number, capacity, location, status, description) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$table_number, $capacity, $location, $status, $description]);
                    $success = "Meja baru berhasil ditambahkan";
                }
            }
            // Update meja
            elseif ($_POST['action'] == 'edit') {
                $id = $_POST['id'];
                $table_number = $_POST['table_number'];
                $capacity = $_POST['capacity'];
                $location = $_POST['location'];
                $status = $_POST['status'];
                $description = $_POST['description'];

                // Cek apakah nomor meja sudah ada (selain meja ini)
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM tables WHERE table_number = ? AND id != ?");
                $stmt->execute([$table_number, $id]);
                if ($stmt->fetchColumn() > 0) {
                    $error = "Nomor meja $table_number sudah ada.";
                } else {
                    // Update meja
                    $stmt = $pdo->prepare("UPDATE tables SET table_number = ?, capacity = ?, location = ?, status = ?, description = ? WHERE id = ?");
                    $stmt->execute([$table_number, $capacity, $location, $status, $description, $id]);
                    $success = "Data meja berhasil diperbarui";
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Ambil daftar meja
try {
    // Check if table 'tables' exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'tables'");
    $tableExists = $stmt->rowCount() > 0;

    if (!$tableExists) {
        // Create tables table if it doesn't exist
        $sql = "CREATE TABLE tables (
            id INT AUTO_INCREMENT PRIMARY KEY,
            table_number INT NOT NULL UNIQUE,
            capacity INT DEFAULT 4,
            location VARCHAR(50) DEFAULT 'Main Area',
            status ENUM('available', 'reserved', 'maintenance') DEFAULT 'available',
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);

        // Insert default tables (1-10)
        for ($i = 1; $i <= 10; $i++) {
            $stmt = $pdo->prepare("INSERT INTO tables (table_number, capacity) VALUES (?, 4)");
            $stmt->execute([$i]);
        }
        $success = "Tabel meja berhasil dibuat dan diisi dengan data default";
    }

    // Get all tables
    $stmt = $pdo->query("SELECT * FROM tables ORDER BY table_number");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get table booking stats
    foreach ($tables as &$table) {
        // Total bookings for this table
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE table_number = ?");
        $stmt->execute([$table['table_number']]);
        $table['total_bookings'] = $stmt->fetchColumn();

        // Upcoming bookings for this table
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE table_number = ? AND booking_date >= CURDATE()");
        $stmt->execute([$table['table_number']]);
        $table['upcoming_bookings'] = $stmt->fetchColumn();

        // Check today's booking for this table
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE table_number = ? AND booking_date = CURDATE() ORDER BY start_time LIMIT 1");
        $stmt->execute([$table['table_number']]);
        $table['today_booking'] = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Check upcoming timeslots for each table
    $selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $timeslots = [
        '10:00-12:00',
        '12:00-14:00',
        '14:00-16:00',
        '16:00-18:00',
        '18:00-20:00',
        '20:00-22:00'
    ];

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Mode edit jika ada parameter GET id
$editMode = false;
$editTable = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM tables WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $editTable = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($editTable) {
            $editMode = true;
        }
    } catch (PDOException $e) {
        $error = "Error retrieving table data: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Meja | Admin Panel</title>
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

        .form-group textarea {
            height: 100px;
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

        /* Table cards grid */
        .table-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .table-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }

        .table-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .table-card-header {
            background: var(--primary);
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-card-header h3 {
            margin: 0;
            font-size: 1.2rem;
        }

        .table-status {
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .status-available {
            background-color: #4CAF50;
            color: white;
        }

        .status-reserved {
            background-color: #FFC107;
            color: #333;
        }

        .status-maintenance {
            background-color: #F44336;
            color: white;
        }

        .table-card-body {
            padding: 15px;
        }

        .table-info {
            margin-bottom: 15px;
        }

        .table-info p {
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
        }

        .table-info strong {
            color: #555;
        }

        .table-booking-info {
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }

        .table-booking-info p {
            margin: 5px 0;
            font-size: 0.9rem;
        }

        .table-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.9rem;
            flex: 1;
            text-align: center;
        }

        .btn-edit {
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn-edit:hover {
            background-color: #0069d9;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        .date-filter {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .date-filter label {
            font-weight: 500;
        }

        .date-filter input {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .date-filter button {
            padding: 8px 15px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .table-availability {
            margin-top: 15px;
        }

        .time-slots {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 10px;
        }

        .time-slot {
            padding: 5px;
            font-size: 0.8rem;
            border-radius: 3px;
            text-align: center;
            width: 75px;
        }

        .slot-available {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .slot-booked {
            background-color: #ffebee;
            color: #d32f2f;
        }

        .header-actions {
            display: flex;
            align-items: center;
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
                <div class="nav-item active">
                    <i class="fas fa-chair"></i>
                    <a href="admin_tables.php">Kelola Meja</a>
                </div>
                <div class="nav-item">
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
                <h1>Kelola Meja</h1>
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
                        onclick="switchTab('list')">Daftar Meja</div>
                    <div class="tab <?= (isset($_GET['tab']) && $_GET['tab'] == 'add') || $editMode ? 'active' : '' ?>"
                        onclick="switchTab('add')">
                        <?= $editMode ? 'Edit Meja' : 'Tambah Meja Baru' ?>
                    </div>
                    <div class="tab <?= (isset($_GET['tab']) && $_GET['tab'] == 'availability') ? 'active' : '' ?>"
                        onclick="switchTab('availability')">Ketersediaan Meja</div>
                </div>

                <!-- Tab Content: List Tables -->
                <div id="tab-list"
                    class="tab-content <?= (!isset($_GET['tab']) || $_GET['tab'] == 'list') ? 'active' : '' ?>">
                    <div class="table-cards">
                        <?php foreach ($tables as $table): ?>
                            <div class="table-card">
                                <div class="table-card-header">
                                    <h3>Meja <?= $table['table_number'] ?></h3>
                                    <span class="table-status status-<?= $table['status'] ?>">
                                        <?php
                                        if ($table['status'] == 'available')
                                            echo 'Tersedia';
                                        else if ($table['status'] == 'reserved')
                                            echo 'Dipesan';
                                        else
                                            echo 'Pemeliharaan';
                                        ?>
                                    </span>
                                </div>
                                <div class="table-card-body">
                                    <div class="table-info">
                                        <p>
                                            <strong>Kapasitas:</strong>
                                            <span><?= $table['capacity'] ?> orang</span>
                                        </p>
                                        <p>
                                            <strong>Lokasi:</strong>
                                            <span><?= htmlspecialchars($table['location']) ?></span>
                                        </p>
                                        <p>
                                            <strong>Total Bookings:</strong>
                                            <span><?= $table['total_bookings'] ?></span>
                                        </p>
                                        <p>
                                            <strong>Booking Mendatang:</strong>
                                            <span><?= $table['upcoming_bookings'] ?></span>
                                        </p>
                                    </div>

                                    <?php if ($table['today_booking']): ?>
                                        <div class="table-booking-info">
                                            <strong>Booking Hari Ini:</strong>
                                            <p><?= htmlspecialchars($table['today_booking']['name']) ?></p>
                                            <p>
                                                <?= date('H:i', strtotime($table['today_booking']['start_time'])) ?> -
                                                <?= date('H:i', strtotime($table['today_booking']['end_time'])) ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($table['description'])): ?>
                                        <div style="margin-top: 15px;">
                                            <strong>Deskripsi:</strong>
                                            <p><?= htmlspecialchars($table['description']) ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <div class="table-actions">
                                        <a href="?tab=add&edit=<?= $table['id'] ?>" class="btn-sm btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="admin_delete_table.php?id=<?= $table['id'] ?>" class="btn-sm btn-delete"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus meja ini? Semua booking terkait akan dihapus juga.')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tab Content: Add/Edit Table -->
                <div id="tab-add"
                    class="tab-content <?= (isset($_GET['tab']) && $_GET['tab'] == 'add') || $editMode ? 'active' : '' ?>">
                    <div class="content-card">
                        <h2><?= $editMode ? 'Edit Meja #' . $editTable['table_number'] : 'Tambah Meja Baru' ?></h2>
                        <form method="post" action="">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="table_number">Nomor Meja</label>
                                    <input type="number" id="table_number" name="table_number" min="1" required
                                        value="<?= $editMode ? $editTable['table_number'] : '' ?>">
                                </div>
                                <div class="form-group">
                                    <label for="capacity">Kapasitas</label>
                                    <input type="number" id="capacity" name="capacity" min="1" required
                                        value="<?= $editMode ? $editTable['capacity'] : '4' ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="location">Lokasi</label>
                                    <select id="location" name="location">
                                        <option value="Main Area" <?= $editMode && $editTable['location'] == 'Main Area' ? 'selected' : '' ?>>Area Utama</option>
                                        <option value="Window" <?= $editMode && $editTable['location'] == 'Window' ? 'selected' : '' ?>>Jendela</option>
                                        <option value="Outdoor" <?= $editMode && $editTable['location'] == 'Outdoor' ? 'selected' : '' ?>>Luar Ruangan</option>
                                        <option value="Private Room" <?= $editMode && $editTable['location'] == 'Private Room' ? 'selected' : '' ?>>Ruang Privat</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select id="status" name="status">
                                        <option value="available" <?= $editMode && $editTable['status'] == 'available' ? 'selected' : '' ?>>Tersedia</option>
                                        <option value="reserved" <?= $editMode && $editTable['status'] == 'reserved' ? 'selected' : '' ?>>Dipesan</option>
                                        <option value="maintenance" <?= $editMode && $editTable['status'] == 'maintenance' ? 'selected' : '' ?>>Pemeliharaan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description">Deskripsi</label>
                                <textarea id="description"
                                    name="description"><?= $editMode ? htmlspecialchars($editTable['description']) : '' ?></textarea>
                            </div>

                            <input type="hidden" name="action" value="<?= $editMode ? 'edit' : 'add' ?>">
                            <?php if ($editMode): ?>
                                <input type="hidden" name="id" value="<?= $editTable['id'] ?>">
                            <?php endif; ?>

                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> <?= $editMode ? 'Update Meja' : 'Tambah Meja' ?>
                            </button>
                            <a href="admin_tables.php" class="btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </form>
                    </div>
                </div>

                <!-- Tab Content: Table Availability -->
                <div id="tab-availability"
                    class="tab-content <?= (isset($_GET['tab']) && $_GET['tab'] == 'availability') ? 'active' : '' ?>">
                    <div class="content-card">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h2>Ketersediaan Meja</h2>
                            <form class="date-filter" method="GET">
                                <input type="hidden" name="tab" value="availability">
                                <label for="availability_date">Tanggal:</label>
                                <input type="date" id="availability_date" name="date"
                                    value="<?= htmlspecialchars($selectedDate) ?>">
                                <button type="submit"><i class="fas fa-filter"></i> Filter</button>
                            </form>
                        </div>

                        <div>
                            <?php
                            foreach ($timeslots as $slot):
                                list($startTime, $endTime) = explode('-', $slot);
                                ?>
                                <div style="margin-bottom: 30px;">
                                    <h3><?= $slot ?></h3>
                                    <div class="table-cards"
                                        style="grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));">
                                        <?php
                                        foreach ($tables as $table):
                                            $isBooked = false;
                                            $bookedBy = '';

                                            try {
                                                // Cek apakah meja dibooking pada timeslot ini
                                                $stmt = $pdo->prepare("
                                                    SELECT * FROM bookings 
                                                    WHERE table_number = ? 
                                                    AND booking_date = ? 
                                                    AND ((start_time <= ? AND end_time > ?) 
                                                        OR (start_time < ? AND end_time >= ?) 
                                                        OR (start_time >= ? AND start_time < ?))
                                                ");
                                                $stmt->execute([
                                                    $table['table_number'],
                                                    $selectedDate,
                                                    $startTime,
                                                    $startTime,
                                                    $endTime,
                                                    $startTime,
                                                    $startTime,
                                                    $endTime
                                                ]);
                                                $bookingData = $stmt->fetch(PDO::FETCH_ASSOC);

                                                if ($bookingData) {
                                                    $isBooked = true;
                                                    $bookedBy = $bookingData['name'];
                                                }
                                            } catch (PDOException $e) {
                                                // Handle error silently
                                            }

                                            $statusClass = $isBooked ? 'status-reserved' : ($table['status'] == 'available' ? 'status-available' : 'status-maintenance');
                                            $statusText = $isBooked ? 'Dipesan' : ($table['status'] == 'available' ? 'Tersedia' : 'Pemeliharaan');
                                            ?>
                                            <div class="table-card" style="margin-bottom: 10px;">
                                                <div class="table-card-header">
                                                    <h3 style="font-size: 1rem;">Meja <?= $table['table_number'] ?></h3>
                                                    <span class="table-status <?= $statusClass ?>"><?= $statusText ?></span>
                                                </div>
                                                <?php if ($isBooked): ?>
                                                    <div class="table-card-body" style="padding: 10px;">
                                                        <p style="margin: 0; font-size: 0.9rem;">
                                                            <strong>Dipesan oleh:</strong><br>
                                                            <?= htmlspecialchars($bookedBy) ?>
                                                        </p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Delete Table Handler -->
    <script>
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus meja ini? Semua booking terkait akan dihapus juga.')) {
                window.location.href = 'admin_delete_table.php?id=' + id;
            }
        }

        function switchTab(tabId) {
            window.location.href = 'admin_tables.php?tab=' + tabId;
        }
    </script>
</body>

</html>