<?php
require_once 'admin_auth_check.php';
require_once 'config.php';

$error = '';
$success = '';
$booking = null;

// Cek apakah ada ID yang diterima
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$booking_id = $_GET['id'];

// Ambil data booking berdasarkan ID
try {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        header('Location: admin_dashboard.php');
        exit;
    }
} catch (PDOException $e) {
    $error = 'Error: ' . $e->getMessage();
}

// Proses form update jika disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $table_number = $_POST['table_number'];
        $booking_date = $_POST['booking_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $message = $_POST['message'];

        // Validasi bahwa meja masih tersedia (skip jika meja tidak berubah)
        if ($table_number != $booking['table_number']) {
            $sql = "SELECT COUNT(*) FROM bookings 
                    WHERE booking_date = ? AND table_number = ? AND id != ?
                    AND ((start_time <= ? AND end_time > ?) 
                    OR (start_time < ? AND end_time >= ?) 
                    OR (start_time >= ? AND start_time < ?))";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $booking_date,
                $table_number,
                $booking_id,
                $end_time,
                $start_time,
                $end_time,
                $start_time,
                $start_time,
                $end_time
            ]);

            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $error = "Maaf, meja tersebut sudah dibooking pada waktu yang dipilih.";
            }
        }

        // Jika tidak ada error, update booking
        if (empty($error)) {
            $sql = "UPDATE bookings SET name = ?, email = ?, phone = ?, table_number = ?, 
                    booking_date = ?, start_time = ?, end_time = ?, message = ? WHERE id = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $name,
                $email,
                $phone,
                $table_number,
                $booking_date,
                $start_time,
                $end_time,
                $message,
                $booking_id
            ]);

            $success = "Booking berhasil diperbarui!";

            // Refresh data booking
            $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
            $stmt->execute([$booking_id]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        $error = "Error updating booking: " . $e->getMessage();
    }
}

// Total meja yang tersedia
$totalTables = 10;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary: #4a2c2a;
            --secondary: #f4b95a;
        }

        body {
            background: #f5f5f5 !important;
            font-family: 'Poppins', sans-serif !important;
            margin: 0 !important;
            padding: 0 !important;
            position: relative !important;
        }

        /* Reset background image dari style.css */
        body::before {
            display: none !important;
        }

        .admin-container {
            display: flex !important;
            min-height: 100vh !important;
            position: relative !important;
            z-index: 1 !important;
        }

        .sidebar {
            width: 250px !important;
            background: var(--primary) !important;
            color: white !important;
            padding: 20px 0 !important;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1) !important;
            position: fixed !important;
            height: 100vh !important;
            overflow-y: auto !important;
            z-index: 10 !important;
        }

        .sidebar-header {
            margin-top: 70px !important;
            text-align: center !important;
            padding: 0 20px 20px !important;
            margin-bottom: 20px !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        .sidebar-header h2 {
            font-family: 'Clicker Script', cursive !important;
            font-size: 2.5rem !important;
            margin: 0 !important;
            color: var(--secondary) !important;
        }

        .sidebar-nav {
            padding: 0 10px !important;
        }

        .nav-item {
            padding: 10px 20px !important;
            margin-bottom: 5px !important;
            border-radius: 5px !important;
            display: flex !important;
            align-items: center !important;
            transition: all 0.3s !important;
            cursor: pointer !important;
        }

        .nav-item i {
            margin-right: 10px !important;
            color: var(--secondary) !important;
        }

        .nav-item a {
            color: white !important;
            text-decoration: none !important;
            font-size: 1rem !important;
            width: 100% !important;
        }

        .nav-item:hover,
        .nav-item.active {
            background: rgba(244, 185, 90, 0.2) !important;
        }

        .main-content {
            flex: 1 !important;
            margin-left: 250px !important;
            padding: 20px !important;
            background: #f9f9f9 !important;
            min-height: 100vh !important;
        }

        .header {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding: 0 0 20px !important;
            border-bottom: 1px solid #eee !important;
            margin-bottom: 20px !important;
        }

        .header h1 {
            color: var(--primary) !important;
            margin: 0 !important;
            font-size: 1.8rem !important;
        }

        /* Style untuk content-card */
        .content-card {
            background: white !important;
            border-radius: 10px !important;
            padding: 20px !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05) !important;
            margin-bottom: 20px !important;
        }

        /* Override form container styles */
        .form-container {
            background: white !important;
            position: static !important;
            top: auto !important;
            left: auto !important;
            width: auto !important;
            max-width: 800px !important;
            margin: 0 auto !important;
            padding: 20px !important;
            border-radius: 10px !important;
            box-shadow: none !important;
            transform: none !important;
            z-index: 1 !important;
        }

        .form-group {
            margin-bottom: 20px !important;
        }

        .form-group label {
            display: block !important;
            margin-bottom: 5px !important;
            font-weight: 500 !important;
            color: #333 !important;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100% !important;
            padding: 10px !important;
            border: 1px solid #ddd !important;
            border-radius: 5px !important;
            font-size: 1rem !important;
            color: #333 !important;
            background-color: white !important;
        }

        .form-row {
            display: flex !important;
            gap: 20px !important;
        }

        .form-row .form-group {
            flex: 1 !important;
        }

        .submit-btn {
            background-color: var(--primary) !important;
            color: white !important;
            border: none !important;
            padding: 12px 20px !important;
            border-radius: 5px !important;
            cursor: pointer !important;
            font-size: 1rem !important;
            transition: all 0.3s !important;
            display: block !important;
        }

        .submit-btn:hover {
            background-color: #3a2320 !important;
        }

        .alert {
            padding: 15px !important;
            margin-bottom: 20px !important;
            border-radius: 5px !important;
        }

        .alert-success {
            background-color: #d4edda !important;
            color: #155724 !important;
        }

        .alert-danger {
            background-color: #f8d7da !important;
            color: #721c24 !important;
        }

        /* Admin info section */
        .admin-info {
            display: flex !important;
            align-items: center !important;
        }

        .admin-info .btn {
            background-color: #6c757d !important;
            color: white !important;
            border: none !important;
            padding: 8px 15px !important;
            border-radius: 5px !important;
            text-decoration: none !important;
            font-size: 0.9rem !important;
        }

        .admin-info .btn:hover {
            background-color: #5a6268 !important;
        }
    </style>
</head>

<body>
    <div class="admin-container">
        <!-- Sidebar (same as in admin_dashboard.php) -->
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
                <div class="nav-item">
                    <i class="fas fa-cog"></i>
                    <a href="admin_settings.php">Pengaturan</a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>Edit Booking</h1>
                <div class="admin-info">
                    <a href="admin_dashboard.php" class="btn" style="background-color: #6c757d;">Kembali</a>
                </div>
            </div>

            <div class="content-card">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?= $success ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <div class="form-container">
                    <form action="" method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Nama</label>
                                <input type="text" id="name" name="name"
                                    value="<?= htmlspecialchars($booking['name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email"
                                    value="<?= htmlspecialchars($booking['email']) ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Nomor Telepon</label>
                                <input type="text" id="phone" name="phone"
                                    value="<?= htmlspecialchars($booking['phone']) ?>">
                            </div>
                            <div class="form-group">
                                <label for="table_number">Nomor Meja</label>
                                <select id="table_number" name="table_number" required>
                                    <?php for ($i = 1; $i <= $totalTables; $i++): ?>
                                        <option value="<?= $i ?>" <?= $booking['table_number'] == $i ? 'selected' : '' ?>>
                                            Meja <?= $i ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="booking_date">Tanggal Booking</label>
                                <input type="date" id="booking_date" name="booking_date"
                                    value="<?= $booking['booking_date'] ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="start_time">Waktu Mulai</label>
                                <select id="start_time" name="start_time" required>
                                    <option value="10:00" <?= $booking['start_time'] == '10:00:00' ? 'selected' : '' ?>>
                                        10:00</option>
                                    <option value="12:00" <?= $booking['start_time'] == '12:00:00' ? 'selected' : '' ?>>
                                        12:00</option>
                                    <option value="14:00" <?= $booking['start_time'] == '14:00:00' ? 'selected' : '' ?>>
                                        14:00</option>
                                    <option value="16:00" <?= $booking['start_time'] == '16:00:00' ? 'selected' : '' ?>>
                                        16:00</option>
                                    <option value="18:00" <?= $booking['start_time'] == '18:00:00' ? 'selected' : '' ?>>
                                        18:00</option>
                                    <option value="20:00" <?= $booking['start_time'] == '20:00:00' ? 'selected' : '' ?>>
                                        20:00</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="end_time">Waktu Selesai</label>
                                <select id="end_time" name="end_time" required>
                                    <option value="12:00" <?= $booking['end_time'] == '12:00:00' ? 'selected' : '' ?>>12:00
                                    </option>
                                    <option value="14:00" <?= $booking['end_time'] == '14:00:00' ? 'selected' : '' ?>>14:00
                                    </option>
                                    <option value="16:00" <?= $booking['end_time'] == '16:00:00' ? 'selected' : '' ?>>16:00
                                    </option>
                                    <option value="18:00" <?= $booking['end_time'] == '18:00:00' ? 'selected' : '' ?>>18:00
                                    </option>
                                    <option value="20:00" <?= $booking['end_time'] == '20:00:00' ? 'selected' : '' ?>>20:00
                                    </option>
                                    <option value="22:00" <?= $booking['end_time'] == '22:00:00' ? 'selected' : '' ?>>22:00
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="message">Pesan/Permintaan Khusus</label>
                            <textarea id="message"
                                name="message"><?= htmlspecialchars($booking['message']) ?></textarea>
                        </div>

                        <button type="submit" class="submit-btn">Perbarui Booking</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Validasi waktu mulai dan selesai
        document.getElementById('start_time').addEventListener('change', function () {
            const startTime = this.value;
            const endTimeSelect = document.getElementById('end_time');

            // Set waktu selesai minimal 2 jam setelah waktu mulai
            const startHour = parseInt(startTime.split(':')[0]);
            let minEndHour = startHour + 2;

            // Disable opsi yang tidak valid
            Array.from(endTimeSelect.options).forEach(option => {
                const endHour = parseInt(option.value.split(':')[0]);
                option.disabled = endHour <= startHour;
            });

            // Pilih opsi terdekat yang valid jika opsi saat ini tidak valid
            if (parseInt(endTimeSelect.value.split(':')[0]) <= startHour) {
                for (let i = 0; i < endTimeSelect.options.length; i++) {
                    const endHour = parseInt(endTimeSelect.options[i].value.split(':')[0]);
                    if (endHour >= minEndHour && !endTimeSelect.options[i].disabled) {
                        endTimeSelect.selectedIndex = i;
                        break;
                    }
                }
            }
        });
    </script>
</body>

</html>