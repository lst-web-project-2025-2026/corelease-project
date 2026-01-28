<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Resource;
use App\Models\Setting;
use App\Models\Application;
use App\Models\Maintenance;
use App\Models\Reservation;
use App\Models\Incident;
use App\Models\Category;
use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Services
        $appService = app(\App\Services\ApplicationService::class);
        $inventoryService = app(\App\Services\InventoryService::class);
        $reservationService = app(\App\Services\ReservationService::class);
        $maintenanceService = app(\App\Services\MaintenanceService::class);
        $incidentService = app(\App\Services\IncidentService::class);
        $systemService = app(\App\Services\SystemControlService::class);
        $userService = app(\App\Services\UserService::class);
        $notificationService = app(\App\Services\NotificationService::class);

        // 2. Global Settings
        $systemService->toggleGlobalMaintenance(false);

        // 3. Create Categories
        $categories = [
            'Rack Server' => ['CPU Processor', 'Physical RAM', 'Storage Capacity', 'Rack Location', 'Operating System'],
            'Virtual Machine' => ['vCPU Cores', 'Memory (GB)', 'Disk Quota', 'allow_os'],
            'Cloud Storage' => ['S3 Bucket Size', 'Storage Type', 'Target IOPS'],
            'Network Switch' => ['Throughput', 'Port Density'],
        ];

        $categoryModels = [];
        foreach ($categories as $name => $specs) {
            $categoryModels[$name] = Category::create([
                'name' => $name,
                'specs' => $specs,
            ]);
        }

        // 4. Seed Admins
        $admins = [];
        $fixedAdmin = User::create([
            "name" => "Data Center Director",
            "email" => "admin@dcrms.com",
            "password" => Hash::make("password"),
            "role" => "Admin",
            "profession" => "Principal Infrastructure Architect",
            "is_active" => true,
        ]);
        $admins[] = $fixedAdmin;
        $admins[] = User::factory()->admin()->create(['name' => 'Lead Support Engineer']);

        // 5. Seed Managers
        $managers = [];
        $fixedManagerApp = Application::create([
            'name' => 'Data Center Manager',
            'email' => 'manager@dcrms.com',
            'password' => Hash::make('password'),
            'profession' => 'Datacenter Operations Manager',
            'user_justification' => 'System default manager account.',
            'status' => 'Approved',
            'decided_by' => $fixedAdmin->id
        ]);
        $fixedManager = User::create([
            'application_id' => $fixedManagerApp->id,
            'name' => 'Operations Head',
            'email' => 'manager@dcrms.com',
            'password' => Hash::make('password'),
            'profession' => 'Datacenter Operations Manager',
            'role' => 'Manager',
            'is_active' => true,
        ]);
        $managers[] = $fixedManager;

        for ($i = 0; $i < 15; $i++) {
            $app = Application::factory()->create(['status' => 'Pending']);
            $appService->process($app, 'Approved', 'Promoted to Technical Manager.', $fixedAdmin);
            $u = User::where('application_id', $app->id)->first();
            $userService->updateRole($u, 'Manager', $fixedAdmin);
            $managers[] = $u;
        }

        // 6. Create Resources (Synthetically Realistic)
        $resourcePool = [
            'Rack Server' => [
                'names' => ['Dell PowerEdge R750', 'HPE ProLiant DL380', 'Lenovo ThinkSystem SR650'],
                'cpus' => ['2x Intel Xeon Gold 6330', '2x AMD EPYC 7763', '2x Intel Xeon Platinum 8380'],
                'rams' => ['512GB DDR4', '1TB DDR4', '256GB DDR4'],
                'storage' => ['3.84TB NVMe SSD', '7.68TB SAS SSD', '15.36TB HDD Array'],
                'os' => ['RHEL 9.2', 'Windows Server 2022', 'Ubuntu 22.04 LTS', 'VMware ESXi']
            ],
            'Virtual Machine' => [
                'names' => ['Compute-Inst', 'Worker-Node', 'DB-Cluster-Member'],
            ],
            'Cloud Storage' => [
                'names' => ['Primary-Bucket', 'Backup-Vault', 'Media-Assets'],
                'types' => ['Object Storage', 'Block Storage', 'File System']
            ],
            'Network Switch' => [
                'names' => ['Cisco Nexus 9300', 'Arista 7050X', 'Juniper QFX5120'],
                'ports' => ['48x 100G', '64x 40G', '32x 400G']
            ]
        ];

        for ($i = 0; $i < 200; $i++) {
            $catName = array_rand($resourcePool);
            $category = $categoryModels[$catName];
            $pool = $resourcePool[$catName];
            
            $specs = [];
            foreach ($category->specs as $spec) {
                $specs[$spec] = match($spec) {
                    'CPU Processor' => $pool['cpus'][array_rand($pool['cpus'])],
                    'vCPU Cores' => rand(4, 128),
                    'Physical RAM' => $pool['rams'][array_rand($pool['rams'])],
                    'Memory (GB)' => fake()->randomElement([32, 64, 128, 256, 512]),
                    'Storage Capacity', 'Disk Quota', 'S3 Bucket Size' => rand(100, 50000) . ' GB',
                    'Rack Location' => 'DC-01-A' . rand(1, 9) . '-U' . rand(1, 42),
                    'Operating System' => $pool['os'][array_rand($pool['os'])],
                    'allow_os' => true,
                    'Storage Type' => $pool['types'][array_rand($pool['types'])],
                    'Target IOPS' => rand(1000, 500000),
                    'Throughput' => rand(10, 800) . ' Gbps',
                    'Port Density' => $pool['ports'][array_rand($pool['ports'])],
                    default => fake()->word()
                };
            }

            $inventoryService->saveResource([
                'name' => $pool['names'][array_rand($pool['names'])] . " - Unit " . ($i + 1),
                'category_id' => $category->id,
                'specs' => $specs,
                'status' => rand(1, 100) <= 5 ? 'Disabled' : 'Enabled',
                'supervisor_id' => $managers[array_rand($managers)]->id,
            ]);
        }
        $allResources = Resource::all();

        // 7. Users
        $users = [];
        $apps = Application::factory(120)->create(['status' => 'Pending']);
        foreach ($apps as $app) {
            $r = rand(1, 10);
            if ($r <= 6) {
                $appService->process($app, 'Approved', 'Identity and profession verified.', $fixedAdmin);
                $users[] = User::where('application_id', $app->id)->first();
            } elseif ($r <= 8) {
                $appService->process($app, 'Rejected', 'Insufficient professional background provided.', $fixedAdmin);
            }
            // 20% remain Pending
        }
        
        $fixedUserApp = Application::create([
            'name' => 'Demo Researcher',
            'email' => 'user@dcrms.com',
            'password' => Hash::make('password'),
            'profession' => 'Senior Research Fellow',
            'user_justification' => 'Seed user for platform demonstration.',
            'status' => 'Pending'
        ]);
        $appService->process($fixedUserApp, 'Approved', 'Account approved for demo.', $fixedAdmin);
        $fixedUser = User::where('email', 'user@dcrms.com')->first();
        $users[] = $fixedUser;

        // 8. Historical Data (Maintenances & Reservations)
        // Maintenances
        foreach ($allResources->random(80) as $res) {
            $m = $managers[array_rand($managers)];
            
            // 1 Past (Completed)
            $maintenanceService->schedule($m, [
                'resource_id' => $res->id,
                'start_date' => now()->subDays(rand(30, 60))->format('Y-m-d'),
                'end_date' => now()->subDays(rand(20, 29))->format('Y-m-d'),
                'description' => 'Periodic cooling system inspection and dust removal.',
                'status' => 'Completed',
            ]);

            // 1 Current or Recent
            $recentStatus = rand(1, 10) > 7 ? 'In Progress' : 'Completed';
            $maintenanceService->schedule($m, [
                'resource_id' => $res->id,
                'start_date' => now()->subDays(rand(1, 5))->format('Y-m-d'),
                'end_date' => $recentStatus === 'Completed' ? now()->subDay()->format('Y-m-d') : now()->addDays(rand(1, 3))->format('Y-m-d'),
                'description' => 'Firmware update for critical security advisory.',
                'status' => $recentStatus,
            ]);

            // 1 Future
            $maintenanceService->schedule($m, [
                'resource_id' => $res->id,
                'start_date' => now()->addDays(rand(10, 40))->format('Y-m-d'),
                'end_date' => now()->addDays(rand(41, 45))->format('Y-m-d'),
                'description' => 'Scheduled UPS battery replacement in the rack zone.',
                'status' => 'Scheduled',
            ]);
        }

        // Reservations
        foreach ($users as $u) {
            $count = rand(10, 15);
            for ($i = 0; $i < $count; $i++) {
                try {
                    $resource = $allResources->random();
                    $timeframe = fake()->randomElement(['past', 'past', 'present', 'future']);
                    
                    [$start, $end] = match($timeframe) {
                        'past' => [now()->subDays(rand(20, 100)), now()->subDays(rand(1, 19))],
                        'present' => [now()->subDays(rand(1, 3)), now()->addDays(rand(2, 6))],
                        'future' => [now()->addDays(rand(5, 60)), now()->addDays(rand(61, 80))],
                    };

                    $res = $reservationService->create($u, [
                        'resource_id' => $resource->id,
                        'start_date' => $start->format('Y-m-d'),
                        'end_date' => $end->format('Y-m-d'),
                        'user_justification' => 'Historical simulation data for research and testing.',
                        'configuration' => $resource->category->name === 'Virtual Machine' ? ['os' => 'Ubuntu 22.04 LTS'] : [],
                        'status' => 'Pending',
                    ], true); // BYPASS DATE CHECKS

                    // 8b. Simulate Decision
                    $timeframeAction = fake()->randomElement(['Approved', 'Approved', 'Rejected', 'Pending']); // Added Pending chance
                    if ($timeframe === 'past' || $timeframe === 'present') {
                        // For past, if it stays Pending, it will become Expired
                        $timeframeAction = fake()->randomElement(['Approved', 'Approved', 'Approved', 'Rejected', 'Pending']);
                    }

                    if ($timeframeAction === 'Approved') {
                        $reservationService->updateStatus($res, 'Approved', 'Auto-vetted for historical simulation.', $managers[array_rand($managers)]);
                        
                        // 8c. Transition to Active/Completed if time allows
                        if ($start <= now()) {
                            $res->update(['status' => 'Active']);
                            
                            if ($timeframe === 'past' && rand(1, 10) > 2) {
                                $reservationService->complete($res, $managers[array_rand($managers)]);
                            }
                        }
                    } else {
                        $reservationService->updateStatus($res, 'Rejected', 'Resource constraints or priority mismatch.', $managers[array_rand($managers)]);
                    }

                    // 8d. Add some historical incidents ONLY to approved/active/completed ones
                    if (in_array($res->status, ['Active', 'Completed']) && rand(1, 10) > 8) {
                        // Temporary status for report validation if it was completed
                        $originalStatus = $res->status;
                        $res->update(['status' => 'Active']);
                        
                        $incident = $incidentService->report($u, [
                            'reservation_id' => $res->id,
                            'description' => fake()->randomElement([
                                'Network connectivity dropped intermittently.',
                                'Disk I/O latency is higher than expected.',
                                'Node kernel panic occurred twice today.',
                                'Fan noise is excessively loud.',
                                'Unable to SSH into the instance.'
                            ]),
                        ]);
                        
                        $res->update(['status' => $originalStatus]);
                        
                        if (rand(1, 10) > 4) {
                            $incidentService->resolveViaMaintenance($incident, [
                                'start_date' => $res->start_date->format('Y-m-d'),
                                'end_date' => $res->start_date->format('Y-m-d'),
                                'description' => 'Fixed hardware issue reported in incident.',
                            ], $managers[array_rand($managers)]);
                        }
                    }

                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        // 9. Notifications noise
        foreach (collect($users)->random(min(count($users), 20)) as $u) {
            for ($i = 0; $i < 5; $i++) {
                $n = $notificationService->notify($u, 'System Update', 'A new security patch has been applied to the cluster.');
                if (rand(1, 10) > 5) {
                    $n->update(['status' => 'read']);
                }
            }
        }

        // 10. Final Status Sync
        \Illuminate\Support\Facades\Artisan::call('app:refresh-statuses');
    }
}
