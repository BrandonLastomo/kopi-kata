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
    $bookings = Booking::with(['user', 'table'])
        ->orderBy('created_at', 'desc')
        ->get();

    $tables = Table::all();
    $totalTables = $tables->count();

    $selectedDate = $request->input('date', Carbon::today()->toDateString());
    $selectedStartTime = $request->input('start_time', '10:00');
    $selectedEndTime = $request->input('end_time', '12:00');

    $bookedTableIds = collect();
    $availableTables = $totalTables;

    // Jika user menekan "Cek Ketersediaan"
    if ($request->has('check_availability')) {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        try {
            // Ambil semua table_id yang sudah terbooking di waktu tertentu
            $bookedTableIds = Booking::where('booking_date', $selectedDate)
                ->where(function ($query) use ($selectedStartTime, $selectedEndTime) {
                    $query->where('start_time', '<', $selectedEndTime)
                          ->where('end_time', '>', $selectedStartTime);
                })
                ->pluck('table_id');

            $availableTables = $totalTables - $bookedTableIds->count();

        } catch (\Exception $e) {
            return redirect()->route('book.index')
                             ->with('error', 'Gagal memeriksa ketersediaan: ' . $e->getMessage());
        }
    }

    return view('book', compact(
        'bookings',
        'tables',    
        'totalTables',
        'selectedDate',
        'selectedStartTime',
        'selectedEndTime',
        'bookedTableIds', 
        'availableTables'
    ));
}


    /**
     * Menyimpan booking baru dari user.
     */
    public function store(Request $request)
{
    $validatedData = $request->validate([
        'user_id' => 'nullable|exists:users,id',
        'table_id' => 'required|exists:tables,id',
        'phone' => 'nullable|string|max:20',
        'message' => 'nullable|string',
        'booking_date' => 'required|date',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
    ]);

    try {
        // Cek apakah meja sudah dibooking di jam yang sama
        $isOccupied = Booking::where('booking_date', $validatedData['booking_date'])
            ->where('table_id', $validatedData['table_id'])
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