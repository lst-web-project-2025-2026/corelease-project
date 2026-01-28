<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Resource;
use App\Models\Application;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function __construct(
        protected \App\Services\SystemControlService $systemService,
        protected \App\Services\ApplicationService $applicationService,
        protected \App\Services\UserService $userService,
        protected \App\Services\NotificationService $notificationService
    ) {}

    /**
     * Process an application (Approve/Reject).
     */
    public function processVetting(Request $request, Application $application)
    {
        $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'admin_justification' => 'required|string|min:10',
        ]);

        try {
            $this->applicationService->process(
                $application,
                $request->status,
                $request->admin_justification,
                Auth::user()
            );

            return redirect()->back()->with('success', 'Application ' . strtolower($request->status) . ' successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error processing application: ' . $e->getMessage());
        }
    }

    /**
     * Change a user's role.
     */
    public function changeRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:User,Manager']);
        
        try {
            $this->userService->updateRole($user, $request->role, Auth::user());
            $this->notificationService->notify($user, 'Account Role Updated', "Your account role has been changed to {$request->role}.");
            return redirect()->back()->with('success', "User role updated to {$request->role}.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Toggle a user's active status.
     */
    public function toggleStatus(User $user)
    {
        try {
            $newStatus = !$user->is_active;
            $this->userService->toggleActivation($user, $newStatus, Auth::user());
            
            $msg = $newStatus ? 'enabled' : 'disabled';
            $this->notificationService->notify($user, 'Account Status Updated', "Your account has been {$msg} by an administrator.");
            
            return redirect()->back()->with('success', "User account {$msg} successfully.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the admin dashboard overview.
     */
    public function index()
    {
        $totalUsers = User::count();
        $totalResources = Resource::count();
        $pendingApplications = Application::where('status', 'Pending')->count();
        $totalLogs = AuditLog::count();

        // Get recent applications for the dashboard
        $recentApplications = Application::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.admin.overview', compact(
            'totalUsers',
            'totalResources',
            'pendingApplications',
            'totalLogs',
            'recentApplications'
        ));
    }

    /**
     * Display the vetting board (Pending only).
     */
    public function vetting()
    {
        $applications = Application::where('status', 'Pending')
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return view('dashboard.admin.vetting.pending', compact('applications'));
    }

    /**
     * Display all applications (History).
     */
    public function vettingAll()
    {
        $applications = Application::orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('dashboard.admin.vetting.all', compact('applications'));
    }

    /**
     * Display user management.
     */
    public function users()
    {
        $users = User::whereIn('role', ['User', 'Manager'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('dashboard.admin.users.index', compact('users'));
    }

    /**
     * Display the audit logs.
     */
    public function audit()
    {
        $logs = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('dashboard.admin.audit', compact('logs'));
    }


    /**
     * Display system settings.
     */
    public function settings()
    {
        $isMaintenanceEnabled = $this->systemService->isSystemLocked();
        return view('dashboard.admin.settings', compact('isMaintenanceEnabled'));
    }

    /**
     * Toggle maintenance mode.
     */
    public function toggleMaintenance(Request $request)
    {
        $enabled = $request->boolean('maintenance_mode');
        $this->systemService->toggleGlobalMaintenance($enabled);

        // Audit Log
        $actor = Auth::user();
        $auditService = app(\App\Services\AuditService::class);
        $auditService->log(
            $actor, 
            $enabled ? 'system_maintenance_enabled' : 'system_maintenance_disabled',
            \App\Models\Setting::where('key', 'facility_maintenance')->first(),
            ['value' => $enabled ? '0' : '1'],
            ['value' => $enabled ? '1' : '0']
        );

        return back()->with('message', 'System maintenance mode ' . ($enabled ? 'enabled' : 'disabled') . ' successfully.');
    }

}
