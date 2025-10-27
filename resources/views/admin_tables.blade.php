<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Meja | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                {{-- <div class="nav-item {{ request()->routeIs('settings') ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    <a href="{{ route('settings') }}">Pengaturan</a>
                </div> --}}
            </nav>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1>Kelola Meja</h1>
                <div class="admin-info">
                    <span>Selamat datang, {{ $admin_name ?? 'Admin' }}</span>
                    {{-- Tombol Logout diubah menjadi form --}}
                        <a href="{{ route('logout') }}" class="logout-btn">
                            Logout
                        </a>
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

            <div class="tab-container">
                @php
                    // Logika untuk menentukan tab aktif
                    $tab = request('tab', $editMode ? 'add' : 'list');
                @endphp
                <div class="tabs">
                    <div class="tab {{ $tab == 'list' ? 'active' : '' }}"
                        onclick="switchTab('list')">Daftar Meja</div>
                    <div class="tab {{ $tab == 'add' ? 'active' : '' }}"
                        onclick="switchTab('add')">
                        {{ $editMode ? 'Edit Meja' : 'Tambah Meja Baru' }}
                    </div>
                    <div class="tab {{ $tab == 'availability' ? 'active' : '' }}"
                        onclick="switchTab('availability')">Ketersediaan Meja</div>
                </div>

                <div id="tab-list"
                    class="tab-content {{ $tab == 'list' ? 'active' : '' }}">
                    <div class="table-cards">
                        @foreach ($tables as $table)
                            <div class="table-card">
                                <div class="table-card-header">
                                    <h3>Meja {{ $table->table_number }}</h3>
                                    <span class="table-status status-{{ $table->status }}">
                                        @if ($table->status == 'available')
                                            Tersedia
                                        @elseif ($table->status == 'reserved')
                                            Dipesan
                                        @else
                                            Pemeliharaan
                                        @endif
                                    </span>
                                </div>
                                <div class="table-card-body">
                                    <div class="table-info">
                                        <p>
                                            <strong>Kapasitas:</strong>
                                            <span>{{ $table->capacity }} orang</span>
                                        </p>
                                        <p>
                                            <strong>Lokasi:</strong>
                                            <span>{{ $table->location }}</span>
                                        </p>
                                        <p>
                                            <strong>Total Bookings:</strong>
                                            <span>{{ $table->total_bookings }}</span>
                                        </p>
                                        <p>
                                            <strong>Booking Mendatang:</strong>
                                            <span>{{ $table->upcoming_bookings }}</span>
                                        </p>
                                    </div>

                                    @if ($table->bookings->isNotEmpty())
                                        {{-- Get the first (and only) booking from that collection --}}
                                        @php $today_booking = $table->bookings->first(); @endphp
                                        <div class="table-booking-info">
                                            <strong>Booking Hari Ini:</strong>
                                            <p>{{ $today_booking->name }}</p>
                                            <p>
                                                {{ \Carbon\Carbon::parse($today_booking->start_time)->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($today_booking->end_time)->format('H:i') }}
                                            </p>
                                        </div>
                                    @endif

                                    @if (!empty($table->description))
                                        <div style="margin-top: 15px;">
                                            <strong>Deskripsi:</strong>
                                            <p>{{ $table->description }}</p>
                                        </div>
                                    @endif

                                    <div class="table-actions">
                                        <a href="{{ route('tables.index', ['tab' => 'add', 'edit' => $table->id]) }}" class="btn-sm btn-edit">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        
                                        <form action="{{ route('tables.destroy', $table->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus meja ini? Semua booking terkait akan dihapus juga.')" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-sm btn-delete">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div id="tab-add"
                    class="tab-content {{ $tab == 'add' ? 'active' : '' }}">
                    <div class="content-card">
                        <h2>{{ $editMode ? 'Edit Meja #' . $editTable->table_number : 'Tambah Meja Baru' }}</h2>
                        
                        <form method="post" action="{{ $editMode ? route('tables.update', $editTable->id) : route('tables.store') }}">
                            @csrf
                            @if ($editMode)
                                @method('PUT')
                            @endif

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="table_number">Nomor Meja</label>
                                    <input type="number" id="table_number" name="table_number" min="1" required
                                        value="{{ old('table_number', $editTable->table_number ?? '') }}">
                                </div>
                                <div class="form-group">
                                    <label for="capacity">Kapasitas</label>
                                    <input type="number" id="capacity" name="capacity" min="1" required
                                        value="{{ old('capacity', $editTable->capacity ?? '4') }}">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="location">Lokasi</label>
                                    <select id="location" name="location">
                                        <option value="Main Area" @selected(old('location', $editTable->location ?? 'Main Area') == 'Main Area')>Area Utama</option>
                                        <option value="Window" @selected(old('location', $editTable->location ?? '') == 'Window')>Jendela</option>
                                        <option value="Outdoor" @selected(old('location', $editTable->location ?? '') == 'Outdoor')>Luar Ruangan</option>
                                        <option value="Private Room" @selected(old('location', $editTable->location ?? '') == 'Private Room')>Ruang Privat</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select id="status" name="status">
                                        <option value="available" @selected(old('status', $editTable->status ?? 'available') == 'available')>Tersedia</option>
                                        <option value="reserved" @selected(old('status', $editTable->status ?? '') == 'reserved')>Dipesan</option>
                                        <option value="maintenance" @selected(old('status', $editTable->status ?? '') == 'maintenance')>Pemeliharaan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="description">Deskripsi</label>
                                <textarea id="description"
                                    name="description">{{ old('description', $editTable->description ?? '') }}</textarea>
                            </div>

                            {{-- Input 'action' dan 'id' tidak lagi diperlukan --}}

                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> {{ $editMode ? 'Update Meja' : 'Tambah Meja' }}
                            </button>
                            <a href="{{ route('tables.index') }}" class="btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </form>
                    </div>
                </div>

                <div id="tab-availability"
                    class="tab-content {{ $tab == 'availability' ? 'active' : '' }}">
                    <div class="content-card">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h2>Ketersediaan Meja</h2>
                            <form class="date-filter" method="GET" action="{{ route('tables.index') }}">
                                <input type="hidden" name="tab" value="availability">
                                <label for="availability_date">Tanggal:</label>
                                <input type="date" id="availability_date" name="date"
                                    value="{{ $selectedDate }}">
                                <button type="submit"><i class="fas fa-filter"></i> Filter</button>
                            </form>
                        </div>

                        <div>
                            @foreach ($timeslots as $slot)
                                @php
                                    list($startTime, $endTime) = explode('-', $slot);
                                @endphp
                                <div style="margin-bottom: 30px;">
                                    <h3>{{ $slot }}</h3>
                                    <div class="table-cards"
                                        style="grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));">
                                        
                                        @foreach ($tables as $table)
                                            @php
                                                $isBooked = false;
                                                $bookedBy = '';

                                                // Logika cek booking yang sudah dioptimasi
                                                if (isset($bookingsOnDate[$table->table_number])) {
                                                    foreach ($bookingsOnDate[$table->table_number] as $booking) {
                                                        if (($booking->start_time < $endTime) && ($booking->end_time > $startTime)) {
                                                            $isBooked = true;
                                                            $bookedBy = $booking->name;
                                                            break;
                                                        }
                                                    }
                                                }

                                                $statusClass = $isBooked ? 'status-reserved' : ($table->status == 'available' ? 'status-available' : 'status-maintenance');
                                                $statusText = $isBooked ? 'Dipesan' : ($table->status == 'available' ? 'Tersedia' : 'Pemeliharaan');
                                            @endphp
                                            <div class="table-card" style="margin-bottom: 10px;">
                                                <div class="table-card-header">
                                                    <h3 style="font-size: 1rem;">Meja {{ $table->table_number }}</h3>
                                                    <span class="table-status {{ $statusClass }}">{{ $statusText }}</span>
                                                </div>
                                                @if ($isBooked)
                                                    <div class="table-card-body" style="padding: 10px;">
                                                        <p style="margin: 0; font-size: 0.9rem;">
                                                            <strong>Dipesan oleh:</strong><br>
                                                            {{ $bookedBy }}
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    {{-- JS disalin langsung, dengan href diubah ke route() --}}
    <script>
        // Fungsi ini tidak lagi digunakan oleh form, tapi mungkin oleh JS lain
        function confirmDelete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus meja ini? Semua booking terkait akan dihapus juga.')) {
                // Logika form submit akan menangani ini
            }
        }

        function switchTab(tabId) {
            // Menggunakan URL::route() untuk membuat URL dengan parameter
            window.location.href = '{{ route('tables.index') }}?tab=' + tabId;
        }
    </script>
</body>

</html>