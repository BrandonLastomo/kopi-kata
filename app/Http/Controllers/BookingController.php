<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Table;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Menampilkan halaman booking (Step 1, 2, 3).
     */
    public function index(Request $request)
    {
        // Mengambil semua booking untuk list "Recent Bookings"
        $bookings = Booking::with('user')->orderBy('created_at', 'desc')->get();
        $totalTables = Table::count();

        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $selectedStartTime = $request->input('start_time', '10:00');
        $selectedEndTime = $request->input('end_time', '12:00');
        
        $bookedTableNumbers = collect();
        $availableTables = $totalTables;

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

        return view('book', compact(
            'bookings',
            'totalTables',
            'selectedDate',
            'selectedStartTime',
            'selectedEndTime',
            'bookedTableNumbers',
            'availableTables'
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

            return redirect()->route('book.index')->with('success', 'Booking berhasil! Terima kasih.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
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
            return redirect()->route('book.index')->with('success', 'Booking berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('book.index')->with('error', 'Gagal menghapus booking: ' . $e->getMessage());
        }
    }
}