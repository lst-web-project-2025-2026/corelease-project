<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\Reservation;
use App\Models\Maintenance;
use App\Models\Incident;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerController extends Controller
{
    public function __construct(protected \App\Services\MaintenanceService $maintenanceService) {}

    /**
     * Display the manager dashboard overview.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Overview counts limited to supervised resources
        $pendingRequestsCount = Reservation::where('status', 'Pending')
            ->whereHas('resource', function($q) use ($user) {
                $q->where('supervisor_id', $user->id);
            })->count();

        $totalResourcesCount = Resource::where('supervisor_id', $user->id)->count();

        $openIncidentsCount = Incident::where('status', 'Open')
            ->whereHas('resource', function($q) use ($user) {
                $q->where('supervisor_id', $user->id);
            })->count();

        $activeMaintenancesCount = Maintenance::where('end_date', '>=', now())
            ->whereHas('resource', function($q) use ($user) {
                $q->where('supervisor_id', $user->id);
            })->count();

        // Get 5 most recent personal reservations
        $recentPersonalReservations = Reservation::with(['resource.category'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.manager.overview', compact(
            'pendingRequestsCount',
            'totalResourcesCount',
            'openIncidentsCount',
            'activeMaintenancesCount',
            'recentPersonalReservations'
        ));
    }

    /**
     * Display the pending requests queue and history.
     */
    public function approvals(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'pending');

        $query = Reservation::with(['user', 'resource.category'])
            ->whereHas('resource', function($q) use ($user) {
                $q->where('supervisor_id', $user->id);
            });

        if ($tab === 'history') {
            $query->whereIn('status', ['Approved', 'Rejected'])->orderBy('updated_at', 'desc');
        } else {
            $query->where('status', 'Pending')->orderBy('created_at', 'desc');
        }

        $requests = $query->paginate(15);

        return view('dashboard.manager.approvals', compact('requests', 'tab'));
    }

    /**
     * Moderate a reservation request.
     */
    public function moderateReservation(Reservation $reservation, Request $request)
    {
        $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'justification' => 'required|string|min:5|max:1000',
        ]);

        $user = Auth::user();
        $status = $request->get('status');
        $justification = $request->get('justification');
        $oldStatus = $reservation->status;

        // 1. Update Reservation
        $reservation->update([
            'status' => $status,
            'decided_by' => $user->id,
            'manager_justification' => $justification
        ]);

        // 2. Create Audit Log
        AuditLog::create([
            'auditable_type' => Reservation::class,
            'auditable_id' => $reservation->id,
            'user_id' => $user->id,
            'event' => 'moderated',
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $status, 'manager_justification' => $justification],
        ]);

        // 3. Create Notification for the user
        Notification::create([
            'user_id' => $reservation->user_id,
            'title' => "Reservation " . $status,
            'content' => "Your reservation for " . $reservation->resource->name . " has been " . strtolower($status) . ". Manager comment: " . $justification,
            'status' => 'unread'
        ]);

        return redirect()->back()->with('success', "Reservation request " . strtolower($status) . " successfully.");
    }

    /**
     * Display the resource creation form.
     */
    public function createResource()
    {
        $categories = Category::all();
        return view('dashboard.manager.resource-create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeResource(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'specs' => 'required|array',
        ]);

        $category = Category::findOrFail($request->category_id);
        
        // Ensure all required specs for the category are present
        foreach ($category->specs as $reqSpec) {
            if (!isset($request->specs[$reqSpec]) || empty($request->specs[$reqSpec])) {
                return redirect()->back()->withErrors(['specs' => "The {$reqSpec} specification is required."])->withInput();
            }
        }

        Resource::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'specs' => $request->specs,
            'status' => 'Enabled',
            'supervisor_id' => Auth::id(),
        ]);

        return redirect()->route('dashboard.manager.inventory')->with('success', 'Resource created successfully.');
    }

    /**
     * Toggle the status of a resource between Enabled and Disabled.
     */
    public function toggleStatus(Resource $resource)
    {
        $newStatus = $resource->status === 'Enabled' ? 'Disabled' : 'Enabled';
        $resource->update(['status' => $newStatus]);

        return redirect()->back()->with('success', "Resource status updated to {$newStatus}.");
    }

    /**
     * Show the form for scheduling maintenance on a resource.
     */
    public function createMaintenance(Resource $resource)
    {
        return view('dashboard.manager.resource-maintenance', compact('resource'));
    }

    /**
     * Store a new maintenance record.
     */
    public function storeMaintenance(Request $request, Resource $resource)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'start_date' => 'required|string',
            'end_date' => 'required|string',
            'description' => 'required|string|min:10',
        ]);

        $validator->after(function ($validator) use ($request, $resource) {
            $startStr = $request->input('start_date');
            $endStr = $request->input('end_date');

            $safeParse = function ($str) {
                if (!$str) return null;
                $d = \DateTime::createFromFormat('Y-m-d', $str);
                return ($d && $d->format('Y-m-d') === $str) ? \Carbon\Carbon::instance($d) : null;
            };

            $start = $safeParse($startStr);
            $end = $safeParse($endStr);

            if ($startStr && !$start) {
                $validator->errors()->add('start_date', 'The start date is not a valid date format (Y-m-d).');
            } elseif ($start) {
                if ($start->isPast() && !$start->isToday()) {
                    $validator->errors()->add('start_date', 'The start date must be today or future.');
                }
            }

            if ($endStr && !$end) {
                $validator->errors()->add('end_date', 'The end date is not a valid date format (Y-m-d).');
            } elseif ($end && $start) {
                if ($end->lt($start)) {
                    $validator->errors()->add('end_date', 'The end date must be after or equal to the start date.');
                }
            }

            // Overlap Check
            if ($start && $end) {
                // 1. Check for existing Maintenances
                $overlapMaintenance = Maintenance::where('resource_id', $resource->id)
                    ->where(function($query) use ($start, $end) {
                        $query->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
                              ->orWhereBetween('end_date', [$start->toDateString(), $end->toDateString()])
                              ->orWhere(function($inner) use ($start, $end) {
                                  $inner->where('start_date', '<=', $start->toDateString())
                                        ->where('end_date', '>=', $end->toDateString());
                              });
                    })->exists();

                if ($overlapMaintenance) {
                    $validator->errors()->add('start_date', 'A maintenance window is already scheduled for this resource during the selected period.');
                }

                // 2. Check for existing Active/Approved Reservations
                $overlapReservation = \App\Models\Reservation::where('resource_id', $resource->id)
                    ->whereIn('status', ['Approved', 'Active'])
                    ->where(function($query) use ($start, $end) {
                        $query->whereBetween('start_date', [$start->toDateString(), $end->toDateString()])
                              ->orWhereBetween('end_date', [$start->toDateString(), $end->toDateString()])
                              ->orWhere(function($inner) use ($start, $end) {
                                  $inner->where('start_date', '<=', $start->toDateString())
                                        ->where('end_date', '>=', $end->toDateString());
                              });
                    })->exists();

                if ($overlapReservation) {
                    $validator->errors()->add('start_date', 'This resource has active or approved reservations during the selected period. Maintenance cannot be scheduled.');
                }
            }
        });

        $validated = $validator->validate();

        $this->maintenanceService->schedule(Auth::user(), array_merge($validated, [
            'resource_id' => $resource->id
        ]));

        return redirect()->route('dashboard.manager.inventory')->with('success', 'Maintenance scheduled successfully.');
    }

    /**
     * Display the inventory management page.
     */
    public function inventory()
    {
        $user = Auth::user();
        $resources = Resource::with('category')
            ->where('supervisor_id', $user->id)
            ->orderBy('name', 'asc')
            ->paginate(15);

        return view('dashboard.manager.inventory', compact('resources'));
    }

    /**
     * Display the maintenance calendar/list.
     */
    public function maintenance(Request $request)
    {
        $user = Auth::user();

        $maintenances = Maintenance::with(['resource', 'user'])
            ->whereHas('resource', function($q) use ($user) {
                $q->where('supervisor_id', $user->id);
            })
            ->orderBy('start_date', 'desc')
            ->paginate(15);

        return view('dashboard.manager.maintenance', compact('maintenances'));
    }

    /**
     * Display the incidents feed.
     */
    public function incidents(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'open');

        $query = Incident::with(['user', 'resource.category'])
            ->whereHas('resource', function($q) use ($user) {
                $q->where('supervisor_id', $user->id);
            });

        if ($tab === 'open') {
            $incidents = $query->where('incidents.status', 'Open')
                ->whereNull('incidents.deleted_at')
                ->orderBy('incidents.created_at', 'asc')
                ->paginate(15);
        } else {
            $incidents = $query->orderBy('incidents.created_at', 'desc')->paginate(15);
        }

        return view('dashboard.manager.incidents', compact('incidents', 'tab'));
    }

    /**
     * Resolve an incident.
     */
    public function resolveIncident(Incident $incident)
    {
        $incident->update(['status' => 'Resolved']);
        return redirect()->back()->with('success', 'Incident marked as resolved.');
    }
}
