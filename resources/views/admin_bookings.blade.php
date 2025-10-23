<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Booking | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

<body>
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
                <h1>Kelola Booking</h1>
                <div class="admin-info">
                    {{-- $admin_name dikirim dari middleware --}}
                    <span>Selamat datang, {{ $admin_name ?? 'Admin' }}</span>
                     {{-- Tombol Logout diubah menjadi form --}}
                    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                        @csrf
                        <a href="{{ route('logout') }}" class="logout-btn"
                           onclick="event.preventDefault(); this.closest('form').submit();">
                            Logout
                        </a>
                    </form>
                </div>
            </div>

            {{-- Menampilkan pesan sukses/error dari session flash --}}
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 15px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="content-card">
                <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                    <h2 style="margin: 0;">Daftar Booking</h2>
                    <div class="btn-group">
                        {{-- Tautan diubah ke route() --}}
                        <a href="{{ route('bookings.create') }}" class="add-btn">
                            <i class="fas fa-plus"></i> Tambah Booking
                        </a>
                        {{-- Tautan Export bisa ditambahkan nanti --}}
                        {{-- <a href="#" class="export-btn">
                            <i class="fas fa-file-export"></i> Export
                        </a> --}}
                    </div>
                </div>

                <form action="{{ route('bookings.index') }}" method="GET" id="filterForm">
                    <div class="filters-container">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Cari nama, email, atau no. telp..."
                                value="{{ $search }}"> {{-- Variabel dari controller --}}
                        </div>
                        <div class="filter-group primary-filters">
                            <select name="status">
                                <option value="all" @selected($status_filter === 'all')>Semua Status</option>
                                <option value="upcoming" @selected($status_filter === 'upcoming')>Mendatang</option>
                                <option value="today" @selected($status_filter === 'today')>Hari Ini</option>
                                <option value="past" @selected($status_filter === 'past')>Lewat</option>
                            </select>
                            <select name="sort">
                                <option value="newest" @selected($sort === 'newest')>Terbaru</option>
                                <option value="oldest" @selected($sort === 'oldest')>Terlama</option>
                                <option value="name_asc" @selected($sort === 'name_asc')>Nama (A-Z)</option>
                                <option value="name_desc" @selected($sort === 'name_desc')>Nama (Z-A)</option>
                            </select>
                            <button type="submit">Terapkan</button>
                        </div>
                    </div>
                    <div class="filter-group date-filters">
                        <div class="date-range">
                            <span>Dari:</span>
                            <input type="date" name="date_from" class="datepicker"
                                value="{{ $date_from }}"> {{-- Variabel dari controller --}}
                            <span>Sampai:</span>
                            <input type="date" name="date_to" class="datepicker"
                                value="{{ $date_to }}"> {{-- Variabel dari controller --}}
                        </div>
                        <button type="button" onclick="resetFilters()">Reset Filter</button>
                    </div>
                </form>
            </div>

            <div class="content-card">
                <div class="table-container">
                    {{-- @if (count($bookings) > 0) --}}
                    @if ($bookings->count() > 0) {{-- Gunakan method count() pada koleksi pagination --}}
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
                                {{-- Loop diubah ke @foreach Blade, $counter diganti $loop->iteration --}}
                                @foreach ($bookings as $booking)
                                    @php
                                        // Logika status yang sama persis
                                        $bookingDate = strtotime($booking->booking_date);
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
                                        {{-- Menggunakan nomor dari pagination --}}
                                        <td>{{ $loop->iteration + ($bookings->currentPage() - 1) * $bookings->perPage() }}</td>
                                        <td>{{ $booking->name }}</td>
                                        <td>
                                            {{ $booking->email }}<br>
                                            <small>{{ $booking->phone }}</small>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                        </td>
                                        <td>
                                            {{ $booking->table_number }}
                                        </td>
                                        <td><span class="status {{ $status }}">{{ $statusText }}</span></td>
                                        <td>
                                            {{-- Tautan Edit diubah ke route() --}}
                                            <a href="{{ route('bookings.edit', $booking->id) }}"
                                                class="action-btn edit-btn">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            {{-- Tautan Hapus diubah ke form --}}
                                            <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus booking ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn delete-btn" style="border:none; cursor:pointer;">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- Menggunakan helper links() dari pagination Laravel --}}
                        @if ($bookings->hasPages())
                            <div class="pagination">
                                {{ $bookings->links() }}
                            </div>
                        @endif
                    @else
                        <div class="no-data">
                            <i class="far fa-calendar-times" style="font-size: 3rem; color: #ccc; margin-bottom: 10px;"></i>
                            <p>Tidak ada data booking yang ditemukan.</p>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    {{-- JS disalin langsung, dengan href diubah ke route() --}}
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
             // Mengarahkan ke URL index tanpa parameter filter
            window.location.href = '{{ route("bookings.index") }}';
        }
    </script>
</body>

</html>