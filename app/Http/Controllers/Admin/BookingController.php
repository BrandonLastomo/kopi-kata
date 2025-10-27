<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Table;
use App\Models\User; // ✅ Tambahan
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

        $search = $request->input('search', '');
        $status_filter = $request->input('status', 'all');
        $date_from = $request->input('date_from', '');
        $date_to = $request->input('date_to', '');
        $sort = $request->input('sort', 'newest');

        $query = Booking::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $records_per_page = 10;
        $bookings = $query->paginate($records_per_page)->withQueryString();

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
            'search',
            'status_filter',
            'date_from',
            'date_to',
            'sort',
        ));
    }

    /**
     * Menampilkan form tambah booking baru (Admin).
     */
    public function create()
    {
        $totalTables = \App\Models\Table::count();
        $bookings = Booking::latest()->take(5)->get();

        // ✅ Tambahan baris berikut agar $users & $tables dikirim ke view
        $users = User::all();
        $tables = Table::all();

        return view('admin_create_booking', compact('totalTables', 'bookings', 'users', 'tables'));
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

            Booking::create($validatedData);

            return redirect()->route('bookings.index')->with('success', 'Booking berhasil! Terima kasih.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function edit(Booking $booking)
    {
        $error = session('error');
        $success = session('success');
        $totalTables = Table::count(); 

        return view('admin_edit_bookings', compact(
            'booking',
            'error',
            'success',
            'totalTables'
        ));
    }

    public function update(Request $request, Booking $booking)
    {
        $error = '';

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

        $name = $validatedData['name'];
        $email = $validatedData['email'];
        $phone = $validatedData['phone'];
        $table_number = $validatedData['table_number'];
        $booking_date = $validatedData['booking_date'];
        $start_time = $validatedData['start_time'];
        $end_time = $validatedData['end_time'];
        $message = $validatedData['message'];
        $booking_id = $booking->id;

        try {
            if ($table_number != $booking->table_number ||
                $booking_date != $booking->booking_date ||
                $start_time != Carbon::parse($booking->start_time)->format('H:i'))
            {
                if ($this->checkAvailability($booking_date, $table_number, $start_time, $end_time, $booking_id)) {
                    $error = "Maaf, meja tersebut sudah dibooking pada waktu yang dipilih.";
                }
            }

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

                return redirect()->route('bookings.edit', $booking->id)
                                 ->with('success', 'Booking berhasil diperbarui!');
            } else {
                return redirect()->route('bookings.edit', $booking->id)
                                 ->withInput()
                                 ->with('error', $error);
            }

        } catch (\Exception $e) {
            return redirect()->route('bookings.edit', $booking->id)
                             ->withInput()
                             ->with('error', "Error updating booking: " . $e->getMessage());
        }
    }

    private function checkAvailability($date, $table, $start, $end, $exceptId = null)
    {
        $query = Booking::where('booking_date', $date)
            ->where('table_number', $table)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    public function destroy(Booking $booking)
    {
        try {
            $booking->delete();
            return redirect()->route('bookings.index')->with('success', 'Booking berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('bookings.index')->with('error', 'Gagal menghapus booking: ' . $e->getMessage());
        }
    }
}
