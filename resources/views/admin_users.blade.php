<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                <h1>Kelola Pengguna</h1>
                <div class="admin-info">
                    {{-- $admin_name dari middleware --}}
                    <span>Selamat datang, {{ $admin_name ?? 'Admin' }}</span>
                     {{-- Tombol Logout --}}
                     
                        <a href="{{ route('logout') }}" class="logout-btn">
                            Logout
                        </a>
                </div>
            </div>

            {{-- Pesan Sukses/Error --}}
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
                    // Logika tab aktif (nama variabel sama)
                    $tab = request('tab', $editMode ? 'add' : 'list');
                @endphp
                <div class="tabs">
                    <div class="tab {{ $tab == 'list' ? 'active' : '' }}"
                        onclick="switchTab('list')">Daftar Pengguna</div>
                    <div class="tab {{ $tab == 'add' ? 'active' : '' }}"
                        onclick="switchTab('add')">
                        {{ $editMode ? 'Edit Pengguna' : 'Tambah Pengguna' }}
                    </div>
                </div>

                <div id="tab-list"
                    class="tab-content {{ $tab == 'list' ? 'active' : '' }}">
                    <div class="content-card">
                        <form action="{{ route('users.index') }}" method="GET">
                            <div class="search-filters">
                                <div class="search-box">
                                    <input type="text" name="search" placeholder="Cari nama atau email..."
                                           value="{{ $search }}"> {{-- Variabel dari controller --}}
                                    <button type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                            <input type="hidden" name="tab" value="list">
                        </form>

                        <div class="table-container">
                            @if ($users->count() > 0)
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            {{-- Ganti Username jadi Name --}}
                                            <th>Nama</th> 
                                            <th>Email</th>
                                            <th>Role</th> {{-- Tambah kolom Role --}}
                                            <th>Tanggal Registrasi</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- Loop Blade --}}
                                        @foreach ($users as $user)
                                            <tr>
                                                <td>{{ $user->id }}</td>
                                                <td>{{ $user->name }}</td> {{-- Ganti ke $user->name --}}
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->role }}</td> {{-- Tampilkan role --}}
                                                <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                                                <td>
                                                    <div class="action-btns">
                                                         {{-- Tautan Edit dengan route() --}}
                                                        <a href="{{ route('users.index', ['tab' => 'add', 'edit' => $user->id]) }}" class="btn-sm btn-edit">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                         {{-- Tombol Hapus dengan form --}}
                                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-sm btn-delete">
                                                                <i class="fas fa-trash"></i> Hapus
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p>Tidak ada data pengguna yang ditemukan.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div id="tab-add"
                    class="tab-content {{ $tab == 'add' ? 'active' : '' }}">
                    <div class="content-card">
                         {{-- Judul dinamis --}}
                        <h2>{{ $editMode ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</h2>
                         {{-- Form action dinamis --}}
                        <form method="post" action="{{ $editMode ? route('users.update', $editUser->id) : route('users.store') }}">
                            @csrf
                            @if($editMode)
                                @method('PUT')
                            @endif

                            <div class="form-row">
                                <div class="form-group">
                                    {{-- Ganti Username jadi Name --}}
                                    <label for="name">Nama</label> 
                                    <input type="text" id="name" name="name" required
                                           value="{{ old('name', $editUser->name ?? '') }}"> {{-- Ganti ke name --}}
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" required
                                           value="{{ old('email', $editUser->email ?? '') }}">
                                </div>
                            </div>
                             {{-- Tambah input Role --}}
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select id="role" name="role" required>
                                    <option value="user" @selected(old('role', $editUser->role ?? 'user') == 'user')>User</option>
                                    <option value="admin" @selected(old('role', $editUser->role ?? '') == 'admin')>Admin</option>
                                </select>
                            </div>
                            <div class="form-group password-field">
                                {{-- Label dinamis --}}
                                <label for="password">{{ $editMode ? 'Password (biarkan kosong jika tidak diubah)' : 'Password' }}</label>
                                <input type="password" id="password" name="password" {{ $editMode ? '' : 'required' }}>
                                <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>

                            {{-- Hidden fields tidak diperlukan lagi --}}

                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i> {{ $editMode ? 'Update Pengguna' : 'Tambah Pengguna' }}
                            </button>
                            <a href="{{ route('users.index') }}" class="btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    {{-- JS disalin langsung, dengan href diubah ke route() --}}
    <script>
        function switchTab(tabId) {
            window.location.href = '{{ route("users.index") }}?tab=' + tabId;
        }

        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const toggleButton = document.querySelector('.toggle-password i');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleButton.className = 'far fa-eye-slash';
            } else {
                passwordField.type = 'password';
                toggleButton.className = 'far fa-eye';
            }
        }
    </script>
</body>

</html>