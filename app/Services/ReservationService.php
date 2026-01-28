<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Resource;
use App\Models\User;
use App\Models\Maintenance;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ReservationService
{
    public function __construct(
        protected AuditService $auditService,
        protected NotificationService $notificationService,
        protected SystemControlService $systemControlService
    ) {}

    /**
     * Create a new reservation.
     */
    public function create(User $user, array $data, bool $bypassDateCheck = false): Reservation
    {
        return DB::transaction(function () use ($user, $data, $bypassDateCheck) {
            $resource = Resource::findOrFail($data['resource_id']);
            
            $parse = fn($s) => ($d = \DateTime::createFromFormat('Y-m-d', $s)) && $d->format('Y-m-d') === $s ? Carbon::instance($d) : null;
            
            $startDate = $parse($data['start_date'])?->startOfDay();
            $endDate = $parse($data['end_date'])?->endOfDay();

            if (!$startDate || !$endDate) {
                throw ValidationException::withMessages([
                    'start_date' => 'The provided date format is invalid.'
                ]);
            }

            // 1. Check Conflicts
            $this->validateConflict($resource, $startDate, $endDate, null, $bypassDateCheck);

            // 2. Validate Dates (unless bypassed)
            if (!$bypassDateCheck) {
                if ($startDate->isPast()) {
                    throw ValidationException::withMessages(['start_date' => 'Start date must be in the future.']);
                }
                if ($endDate->lte($startDate)) {
                    throw ValidationException::withMessages(['end_date' => 'End date must be after start date.']);
                }
            }

            // 3. Create Reservation
            $reservation = Reservation::create([
                'user_id' => $user->id,
                'resource_id' => $resource->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'user_justification' => $data['user_justification'],
                'configuration' => $data['configuration'] ?? [],
                'status' => $data['status'] ?? 'Pending',
            ]);

            $this->auditService->log($user, 'reservation_created', $reservation, null, $reservation->toArray());

            return $reservation;
        });
    }

    /**
     * Mark a reservation as completed (possibly ahead of time).
     */
    public function complete(Reservation $reservation, ?User $actor = null): Reservation
    {
        return DB::transaction(function () use ($reservation, $actor) {
            if ($reservation->status !== 'Active' && $reservation->status !== 'Approved') {
                throw ValidationException::withMessages(['status' => 'Only approved or active reservations can be completed.']);
            }

            $oldValues = $reservation->getOriginal();
            $reservation->update([
                'status' => 'Completed',
                'end_date' => now() < $reservation->end_date ? now() : $reservation->end_date,
            ]);

            $this->auditService->log($actor ?: auth()->user(), 'reservation_completed', $reservation, $oldValues, $reservation->toArray());

            return $reservation;
        });
    }

    /**
     * Cancel a reservation (User-initiated).
     */
    public function cancel(Reservation $reservation, User $actor): Reservation
    {
        return DB::transaction(function () use ($reservation, $actor) {
            // Only Pending reservations can be cancelled by the user
            if ($reservation->status !== 'Pending') {
                throw ValidationException::withMessages(['status' => 'Only pending reservations can be cancelled.']);
            }

            if ($reservation->user_id !== $actor->id) {
                throw ValidationException::withMessages(['auth' => 'You do not have permission to cancel this reservation.']);
            }

            $oldValues = $reservation->getOriginal();
            $reservation->update(['status' => 'Cancelled']);

            $this->auditService->log($actor, 'reservation_cancelled', $reservation, $oldValues, $reservation->toArray());

            return $reservation;
        });
    }


    /**
     * Approve or Reject a reservation.
     */
    public function updateStatus(Reservation $reservation, string $status, string $justification, User $manager): Reservation
    {
        return DB::transaction(function () use ($reservation, $status, $justification, $manager) {
            if ($reservation->status === 'Expired') {
                throw ValidationException::withMessages(['status' => 'This reservation has expired and cannot be processed.']);
            }
            if ($reservation->status === 'Completed') {
                throw ValidationException::withMessages(['status' => 'This reservation is already completed.']);
            }

            $oldValues = $reservation->getOriginal();

            // Re-verify conflicts for approval (in case someone else booked in the meantime)
            if ($status === 'Approved') {
                $this->validateConflict($reservation->resource, $reservation->start_date, $reservation->end_date, $reservation->id);
            }

            $reservation->update([
                'status' => $status,
                'manager_justification' => $justification,
                'decided_by' => $manager->id,
            ]);

            $this->auditService->log($manager, 'reservation_status_updated', $reservation, $oldValues, $reservation->toArray());

            $this->notificationService->notify(
                $reservation->user, 
                "Reservation {$status}", 
                "Your reservation for {$reservation->resource->name} has been {$status}."
            );

            return $reservation;
        });
    }

    /**
     * The Conflict Prevention Algorithm.
     */
    public function validateConflict(Resource $resource, Carbon $start, Carbon $end, ?int $excludeId = null, bool $bypassDateCheck = false): void
    {
        // Rule 1: Global Lock
        if (!$bypassDateCheck && $this->systemControlService->isSystemLocked()) {
            throw ValidationException::withMessages(['system' => 'The facility is under global maintenance. No new reservations allowed.']);
        }

        // Rule 2: Resource Status
        if (!$bypassDateCheck && $resource->status === 'Disabled') {
            throw ValidationException::withMessages(['resource' => "This resource is manually disabled and cannot be booked."]);
        }

        // Rule 3: Maintenance Overlaps
        if (!$bypassDateCheck) {
            $maintenanceOverlap = Maintenance::where('resource_id', $resource->id)
                ->whereIn('status', ['Scheduled', 'In Progress'])
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('start_date', [$start, $end])
                        ->orWhereBetween('end_date', [$start, $end])
                        ->orWhere(function ($q2) use ($start, $end) {
                            $q2->where('start_date', '<=', $start)
                                ->where('end_date', '>=', $end);
                        });
                })
                ->exists();

            if ($maintenanceOverlap) {
                throw ValidationException::withMessages(['maintenance' => 'This resource has a scheduled maintenance window during your requested period.']);
            }
        }

        // Rule 4: Reservation Overlaps
        $query = Reservation::where('resource_id', $resource->id)
            ->whereIn('status', ['Approved', 'Active', 'Completed'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if (!$bypassDateCheck && $query->exists()) {
            throw ValidationException::withMessages(['reservation' => 'This resource is already reserved during your requested period.']);
        }
    }
}
