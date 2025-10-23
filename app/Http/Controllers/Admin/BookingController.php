<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    /**
     * Menampilkan halaman booking (Step 1, 2, 3).
     */
    public function index(Request $request)
    {
        // Mengambil semua booking untuk list "Recent Bookings"
        $bookings = Booking::orderBy('created_at', 'desc')->limit(10)->get();
        $totalTables = Table::count();

        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $selectedStartTime = $request->input('start_time', '10:00');
        $selectedEndTime = $request->input('end_time', '12:00');
        
        $bookedTableNumbers = collect();
        $availableTables = $totalTables;

        // ... (Error/Success handling) ...

        // 1. PASTIKAN $search DIDEFINISIKAN DI SINI
        $search = $request->input('search', ''); // Get search from request or default to empty string
        $status_filter = $request->input('status', 'all');
        $date_from = $request->input('date_from', '');
        $date_to = $request->input('date_to', '');
        $sort = $request->input('sort', 'newest');

        $query = Booking::query();

        // Gunakan $search dalam query
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $records_per_page = 10;
        $bookings = $query->paginate($records_per_page)->withQueryString();

        // Jika user "Cek Ketersediaan"
        if ($request->has('check_availability')) {
            $request->validate([
                'date' => 'required|date|after_or_equal:today',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
            ]);

            try {
                $bookedTableNumbers = Booking::where('booking_date', $selectedDate)
                    ->where(function ($query) use ($selectedStartTime, $selectedEndTime) {
                        $query->where('start_time', '<', $selectedEndTime)
                              ->where('end_time', '>', $selectedStartTime);
                    })
                    ->pluck('table_number');

                $availableTables = $totalTables - $bookedTableNumbers->count();

            } catch (\Exception $e) {
                return redirect()->route('book.index')
                                 ->with('error', 'Gagal memeriksa ketersediaan: ' . $e->getMessage());
            }
        }

        return view('admin_bookings', compact(
            'bookings',
            'totalTables',
            'selectedDate',
            'selectedStartTime',
            'selectedEndTime',
            'bookedTableNumbers',
            'availableTables',
            'search',        // <-- KIRIM $search KE VIEW
            'status_filter',
            'date_from',
            'date_to',
            'sort',
        ));
    }

    /**
     * Menyimpan booking baru dari user.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'nullable|string',
            'table_number' => 'required|integer|min:1',
            'booking_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        try {
            // Validasi ketersediaan meja sekali lagi
            $isOccupied = Booking::where('booking_date', $validatedData['booking_date'])
                ->where('table_number', $validatedData['table_number'])
                ->where(function ($query) use ($validatedData) {
                    $query->where('start_time', '<', $validatedData['end_time'])
                          ->where('end_time', '>', $validatedData['start_time']);
                })
                ->exists();

            if ($isOccupied) {
                return redirect()->back()
                                 ->withInput()
                                 ->with('error', 'Maaf, meja tersebut sudah dibooking pada waktu yang Anda pilih.');
            }

            // Buat booking baru
            Booking::create($validatedData);

            return redirect()->route('bookings.index')->with('success', 'Booking berhasil! Terima kasih.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    /**
     * Menampilkan form edit booking (Admin).
     * Menggantikan logika GET di admin_edit_booking.php
     * Laravel otomatis mengirimkan $booking melalui Route Model Binding.
     */
    public function edit(Booking $booking)
    {
        // Variabel error dan success diambil dari session flash
        $error = session('error');
        $success = session('success');

        // Total meja yang tersedia (nama variabel sama)
        // Sebaiknya ambil dari DB, bukan hardcode
        $totalTables = Table::count(); 

        // Kirim variabel ke view
        return view('admin_edit_bookings', compact(
            'booking', // Variabel $booking dari Route Model Binding
            'error',
            'success',
            'totalTables'
        ));
    }

    /**
     * Update booking yang ada (Admin).
     * Menggantikan logika POST di admin_edit_booking.php
     */
    public function update(Request $request, Booking $booking)
    {
        $error = ''; // Inisialisasi error (nama variabel sama)

        // Validasi data input (menggunakan nama dari form)
        $validatedData = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:20',
            'table_number' => 'required|integer|min:1',
            'booking_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'message' => 'nullable|string',
        ]);

        // Variabel dari form (nama sama)
        $name = $validatedData['name'];
        $email = $validatedData['email'];
        $phone = $validatedData['phone'];
        $table_number = $validatedData['table_number'];
        $booking_date = $validatedData['booking_date'];
        $start_time = $validatedData['start_time'];
        $end_time = $validatedData['end_time'];
        $message = $validatedData['message'];
        $booking_id = $booking->id; // Ambil ID dari model

        try {
            // Validasi bahwa meja masih tersedia (logika sama, pakai Eloquent)
            // Cek hanya jika nomor meja atau tanggal atau waktu mulai berubah
            if ($table_number != $booking->table_number ||
                $booking_date != $booking->booking_date || // Bandingkan string tanggal
                $start_time != Carbon::parse($booking->start_time)->format('H:i')) // Bandingkan string waktu H:i
            {
                 // Helper function untuk cek ketersediaan
                if ($this->checkAvailability($booking_date, $table_number, $start_time, $end_time, $booking_id)) {
                    $error = "Maaf, meja tersebut sudah dibooking pada waktu yang dipilih.";
                }
            }

            // Jika tidak ada error, update booking
            if (empty($error)) {
                $booking->update([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'table_number' => $table_number,
                    'booking_date' => $booking_date,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'message' => $message,
                ]);

                // Redirect kembali ke halaman edit dengan pesan sukses
                return redirect()->route('bookings.edit', $booking->id)
                                 ->with('success', 'Booking berhasil diperbarui!');
            } else {
                 // Redirect kembali ke halaman edit dengan pesan error dan input lama
                 return redirect()->route('bookings.edit', $booking->id)
                                  ->withInput() // Mengirimkan kembali input user
                                  ->with('error', $error);
            }

        } catch (\Exception $e) {
            // Tangani error database
            return redirect()->route('bookings.edit', $booking->id)
                             ->withInput()
                             ->with('error', "Error updating booking: " . $e->getMessage());
        }
    }

    // ... (Metode destroy dan checkAvailability) ...
    /**
     * Helper function untuk cek ketersediaan meja.
     */
    private function checkAvailability($date, $table, $start, $end, $exceptId = null)
    {
        $query = Booking::where('booking_date', $date)
            ->where('table_number', $table)
            ->where('start_time', '<', $end) // Cek tumpang tindih waktu
            ->where('end_time', '>', $start);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId); // Abaikan booking yang sedang diedit
        }

        return $query->exists(); // Return true jika ada booking lain di slot itu
    }

    /**
     * Menghapus booking (milik user sendiri).
     */
    public function destroy(Booking $booking)
    {
        // Otorisasi: User hanya boleh hapus booking jika emailnya cocok ATAU dia adalah admin
        // if (Auth::user()->email !== $booking->email && !Auth::user()->isAdmin()) {
        //     return redirect()->route('book.index')->with('error', 'Anda tidak diizinkan menghapus booking ini.');
        // }

        try {
            $booking->delete();
            return redirect()->route('bookings.index')->with('success', 'Booking berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('bookings.index')->with('error', 'Gagal menghapus booking: ' . $e->getMessage());
        }
    }
}