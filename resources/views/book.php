<?php
// Gunakan auth_check.php untuk memverifikasi login
require_once 'auth_check.php';

// Gunakan config.php untuk koneksi database
require_once 'config.php';

// Sudah login, ambil username dari session jika ada
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';

// Ambil data booking dari database menggunakan PDO
try {
    $sql = "SELECT * FROM bookings ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Inisialisasi array untuk menyimpan data meja yang sudah dibooking berdasarkan waktu
    $bookedTables = [];

    // Total meja yang tersedia
    $totalTables = 10;
} catch (PDOException $e) {
    die("Error fetching bookings: " . $e->getMessage());
}

// Proses pemeriksaan ketersediaan meja jika ada tanggal dan waktu yang dipilih
$selectedDate = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$selectedStartTime = isset($_GET['start_time']) ? $_GET['start_time'] : '10:00';
$selectedEndTime = isset($_GET['end_time']) ? $_GET['end_time'] : '12:00';

// Jika tanggal dan waktu sudah dipilih, cek meja yang tersedia
if (isset($_GET['check_availability'])) {
    try {
        // Query untuk mendapatkan meja yang sudah dibooking pada waktu tersebut
        $sql = "SELECT table_number FROM bookings 
                WHERE booking_date = ? 
                AND ((start_time <= ? AND end_time > ?) 
                OR (start_time < ? AND end_time >= ?) 
                OR (start_time >= ? AND start_time < ?))";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $selectedDate,
            $selectedEndTime,
            $selectedStartTime,
            $selectedEndTime,
            $selectedStartTime,
            $selectedStartTime,
            $selectedEndTime
        ]);

        // Ambil semua nomor meja yang sudah dibooking
        $bookedTableNumbers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Hitung meja yang tersedia
        $availableTables = $totalTables - count($bookedTableNumbers);
    } catch (PDOException $e) {
        $errorMessage = "Error checking availability: " . $e->getMessage();
    }
} else {
    // Default jika belum ada pemilihan waktu
    $bookedTableNumbers = [];
    $availableTables = $totalTables;
}

// Proses booking jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $message = $_POST['message'];
        $table_number = isset($_POST['table_number']) ? $_POST['table_number'] : null;
        $booking_date = $_POST['booking_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        // Validasi bahwa meja masih tersedia
        $sql = "SELECT COUNT(*) FROM bookings 
                WHERE booking_date = ? AND table_number = ? 
                AND ((start_time <= ? AND end_time > ?) 
                OR (start_time < ? AND end_time >= ?) 
                OR (start_time >= ? AND start_time < ?))";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $booking_date,
            $table_number,
            $end_time,
            $start_time,
            $end_time,
            $start_time,
            $start_time,
            $end_time
        ]);

        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $errorMessage = "Maaf, meja tersebut sudah dibooking pada waktu yang Anda pilih.";
        } else {
            // Jika meja masih tersedia, lakukan booking
            $sql = "INSERT INTO bookings (name, email, phone, message, table_number, booking_date, start_time, end_time) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $email, $phone, $message, $table_number, $booking_date, $start_time, $end_time]);

            // Refresh halaman setelah booking berhasil
            header("Location: book.php?success=1");
            exit;
        }
    } catch (PDOException $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// Pesan sukses jika ada
$successMessage = isset($_GET['success']) ? "Booking berhasil! Terima kasih." : "";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking | Kopi & Kata</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .book {
            padding-top: 100px;
            position: relative;
        }


        .book::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }

        .table-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
            margin: 40px 0;
        }

        .table-item {
            width: 130px;
            height: 130px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
            overflow: hidden;
            border: 2px solid #f4b95a;
            /* Tambahkan border langsung ke elemen */
            border-radius: 10px;
            /* Tambahkan border radius langsung */
            background-color: rgba(74, 44, 42, 0.7);
            /* Untuk available */
        }

        .booked {
            background-color: rgba(169, 68, 66, 0.7);
            border-color: #a94442;
            cursor: not-allowed;
        }

        .table-item:hover::before {
            border-radius: var(--border-radius-hover);
        }

        .available::before {
            background-color: rgba(74, 44, 42, 0.7);
            border: var(--border);
        }

        .booked::before {
            background-color: rgba(169, 68, 66, 0.7);
            border: var(--border);
            cursor: not-allowed;
        }

        .table-item:hover.available::before {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            border: var(--border-hover);
        }

        .table-item div {
            color: white;
            font-size: 1.8rem;
            text-align: center;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .table-item .table-number {
            font-family: 'Clicker Script', cursive;
            font-size: 3.5rem;
            margin-bottom: 5px;
        }

        .booking-status {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin: 30px 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            padding: 15px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .status-indicator {
            width: 25px;
            height: 25px;
            border-radius: 50%;
        }

        .status-available {
            background-color: rgba(74, 44, 42, 0.9);
            border: 2px solid #f4b95a;
        }

        .status-booked {
            background-color: rgba(169, 68, 66, 0.9);
            border: 2px solid #f4b95a;
        }

        .status-item span {
            font-size: 1.8rem;
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .success-message {
            background-color: rgba(76, 175, 80, 0.8);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
            border-left: 5px solid #2e7d32;
            font-size: 1.8rem;
        }

        .error-message {
            background-color: rgba(244, 67, 54, 0.8);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
            border-left: 5px solid #d32f2f;
            font-size: 1.8rem;
        }

        .book form,
        .time-selection-form {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .book form .box,
        .time-selection-form .box {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .book form .box::placeholder,
        .time-selection-form .box::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .book form .btn,
        .time-selection-form .btn {
            background: rgba(74, 44, 42, 0.8);
            color: white;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .book form .btn:hover,
        .time-selection-form .btn:hover {
            background: rgba(244, 185, 90, 0.8);
            color: #443;
        }

        .time-selection-form select option {
            color: #333;
            /* Warna gelap untuk teks */
            background-color: white;
            /* Latar belakang putih */
        }

        .booking-details {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 20px;
            margin-top: 50px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .booking-details h2 {
            margin-top: 0;
            color: white;
            font-family: 'Clicker Script', cursive;
            font-size: 4rem;
            text-align: center;
            margin-bottom: 20px;
        }

        .booking-list {
            max-height: 350px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .booking-list::-webkit-scrollbar {
            width: 5px;
        }

        .booking-list::-webkit-scrollbar-thumb {
            background: rgba(244, 185, 90, 0.7);
            border-radius: 10px;
        }

        .booking-item {
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }

        .booking-item p {
            margin: 8px 0;
            font-size: 1.6rem;
        }

        .booking-item strong {
            color: #f4b95a;
        }

        .booking-item:last-child {
            margin-bottom: 0;
        }

        .coffee-decoration {
            position: absolute;
            opacity: 0.1;
            z-index: -1;
        }

        .coffee-1 {
            top: 20%;
            left: 5%;
            width: 100px;
            transform: rotate(-15deg);
        }

        .coffee-2 {
            bottom: 10%;
            right: 5%;
            width: 120px;
            transform: rotate(20deg);
        }

        .delete-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 10px;
            background-color: rgba(244, 67, 54, 0.7);
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1.4rem;
            transition: all 0.3s ease;
        }

        .delete-btn:hover {
            background-color: rgba(244, 67, 54, 1);
            transform: translateY(-2px);
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        .form-group label {
            display: block;
            color: white;
            margin-bottom: 5px;
            font-size: 1.6rem;
        }

        .time-selection-form h3 {
            color: white;
            font-size: 2.2rem;
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
            font-family: 'Poppins', sans-serif;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 20px;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin-bottom: 10px;
            position: relative;
        }

        .step-circle.active {
            background: rgba(244, 185, 90, 0.8);
        }

        .step-title {
            font-size: 1.4rem;
            color: white;
            text-align: center;
        }

        .step-line {
            position: absolute;
            top: 50%;
            left: 100%;
            width: 100px;
            height: 2px;
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-50%);
        }

        .step-circle.active .step-line {
            background: rgba(244, 185, 90, 0.8);
        }

        .hidden {
            display: none;
        }

        .section-title {
            color: white;
            font-size: 2.5rem;
            margin-top: 40px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>

<body>

    <header class="header">
        <div id="menu-btn" class="fas fa-bars"></div>
        <a href="#" class="logo">Kopi & Kata</a>
        <nav class="navbar">
            <a href="index.php">home</a>
            <a href="index.php#about">about</a>
            <a href="index.php#menu">menu</a>
            <a href="index.php#review">review</a>
            <a href="book.php">book</a>
        </nav>
        <div class="user-info">
            <a href="#" class="btn">Hi, <?= htmlspecialchars($username) ?></a>
            <a href="logout.php" class="btn">logout</a>
        </div>
    </header>

    <section class="book" id="book">
        <h1 class="heading">booking <span>reserve a table</span></h1>

        <!-- Dekorasi coffee -->
        <img src="src/images/menu-1.png" alt="" class="coffee-decoration coffee-1">
        <img src="src/images/menu-3.png" alt="" class="coffee-decoration coffee-2">

        <?php if (!empty($successMessage)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?= $successMessage ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?= $errorMessage ?>
            </div>
        <?php endif; ?>

        <!-- Indikator Step -->
        <div class="step-indicator">
            <div class="step">
                <div class="step-circle active" id="step1">
                    1
                    <div class="step-line"></div>
                </div>
                <div class="step-title">Pilih Tanggal & Waktu</div>
            </div>
            <div class="step">
                <div class="step-circle" id="step2">
                    2
                    <div class="step-line"></div>
                </div>
                <div class="step-title">Pilih Meja</div>
            </div>
            <div class="step">
                <div class="step-circle" id="step3">
                    3
                </div>
                <div class="step-title">Isi Informasi</div>
            </div>
        </div>

        <!-- Step 1: Form Pemilihan Waktu -->
        <div id="timeSelectionForm" class="time-selection-form">
            <h3>Pilih Tanggal dan Waktu Kunjungan Anda</h3>
            <form action="book.php" method="GET">
                <div class="form-row">
                    <div class="form-group">
                        <label for="date"><i class="fas fa-calendar-alt"></i> Tanggal</label>
                        <input type="date" id="date" name="date" class="box" value="<?= $selectedDate ?>"
                            min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="start_time"><i class="fas fa-clock"></i> Waktu Mulai</label>
                        <select id="start_time" name="start_time" class="box" required>
                            <option value="10:00" <?= $selectedStartTime == '10:00' ? 'selected' : '' ?>>10:00</option>
                            <option value="12:00" <?= $selectedStartTime == '12:00' ? 'selected' : '' ?>>12:00</option>
                            <option value="14:00" <?= $selectedStartTime == '14:00' ? 'selected' : '' ?>>14:00</option>
                            <option value="16:00" <?= $selectedStartTime == '16:00' ? 'selected' : '' ?>>16:00</option>
                            <option value="18:00" <?= $selectedStartTime == '18:00' ? 'selected' : '' ?>>18:00</option>
                            <option value="20:00" <?= $selectedStartTime == '20:00' ? 'selected' : '' ?>>20:00</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="end_time"><i class="fas fa-clock"></i> Waktu Selesai</label>
                        <select id="end_time" name="end_time" class="box" required>
                            <option value="12:00" <?= $selectedEndTime == '12:00' ? 'selected' : '' ?>>12:00</option>
                            <option value="14:00" <?= $selectedEndTime == '14:00' ? 'selected' : '' ?>>14:00</option>
                            <option value="16:00" <?= $selectedEndTime == '16:00' ? 'selected' : '' ?>>16:00</option>
                            <option value="18:00" <?= $selectedEndTime == '18:00' ? 'selected' : '' ?>>18:00</option>
                            <option value="20:00" <?= $selectedEndTime == '20:00' ? 'selected' : '' ?>>20:00</option>
                            <option value="22:00" <?= $selectedEndTime == '22:00' ? 'selected' : '' ?>>22:00</option>
                        </select>
                    </div>
                </div>
                <input type="hidden" name="check_availability" value="1">
                <button type="submit" class="btn">Cek Ketersediaan Meja</button>
            </form>
        </div>

        <?php if (isset($_GET['check_availability'])): ?>
            <!-- Step 2: Visualisasi Meja -->
            <div id="tableSelectionSection">
                <h3 class="section-title">Pilih Meja yang Tersedia</h3>

                <!-- Status Meja -->
                <div class="booking-status">
                    <div class="status-item">
                        <div class="status-indicator status-available"></div>
                        <span>Available (<?= $availableTables ?>)</span>
                    </div>
                    <div class="status-item">
                        <div class="status-indicator status-booked"></div>
                        <span>Booked (<?= count($bookedTableNumbers) ?>)</span>
                    </div>
                </div>

                <!-- Visualisasi Meja -->
                <div class="table-container">
                    <?php for ($i = 1; $i <= $totalTables; $i++): ?>
                        <?php
                        $isBooked = in_array($i, $bookedTableNumbers);
                        $tableClass = $isBooked ? 'booked' : 'available';
                        ?>
                        <div class="table-item <?= $tableClass ?>" data-table="<?= $i ?>">
                            <div class="table-number"><?= $i ?></div>
                            <div><?= $isBooked ? 'Booked' : 'Available' ?></div>
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- Step 3: Form Booking -->
                <div id="bookingForm" class="hidden">
                    <h3 class="section-title">Isi Informasi Booking</h3>
                    <form action="book.php" method="POST">
                        <input type="text" name="name" placeholder="Your Name" class="box" required>
                        <input type="email" name="email" placeholder="Your Email" class="box" required>
                        <input type="text" name="phone" placeholder="Phone Number" class="box">
                        <textarea name="message" placeholder="Special requests or message" class="box" cols="30"
                            rows="10"></textarea>

                        <!-- Hidden fields untuk menyimpan informasi tanggal dan waktu -->
                        <input type="hidden" name="booking_date" value="<?= $selectedDate ?>">
                        <input type="hidden" name="start_time" value="<?= $selectedStartTime ?>">
                        <input type="hidden" name="end_time" value="<?= $selectedEndTime ?>">
                        <input type="hidden" name="table_number" id="selected_table">

                        <input type="submit" id="submitBtn" value="Book Your Table Now" class="btn">
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Daftar Booking Terbaru -->
        <div class="booking-details">
            <h2>Recent Bookings</h2>
            <div class="booking-list">
                <?php if (empty($bookings)): ?>
                    <p style="color: white; text-align: center; font-size: 1.8rem;">No bookings yet. Be the first to book a
                        table!</p>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-item">
                            <p><strong><i class="fas fa-user"></i> Name:</strong> <?= htmlspecialchars($booking['name']) ?></p>
                            <p><strong><i class="fas fa-envelope"></i> Email:</strong>
                                <?= htmlspecialchars($booking['email']) ?></p>
                            <p><strong><i class="fas fa-phone"></i> Phone:</strong> <?= htmlspecialchars($booking['phone']) ?>
                            </p>
                            <?php if (isset($booking['booking_date'])): ?>
                                <p><strong><i class="fas fa-calendar-day"></i> Date:</strong>
                                    <?= date('d M Y', strtotime($booking['booking_date'])) ?></p>
                            <?php endif; ?>
                            <?php if (isset($booking['start_time']) && isset($booking['end_time'])): ?>
                                <p><strong><i class="fas fa-clock"></i> Time:</strong>
                                    <?= date('H:i', strtotime($booking['start_time'])) ?> -
                                    <?= date('H:i', strtotime($booking['end_time'])) ?>
                                </p>
                            <?php endif; ?>
                            <?php if (isset($booking['table_number'])): ?>
                                <p><strong><i class="fas fa-chair"></i> Table:</strong>
                                    #<?= htmlspecialchars($booking['table_number']) ?></p>
                            <?php endif; ?>
                            <p><strong><i class="fas fa-calendar-alt"></i> Booked on:</strong>
                                <?= date('d M Y H:i', strtotime($booking['created_at'])) ?></p>
                            <?php if (!empty($booking['message'])): ?>
                                <p><strong><i class="fas fa-comment"></i> Message:</strong>
                                    <?= htmlspecialchars($booking['message']) ?></p>
                            <?php endif; ?>

                            <!-- Tombol Delete -->
                            <a href="delete_booking.php?id=<?= $booking['id'] ?>" class="delete-btn"
                                onclick="return confirm('Apakah Anda yakin ingin menghapus booking ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script>
        // Update step indicator
        function updateSteps() {
            if (window.location.href.includes('check_availability')) {
                document.getElementById('step1').classList.add('active');
                document.getElementById('step2').classList.add('active');

                // Jika ada meja yang dipilih, aktifkan step 3
                if (document.getElementById('selected_table').value) {
                    document.getElementById('step3').classList.add('active');
                }
            }
        }

        // Script untuk validasi waktu
        document.getElementById('start_time').addEventListener('change', function () {
            const startTime = this.value;
            const endTimeSelect = document.getElementById('end_time');

            // Set waktu selesai minimal 2 jam setelah waktu mulai
            const startHour = parseInt(startTime.split(':')[0]);
            let minEndHour = startHour + 2;

            // Hilangkan opsi yang tidak valid
            Array.from(endTimeSelect.options).forEach(option => {
                const endHour = parseInt(option.value.split(':')[0]);
                if (endHour <= startHour) {
                    option.disabled = true;
                } else {
                    option.disabled = false;
                }
            });

            // Pilih opsi terdekat yang valid
            for (let i = 0; i < endTimeSelect.options.length; i++) {
                const endHour = parseInt(endTimeSelect.options[i].value.split(':')[0]);
                if (endHour >= minEndHour) {
                    endTimeSelect.selectedIndex = i;
                    break;
                }
            }
        });

        // Script untuk menangani klik pada meja
        document.addEventListener('DOMContentLoaded', function () {
            <?php if (isset($_GET['check_availability'])): ?>
                // Aktifkan langkah 2 secara otomatis
                document.getElementById('step2').classList.add('active');

                // Tambahkan event listener ke meja yang tersedia
                document.querySelectorAll('.table-item.available').forEach(table => {
                    table.addEventListener('click', function () {
                        const tableNumber = this.getAttribute('data-table');

                        // Highlight meja yang dipilih
                        document.querySelectorAll('.table-item.available').forEach(t => {
                            t.style.transform = 'scale(1)';
                            t.style.boxShadow = 'none';
                        });

                        this.style.transform = 'scale(1.1)';
                        this.style.boxShadow = '0 0 20px rgba(244, 185, 90, 0.7)';

                        // Aktifkan langkah 3
                        document.getElementById('step3').classList.add('active');

                        // Tampilkan form booking
                        document.getElementById('bookingForm').classList.remove('hidden');

                        // Scroll ke form
                        document.getElementById('bookingForm').scrollIntoView({ behavior: 'smooth' });

                        // Set nomor meja pada form
                        document.getElementById('selected_table').value = tableNumber;

                        // Update teks tombol submit
                        document.getElementById('submitBtn').value = `Book Table ${tableNumber} on ${document.getElementById('date').value}`;
                    });
                });
            <?php endif; ?>

            // Validasi form sebelum submit
            const bookingForm = document.querySelector('form[action="book.php"][method="POST"]');
            if (bookingForm) {
                bookingForm.addEventListener('submit', function (e) {
                    const tableNumber = document.getElementById('selected_table').value;
                    if (!tableNumber) {
                        e.preventDefault();
                        alert('Silakan pilih meja terlebih dahulu!');
                    }
                });
            }
        });
    </script>

</body>

</html>