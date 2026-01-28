<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the general dashboard overview.
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'Admin') {
            return redirect()->route('dashboard.admin.index');
        }

        
        $activeReservationsCount = Reservation::where('user_id', $user->id)
            ->where('status', 'Active')
            ->count();
            
        $pendingReservationsCount = Reservation::where('user_id', $user->id)
            ->where('status', 'Pending')
            ->count();

        // Get recent reservations
        $recentReservations = Reservation::with('resource.category')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.user.overview', compact(
            'user', 
            'activeReservationsCount', 
            'pendingReservationsCount',
            'recentReservations'
        ));
    }

    /**
     * Display the user's reservations list.
     */
    public function reservations()
    {
        $user = Auth::user();
        
        $reservations = Reservation::with('resource.category')
            ->where('user_id', $user->id)
            ->orderByRaw("CASE WHEN status = 'Pending' THEN 0 ELSE 1 END")
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboard.user.reservations', compact('reservations'));
    }
}
