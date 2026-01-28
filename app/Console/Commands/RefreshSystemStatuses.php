<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use App\Models\Maintenance;
use App\Models\Resource;
use App\Services\ReservationService;
use App\Services\MaintenanceService;
use Carbon\Carbon;

class RefreshSystemStatuses extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:refresh-statuses';

    /**
     * The console command description.
     */
    protected $description = 'Refresh statuses for reservations, maintenance windows, and resource availability.';

    public function __construct(
        protected ReservationService $reservationService,
        protected MaintenanceService $maintenanceService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        // 1. Handle Maintenances
        $startingMaintenances = Maintenance::where('status', 'Scheduled')
            ->where('start_date', '<=', $now)
            ->get();

        foreach ($startingMaintenances as $m) {
            $m->update(['status' => 'In Progress']);
            $m->resource->update(['status' => 'Maintenance']);
            $this->info("Maintenance #{$m->id} started for resource #{$m->resource_id}.");
        }

        // In Progress -> Completed (Natural completion if end_date reached)
        $finishingMaintenances = Maintenance::where('status', 'In Progress')
            ->where('end_date', '<=', $now)
            ->get();

        foreach ($finishingMaintenances as $m) {
            $this->maintenanceService->complete($m);
            $this->info("Maintenance #{$m->id} completed naturally.");
        }

        // 2. Handle Reservations
        // Approved -> Active (if start_date reached)
        $activatingReservations = Reservation::where('status', 'Approved')
            ->where('start_date', '<=', $now)
            ->get();

        foreach ($activatingReservations as $r) {
            $r->update(['status' => 'Active']);
            $this->info("Reservation #{$r->id} is now Active.");
        }

        // Active -> Completed (Natural completion if end_date reached)
        // Note: We only complete ACTIVE ones to follow user requirements.
        // Approved ones that passed their end date are picked up by the step above first.
        $completingReservations = Reservation::where('status', 'Active')
            ->where('end_date', '<=', $now)
            ->get();

        foreach ($completingReservations as $r) {
            $this->reservationService->complete($r);
            $this->info("Reservation #{$r->id} completed naturally.");
        }

        // 3. Handle Expired Reservations
        // Pending -> Expired (if end_date passed)
        $expiredCount = Reservation::where('status', 'Pending')
            ->where('end_date', '<=', $now)
            ->update(['status' => 'Expired']);

        if ($expiredCount > 0) {
            $this->info("{$expiredCount} pending reservations marked as Expired.");
        }

        return Command::SUCCESS;
    }
}
