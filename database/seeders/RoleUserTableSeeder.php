<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Admin\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleUserTableSeeder extends Seeder
{
    private const ROLE_IDS = [
        UserType::Owner->value => 1,
        UserType::Agent->value => 2,
        UserType::SubAgent->value => 3,
        UserType::Player->value => 4,
        UserType::SystemWallet->value => 5,
    ];

    private const ROLE_NAMES = [
        UserType::Owner->value => 'Owner',
        UserType::Agent->value => 'Agent',
        UserType::SubAgent->value => 'SubAgent',
        UserType::Player->value => 'Player',
        UserType::SystemWallet->value => 'SystemWallet',
    ];

    public function run(): void
    {
        try {
            DB::beginTransaction();

            // Validate roles exist
            $this->validateRoles();

            // Clean up existing role assignments
            $this->cleanupExistingAssignments();

            $totalUsers = 0;
            $successCount = 0;

            foreach (self::ROLE_IDS as $userType => $roleId) {
                $users = User::where('type', $userType)->get();
                $totalUsers += $users->count();

                if ($users->isEmpty()) {
                    Log::warning("No users found for type: {$userType}");

                    continue;
                }

                // Bulk assign roles
                $users->each(function ($user) use ($roleId, &$successCount) {
                    try {
                        $user->roles()->sync($roleId);
                        $successCount++;
                        Log::info("Assigned role '{$roleId}' to user: {$user->user_name}");
                    } catch (\Exception $e) {
                        Log::error("Failed to assign role '{$roleId}' to user {$user->user_name}: ".$e->getMessage());
                    }
                });

                Log::info("Successfully assigned role '{$roleId}' to {$users->count()} users");
            }

            // Verify role assignments
            $this->verifyRoleAssignments($totalUsers, $successCount);

            DB::commit();
            Log::info("Role assignment completed successfully. Total users: {$totalUsers}, Successful assignments: {$successCount}");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in RoleUserTableSeeder: '.$e->getMessage());
            throw $e;
        }
    }

    private function validateRoles(): void
    {
        $existingRoles = Role::whereIn('id', array_values(self::ROLE_IDS))->pluck('id')->toArray();
        $missingRoles = array_diff(array_values(self::ROLE_IDS), $existingRoles);

        if (! empty($missingRoles)) {
            throw new \RuntimeException('Missing required roles with IDs: '.implode(', ', $missingRoles));
        }
    }

    private function cleanupExistingAssignments(): void
    {
        try {
            DB::table('role_user')->truncate();
            Log::info('Cleaned up existing role assignments');
        } catch (\Exception $e) {
            Log::error('Failed to cleanup existing role assignments: '.$e->getMessage());
            throw $e;
        }
    }

    private function verifyRoleAssignments(int $totalUsers, int $successCount): void
    {
        if ($successCount !== $totalUsers) {
            Log::warning("Role assignment verification failed. Expected: {$totalUsers}, Actual: {$successCount}");
            throw new \RuntimeException('Role assignment verification failed. Some users may not have received their roles.');
        }

        // Verify each user has exactly one role
        $usersWithMultipleRoles = DB::table('role_user')
            ->select('user_id', DB::raw('COUNT(*) as count'))
            ->groupBy('user_id')
            ->having(DB::raw('COUNT(*)'), '>', 1)
            ->get();

        if ($usersWithMultipleRoles->isNotEmpty()) {
            Log::error('Found users with multiple roles: '.$usersWithMultipleRoles->pluck('user_id')->implode(', '));
            throw new \RuntimeException('Some users have multiple roles assigned. This should not happen.');
        }
    }
}
