<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class TableController extends Controller
{
    public function index(Request $request) {
        // Take tables with related counts and today's booking eagerly
        $tables = Table::orderBy('table_number')
            ->withCount([
                'bookings as total_bookings', // Counts all related bookings
                'bookings as upcoming_bookings' => function ($query) {
                    // Counts only upcoming/today bookings
                    $query->where('booking_date', '>=', Carbon::today());
                }
            ])
                ->with(['bookings' => function ($query) {
                    $query->whereDate('booking_date', Carbon::today())
                        ->orderBy('start_time')
                        ->limit(1);
                }])
                ->get();

        $editMode = false;
        $editTable = null;
        if ($editId = $request->input('edit')) {
            $editTable = Table::find($editId);
            if ($editTable) {
                $editMode = true;
            }
        }

        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $timeslots = [
            '10:00-12:00', '12:00-14:00', '14:00-16:00',
            '16:00-18:00', '18:00-20:00', '20:00-22:00'
        ];
        $bookingsOnDate = Booking::whereDate('booking_date', $selectedDate)
                                ->get()
                                ->groupBy('table_number');

        return view('admin_tables', compact(
            'tables',
            'editTable',
            'editMode',
            'selectedDate',
            'timeslots',
            'bookingsOnDate'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'table_number' => 'required|integer|unique:tables',
            'capacity' => 'required|integer|min:1',
            'location' => 'required|string',
            'status' => 'required|in:available,reserved,maintenance',
            'description' => 'nullable|string',
        ]);

        Table::create($data);
        return redirect()->route('tables.index')->with('success', 'Meja baru berhasil ditambahkan');
    }

    public function update(Request $request, Table $table)
    {
        $data = $request->validate([
            'table_number' => ['required', 'integer', Rule::unique('tables')->ignore($table->id)],
            'capacity' => 'required|integer|min:1',
            'location' => 'required|string',
            'status' => 'required|in:available,reserved,maintenance',
            'description' => 'nullable|string',
        ]);

        $table->update($data);
        return redirect()->route('tables.index')->with('success', 'Data meja berhasil diperbarui');
    }

    public function destroy(Table $table)
    {
        $hasUpcoming = $table->bookings()
                             ->whereDate('booking_date', '>=', Carbon::today())
                             ->exists();
                              
        if ($hasUpcoming) {
            return redirect()->back()->with('error', 'Tidak bisa menghapus meja. Masih ada booking mendatang.');
        }

        $table->delete();
        return redirect()->route('tables.index')->with('success', 'Meja berhasil dihapus');
    }
}