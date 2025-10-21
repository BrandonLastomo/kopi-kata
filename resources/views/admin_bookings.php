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

// Parameter filter dan pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Pagination
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Base query
$query = "SELECT * FROM bookings WHERE 1=1";
$count_query = "SELECT COUNT(*) FROM bookings WHERE 1=1";
$params = [];

// Terapkan filter pencarian
if (!empty($search)) {
    $query .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $count_query .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// Filter berdasarkan status
if ($status_filter !== 'all') {
    if ($status_filter === 'upcoming') {
        $query .= " AND booking_date > CURDATE()";
        $count_query .= " AND booking_date > CURDATE()";
    } elseif ($status_filter === 'today') {
        $query .= " AND booking_date = CURDATE()";
        $count_query .= " AND booking_date = CURDATE()";
    } elseif ($status_filter === 'past') {
        $query .= " AND booking_date < CURDATE()";
        $count_query .= " AND booking_date < CURDATE()";
    }
}

// Filter berdasarkan rentang tanggal
if (!empty($date_from)) {
    $query .= " AND booking_date >= ?";
    $count_query .= " AND booking_date >= ?";
    $params[] = $date_from;
}
if (!empty($date_to)) {
    $query .= " AND booking_date <= ?";
    $count_query .= " AND booking_date <= ?";
    $params[] = $date_to;
}

// Sorting
if ($sort === 'newest') {
    $query .= " ORDER BY booking_date DESC, start_time DESC";
} elseif ($sort === 'oldest') {
    $query .= " ORDER BY booking_date ASC, start_time ASC";
} elseif ($sort === 'name_asc') {
    $query .= " ORDER BY name ASC";
} elseif ($sort === 'name_desc') {
    $query .= " ORDER BY name DESC";
}

// Tambahkan limit untuk pagination
$query .= " LIMIT $records_per_page OFFSET $offset";

// Jalankan query untuk mengambil data
try {
    // Query untuk total records (untuk pagination)
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total_records = $stmt->fetchColumn();
    $total_pages = ceil($total_records / $records_per_page);

    // Query untuk data
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Booking | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
            margin-top: 100px;
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

        .filters-container {
            display: grid;
            grid-template-columns: minmax(200px, 300px) 1fr;
            gap: 30px;
            /* Increased from 20px to 30px */
            margin-bottom: 20px;
        }

        .filter-group {
            display: flex;
            gap: 15px;
            /* Increased from 10px to 15px */
        }

        /* Add this new style for the second filter group with date range */
        .filter-group+.filter-group {
            margin-top: 15px;
            /* Add margin between the two filter groups */
        }

        .filter-group select,
        .filter-group input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            flex-grow: 1;
            max-width: 180px;
            /* Add max-width to prevent excessive stretching */
        }

        .filter-group button {
            padding: 10px 15px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-group button:hover {
            background-color: #3a2320;
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
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #f5f5f5;
            color: #333;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status.upcoming {
            background-color: rgba(52, 168, 83, 0.1);
            color: #34a853;
        }

        .status.past {
            background-color: rgba(234, 67, 53, 0.1);
            color: #ea4335;
        }

        .status.today {
            background-color: rgba(251, 188, 5, 0.1);
            color: #fbbc05;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 0.8rem;
            cursor: pointer;
            margin-right: 5px;
            border: none;
            text-decoration: none;
            display: inline-block;
        }

        .edit-btn {
            background-color: #4285f4;
            color: white;
        }

        .delete-btn {
            background-color: #ea4335;
            color: white;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .pagination a {
            padding: 8px 12px;
            margin: 0 5px;
            border-radius: 5px;
            background-color: white;
            color: var(--primary);
            text-decoration: none;
            transition: all 0.3s;
            border: 1px solid #ddd;
        }

        .pagination a.active {
            background-color: var(--primary);
            color: white;
        }

        .pagination a:hover:not(.active) {
            background-color: #f1f1f1;
        }

        .add-btn {
            padding: 8px 15px;
            background-color: #4a2c2a;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }

        .add-btn:hover {
            background-color: #3a2320;
        }

        .export-btn {
            padding: 8px 15px;
            background-color: #34a853;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }

        .export-btn:hover {
            background-color: #2d9348;
        }

        .btn-group {
            display: flex;
            gap: 10px;
        }

        @media (max-width: 768px) {
            .filters-container {
                grid-template-columns: 1fr;
            }

            .filter-group {
                flex-direction: column;
            }
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        .search-box {
            position: relative;
            width: 85%;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            /* Lebih banyak padding di kiri untuk ikon */
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        /* Tambahkan styling ketika hover/fokus */
        .search-box input:hover,
        .search-box input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(74, 44, 42, 0.1);
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
                <div class="nav-item active">
                    <i class="fas fa-calendar-alt"></i>
                    <a href="admin_bookings.php">Kelola Booking</a>
                </div>
                <div class="nav-item">
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
                <h1>Kelola Booking</h1>
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

            <!-- Filter & Search -->
            <div class="content-card">
                <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                    <h2 style="margin: 0;">Daftar Booking</h2>
                    <div class="btn-group">
                        <a href="admin_add_booking.php" class="add-btn">
                            <i class="fas fa-plus"></i> Tambah Booking
                        </a>
                        <a href="admin_export_bookings.php" class="export-btn">
                            <i class="fas fa-file-export"></i> Export
                        </a>
                    </div>
                </div>

                <form action="" method="GET" id="filterForm">
                    <div class="filters-container">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Cari nama, email, atau no. telp..."
                                value="<?= htmlspecialchars($search) ?>">
                        </div>
                        <div class="filter-group primary-filters">
                            <select name="status">
                                <option value="all" <?= $status_filter === 'all' ? 'selected' : '' ?>>Semua Status</option>
                                <option value="upcoming" <?= $status_filter === 'upcoming' ? 'selected' : '' ?>>Mendatang
                                </option>
                                <option value="today" <?= $status_filter === 'today' ? 'selected' : '' ?>>Hari Ini</option>
                                <option value="past" <?= $status_filter === 'past' ? 'selected' : '' ?>>Lewat</option>
                            </select>
                            <select name="sort">
                                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Terbaru</option>
                                <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Terlama</option>
                                <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Nama (A-Z)</option>
                                <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Nama (Z-A)
                                </option>
                            </select>
                            <button type="submit">Terapkan</button>
                        </div>
                    </div>
                    <div class="filter-group date-filters">
                        <div class="date-range">
                            <span>Dari:</span>
                            <input type="date" name="date_from" class="datepicker"
                                value="<?= htmlspecialchars($date_from) ?>">
                            <span>Sampai:</span>
                            <input type="date" name="date_to" class="datepicker"
                                value="<?= htmlspecialchars($date_to) ?>">
                        </div>
                        <button type="button" onclick="resetFilters()">Reset Filter</button>
                    </div>
                </form>
            </div>

            <!-- Bookings Table -->
            <div class="content-card">
                <div class="table-container">
                    <?php if (count($bookings) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nama</th>
                                    <th>Email / Telepon</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Meja</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $counter = ($page - 1) * $records_per_page + 1;
                                foreach ($bookings as $booking):
                                    $bookingDate = strtotime($booking['booking_date']);
                                    $today = strtotime(date('Y-m-d'));

                                    if ($bookingDate > $today) {
                                        $status = 'upcoming';
                                        $statusText = 'Mendatang';
                                    } elseif ($bookingDate == $today) {
                                        $status = 'today';
                                        $statusText = 'Hari Ini';
                                    } else {
                                        $status = 'past';
                                        $statusText = 'Lewat';
                                    }
                                    ?>
                                    <tr>
                                        <td><?= $counter++ ?></td>
                                        <td><?= htmlspecialchars($booking['name']) ?></td>
                                        <td>
                                            <?= htmlspecialchars($booking['email']) ?><br>
                                            <small><?= htmlspecialchars($booking['phone']) ?></small>
                                        </td>
                                        <td><?= date('d M Y', strtotime($booking['booking_date'])) ?></td>
                                        <td>
                                            <?= date('H:i', strtotime($booking['start_time'])) ?> -
                                            <?= date('H:i', strtotime($booking['end_time'])) ?>
                                        </td>
                                        <td>
                                            <?= $booking['table_number'] ?>
                                        </td>
                                        <td><span class="status <?= $status ?>"><?= $statusText ?>
                                            </span></td>
                                        <td>
                                            <a href="admin_edit_booking.php?id=<?= $booking['id'] ?>"
                                                class="action-btn edit-btn">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="admin_delete_booking.php?id=<?= $booking['id'] ?>"
                                                class=" action-btn delete-btn"
                                                onclick="return confirm('Yakin ingin menghapus booking ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a
                                        href="?page=1&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&sort=<?= $sort ?>">
                                        &laquo; Pertama
                                    </a>
                                    <a
                                        href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&sort=<?= $sort ?>">
                                        &lsaquo; Prev
                                    </a>
                                <?php endif; ?>

                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);

                                for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&sort=<?= $sort ?>"
                                        class="<?= ($i == $page) ? 'active' : '' ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <a
                                        href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&sort=<?= $sort ?>">
                                        Next &rsaquo;
                                    </a>
                                    <a
                                        href="?page=<?= $total_pages ?>&search=<?= urlencode($search) ?>&status=<?= $status_filter ?>&date_from=<?= urlencode($date_from) ?>&date_to=<?= urlencode($date_to) ?>&sort=<?= $sort ?>">
                                        Terakhir &raquo;
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="far fa-calendar-times" style="font-size: 3rem; color: #ccc; margin-bottom: 10px;"></i>
                            <p>Tidak ada data booking yang ditemukan.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inisialisasi date picker
            flatpickr(".datepicker", {
                dateFormat: "Y-m-d",
                allowInput: true
            });

            // Auto-submit form saat perubahan select
            document.querySelectorAll('select[name="status"], select[name="sort"]').forEach(function (element) {
                element.addEventListener('change', function () {
                    document.getElementById('filterForm').submit();
                });
            });
        });

        // Reset form filter
        function resetFilters() {
            document.querySelector('input[name="search"]').value = '';
            document.querySelector('select[name="status"]').value = 'all';
            document.querySelector('select[name="sort"]').value = 'newest';
            document.querySelector('input[name="date_from"]').value = '';
            document.querySelector('input[name="date_to"]').value = '';
            document.getElementById('filterForm').submit();
        }
    </script>
</body>

</html>