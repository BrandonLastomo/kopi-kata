<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Kopi & Kata</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    {{-- Menggunakan helper asset() untuk memuat style.css dari folder public --}}
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body class="dashboard-body">
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Kopi & Kata</h2>
                <p>Admin Panel</p>
            </div>
            <nav class="sidebar-nav">
                {{-- Tautan diubah ke helper route() --}}
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
                {{-- <div class="nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    <a href="{{ route('admin.settings') }}">Pengaturan</a>
                </div> --}}
            </nav>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="admin-info">
                    {{-- $admin_name dikirim dari middleware --}}
                    <span>Selamat datang, {{ $admin_name ?? 'Admin' }}</span>
                    
                    {{-- Tombol Logout diubah menjadi form --}}
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <a href="{{ route('logout') }}" class="logout-btn">
                            Logout
                        </a>
                    </form>
                </div>
            </div>

            {{-- Menampilkan error jika ada --}}
            @if(!empty($error))
                <div style="padding: 15px; margin-bottom: 20px; border-radius: 5px; background-color: #f8d7da; color: #721c24;">
                    {{ $error }}
                </div>
            @endif

            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        {{-- Menggunakan sintaks Blade dan null safety --}}
                        <h3>{{ $stats['total_bookings'] ?? 0 }}</h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>{{ $stats['today_bookings'] ?? 0 }}</h3>
                        <p>Bookings Hari Ini</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon yellow">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>{{ $stats['upcoming_bookings'] ?? 0 }}</h3>
                        <p>Bookings Mendatang</p>
                    </div>
                </div>
            </div>

            <div class="content-card">
                <div class="content-header">
                    <h2>Visualisasi Meja</h2>
                    {{-- Form action diubah ke route() --}}
                    <form class="filter-form" method="GET" action="{{ route('dashboard') }}">
                        <input type="date" name="filter_date" value="{{ $selectedDate }}" required>
                        <button type="submit">Filter</button>
                    </form>
                </div>

                <div class="time-slots">
                    {{-- PHP Loop diubah ke Blade --}}
                    @php
                        $timeSlots = ['10:00-12:00', '12:00-14:00', '14:00-16:00', '16:00-18:00', '18:00-20:00', '20:00-22:00'];
                    @endphp
                    @foreach ($timeSlots as $slot)
                        @php
                            list($startTime, $endTime) = explode('-', $slot);
                        @endphp
                        <div class="time-slot">
                            <div class="time-slot-header">
                                <h3>{{ $slot }}</h3>
                            </div>
                            <div class="time-tables">
                                {{-- Asumsi 10 meja, ini bisa diganti $totalTables dari controller --}}
                                @for ($i = 1; $i <= 10; $i++)
                                    @php
                                        $isBooked = false;
                                        $bookedBy = '';

                                        // Logika PHP yang sama persis
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
                                    @endphp
                                    {{-- Output Blade --}}
                                    <div class="mini-table {{ $isBooked ? 'booked' : '' }}"
                                        title="{{ $isBooked ? 'Booked by: ' . htmlspecialchars($bookedBy) : 'Available' }}">
                                        {{ $i }}
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="content-card">
                <div class="content-header">
                    <h2>Booking Terbaru</h2>
                    <a href="{{ route('bookings.index') }}" style="color: var(--primary); text-decoration: none;">Lihat Semua</a>
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
                            {{-- PHP if/else diubah ke @forelse --}}
                            @forelse ($recent_bookings as $booking)
                                @php
                                    // Logika status yang sama persis
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
                                @endphp
                                <tr>
                                    <td>{{ $booking['name'] }}</td>
                                    <td>{{ $booking['email'] }}</td>
                                    {{-- Menggunakan Carbon untuk format tanggal --}}
                                    <td>{{ \Carbon\Carbon::parse($booking['booking_date'])->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($booking['start_time'])->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($booking['end_time'])->format('H:i') }}
                                    </td>
                                    <td>{{ $booking['table_number'] }}</td>
                                    <td><span class="status {{ $status }}">{{ $statusText }}</span></td>
                                    <td>
                                        {{-- Tautan diubah ke route() dan form Hapus --}}
                                        <a href="{{ route('bookings.edit', $booking['id']) }}"
                                            class="action-btn edit-btn">Edit</a>
                                        
                                        <form action="{{ route('bookings.destroy', $booking['id']) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus booking ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-btn delete-btn" style="border:none; cursor:pointer;">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center;">Tidak ada data booking</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    {{-- Script JS disalin langsung --}}
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