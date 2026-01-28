<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IncidentService
{
    public function __construct(
        protected AuditService $auditService,
        protected MaintenanceService $maintenanceService
    ) {}

    /**
     * Report an incident.
     */
    public function report(User $user, array $data): Incident
    {
        $reservation = Reservation::findOrFail($data['reservation_id']);

        // Rule: Can only report for active reservations
        if ($reservation->status !== 'Active') {
            throw ValidationException::withMessages(['reservation' => 'You can only report incidents for active reservations.']);
        }

        if ($reservation->user_id !== $user->id) {
            throw ValidationException::withMessages(['user' => 'Unauthorized.']);
        }

        $incident = Incident::create([
            'user_id' => $user->id,
            'resource_id' => $reservation->resource_id,
            'reservation_id' => $reservation->id,
            'description' => $data['description'],
            'status' => 'Open',
        ]);

        $this->auditService->log($user, 'incident_reported', $incident, null, $incident->toArray());

        return $incident;
    }

    /**
     * Resolve an incident via maintenance.
     */
    public function resolveViaMaintenance(Incident $incident, array $maintenanceData, User $manager): Incident
    {
        return DB::transaction(function () use ($incident, $maintenanceData, $manager) {
            $oldValues = $incident->getOriginal();
            
            // 1. Create Maintenance
            $this->maintenanceService->schedule($manager, array_merge($maintenanceData, [
                'resource_id' => $incident->resource_id,
                'resolve_conflicts' => true,
            ]));

            // 2. Resolve Incident
            $incident->update(['status' => 'Resolved']);

            $this->auditService->log($manager, 'incident_resolved_via_maintenance', $incident, $oldValues, $incident->toArray());

            return $incident;
        });
    }
}
