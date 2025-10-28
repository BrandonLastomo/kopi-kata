@extends('app')

@section('content')
    <section class="book" id="book">
        <h1 class="heading">Reserve Your Place</h1>

        @if (session('success'))
            <div id="success-message">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div id="error-message">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
             <div id="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- step --}}
        <div class="step-container">
            <div class="step-indicator">
                <div class="step">
                    <div class="step-circle active" id="step1">1</div>
                    <div class="step-title">Pilih Tanggal & Waktu</div>
                </div>
                <div class="step">
                    <div class="step-circle {{ request()->has('check_availability') ? 'active' : '' }}" id="step2">2</div>
                    <div class="step-title">Pilih Meja</div>
                </div>
                <div class="step">
                    <div class="step-circle" id="step3">3</div>
                    <div class="step-title">Isi Informasi</div>
                </div>
            </div>
        </div>

        {{-- time select --}}
        <form action="{{ route('book.index') }}" class="form-reserve" method="GET">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label for="date"><i class="fas fa-calendar-alt"></i> Tanggal</label>
                    <input type="date" id="date" name="date" class="box" value="{{ $selectedDate }}"
                        min="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label for="start_time"><i class="fas fa-clock"></i> Waktu Mulai</label>
                    <select id="start_time" name="start_time" class="box" required>
                        <option value="10:00" @selected($selectedStartTime == '10:00')>10:00</option>
                        <option value="12:00" @selected($selectedStartTime == '12:00')>12:00</option>
                        <option value="14:00" @selected($selectedStartTime == '14:00')>14:00</option>
                        <option value="16:00" @selected($selectedStartTime == '16:00')>16:00</option>
                        <option value="18:00" @selected($selectedStartTime == '18:00')>18:00</option>
                        <option value="20:00" @selected($selectedStartTime == '20:00')>20:00</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="end_time"><i class="fas fa-clock"></i> Waktu Selesai</label>
                    <select id="end_time" name="end_time" class="box" required>
                        <option value="12:00" @selected($selectedEndTime == '12:00')>12:00</option>
                        <option value="14:00" @selected($selectedEndTime == '14:00')>14:00</option>
                        <option value="16:00" @selected($selectedEndTime == '16:00')>16:00</option>
                        <option value="18:00" @selected($selectedEndTime == '18:00')>18:00</option>
                        <option value="20:00" @selected($selectedEndTime == '20:00')>20:00</option>
                        <option value="22:00" @selected($selectedEndTime == '22:00')>22:00</option>
                    </select>
                </div>
            </div>
            <input type="hidden" name="check_availability" value="1">
            <button type="submit" class="form-btn">Cek Ketersediaan Meja</button>
        </form>

        @if (request()->has('check_availability'))
        <div id="tableSelectionSection">
            <h1 class="heading">Choose Your Table</h1>

            <div class="booking-status">
                <div class="status-item">
                    <div class="status-indicator status-available"></div>
                    <span>Available ({{ $availableTables }})</span>
                </div>
                <div class="status-item">
                    <div class="status-indicator status-booked"></div>
                    <span>Booked ({{ $bookedTableIds->count() }})</span>
                </div>
        </div>

        {{-- Table list --}}
        <div class="table-container">
            @foreach ($tables as $table)
                @php
                    $isBooked = $bookedTableIds->contains($table->id);
                    $tableClass = $isBooked ? 'booked' : 'available';
                @endphp
                <div class="table-item {{ $tableClass }}"
                    data-table-id="{{ $table->id }}"
                    data-table-number="{{ $table->table_number }}">
                    <div class="table-number">{{ $table->table_number }}</div>
                    <div>{{ $isBooked ? 'Booked' : 'Available' }}</div>
                </div>
            @endforeach
        </div>

        {{-- Booking form --}}
        <div id="bookingForm" class="hidden">
            <h1 class="heading">Isi Informasi Booking</h1>
            <form action="{{ route('book.store') }}" class="form-reserve" method="POST">
                @csrf

                {{-- Hidden data for relations --}}
                <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                <input type="hidden" name="table_id" id="selected_table" value="{{ old('table_id') }}">
                <input type="hidden" name="booking_date" value="{{ $selectedDate }}">
                <input type="hidden" name="start_time" value="{{ $selectedStartTime }}">
                <input type="hidden" name="end_time" value="{{ $selectedEndTime }}">

                <label for="Name">Name</label>
                <input type="text" id="name" placeholder="Your Name" class="box"
                       value="{{ old('name', Auth::user()->name) }}" disabled required>

                <label for="Email">Email</label>
                <input type="email" id="email" placeholder="Your Email" class="box"
                       value="{{ old('email', Auth::user()->email) }}" disabled required>

                <label for="Phone Number">Phone Number</label>
                <input type="text" name="phone" id="phone" placeholder="Phone Number" class="box"
                       value="{{ old('phone') }}">

                <label for="Message(s)">Message(s)</label>
                <textarea name="message" placeholder="Special requests or message" class="box" cols="30" rows="10">{{ old('message') }}</textarea>

                <input type="submit" id="submitBtn" value="Book Your Table Now" class="form-btn">
            </form>
        </div>
    </div>
@endif


        <div class="booking-details">
            <h1 class="heading">Recent Bookings</h1>
            <div class="booking-list">
                @forelse ($bookings as $booking)
                    <div class="booking-item">
                        <p><strong><i class="fas fa-user"></i> Name:</strong> {{ $booking->user->name ?? 'Tamu' }}</p>
                        <p><strong><i class="fas fa-envelope"></i> Email:</strong> {{ $booking->user->email ?? 'N/A' }}</p>
                        <p><strong><i class="fas fa-phone"></i> Phone:</strong> {{ $booking->phone }}</p>
                        <p><strong><i class="fas fa-calendar-day"></i> Date:</strong>
                            {{ $booking->booking_date->format('d M Y') }}</p>
                        <p><strong><i class="fas fa-clock"></i> Time:</strong>
                            {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} -
                            {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                        </p>
                        <p><strong><i class="fas fa-chair"></i> Table:</strong> #{{ $booking->table->id ?? 'Unknown' }}</p>
                        <p><strong><i class="fas fa-calendar-alt"></i> Booked on:</strong>
                            {{ $booking->created_at->format('d M Y H:i') }}</p>
                        @if (!empty($booking->message))
                            <p><strong><i class="fas fa-comment"></i> Message:</strong> {{ $booking->message }}</p>
                        @endif
                        @if (auth()->user()->name == $booking->user->name)
                            <form action="{{ route('book.destroy', $booking->id) }}" class="delete-book" method="POST" 
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus booking ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="form-btn">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="heading">Belum ada booking. Jadilah yang pertama!</p>
                @endforelse
            </div>
        </div>
    </section>  

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Validate time selection
    const startTimeSelect = document.getElementById('start_time');
    if (startTimeSelect) {
        startTimeSelect.addEventListener('change', function () {
            const startTime = this.value;
            const endTimeSelect = document.getElementById('end_time');
            const startHour = parseInt(startTime.split(':')[0]);
            const minEndHour = startHour + 2;

            Array.from(endTimeSelect.options).forEach(option => {
                const endHour = parseInt(option.value.split(':')[0]);
                option.disabled = endHour <= startHour;
            });

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
    }

    // Handle table selection
    @if (request()->has('check_availability'))
        const availableTables = document.querySelectorAll('.table-item.available');
        const bookingForm = document.getElementById('bookingForm');
        const selectedTableInput = document.getElementById('selected_table');
        const submitBtn = document.getElementById('submitBtn');

        availableTables.forEach(table => {
            table.addEventListener('click', function () {
                // Reset previous selection
                availableTables.forEach(t => {
                    t.style.transform = 'scale(1)';
                    t.style.boxShadow = 'none';
                });

                // Highlight selected table
                this.style.transform = 'scale(1.1)';
                this.style.boxShadow = '0 0 16px var(--secondary)';

                // Get data from table
                const tableId = this.getAttribute('data-table-id');
                const tableNumber = this.getAttribute('data-table-number');

                // Fill hidden input and show form
                selectedTableInput.value = tableId;
                bookingForm.classList.remove('hidden');
                document.getElementById('step3').classList.add('active');
                bookingForm.scrollIntoView({ behavior: 'smooth' });

                // Update submit button label
                submitBtn.value = `Book Table ${tableNumber} on {{ $selectedDate }}`;
            });
        });

        // Show form again if user had selected a table before (after validation error)
        const oldTable = selectedTableInput.value;
        if (oldTable) {
            bookingForm.classList.remove('hidden');
            document.getElementById('step3').classList.add('active');
            const selectedTableEl = document.querySelector(`.table-item[data-table-id="${oldTable}"]`);
            if (selectedTableEl && selectedTableEl.classList.contains('available')) {
                selectedTableEl.style.transform = 'scale(1.1)';
                selectedTableEl.style.boxShadow = '0 0 16px var(--secondary)';
            }
        }
    @endif
});
</script>
@endpush

@endsection