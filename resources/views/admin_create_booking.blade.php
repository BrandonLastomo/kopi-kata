<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Booking | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

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
            height: 100vh;
            position: fixed;
        }

        .sidebar-header {
            margin-top: 70px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 10px;
        }

        .sidebar-header h2 {
            font-family: 'Clicker Script', cursive;
            font-size: 2.5rem;
            color: var(--secondary);
        }

        .nav-item {
            padding: 10px 20px;
            display: flex;
            align-items: center;
        }

        .nav-item a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
        }

        .nav-item i {
            margin-right: 10px;
            color: var(--secondary);
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            background: #f9f9f9;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }

        .header h1 {
            color: var(--primary);
        }

        .form-container {
            background: white;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            gap: 20px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .submit-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .submit-btn:hover {
            background-color: #3a2320;
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
                <div class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <a href="{{ route('dashboard') }}">Dashboard</a>
                </div>
                <div class="nav-item {{ request()->routeIs('bookings.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i>
                    <a href="{{ route('bookings.index') }}">Kelola Booking</a>
                </div>
                <div class="nav-item {{ request()->routeIs('tables.*') ? 'active' : '' }}">
                    <i class="fas fa-chair"></i>
                    <a href="{{ route('tables.index') }}">Kelola Meja</a>
                </div>
                <div class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <a href="{{ route('users.index') }}">Kelola Pengguna</a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>Tambah Booking</h1>
                <div class="admin-info">
                    <a href="{{ route('bookings.index') }}" class="btn" style="background-color:#6c757d;color:white;padding:8px 15px;border-radius:5px;text-decoration:none;">Kembali</a>
                </div>
            </div>

            <div class="content-card">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="form-container">
                    <!-- ✅ FORM TAMBAH BOOKING -->
                    <form action="{{ route('bookings.store') }}" method="POST">
                        @csrf

                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Nama</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Nomor Telepon</label>
                                <input type="text" id="phone" name="phone" value="{{ old('phone') }}">
                            </div>
                            <div class="form-group">
                                <label for="table_number">Nomor Meja</label>
                                <select id="table_number" name="table_number" required>
                                    <option value="">-- Pilih Meja --</option>
                                    @for ($i = 1; $i <= $totalTables; $i++)
                                        <option value="{{ $i }}" {{ old('table_number') == $i ? 'selected' : '' }}>Meja {{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="booking_date">Tanggal Booking</label>
                                <input type="date" id="booking_date" name="booking_date" value="{{ old('booking_date') }}" required>
                            </div>
                            <div class="form-group">
                                <label for="start_time">Waktu Mulai</label>
                                <select id="start_time" name="start_time" required>
                                    <option value="">-- Pilih Waktu --</option>
                                    @foreach (['10:00', '12:00', '14:00', '16:00', '18:00', '20:00'] as $time)
                                        <option value="{{ $time }}" {{ old('start_time') == $time ? 'selected' : '' }}>{{ $time }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="end_time">Waktu Selesai</label>
                                <select id="end_time" name="end_time" required>
                                    <option value="">-- Pilih Waktu --</option>
                                    @foreach (['12:00', '14:00', '16:00', '18:00', '20:00', '22:00'] as $time)
                                        <option value="{{ $time }}" {{ old('end_time') == $time ? 'selected' : '' }}>{{ $time }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="message">Pesan / Permintaan Khusus</label>
                            <textarea id="message" name="message">{{ old('message') }}</textarea>
                        </div>

                        <button type="submit" class="submit-btn">Tambah Booking</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Validasi waktu mulai & selesai
        document.getElementById('start_time').addEventListener('change', function() {
            const startTime = this.value;
            const endSelect = document.getElementById('end_time');
            const startHour = parseInt(startTime.split(':')[0]);

            Array.from(endSelect.options).forEach(option => {
                const endHour = parseInt(option.value.split(':')[0]);
                option.disabled = endHour <= startHour;
            });
        });
    </script>
</body>

</html>
