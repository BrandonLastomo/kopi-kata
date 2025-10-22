<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Table;
use Carbon\Carbon;

class DashboardController extends Controller
{

    public function index(Request $request)
    {
        $stats = [
            'total_bookings' => Booking::count(),
            'today_bookings' => Booking::whereDate('booking_date', Carbon::today())->count(),
            'upcoming_bookings' => Booking::whereDate('booking_date', '>', Carbon::today())->count(),
        ];

        $recent_bookings = Booking::orderBy('booking_date', 'desc')
                                    ->orderBy('start_time', 'desc')
                                    ->limit(10)
                                    ->get();

        $selectedDate = $request->input('filter_date', Carbon::today()->toDateString());

        $filtered_bookings = Booking::whereDate('booking_date', $selectedDate)
                                      ->orderBy('start_time')
                                      ->get();
                                      
        $totalTables = Table::count(); // Ambil dari DB

        return view('admin_dashboard', compact(
            'stats', 
            'recent_bookings', 
            'selectedDate', 
            'filtered_bookings',
            'totalTables'
        ));
    }
}