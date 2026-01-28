<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\User;
use App\Models\Reservation;
use App\Services\SystemControlService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(protected SystemControlService $systemService) {}

    public function index()
    {
        $totalResources = Resource::count();
        $activeReservationsCount = Reservation::where('status', 'Active')->count();
        $maintenanceCount = Resource::where('status', 'Maintenance')->count();
        $disabledCount = Resource::where('status', 'Disabled')->count();
        
        // Available now = Total - (Active Reservations + Maintenance + Disabled)
        $availableNow = $totalResources - ($activeReservationsCount + $maintenanceCount + $disabledCount);
        $activeUsers = User::where('is_active', true)->count();
        
        $systemStatus = $this->systemService->isSystemLocked() ? 'Maintenance' : 'Operational';

        return view('welcome', compact('totalResources', 'availableNow', 'activeUsers', 'systemStatus'));
    }
}
