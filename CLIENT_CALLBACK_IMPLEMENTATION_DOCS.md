# Shan Provider API - Callback URL Documentation

## Overview

The Shan Provider API uses a sophisticated callback URL system to maintain real-time synchronization between the provider (game server) and client sites. This system ensures that all balance changes, game results, and transaction settlements are properly communicated and processed across all connected systems.

## What is a Callback URL?

A **callback URL** is an endpoint on your client site that the Shan Provider will automatically call whenever important events occur, such as:

- Game transaction settlements
- Balance updates
- Player wins/losses
- Banker rotations
- System state changes

## Callback URL Architecture

### 1. **Provider → Client Communication**

```
Shan Provider (Game Server)
         ↓ HTTP POST
Client Site Callback Endpoint
         ↓ Process & Update
Client Database
```

### 2. **Callback URL Storage**

Callback URLs are stored in multiple places in the system:

#### **Agent Level Storage**
```php
// In User model (agents table)
'shan_callback_url' => 'https://your-client-site.com/api/shan/client/balance-update'
```

#### **Operator Level Storage**
```php
// In operators table
'callback_url' => 'https://your-client-site.com/api/shan/balance'
```

#### **Request Level Override**
```php
// Can be passed in launch game requests
'callback_url' => 'https://your-client-site.com/api/shan/client/balance-update'
```

## Callback URL Priority System

The system uses the following priority order for callback URLs:

1. **Request-level callback_url** (highest priority)
2. **Agent's shan_callback_url**
3. **Operator's callback_url**
4. **Default fallback URL**

## Callback Endpoints

### 1. **Balance Update Callback**

**Endpoint**: `POST /api/shan/client/balance-update`

**Purpose**: Receives game transaction results and balance updates from the provider.

**When Called**:
- After each game round completion
- When players win or lose money
- During banker balance changes
- When SKP0101 (provider default player) balance changes

**Callback Payload Structure**:
```json
{
  "wager_code": "abc123def456",
  "game_type_id": 15,
  "players": [
    {
      "player_id": "PLAYER001",
      "balance": 1150.00
    },
    {
      "player_id": "SKP0101",
      "balance": 9850.00
    }
  ],
  "banker_balance": 9850.00,
  "agent_balance": 9850.00,
  "timestamp": "2025-01-15T10:30:00Z",
  "total_player_net": 100.00,
  "banker_amount_change": -100.00,
  "signature": "abc123def456..."
}
```

**Required Response**:
```json
{
  "status": "success",
  "code": "SUCCESS",
  "message": "Balances updated successfully."
}
```

### 2. **Game Launch Callback**

**Endpoint**: `POST /api/shan/balance` (legacy)

**Purpose**: Receives balance information for game launches.

**When Called**:
- During game initialization
- When checking player balances before game start

## Critical Callback Requirements

### 1. **SKP0101 Provider Default Player**

**CRITICAL**: The provider default player `SKP0101` must ALWAYS be included in callback responses. This player represents the system's bank/agent balance and is essential for continuous game operation.

```json
{
  "player_id": "SKP0101",
  "balance": 9850.00
}
```

**Why SKP0101 is Critical**:
- Prevents the Java game server from crashing
- Maintains banker rotation logic
- Ensures continuous game operation
- Represents the provider's available balance

### 2. **Banker Inclusion**

The current banker must always be included in callback responses, even if they're not in the original request players list.

### 3. **Idempotency**

All callbacks must be idempotent - processing the same `wager_code` multiple times should not cause duplicate transactions.

## Callback Security

### 1. **Signature Verification**

All callbacks include HMAC-MD5 signatures for security:

```php
// Signature generation (on provider side)
ksort($callbackPayload);
$signature = hash_hmac('md5', json_encode($callbackPayload), $secretKey);
$callbackPayload['signature'] = $signature;
```

### 2. **Secret Key Management**

Each agent has a unique secret key stored in the database:
```php
'shan_secret_key' => 'HyrmLxMg4rvOoTZ'
```

### 3. **Request Headers**

Callbacks include security headers:
```php
'X-Transaction-Key' => 'yYpfrVcWmkwxWx7um0TErYHj4YcHOOWr'
```

## Callback Processing Flow

### 1. **Provider Side (ShanTransactionController)**

```php
// After processing all transactions
$this->sendCallbackToClient(
    $callbackUrlBase,
    $wagerCode,
    $gameTypeId,
    $finalCallbackPlayers,
    $bankerAfterBalance,
    $totalPlayerNet,
    $bankerAmountChange,
    $secretKey
);
```

### 2. **Client Side (BalanceUpdateCallbackController)**

```php
// Process each player's balance update
foreach ($validated['players'] as $playerData) {
    $user = User::where('user_name', $playerData['player_id'])->first();
    
    $currentBalance = $user->wallet->balanceFloat;
    $newBalance = $playerData['balance'];
    $balanceDifference = $newBalance - $currentBalance;
    
    if ($balanceDifference > 0) {
        $user->depositFloat($balanceDifference, $meta);
    } elseif ($balanceDifference < 0) {
        $user->forceWithdrawFloat(abs($balanceDifference), $meta);
    }
}
```

## Callback URL Configuration

### 1. **Agent Configuration**

When creating or updating agents, set the callback URL:

```php
User::create([
    'user_name' => 'AG61735374',
    'shan_agent_code' => 'A3H4',
    'shan_secret_key' => 'HyrmLxMg4rvOoTZ',
    'shan_callback_url' => 'https://your-client-site.com/api/shan/client/balance-update',
    // ... other fields
]);
```

### 2. **Launch Game Request**

Include callback URL in launch game requests:

```json
{
    "agent_code": "A3H4",
    "member_account": "player001",
    "balance": 1000.00,
    "callback_url": "https://your-client-site.com/api/shan/client/balance-update",
    // ... other fields
}
```

## Error Handling

### 1. **Callback Failures**

If a callback fails, the provider will log the error but continue processing:

```php
Log::error('ShanTransaction: Callback failed', [
    'callback_url' => $callbackUrl,
    'error' => $e->getMessage(),
    'wager_code' => $wagerCode,
]);
```

### 2. **Retry Logic**

The system includes timeout and retry mechanisms:

```php
'timeout' => 10,
'connect_timeout' => 5,
```

### 3. **Client Side Error Responses**

Return appropriate error codes:

```json
{
  "status": "error",
  "code": "INVALID_REQUEST_DATA",
  "message": "Invalid request data"
}
```

## Testing Callbacks

### 1. **Using Postman**

Test your callback endpoint with this payload:

```json
{
  "wager_code": "test123",
  "game_type_id": 15,
  "players": [
    {
      "player_id": "testplayer",
      "balance": 1000.00
    },
    {
      "player_id": "SKP0101",
      "balance": 9000.00
    }
  ],
  "banker_balance": 9000.00,
  "agent_balance": 9000.00,
  "timestamp": "2025-01-15T10:30:00Z",
  "total_player_net": 0.00,
  "banker_amount_change": 0.00
}
```

### 2. **Callback URL Validation**

Ensure your callback URL:
- Is accessible via HTTPS
- Returns proper JSON responses
- Handles all required fields
- Implements idempotency checks
- Processes SKP0101 correctly

## Best Practices

### 1. **URL Format**
- Use HTTPS for security
- Include full path: `https://your-site.com/api/shan/client/balance-update`
- Avoid trailing slashes

### 2. **Response Time**
- Keep response time under 5 seconds
- Implement async processing if needed
- Use database transactions for consistency

### 3. **Logging**
- Log all callback requests
- Include wager_code in all log entries
- Monitor for failed callbacks

### 4. **Monitoring**
- Set up alerts for callback failures
- Monitor response times
- Track callback success rates

## Troubleshooting

### Common Issues

1. **Callback Not Received**
   - Check URL accessibility
   - Verify firewall settings
   - Check DNS resolution

2. **Invalid Signature**
   - Verify secret key configuration
   - Check signature generation logic
   - Ensure proper JSON encoding

3. **SKP0101 Missing**
   - Always include SKP0101 in responses
   - Check banker inclusion logic
   - Verify player list completeness

4. **Duplicate Processing**
   - Implement idempotency checks
   - Use wager_code for deduplication
   - Check database constraints

## Example Implementation

### Client Side Callback Handler

```php
<?php

class BalanceUpdateCallbackController extends Controller
{
    public function handleBalanceUpdate(Request $request)
    {
        try {
            $validated = $request->validate([
                'wager_code' => 'required|string',
                'players' => 'required|array',
                'players.*.player_id' => 'required|string',
                'players.*.balance' => 'required|numeric',
                // ... other validations
            ]);

            // Idempotency check
            if (ProcessedWagerCallback::where('wager_code', $validated['wager_code'])->exists()) {
                return response()->json(['status' => 'success', 'code' => 'ALREADY_PROCESSED']);
            }

            DB::beginTransaction();

            foreach ($validated['players'] as $playerData) {
                $user = User::where('user_name', $playerData['player_id'])->first();
                
                if (!$user) {
                    throw new \RuntimeException("Player {$playerData['player_id']} not found");
                }

                $currentBalance = $user->wallet->balanceFloat;
                $newBalance = $playerData['balance'];
                $difference = $newBalance - $currentBalance;

                if ($difference > 0) {
                    $user->depositFloat($difference, ['wager_code' => $validated['wager_code']]);
                } elseif ($difference < 0) {
                    $user->forceWithdrawFloat(abs($difference), ['wager_code' => $validated['wager_code']]);
                }
            }

            // Record processed callback
            ProcessedWagerCallback::create([
                'wager_code' => $validated['wager_code'],
                'players' => json_encode($validated['players']),
                // ... other fields
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'code' => 'SUCCESS',
                'message' => 'Balances updated successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Callback processing failed', ['error' => $e->getMessage()]);
            
            return response()->json([
                'status' => 'error',
                'code' => 'INTERNAL_ERROR',
                'message' => 'Processing failed'
            ], 500);
        }
    }
}
```

## Conclusion

The callback URL system is the backbone of real-time communication between the Shan Provider and client sites. Proper implementation ensures:

- Real-time balance synchronization
- Accurate game settlement
- System stability and reliability
- Secure transaction processing

Always ensure your callback endpoints are properly configured, secure, and handle all edge cases including the critical SKP0101 provider default player.

# Client Callback Implementation Documentation

## Overview

This documentation explains how to implement the client-side callback handler for receiving balance updates from the Shan Provider API. The `BalanceUpdateCallbackController` is responsible for processing real-time balance updates sent by the provider after game transactions.

## Endpoint Details

- **URL**: `POST /api/shan/client/balance-update`
- **Purpose**: Process balance updates from Shan Provider
- **Authentication**: None (signature-based verification)
- **Content-Type**: `application/json`

## Request Payload Structure

### Required Fields

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `wager_code` | string | Unique transaction identifier | "abc123def456" |
| `players` | array | Array of player balance updates | See below |
| `timestamp` | string | ISO 8601 timestamp | "2025-01-15T10:30:00Z" |

### Optional Fields

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `game_type_id` | integer | Game type identifier | 15 |
| `banker_balance` | numeric | Current banker balance | 9850.00 |
| `total_player_net` | numeric | Net amount from all players | 100.00 |
| `banker_amount_change` | numeric | Banker's balance change | -100.00 |
| `signature` | string | HMAC signature for verification | "abc123..." |

### Players Array Structure

Each player object in the `players` array contains:

```json
{
  "player_id": "string",    // Player username
  "balance": "numeric"      // New balance amount
}
```

## Complete Request Example

```json
{
  "wager_code": "abc123def456",
  "game_type_id": 15,
  "players": [
    {
      "player_id": "PLAYER001",
      "balance": 1150.00
    },
    {
      "player_id": "SKP0101",
      "balance": 9850.00
    },
    {
      "player_id": "BANKER001",
      "balance": 9000.00
    }
  ],
  "banker_balance": 9000.00,
  "agent_balance": 9850.00,
  "timestamp": "2025-01-15T10:30:00Z",
  "total_player_net": 100.00,
  "banker_amount_change": -100.00,
  "signature": "abc123def456..."
}
```

## Response Format

### Success Response

```json
{
  "status": "success",
  "code": "SUCCESS",
  "message": "Balances updated successfully."
}
```

### Error Responses

#### Validation Error (400)
```json
{
  "status": "error",
  "code": "INVALID_REQUEST_DATA",
  "message": "Invalid request data: The players field is required."
}
```

#### Duplicate Transaction (200)
```json
{
  "status": "success",
  "code": "ALREADY_PROCESSED",
  "message": "Wager already processed."
}
```

#### Internal Error (500)
```json
{
  "status": "error",
  "code": "INTERNAL_SERVER_ERROR",
  "message": "Internal server error: Player PLAYER001 not found on client site."
}
```

## Complete Implementation Code

### Full Controller Implementation

Here's the complete `BalanceUpdateCallbackController` implementation:

```php
<?php

namespace App\Http\Controllers\Api\V1\Shan;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Models\ProcessedWagerCallback;
use Bavix\Wallet\External\Dto\Extra; // Needed for meta data with forceDeposit/forceWithdraw
use Bavix\Wallet\External\Dto\Option; // Needed for meta data with forceDeposit/forceWithdraw
// Assuming you have TransactionName Enums on the client side too, or define strings directly
// use App\Enums\TransactionName; // If you have this on client side
use DateTimeImmutable; // <--- ADD THIS LINE
use DateTimeZone;     // <--- ADD THIS LINE FOR CONSISTENCY WITH UTC

class BalanceUpdateCallbackController extends Controller
{
    // You might want a WalletService on the client side too, or put these methods directly here
    // For simplicity, I'll put the logic directly here.

    public function handleBalanceUpdate(Request $request)
    {
        Log::info('ClientSite: BalanceUpdateCallback received', [
            'payload' => $request->all(),
            'ip' => $request->ip(),
        ]);

        try {
            $validated = $request->validate([
                'wager_code' => 'required|string|max:255',
                'game_type_id' => 'nullable|integer',
                'players' => 'required|array',
                'players.*.player_id' => 'required|string|max:255',
                'players.*.balance' => 'required|numeric|min:0', // Player's NEW balance from provider
                'banker_balance' => 'nullable|numeric',
                'timestamp' => 'required|string',
                'total_player_net' => 'nullable|numeric',
                'banker_amount_change' => 'nullable|numeric',
                //'signature' => 'required|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('ClientSite: BalanceUpdateCallback validation failed', [
                'errors' => $e->errors(),
                'payload' => $request->all(),
            ]);
            return response()->json([
                'status' => 'error',
                'code' => 'INVALID_REQUEST_DATA',
                'message' => 'Invalid request data: ' . $e->getMessage(),
            ], 400);
        }

        $providerSecretKey = Config::get('shan_key.secret_key');
        Log::info('ClientSite: Provider secret key', ['provider_secret_key' => $providerSecretKey]);
        if (!$providerSecretKey) {
            Log::critical('ClientSite: Provider secret key not configured!');
            return response()->json([
                'status' => 'error', 'code' => 'INTERNAL_ERROR', 'message' => 'Provider secret key not configured on client site.',
            ], 500);
        }

        
        try {
            DB::beginTransaction();

            // Idempotency Check (CRITICAL) - Uncomment and implement this
            if (ProcessedWagerCallback::where('wager_code', $validated['wager_code'])->exists()) {
                DB::commit();
                Log::info('ClientSite: Duplicate wager_code received, skipping processing.', ['wager_code' => $validated['wager_code']]);
                return response()->json(['status' => 'success', 'code' => 'ALREADY_PROCESSED', 'message' => 'Wager already processed.'], 200);
            }

            foreach ($validated['players'] as $playerData) {
                $user = User::where('user_name', $playerData['player_id'])->first();

                if (!$user) {
                    Log::error('ClientSite: Player not found for balance update. Rolling back transaction.', [
                        'player_id' => $playerData['player_id'], 'wager_code' => $validated['wager_code'],
                    ]);
                    throw new \RuntimeException("Player {$playerData['player_id']} not found on client site.");
                }

                $currentBalance = $user->wallet->balanceFloat; // Get current balance
                $newBalance = $playerData['balance']; // New balance from provider
                $balanceDifference = $newBalance - $currentBalance; // Calculate difference

                $meta = [
                    'wager_code' => $validated['wager_code'],
                    'game_type_id' => 15,
                    'provider_new_balance' => $newBalance,
                    'client_old_balance' => $currentBalance,
                    'description' => 'Game settlement from provider',
                ];

                if ($balanceDifference > 0) {
                    // Player won or received funds
                    $user->depositFloat($balanceDifference, $meta);
                    Log::info('ClientSite: Deposited to player wallet', [
                        'player_id' => $user->user_name, 'amount' => $balanceDifference,
                        'new_balance' => $user->wallet->balanceFloat, 'wager_code' => $validated['wager_code'],
                    ]);
                } elseif ($balanceDifference < 0) {
                    // Player lost or paid funds
                    // Use forceWithdrawFloat if balance might go below zero (e.g., for game losses)
                    // Otherwise, use withdrawFloat which checks for sufficient funds.
                    $user->forceWithdrawFloat(abs($balanceDifference), $meta);
                    Log::info('ClientSite: Withdrew from player wallet', [
                        'player_id' => $user->user_name, 'amount' => abs($balanceDifference),
                        'new_balance' => $user->wallet->balanceFloat, 'wager_code' => $validated['wager_code'],
                    ]);
                } else {
                    // Balance is the same, no action needed
                    Log::info('ClientSite: Player balance unchanged', [
                        'player_id' => $user->user_name, 'balance' => $newBalance, 'wager_code' => $validated['wager_code'],
                    ]);
                }

                // Refresh the user model to reflect the latest balance if needed for subsequent operations in the loop
                $user->refresh();
            }

            

            ProcessedWagerCallback::create([
                'wager_code' => $validated['wager_code'],
                'game_type_id' => 15,
                'players' => json_encode($validated['players']), // ✅ encode array to JSON string
                'banker_balance' => $validated['banker_balance'],
                'timestamp' => $validated['timestamp'],
                'total_player_net' => $validated['total_player_net'],
                'banker_amount_change' => $validated['banker_amount_change'],
            ]);

            Log::info('ClientSite: ProcessedWagerCallback created', ['wager_code' => $validated['wager_code']]);
            

            DB::commit();

            Log::info('ClientSite: All balances updated successfully', ['wager_code' => $validated['wager_code']]);

            return response()->json([
                'status' => 'success', 'code' => 'SUCCESS', 'message' => 'Balances updated successfully.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ClientSite: Error processing balance update', [
                'error' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'payload' => $request->all(),
                'wager_code' => $request->input('wager_code'),
            ]);
            return response()->json([
                'status' => 'error', 'code' => 'INTERNAL_SERVER_ERROR', 'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
```

## Implementation Details

### 1. Validation Rules

The controller validates incoming requests with these rules:

```php
$validated = $request->validate([
    'wager_code' => 'required|string|max:255',
    'game_type_id' => 'nullable|integer',
    'players' => 'required|array',
    'players.*.player_id' => 'required|string|max:255',
    'players.*.balance' => 'required|numeric|min:0',
    'banker_balance' => 'nullable|numeric',
    'timestamp' => 'required|string',
    'total_player_net' => 'nullable|numeric',
    'banker_amount_change' => 'nullable|numeric',
    //'signature' => 'required|string|max:255',
]);
```

### 2. Idempotency Check

**CRITICAL**: The system implements idempotency to prevent duplicate processing:

```php
if (ProcessedWagerCallback::where('wager_code', $validated['wager_code'])->exists()) {
    DB::commit();
    Log::info('ClientSite: Duplicate wager_code received, skipping processing.', ['wager_code' => $validated['wager_code']]);
    return response()->json(['status' => 'success', 'code' => 'ALREADY_PROCESSED', 'message' => 'Wager already processed.'], 200);
}
```

### 3. Balance Update Logic

For each player, the system:

1. **Finds the player** in the database
2. **Calculates the difference** between current and new balance
3. **Updates the wallet** based on the difference:

```php
foreach ($validated['players'] as $playerData) {
    $user = User::where('user_name', $playerData['player_id'])->first();

    if (!$user) {
        Log::error('ClientSite: Player not found for balance update. Rolling back transaction.', [
            'player_id' => $playerData['player_id'], 'wager_code' => $validated['wager_code'],
        ]);
        throw new \RuntimeException("Player {$playerData['player_id']} not found on client site.");
    }

    $currentBalance = $user->wallet->balanceFloat; // Get current balance
    $newBalance = $playerData['balance']; // New balance from provider
    $balanceDifference = $newBalance - $currentBalance; // Calculate difference

    $meta = [
        'wager_code' => $validated['wager_code'],
        'game_type_id' => 15,
        'provider_new_balance' => $newBalance,
        'client_old_balance' => $currentBalance,
        'description' => 'Game settlement from provider',
    ];

    if ($balanceDifference > 0) {
        // Player won or received funds
        $user->depositFloat($balanceDifference, $meta);
        Log::info('ClientSite: Deposited to player wallet', [
            'player_id' => $user->user_name, 'amount' => $balanceDifference,
            'new_balance' => $user->wallet->balanceFloat, 'wager_code' => $validated['wager_code'],
        ]);
    } elseif ($balanceDifference < 0) {
        // Player lost or paid funds
        // Use forceWithdrawFloat if balance might go below zero (e.g., for game losses)
        // Otherwise, use withdrawFloat which checks for sufficient funds.
        $user->forceWithdrawFloat(abs($balanceDifference), $meta);
        Log::info('ClientSite: Withdrew from player wallet', [
            'player_id' => $user->user_name, 'amount' => abs($balanceDifference),
            'new_balance' => $user->wallet->balanceFloat, 'wager_code' => $validated['wager_code'],
        ]);
    } else {
        // Balance is the same, no action needed
        Log::info('ClientSite: Player balance unchanged', [
            'player_id' => $user->user_name, 'balance' => $newBalance, 'wager_code' => $validated['wager_code'],
        ]);
    }

    // Refresh the user model to reflect the latest balance if needed for subsequent operations in the loop
    $user->refresh();
}
```

### 4. Transaction Metadata

Each wallet transaction includes metadata for tracking:

```php
$meta = [
    'wager_code' => $validated['wager_code'],
    'game_type_id' => 15,
    'provider_new_balance' => $newBalance,
    'client_old_balance' => $currentBalance,
    'description' => 'Game settlement from provider',
];
```

### 5. Database Transaction Safety

All operations are wrapped in database transactions:

```php
DB::beginTransaction();
try {
    // Process all players
    // Record processed callback
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    // Handle error
}
```

### 6. ProcessedWagerCallback Recording

After processing all players, the system records the processed callback:

```php
ProcessedWagerCallback::create([
    'wager_code' => $validated['wager_code'],
    'game_type_id' => 15,
    'players' => json_encode($validated['players']), // ✅ encode array to JSON string
    'banker_balance' => $validated['banker_balance'],
    'timestamp' => $validated['timestamp'],
    'total_player_net' => $validated['total_player_net'],
    'banker_amount_change' => $validated['banker_amount_change'],
]);
```

### 7. Error Handling

Comprehensive error handling with proper logging:

```php
} catch (\Exception $e) {
    DB::rollBack();
    Log::error('ClientSite: Error processing balance update', [
        'error' => $e->getMessage(), 'trace' => $e->getTraceAsString(), 'payload' => $request->all(),
        'wager_code' => $request->input('wager_code'),
    ]);
    return response()->json([
        'status' => 'error', 'code' => 'INTERNAL_SERVER_ERROR', 'message' => 'Internal server error: ' . $e->getMessage(),
    ], 500);
}
```

## Required Database Tables

### 1. ProcessedWagerCallback Table

Store processed callbacks to prevent duplicates:

```sql
CREATE TABLE processed_wager_callbacks (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    wager_code VARCHAR(255) UNIQUE NOT NULL,
    game_type_id INT,
    players TEXT, -- JSON encoded array
    banker_balance DECIMAL(15,2),
    timestamp VARCHAR(255),
    total_player_net DECIMAL(15,2),
    banker_amount_change DECIMAL(15,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 2. ProcessedWagerCallback Model

Create the corresponding Eloquent model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessedWagerCallback extends Model
{
    use HasFactory;

    protected $fillable = [
        'wager_code',
        'game_type_id',
        'players',
        'banker_balance',
        'timestamp',
        'total_player_net',
        'banker_amount_change',
    ];

    protected $casts = [
        'players' => 'array', // Automatically cast JSON to array
        'banker_balance' => 'decimal:2',
        'total_player_net' => 'decimal:2',
        'banker_amount_change' => 'decimal:2',
    ];
}
```

### 3. Users Table

Must have wallet functionality:

```sql
-- Users table should have wallet columns
-- Using Bavix Wallet package
```

### 4. Migration for ProcessedWagerCallback

Create the migration file:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('processed_wager_callbacks', function (Blueprint $table) {
            $table->id();
            $table->string('wager_code')->unique();
            $table->integer('game_type_id')->nullable();
            $table->text('players'); // JSON encoded array
            $table->decimal('banker_balance', 15, 2)->nullable();
            $table->string('timestamp');
            $table->decimal('total_player_net', 15, 2)->nullable();
            $table->decimal('banker_amount_change', 15, 2)->nullable();
            $table->timestamps();
            
            $table->index('wager_code');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('processed_wager_callbacks');
    }
};
```

## Route Configuration

Add the callback route to your `routes/api.php`:

```php
<?php

use App\Http\Controllers\Api\V1\Shan\BalanceUpdateCallbackController;
use Illuminate\Support\Facades\Route;

// Callback route for balance updates from Shan Provider
Route::post('/shan/client/balance-update', [BalanceUpdateCallbackController::class, 'handleBalanceUpdate']);
```

## Security Considerations

### 1. Signature Verification (Optional)

The controller is prepared for signature verification:

```php
// Currently commented out but can be enabled
//'signature' => 'required|string|max:255',
```

To enable signature verification:

```php
$validated = $request->validate([
    // ... other fields
    'signature' => 'required|string|max:255',
]);

// Verify signature
$expectedSignature = hash_hmac('md5', json_encode($validated), $providerSecretKey);
if (!hash_equals($expectedSignature, $validated['signature'])) {
    return response()->json([
        'status' => 'error',
        'code' => 'INVALID_SIGNATURE',
        'message' => 'Invalid signature'
    ], 401);
}
```

### 2. Secret Key Configuration

Configure the provider secret key:

```php
// In config/shan_key.php
return [
    'secret_key' => env('SHAN_PROVIDER_SECRET_KEY', 'your-secret-key'),
];
```

### 3. Rate Limiting

Implement rate limiting to prevent abuse:

```php
// In routes/api.php
Route::post('/shan/client/balance-update', [BalanceUpdateCallbackController::class, 'handleBalanceUpdate'])
    ->middleware('throttle:100,1'); // 100 requests per minute
```

## Error Handling

### 1. Player Not Found

If a player doesn't exist in the client database:

```php
if (!$user) {
    Log::error('ClientSite: Player not found for balance update', [
        'player_id' => $playerData['player_id'],
        'wager_code' => $validated['wager_code'],
    ]);
    throw new \RuntimeException("Player {$playerData['player_id']} not found on client site.");
}
```

### 2. Database Errors

All database operations are wrapped in try-catch:

```php
try {
    DB::beginTransaction();
    // Process players
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    Log::error('ClientSite: Error processing balance update', [
        'error' => $e->getMessage(),
        'wager_code' => $request->input('wager_code'),
    ]);
    return response()->json([
        'status' => 'error',
        'code' => 'INTERNAL_SERVER_ERROR',
        'message' => 'Internal server error: ' . $e->getMessage()
    ], 500);
}
```

## Logging

### 1. Request Logging

Log all incoming requests:

```php
Log::info('ClientSite: BalanceUpdateCallback received', [
    'payload' => $request->all(),
    'ip' => $request->ip(),
]);
```

### 2. Transaction Logging

Log each balance update:

```php
Log::info('ClientSite: Deposited to player wallet', [
    'player_id' => $user->user_name,
    'amount' => $balanceDifference,
    'new_balance' => $user->wallet->balanceFloat,
    'wager_code' => $validated['wager_code'],
]);
```

### 3. Error Logging

Log all errors with context:

```php
Log::error('ClientSite: Error processing balance update', [
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString(),
    'payload' => $request->all(),
    'wager_code' => $request->input('wager_code'),
]);
```

## Testing

### 1. Unit Testing

Test the callback handler:

```php
public function test_balance_update_callback()
{
    $payload = [
        'wager_code' => 'test123',
        'players' => [
            ['player_id' => 'testplayer', 'balance' => 1000.00]
        ],
        'timestamp' => now()->toISOString(),
    ];

    $response = $this->postJson('/api/shan/client/balance-update', $payload);

    $response->assertStatus(200)
             ->assertJson([
                 'status' => 'success',
                 'code' => 'SUCCESS'
             ]);
}
```

### 2. Integration Testing

Test with real database:

```php
public function test_duplicate_wager_code_handling()
{
    // Create processed callback
    ProcessedWagerCallback::create([
        'wager_code' => 'test123',
        'players' => json_encode([]),
    ]);

    $payload = [
        'wager_code' => 'test123',
        'players' => [
            ['player_id' => 'testplayer', 'balance' => 1000.00]
        ],
        'timestamp' => now()->toISOString(),
    ];

    $response = $this->postJson('/api/shan/client/balance-update', $payload);

    $response->assertStatus(200)
             ->assertJson([
                 'status' => 'success',
                 'code' => 'ALREADY_PROCESSED'
             ]);
}
```

## Monitoring

### 1. Health Checks

Implement health check endpoint:

```php
public function healthCheck()
{
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected'
    ]);
}
```

### 2. Metrics

Track callback performance:

```php
// Log metrics
Log::info('Callback metrics', [
    'wager_code' => $validated['wager_code'],
    'player_count' => count($validated['players']),
    'processing_time' => microtime(true) - $startTime,
    'memory_usage' => memory_get_usage(true),
]);
```

## Best Practices

### 1. Performance
- Use database transactions for consistency
- Implement proper indexing on `wager_code`
- Use connection pooling for high traffic

### 2. Reliability
- Implement idempotency checks
- Use proper error handling
- Log all operations for debugging

### 3. Security
- Validate all input data
- Implement signature verification
- Use HTTPS for all communications
- Implement rate limiting

### 4. Monitoring
- Set up alerts for failures
- Monitor response times
- Track callback success rates
- Monitor database performance

## Troubleshooting

### Common Issues

1. **Player Not Found**
   - Ensure player exists in database
   - Check player_id format
   - Verify user creation process

2. **Duplicate Processing**
   - Check idempotency implementation
   - Verify wager_code uniqueness
   - Check database constraints

3. **Balance Mismatches**
   - Verify balance calculation logic
   - Check wallet transaction integrity
   - Monitor for race conditions

4. **Database Errors**
   - Check connection status
   - Verify table structure
   - Monitor transaction logs

## Conclusion

The `BalanceUpdateCallbackController` is a critical component for maintaining real-time balance synchronization between the Shan Provider and client sites. Proper implementation ensures:

- Accurate balance updates
- Prevention of duplicate processing
- Reliable transaction handling
- Comprehensive error handling
- Security and monitoring

Follow this documentation to implement a robust callback handler that can handle high-volume game transactions reliably and securely.
