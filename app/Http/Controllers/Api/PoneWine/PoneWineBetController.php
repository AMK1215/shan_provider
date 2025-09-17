<?php

namespace App\Http\Controllers\Api\PoneWine;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PoneWineBetRequest;
use App\Models\PoneWineBet;
use App\Models\PoneWineBetInfo;
use App\Models\PoneWinePlayerBet;
use App\Models\User;
use App\Services\WalletService;
use App\Traits\HttpResponses;
use DateTimeImmutable;
use DateTimeZone;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PoneWineBetController extends Controller
{
    use HttpResponses;

    protected WalletService $walletService;

    /**
     * Pone Wine Game Controller
     * 
     * This controller handles Pone Wine (dice game) transactions.
     * Unlike Shan game which uses a banker system, Pone Wine uses a 
     * house vs players system where the agent represents the house.
     */
    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index(PoneWineBetRequest $request): JsonResponse
    {
        Log::info('PoneWineTransaction: Request received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        try {
            $validatedData = $request->validated();

            // Extract data from 'req' wrapper if present
            if (isset($validatedData['req'])) {
                $validatedData = $validatedData['req'];
            }

            DB::beginTransaction();
            $results = [];

            // Handle both single object and array of objects
            $dataArray = is_array($validatedData) && isset($validatedData[0]) && is_array($validatedData[0]) 
                ? $validatedData 
                : [$validatedData];

            foreach ($dataArray as $data) {
                $bet = $this->createBet($data);
                $results = array_merge($results, $this->processPlayersWithAgentHandling($data, $bet));
            }

            DB::commit();

            return $this->success($results, 'Transaction Successful');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PoneWineTransaction: Transaction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('Transaction failed', $e->getMessage(), 500);
        }
    }

    private function processPlayersWithAgentHandling(array $data, $bet): array
    {
        // Step 1: Validate players array
        if (empty($data['players'])) {
            Log::error('PoneWineTransaction: No players provided', [
                'match_id' => $data['matchId'] ?? 'UNKNOWN',
            ]);
            throw new \RuntimeException("No players provided for transaction");
        }

        // Step 2: Get first player for agent lookup
        $firstPlayerId = $data['players'][0]['playerId'];
        $firstPlayer = User::where('user_name', $firstPlayerId)->first();

        if (!$firstPlayer) {
            Log::error('PoneWineTransaction: First player not found', [
                'player_id' => $firstPlayerId,
            ]);
            throw new \RuntimeException("First player not found: {$firstPlayerId}");
        }

        // Step 3: Get agent information - use player's direct agent relationship
        $agent = null;
        
        // Primary: Find agent by player's agent_id (direct relationship)
        if ($firstPlayer->client_agent_id) {
            $agent = User::find($firstPlayer->client_agent_id);
            
            if ($agent && $agent->type == 20) {
                Log::info('PoneWineTransaction: Found agent by agent_id', [
                    'player_id' => $firstPlayer->id,
                    'player_username' => $firstPlayer->user_name,
                    'player_agent_id' => $firstPlayer->client_agent_id,
                    'agent_id' => $agent->id,
                    'agent_username' => $agent->user_name,
                    'agent_shan_code' => $agent->shan_agent_code,
                ]);
            } else {
                $agent = null; // Not a valid agent
                Log::warning('PoneWineTransaction: Invalid agent found by agent_id', [
                    'player_id' => $firstPlayer->id,
                    'player_username' => $firstPlayer->user_name,
                    'player_agent_id' => $firstPlayer->agent_id,
                    'found_agent_type' => $agent?->type,
                ]);
            }
        }

        // Fallback: Find agent by player's shan_agent_code (legacy support)
        if (!$agent && $firstPlayer->shan_agent_code) {
            $agent = User::where('shan_agent_code', $firstPlayer->shan_agent_code)
                        ->where('type', 20)
                        ->first();
            
            if ($agent) {
                Log::info('PoneWineTransaction: Found agent by shan_agent_code (fallback)', [
                    'player_id' => $firstPlayer->id,
                    'player_username' => $firstPlayer->user_name,
                    'shan_agent_code' => $firstPlayer->shan_agent_code,
                    'agent_id' => $agent->id,
                    'agent_username' => $agent->user_name,
                ]);
            }
        }

        // Last resort: Get any available agent
        if (!$agent) {
            $agent = User::where('type', 20)->first();
            
            if ($agent) {
                Log::warning('PoneWineTransaction: Using fallback agent', [
                    'player_id' => $firstPlayer->id,
                    'player_username' => $firstPlayer->user_name,
                    'fallback_agent_id' => $agent->id,
                    'fallback_agent_username' => $agent->user_name,
                ]);
            }
        }

        $secretKey = $agent?->shan_secret_key;
        $callbackUrlBase = $agent?->shan_callback_url;
        

        Log::info('PoneWineTransaction: Agent information', [
            'agent_id' => $agent?->client_agent_id,
            'agent_username' => $agent?->client_agent_name,
            'agent_type' => $agent?->type,
            'agent_shan_code' => $agent?->shan_agent_code,
            'agent_client_agent_id' => $agent?->client_agent_id,
            'agent_client_agent_name' => $agent?->client_agent_name,
            'has_secret_key' => !empty($secretKey),
            'has_callback_url' => !empty($callbackUrlBase),
        ]);

        // Step 4: Process players (no wager_code needed for Pone Wine)

        // Step 5: Process each player's transaction
        $results = [];
        $callbackPlayers = [];
        $totalPlayerNet = 0;

        // Validate all players exist before processing any transactions
        $validPlayers = [];
        foreach ($data['players'] as $playerData) {
            $player = $this->getUserByUsername($playerData['playerId']);
            if (!$player) {
                Log::error('PoneWineTransaction: Player not found', [
                    'player_id' => $playerData['playerId'],
                    'match_id' => $data['matchId'] ?? 'UNKNOWN',
                ]);
                throw new \RuntimeException("Player not found: {$playerData['playerId']}");
            }
            $validPlayers[] = ['player' => $player, 'data' => $playerData];
        }

        foreach ($validPlayers as $playerInfo) {
            $player = $playerInfo['player'];
            $playerData = $playerInfo['data'];

            $beforeBalance = $player->balanceFloat;
            $this->handlePlayerTransaction($data, $playerData, $player, $bet);
            $player->refresh();
            $afterBalance = $player->balanceFloat;
            $amountChanged = $afterBalance - $beforeBalance;
            $totalPlayerNet += $amountChanged;

            // Add to callback players (format for Pone Wine client callback)
            $callbackPlayers[] = [
                'player_id' => $player->user_name,
                'balance' => $afterBalance, // Player's NEW balance from provider
                'winLoseAmount' => $playerData['winLoseAmount'],
                'betInfos' => $playerData['betInfos'],
                'client_agent_name' => $player->client_agent_name,
                'client_agent_id' => $player->client_agent_id,
            ];

            $results[] = [
                'playerId' => $player->user_name,
                'balance' => $afterBalance,
                'amountChanged' => $amountChanged,
            ];
        }

        // Step 6: Send callback to client site if agent and callback URL available
        if ($agent && $callbackUrlBase) {
            $this->sendCallbackToClient(
                $callbackUrlBase,
                $data,
                $callbackPlayers
            );
        } else {
            Log::warning('PoneWineTransaction: Skipping callback - missing agent or URL', [
                'has_agent' => !empty($agent),
                'has_callback_url' => !empty($callbackUrlBase),
            ]);
        }

        Log::info('PoneWineTransaction: Transaction completed successfully', [
            'total_player_net' => $totalPlayerNet,
            'processed_players_count' => count($results),
        ]);

        return $results;
    }

    private function getUserByUsername(string $username): ?User
    {
        return User::where('user_name', $username)->first();
    }

    private function handlePlayerTransaction(array $data, array $playerData, User $player, $bet): void
    {
        $betPlayer = $this->createBetPlayer($bet, $player, $playerData['winLoseAmount']);
        $this->createBetInfos($betPlayer, $playerData['betInfos']);
        $this->updatePlayerBalance($player, $playerData['winLoseAmount']);
    }

    private function createBet(array $data): PoneWineBet
    {
        return PoneWineBet::create([
            'room_id' => $data['roomId'],
            'match_id' => $data['matchId'],
            'win_number' => $data['winNumber'], // Now properly stores integer
        ]);
    }

    private function createBetPlayer(PoneWineBet $bet, User $player, float $winLoseAmount): PoneWinePlayerBet
    {
        return PoneWinePlayerBet::create([
            'pone_wine_bet_id' => $bet->id,
            'user_id' => $player->id,
            'user_name' => $player->user_name,
            'win_lose_amt' => $winLoseAmount,
        ]);
    }

    private function createBetInfos(PoneWinePlayerBet $betPlayer, array $betInfos): void
    {
        foreach ($betInfos as $info) {
            PoneWineBetInfo::create([
                'bet_no' => $info['betNumber'],
                'bet_amount' => $info['betAmount'],
                'pone_wine_player_bet_id' => $betPlayer->id,
            ]);
        }
    }

    private function updatePlayerBalance(User $player, float $amountChanged): void
    {
        if ($amountChanged > 0) {
            $this->walletService->deposit($player, $amountChanged, TransactionName::PoneWineWin, [
                'description' => 'Pone Wine game win',
                'game_type' => 'pone_wine',
            ]);
        } else {
            $this->walletService->withdraw($player, abs($amountChanged), TransactionName::PoneWineLoss, [
                'description' => 'Pone Wine game loss',
                'game_type' => 'pone_wine',
            ]);
        }
    }

    /**
     * Send callback to client site with Pone Wine game details
     */
    private function sendCallbackToClient(
        string $callbackUrlBase,
        array $gameData,
        array $callbackPlayers
    ): void {
        $callbackUrl = $callbackUrlBase . '/api/pone-wine/client-report';

        $callbackPayload = [
            'roomId' => $gameData['roomId'] ?? 0,
            'matchId' => $gameData['matchId'] ?? 'UNKNOWN',
            'winNumber' => $gameData['winNumber'] ?? 0,
            'players' => $callbackPlayers,
        ];

        try {
            $client = new Client();
            $response = $client->post($callbackUrl, [
                'json' => $callbackPayload,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-Transaction-Key' => 'yYpfrVcWmkwxWx7um0TErYHj4YcHOOWr',
                ],
                'timeout' => 10,
                'connect_timeout' => 5,
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = $response->getBody()->getContents();

            if ($statusCode >= 200 && $statusCode < 300) {
                Log::info('PoneWineTransaction: Callback successful', [
                    'callback_url' => $callbackUrl,
                    'status_code' => $statusCode,
                    'response_body' => $responseBody,
                    'match_id' => $gameData['matchId'] ?? 'UNKNOWN',
                ]);
            } else {
                Log::error('PoneWineTransaction: Callback failed with non-2xx status', [
                    'callback_url' => $callbackUrl,
                    'status_code' => $statusCode,
                    'response_body' => $responseBody,
                    'payload' => $callbackPayload,
                    'match_id' => $gameData['matchId'] ?? 'UNKNOWN',
                ]);
            }

        } catch (RequestException $e) {
            Log::error('PoneWineTransaction: Callback failed (Guzzle RequestException)', [
                'callback_url' => $callbackUrl,
                'error' => $e->getMessage(),
                'payload' => $callbackPayload,
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'No response',
                'match_id' => $gameData['matchId'] ?? 'UNKNOWN',
            ]);
        } catch (\Exception $e) {
            Log::error('PoneWineTransaction: Callback failed (General Exception)', [
                'callback_url' => $callbackUrl,
                'error' => $e->getMessage(),
                'payload' => $callbackPayload,
                'match_id' => $gameData['matchId'] ?? 'UNKNOWN',
            ]);
        }
    }
}
