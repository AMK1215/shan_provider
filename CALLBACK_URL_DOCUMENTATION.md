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
