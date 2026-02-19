<?php

namespace App\Services;

use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ApplicationService
{
    public function __construct(
        protected AuditService $auditService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Submit a new application.
     */
    public function submit(array $data): Application
    {
        return DB::transaction(function () use ($data) {
            $application = Application::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'profession' => $data['profession'],
                'user_justification' => $data['user_justification'],
                'status' => 'Pending',
            ]);

            $this->auditService->log(null, 'application_submitted', $application, null, $application->toArray());

            return $application;
        });
    }

    /**
     * Process an application (Approve/Reject).
     */
    public function process(Application $application, string $status, string $justification, User $admin): Application
    {
        return DB::transaction(function () use ($application, $status, $justification, $admin) {
            $oldValues = $application->getOriginal();
            
            $application->update([
                'status' => $status,
                'admin_justification' => $justification,
                'decided_by' => $admin->id,
            ]);

            $this->auditService->log($admin, 'application_processed', $application, $oldValues, $application->toArray());

            if ($status === 'Approved') {
                $user = User::create([
                    'application_id' => $application->id,
                    'name' => $application->name,
                    'email' => $application->email,
                    'password' => $application->password, // Already hashed
                    'profession' => $application->profession,
                    'role' => 'User',
                    'is_active' => true,
                ]);

                $this->auditService->log($admin, 'user_created_from_application', $user, null, $user->toArray());
                $this->notificationService->notify($user, 'Application Approved', 'Your account has been approved. You can now log in.');
            }

            return $application;
        });
    }
}
