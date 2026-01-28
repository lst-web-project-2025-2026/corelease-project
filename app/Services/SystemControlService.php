<?php

namespace App\Services;

use App\Models\Setting;

class SystemControlService
{
    /**
     * Toggle the global facility maintenance mode.
     */
    public function toggleGlobalMaintenance(bool $enabled): void
    {
        Setting::updateOrCreate(
            ['key' => 'facility_maintenance'],
            ['value' => $enabled ? '1' : '0']
        );
    }

    /**
     * Check if the facility is under global maintenance.
     */
    public function isSystemLocked(): bool
    {
        $setting = Setting::where('key', 'facility_maintenance')->first();
        return $setting && $setting->value === '1';
    }
}
