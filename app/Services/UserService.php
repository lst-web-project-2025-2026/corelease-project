<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function __construct(protected AuditService $auditService) {}

    /**
     * Update user role.
     */
    public function updateRole(User $user, string $role, User $actor): User
    {
        $oldValues = $user->getOriginal();
        $user->update(['role' => $role]);

        $this->auditService->log($actor, 'user_role_updated', $user, $oldValues, $user->toArray());

        return $user;
    }

    /**
     * Toggle user activation status.
     */
    public function toggleActivation(User $user, bool $active, User $actor): User
    {
        $oldValues = $user->getOriginal();
        $user->update(['is_active' => $active]);

        $this->auditService->log($actor, 'user_activation_toggled', $user, $oldValues, $user->toArray());

        return $user;
    }
}
