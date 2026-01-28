<?php

namespace App\Services;

use App\Models\Maintenance;
use App\Models\Resource;
use App\Models\User;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MaintenanceService
{
    public function __construct(
        protected AuditService $auditService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Schedule a maintenance window.
     */
    public function schedule(User $manager, array $data): Maintenance
    {
        return DB::transaction(function () use ($manager, $data) {
            $startDate = Carbon::parse($data['start_date'] ?? now())->startOfDay();
            $endDate = Carbon::parse($data['end_date'] ?? now()->addHour())->endOfDay();

            // Determine initial status based on start date
            $status = $data['status'] ?? 'Scheduled';
            if ($status === 'Scheduled' && $startDate->isPast() || $startDate->isToday()) {
                $status = 'In Progress';
            }

            $maintenance = Maintenance::create([
                'resource_id' => $data['resource_id'] ?? $data['resource']->id,
                'user_id' => $manager->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'description' => $data['description'],
                'status' => $status,
            ]);

            // If it's starting now or already active, update resource status
            if ($maintenance->status === 'In Progress') {
                $maintenance->resource->update(['status' => 'Maintenance']);
            }

            $this->auditService->log($manager, 'maintenance_scheduled', $maintenance, null, $maintenance->toArray());

            // Handle existing overlaps if requested
            if ($data['resolve_conflicts'] ?? false) {
                $this->cancelOverlappingReservations($maintenance, $manager);
            }

            return $maintenance;
        });
    }

    /**
     * Mark maintenance as completed (possibly ahead of time).
     */
    public function complete(Maintenance $maintenance, ?User $actor = null): Maintenance
    {
        return DB::transaction(function () use ($maintenance, $actor) {
            $oldValues = $maintenance->getOriginal();
            $maintenance->update([
                'status' => 'Completed',
                'end_date' => now() < $maintenance->end_date ? now() : $maintenance->end_date,
            ]);

            // Restore resource status if it was in maintenance
            if ($maintenance->resource->status === 'Maintenance') {
                $maintenance->resource->update(['status' => 'Enabled']);
            }

            $this->auditService->log($actor ?: auth()->user(), 'maintenance_completed', $maintenance, $oldValues, $maintenance->toArray());

            return $maintenance;
        });
    }

    /**
     * Cancel reservations that overlap with a maintenance window.
     */
    public function cancelOverlappingReservations(Maintenance $maintenance, User $manager): int
    {
        $overlapping = Reservation::where('resource_id', $maintenance->resource_id)
            ->whereIn('status', ['Pending', 'Approved', 'Active'])
            ->where(function ($query) use ($maintenance) {
                $query->whereBetween('start_date', [$maintenance->start_date, $maintenance->end_date])
                    ->orWhereBetween('end_date', [$maintenance->start_date, $maintenance->end_date])
                    ->orWhere(function ($q) use ($maintenance) {
                        $q->where('start_date', '<=', $maintenance->start_date)
                            ->where('end_date', '>=', $maintenance->end_date);
                    });
            })->get();

        foreach ($overlapping as $reservation) {
            $oldValues = $reservation->getOriginal();
            $reservation->update([
                'status' => 'Rejected',
                'manager_justification' => 'Cancelled due to scheduled maintenance: ' . $maintenance->description,
                'decided_by' => $manager->id,
            ]);

            $this->auditService->log($manager, 'reservation_cancelled_by_maintenance', $reservation, $oldValues, $reservation->toArray());
            $this->notificationService->notify(
                $reservation->user, 
                'Reservation Cancelled', 
                "Your reservation for {$reservation->resource->name} was cancelled due to maintenance."
            );
        }

        return $overlapping->count();
    }
}
