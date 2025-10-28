<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Table;
use App\Models\User;
use Carbon\Carbon;

class BookingController extends Controller
{
    // booking page admin
    public function index(Request $request) {
        $search = $request->input('search', '');
        $status_filter = $request->input('status', 'all');
        $date_from = $request->input('date_from', '');
        $date_to = $request->input('date_to', '');
        $sort = $request->input('sort', 'newest');

        $query = Booking::with(['user', 'table']);

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }

        if ($date_from && $date_to) {
            $query->whereBetween('booking_date', [$date_from, $date_to]);
        }

        if ($status_filter === 'today') {
            $query->whereDate('booking_date', Carbon::today());
        } elseif ($status_filter === 'upcoming') {
            $query->whereDate('booking_date', '>', Carbon::today());
        } elseif ($status_filter === 'past') {
            $query->whereDate('booking_date', '<', Carbon::today());
        }

        switch ($sort) {
            case 'oldest':
                $query->orderBy('booking_date', 'asc');
                break;
            case 'name_asc':
                $query->join('users', 'bookings.user_id', '=', 'users.id')
                      ->orderBy('users.name', 'asc');
                break;
            case 'name_desc':
                $query->join('users', 'bookings.user_id', '=', 'users.id')
                      ->orderBy('users.name', 'desc');
                break;
            default:
                $query->orderBy('booking_date', 'desc');
        }

        $bookings = $query->paginate(10)->withQueryString();

        return view('admin_bookings', compact('bookings', 'search', 'status_filter', 'date_from', 'date_to', 'sort'));
    }

    // add-booking page admin
    public function create() {
        $tables = Table::all();
        $users = User::all();

        return view('admin_create_booking', compact('tables', 'users'));
    }

    // store inputted booking
    public function store(Request $request) {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'table_id' => 'required|exists:tables,id',
            'booking_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'message' => 'nullable|string|max:500',
        ]);

        // is table available
        $isOccupied = Booking::where('booking_date', $validated['booking_date'])
            ->where('table_id', $validated['table_id'])
            ->where(function ($q) use ($validated) {
                $q->where('start_time', '<', $validated['end_time'])
                  ->where('end_time', '>', $validated['start_time']);
            })
            ->exists();

        if ($isOccupied) {
            return back()->withInput()->with('error', 'Meja tersebut sudah dibooking pada waktu tersebut.');
        }

        Booking::create($validated);

        return redirect()->route('bookings.index')->with('success', 'Booking berhasil ditambahkan.');
    }

    // edit booking page admin
    public function edit(Booking $booking) {
        $tables = Table::all();
        $users = User::all();

        return view('admin_edit_bookings', compact('booking', 'tables', 'users'));
    }

    // store edited booking
    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'table_id' => 'required|exists:tables,id',
            'booking_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'message' => 'nullable|string|max:500',
        ]);

        // is table available
        $conflict = Booking::where('table_id', $validated['table_id'])
            ->where('booking_date', $validated['booking_date'])
            ->where('id', '!=', $booking->id)
            ->where(function ($q) use ($validated) {
                $q->where('start_time', '<', $validated['end_time'])
                  ->where('end_time', '>', $validated['start_time']);
            })
            ->exists();

        if ($conflict) {
            return back()->withInput()->with('error', 'Meja sudah dibooking pada waktu tersebut.');
        }

        $booking->update($validated);

        return redirect()->route('bookings.edit', $booking->id)->with('success', 'Booking berhasil diperbarui.');
    }

    // delete booking
    public function destroy(Booking $booking)
    {
        $booking->delete();
        return redirect()->route('bookings.index')->with('success', 'Booking berhasil dihapus.');
    }
}
