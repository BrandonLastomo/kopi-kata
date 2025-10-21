<?php
require_once 'admin_auth_check.php';
require_once 'config.php';

$admin_name = $_SESSION['admin_name'];

// Statistik Dashboard
$stats = [];

try {
    // Total bookings
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
    $stats['total_bookings'] = $stmt->fetchColumn();

    // Bookings hari ini
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE DATE(booking_date) = CURDATE()");
    $stmt->execute();
    $stats['today_bookings'] = $stmt->fetchColumn();

    // Bookings mendatang
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE booking_date > CURDATE()");
    $stmt->execute();
    $stats['upcoming_bookings'] = $stmt->fetchColumn();

    // Ambil semua booking terurut berdasarkan tanggal (terbaru di atas)
    $stmt = $pdo->query("SELECT * FROM bookings ORDER BY booking_date DESC, start_time DESC LIMIT 10");
    $recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = 'Error: ' . $e->getMessage();
}

// Filter date
$selectedDate = isset($_GET['filter_date']) ? $_GET['filter_date'] : date('Y-m-d');

// Ambil booking berdasarkan tanggal yang dipilih
try {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_date = ? ORDER BY start_time");
    $stmt->execute([$selectedDate]);
    $filtered_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = 'Error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Kopi & Kata</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
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
            margin-top: 100px;
            padding: 20px;
            background: #f9f9f9;
            overflow-y: auto;
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
        }

        .logout-btn:hover {
            background: #d32f2f;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
        }

        .stat-icon.blue {
            background: rgba(66, 133, 244, 0.2);
            color: #4285f4;
        }

        .stat-icon.green {
            background: rgba(52, 168, 83, 0.2);
            color: #34a853;
        }

        .stat-icon.yellow {
            background: rgba(251, 188, 5, 0.2);
            color: #fbbc05;
        }

        .stat-info h3 {
            margin: 0 0 5px;
            color: #333;
            font-size: 1.8rem;
        }

        .stat-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }

        .content-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .content-header h2 {
            margin: 0;
            color: var(--primary);
            font-size: 1.5rem;
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
        }

        .edit-btn {
            background-color: #4285f4;
            color: white;
        }

        .delete-btn {
            background-color: #ea4335;
            color: white;
        }

        .filter-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filter-form input,
        .filter-form button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .filter-form button {
            background-color: var(--primary);
            color: white;
            cursor: pointer;
        }

        .table-visualization {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .table-item {
            border: 2px solid var(--primary);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            position: relative;
            transition: all 0.3s;
        }

        .table-item.booked {
            background-color: rgba(234, 67, 53, 0.1);
            border-color: #ea4335;
        }

        .table-item h3 {
            margin: 0;
            font-size: 2rem;
            color: var(--primary);
        }

        .table-item.booked h3 {
            color: #ea4335;
        }

        .table-item p {
            margin: 5px 0 0;
            font-size: 0.9rem;
            color: #666;
        }

        .table-item.booked p.booked-by {
            font-weight: 500;
            margin-top: 10px;
            color: #ea4335;
        }

        .time-slot {
            position: relative;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            margin-bottom: 5px;
        }

        .time-slot-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .time-slot-header h3 {
            margin: 0;
            font-size: 1.2rem;
            color: var(--primary);
        }

        .time-tables {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .mini-table {
            width: 60px;
            height: 60px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #e8f5e9;
            border: 1px solid #c8e6c9;
            color: #388e3c;
            font-weight: 600;
        }

        .mini-table.booked {
            background-color: #ffebee;
            border: 1px solid #ffcdd2;
            color: #d32f2f;
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
                <div class="nav-item active">
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
                <h1>Dashboard</h1>
                <div class="admin-info">
                    <span>Selamat datang, <?= htmlspecialchars($admin_name) ?></span>
                    <a href="admin_logout.php" class="logout-btn">Logout</a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['total_bookings'] ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['today_bookings'] ?></h3>
                        <p>Bookings Hari Ini</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon yellow">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['upcoming_bookings'] ?></h3>
                        <p>Bookings Mendatang</p>
                    </div>
                </div>
            </div>

            <!-- Table Visualization Based on Date -->
            <div class="content-card">
                <div class="content-header">
                    <h2>Visualisasi Meja</h2>
                    <form class="filter-form" method="GET" action="">
                        <input type="date" name="filter_date" value="<?= $selectedDate ?>" required>
                        <button type="submit">Filter</button>
                    </form>
                </div>

                <!-- Time Slots -->
                <div class="time-slots">
                    <?php
                    $timeSlots = ['10:00-12:00', '12:00-14:00', '14:00-16:00', '16:00-18:00', '18:00-20:00', '20:00-22:00'];
                    foreach ($timeSlots as $slot):
                        list($startTime, $endTime) = explode('-', $slot);
                        ?>
                        <div class="time-slot">
                            <div class="time-slot-header">
                                <h3><?= $slot ?></h3>
                            </div>
                            <div class="time-tables">
                                <?php for ($i = 1; $i <= 10; $i++):
                                    $isBooked = false;
                                    $bookedBy = '';

                                    // Cek apakah meja dibooking pada timeSlot ini
                                    foreach ($filtered_bookings as $booking) {
                                        if (
                                            $booking['table_number'] == $i &&
                                            (($booking['start_time'] <= $startTime && $booking['end_time'] > $startTime) ||
                                                ($booking['start_time'] < $endTime && $booking['end_time'] >= $endTime) ||
                                                ($booking['start_time'] >= $startTime && $booking['start_time'] < $endTime))
                                        ) {
                                            $isBooked = true;
                                            $bookedBy = $booking['name'];
                                            break;
                                        }
                                    }
                                    ?>
                                    <div class="mini-table <?= $isBooked ? 'booked' : '' ?>"
                                        title="<?= $isBooked ? 'Booked by: ' . htmlspecialchars($bookedBy) : 'Available' ?>">
                                        <?= $i ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Bookings Table -->
            <div class="content-card">
                <div class="content-header">
                    <h2>Booking Terbaru</h2>
                    <a href="admin_bookings.php" style="color: var(--primary); text-decoration: none;">Lihat Semua</a>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Meja</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_bookings)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">Tidak ada data booking</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recent_bookings as $booking):
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
                                        <td><?= htmlspecialchars($booking['name']) ?></td>
                                        <td><?= htmlspecialchars($booking['email']) ?></td>
                                        <td><?= date('d M Y', strtotime($booking['booking_date'])) ?></td>
                                        <td><?= date('H:i', strtotime($booking['start_time'])) ?> -
                                            <?= date('H:i', strtotime($booking['end_time'])) ?>
                                        </td>
                                        <td><?= $booking['table_number'] ?></td>
                                        <td><span class="status <?= $status ?>"><?= $statusText ?></span></td>
                                        <td>
                                            <a href="admin_edit_booking.php?id=<?= $booking['id'] ?>"
                                                class="action-btn edit-btn">Edit</a>
                                            <a href="admin_delete_booking.php?id=<?= $booking['id'] ?>"
                                                class="action-btn delete-btn"
                                                onclick="return confirm('Yakin ingin menghapus booking ini?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Toggle sidebar on mobile view
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.querySelector('.toggle-sidebar');
            const sidebar = document.querySelector('.sidebar');

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('active');
                });
            }
        });
    </script>
</body>

</html>