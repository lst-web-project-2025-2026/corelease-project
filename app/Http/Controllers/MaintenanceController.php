<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    /**
     * Display the maintenance schedule page.
     */
    public function schedule()
    {
        $today = Carbon::today();

        // Current: All ongoing maintenance (start date <= today <= end date)
        $currentMaintenance = Maintenance::with('resource.category')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where('status', '!=', 'Completed')
            ->orderBy('start_date', 'asc')
            ->get();

        // Future: Upcoming maintenance (start date > today), limited to 10
        $futureMaintenance = Maintenance::with('resource.category')
            ->where('start_date', '>', $today)
            ->where('status', 'Scheduled')
            ->orderBy('start_date', 'asc')
            ->limit(10)
            ->get();

        // Past: Recently completed maintenance (end date < today), limited to 10
        $pastMaintenance = Maintenance::with('resource.category')
            ->where(function ($query) use ($today) {
                $query->where('end_date', '<', $today)
                    ->orWhere('status', 'Completed');
            })
            ->orderBy('end_date', 'desc')
            ->limit(10)
            ->get();

        return view('maintenance.schedule', compact('currentMaintenance', 'futureMaintenance', 'pastMaintenance'));
    }
}
