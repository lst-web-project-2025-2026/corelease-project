<?php

namespace App\Services;

use App\Models\Resource;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function __construct(protected AuditService $auditService) {}

    /**
     * Create or update a resource.
     */
    public function saveResource(array $data, ?Resource $resource = null): Resource
    {
        return DB::transaction(function () use ($data, $resource) {
            $category = Category::findOrFail($data['category_id']);
            $this->validateSpecs($category, $data['specs'] ?? []);

            $isNew = !$resource;
            $oldValues = $resource ? $resource->getOriginal() : null;

            if ($isNew) {
                $resource = new Resource();
            }

            $resource->fill([
                'name' => $data['name'],
                'category_id' => $data['category_id'],
                'specs' => $data['specs'],
                'status' => $data['status'] ?? 'Enabled',
                'supervisor_id' => $data['supervisor_id'] ?? null,
            ]);

            $resource->save();

            $this->auditService->log(
                auth()->user(), 
                $isNew ? 'resource_created' : 'resource_updated', 
                $resource, 
                $oldValues, 
                $resource->toArray()
            );

            return $resource;
        });
    }

    /**
     * Toggle resource status.
     */
    public function toggleStatus(Resource $resource, string $status, ?User $actor = null): Resource
    {
        $oldValues = $resource->getOriginal();
        $resource->update(['status' => $status]);

        $this->auditService->log($actor ?: auth()->user(), 'resource_status_toggled', $resource, $oldValues, $resource->toArray());

        return $resource;
    }

    /**
     * Validate JSON specs against category requirements.
     */
    protected function validateSpecs(Category $category, array $specs): void
    {
        $requiredKeys = $category->specs; // This is an array of keys from the seeder/migration

        foreach ($requiredKeys as $key) {
            if (!isset($specs[$key]) || $specs[$key] === '') {
                throw ValidationException::withMessages([
                    'specs' => ["The specification field '{$key}' is required for category '{$category->name}'."]
                ]);
            }
        }
    }
}
