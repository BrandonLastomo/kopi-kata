<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking | Admin Panel</title>
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
        {{-- sidebar --}}
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

        {{-- main content --}}
        <main class="main-content">
            <div class="header">
                <h1>Edit Booking</h1>
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

                {{-- edit book form --}}
                <div class="form-container">
                    <form action="{{ route('bookings.update', $booking->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="user_id">User</label>
                            <select name="user_id" id="user_id">
                                <option value="">Guest</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ $booking->user_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="table_id">Meja</label>
                            <select name="table_id" id="table_id" required>
                                @foreach ($tables as $table)
                                    <option value="{{ $table->id }}" {{ $booking->table_id == $table->id ? 'selected' : '' }}>
                                        Meja {{ $table->table_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="booking_date">Tanggal</label>
                            <input type="date" name="booking_date" value="{{ $booking->booking_date }}" required>
                        </div>

                        <div class="form-group">
                            <label for="start_time">Waktu Mulai</label>
                            <input type="time" name="start_time" value="{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="end_time">Waktu Selesai</label>
                            <input type="time" name="end_time" value="{{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="message">Pesan</label>
                            <textarea name="message">{{ $booking->message }}</textarea>
                        </div>

                        <button type="submit" class="submit-btn">Perbarui Booking</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // validate start and end time
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
