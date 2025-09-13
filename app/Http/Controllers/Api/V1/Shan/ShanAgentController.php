<?php

namespace App\Http\Controllers\Api\V1\Shan;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShanAgentController extends Controller
{
    use HttpResponses;

    /**
     * Get all users that belong to a specific agent
     */
    public function getUsersByAgent(Request $request): JsonResponse
    {
        // Validate the request
        $validated = $request->validate([
            'agent_id' => 'required|integer|exists:users,id',
        ]);

        $agentId = $validated['agent_id'];

        try {
            // Get the agent information
            $agent = User::where('id', $agentId)
                        ->where('type', 20) // Ensure it's an agent
                        ->first();

            if (!$agent) {
                return $this->error(
                    'Agent not found', 
                    'Agent with ID ' . $agentId . ' not found or is not a valid agent', 
                    404
                );
            }

            // Get all users that belong to this agent
            $users = User::where('agent_id', $agentId)
                        ->with('wallet') // Include wallet information
                        ->select([
                            'id',
                            'user_name',
                            'name',
                            'phone',
                            'email',
                            'type',
                            'status',
                            'agent_id',
                            'shan_agent_code',
                            'created_at',
                            'updated_at'
                        ])
                        ->get();

            // Format the response with balance information
            $formattedUsers = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'user_name' => $user->user_name,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'type' => $user->type,
                    'status' => $user->status,
                    'agent_id' => $user->agent_id,
                    'shan_agent_code' => $user->shan_agent_code,
                    'balance' => $user->wallet ? round($user->wallet->balanceFloat, 2) : 0.00,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ];
            });

            return $this->success([
                'agent' => [
                    'id' => $agent->id,
                    'user_name' => $agent->user_name,
                    'name' => $agent->name,
                    'shan_agent_code' => $agent->shan_agent_code,
                ],
                'users_count' => $formattedUsers->count(),
                'users' => $formattedUsers,
            ], 'Users retrieved successfully');

        } catch (\Exception $e) {
            return $this->error(
                'Server error', 
                'Failed to retrieve users: ' . $e->getMessage(), 
                500
            );
        }
    }

    /**
     * Get all users that belong to agent_id = 1 specifically
     */
    public function getUsersByAgentOne(): JsonResponse
    {
        try {
            // Get the agent with ID 1
            $agent = User::where('id', 1)
                        ->where('type', 20) // Ensure it's an agent
                        ->first();

            if (!$agent) {
                return $this->error(
                    'Agent not found', 
                    'Agent with ID 1 not found or is not a valid agent', 
                    404
                );
            }

            // Get all users that belong to agent_id = 1
            $users = User::where('agent_id', 1)
                        ->with('wallet') // Include wallet information
                        ->select([
                            'id',
                            'user_name',
                            'name',
                            'phone',
                            'email',
                            'type',
                            'status',
                            'agent_id',
                            'shan_agent_code',
                            'created_at',
                            'updated_at'
                        ])
                        ->orderBy('created_at', 'desc')
                        ->get();

            // Format the response with balance information
            $formattedUsers = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'user_name' => $user->user_name,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'type' => $this->getUserTypeLabel($user->type),
                    'type_code' => $user->type,
                    'status' => $user->status == 1 ? 'Active' : 'Inactive',
                    'status_code' => $user->status,
                    'agent_id' => $user->agent_id,
                    'shan_agent_code' => $user->shan_agent_code,
                    'balance' => $user->wallet ? round($user->wallet->balanceFloat, 2) : 0.00,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ];
            });

            return $this->success([
                'agent' => [
                    'id' => $agent->id,
                    'user_name' => $agent->user_name,
                    'name' => $agent->name,
                    'shan_agent_code' => $agent->shan_agent_code,
                    'balance' => $agent->wallet ? round($agent->wallet->balanceFloat, 2) : 0.00,
                ],
                'users_count' => $formattedUsers->count(),
                'users' => $formattedUsers,
            ], 'Agent 1 users retrieved successfully');

        } catch (\Exception $e) {
            return $this->error(
                'Server error', 
                'Failed to retrieve users for agent 1: ' . $e->getMessage(), 
                500
            );
        }
    }

    /**
     * Get all agents with their user counts
     */
    public function getAllAgentsWithUserCounts(): JsonResponse
    {
        try {
            // Get all agents with their user counts
            $agents = User::where('type', 20) // Only agents
                        ->with('wallet')
                        ->withCount('players') // Count users that belong to this agent
                        ->select([
                            'id',
                            'user_name',
                            'name',
                            'phone',
                            'email',
                            'status',
                            'shan_agent_code',
                            'shan_agent_name',
                            'created_at',
                            'updated_at'
                        ])
                        ->orderBy('created_at', 'desc')
                        ->get();

            // Format the response
            $formattedAgents = $agents->map(function ($agent) {
                return [
                    'id' => $agent->id,
                    'user_name' => $agent->user_name,
                    'name' => $agent->name,
                    'phone' => $agent->phone,
                    'email' => $agent->email,
                    'status' => $agent->status == 1 ? 'Active' : 'Inactive',
                    'status_code' => $agent->status,
                    'shan_agent_code' => $agent->shan_agent_code,
                    'shan_agent_name' => $agent->shan_agent_name,
                    'balance' => $agent->wallet ? round($agent->wallet->balanceFloat, 2) : 0.00,
                    'users_count' => $agent->players_count ?? 0,
                    'created_at' => $agent->created_at,
                    'updated_at' => $agent->updated_at,
                ];
            });

            return $this->success([
                'agents_count' => $formattedAgents->count(),
                'agents' => $formattedAgents,
            ], 'Agents retrieved successfully');

        } catch (\Exception $e) {
            return $this->error(
                'Server error', 
                'Failed to retrieve agents: ' . $e->getMessage(), 
                500
            );
        }
    }

    /**
     * Get agent details by ID
     */
    public function getAgentById(Request $request): JsonResponse
    {
        // Validate the request
        $validated = $request->validate([
            'agent_id' => 'required|integer|exists:users,id',
        ]);

        $agentId = $validated['agent_id'];

        try {
            // Get the agent information
            $agent = User::where('id', $agentId)
                        ->where('type', 20) // Ensure it's an agent
                        ->with('wallet')
                        ->first();

            if (!$agent) {
                return $this->error(
                    'Agent not found', 
                    'Agent with ID ' . $agentId . ' not found or is not a valid agent', 
                    404
                );
            }

            // Get user count for this agent
            $userCount = User::where('agent_id', $agentId)->count();

            // Format the response
            $formattedAgent = [
                'id' => $agent->id,
                'user_name' => $agent->user_name,
                'name' => $agent->name,
                'phone' => $agent->phone,
                'email' => $agent->email,
                'status' => $agent->status == 1 ? 'Active' : 'Inactive',
                'status_code' => $agent->status,
                'type' => 'Agent',
                'type_code' => $agent->type,
                'shan_agent_code' => $agent->shan_agent_code,
                'shan_agent_name' => $agent->shan_agent_name,
                'shan_secret_key' => $agent->shan_secret_key ? '***HIDDEN***' : null,
                'shan_callback_url' => $agent->shan_callback_url,
                'balance' => $agent->wallet ? round($agent->wallet->balanceFloat, 2) : 0.00,
                'users_count' => $userCount,
                'created_at' => $agent->created_at,
                'updated_at' => $agent->updated_at,
            ];

            return $this->success([
                'agent' => $formattedAgent,
            ], 'Agent details retrieved successfully');

        } catch (\Exception $e) {
            return $this->error(
                'Server error', 
                'Failed to retrieve agent details: ' . $e->getMessage(), 
                500
            );
        }
    }

    /**
     * Helper method to get user type label
     */
    private function getUserTypeLabel(string $type): string
    {
        switch ($type) {
            case '10':
                return 'Owner';
            case '20':
                return 'Agent';
            case '40':
                return 'Player';
            default:
                return 'Unknown';
        }
    }
}
