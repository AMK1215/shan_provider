# Shankomee Provider API Integration Guide

## Quick Start

### 1. Prerequisites
- Valid agent account with `shan_agent_code`
- HTTPS-enabled client site
- Webhook endpoint for callbacks

### 2. Authentication Setup
```javascript
// Login to get token
const loginResponse = await fetch('/api/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    user_name: 'your_agent_username',
    password: 'your_password'
  })
});

const { data } = await loginResponse.json();
const token = data.token;
```

### 3. Launch Game Integration
```javascript
// Launch game for player
const launchResponse = await fetch('/api/client/launch-game', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    agent_code: 'SCT931',
    product_code: 15,
    game_type: 'shan_komee',
    member_account: 'PLAYER001',
    balance: 1000.00,
    callback_url: 'https://your-site.com/callback'
  })
});

const { url } = await launchResponse.json();
window.location.href = url; // Redirect to game
```

### 4. Balance Check
```javascript
// Get player balance
const balanceResponse = await fetch('/api/shan/getbalance', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    batch_requests: [{
      member_account: 'PLAYER001',
      product_code: 15
    }],
    currency: 'MMK'
  })
});

const { data } = await balanceResponse.json();
const balance = data[0].balance;
```

### 5. Webhook Setup
```php
<?php
// Handle balance update callbacks
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Verify signature
    $signature = $input['signature'];
    unset($input['signature']);
    
    $expectedSignature = hash_hmac('md5', json_encode($input), $secretKey);
    
    if (hash_equals($expectedSignature, $signature)) {
        // Process balance updates
        foreach ($input['players'] as $player) {
            updatePlayerBalance($player['player_id'], $player['balance']);
        }
        
        // Respond with success
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'code' => 'SUCCESS',
            'message' => 'Balances updated successfully.'
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid signature']);
    }
}
?>
```

## Complete Integration Examples

### PHP Integration
```php
<?php
class ShankomeeProvider {
    private $baseUrl;
    private $agentCode;
    private $secretKey;
    
    public function __construct($baseUrl, $agentCode, $secretKey) {
        $this->baseUrl = $baseUrl;
        $this->agentCode = $agentCode;
        $this->secretKey = $secretKey;
    }
    
    public function launchGame($memberAccount, $balance, $callbackUrl = null) {
        $data = [
            'agent_code' => $this->agentCode,
            'product_code' => 15,
            'game_type' => 'shan_komee',
            'member_account' => $memberAccount,
            'balance' => $balance
        ];
        
        if ($callbackUrl) {
            $data['callback_url'] = $callbackUrl;
        }
        
        $response = $this->makeRequest('/client/launch-game', $data);
        
        if ($response && $response['code'] === 200) {
            return $response['url'];
        }
        
        return false;
    }
    
    public function getBalance($memberAccount) {
        $data = [
            'batch_requests' => [
                [
                    'member_account' => $memberAccount,
                    'product_code' => 15
                ]
            ],
            'currency' => 'MMK'
        ];
        
        $response = $this->makeRequest('/shan/getbalance', $data);
        
        if ($response && isset($response['data'][0])) {
            return $response['data'][0]['balance'];
        }
        
        return 0;
    }
    
    public function getReportTransactions($dateFrom = null, $dateTo = null, $memberAccount = null) {
        $data = [
            'agent_code' => $this->agentCode,
            'group_by' => 'both'
        ];
        
        if ($dateFrom) $data['date_from'] = $dateFrom;
        if ($dateTo) $data['date_to'] = $dateTo;
        if ($memberAccount) $data['member_account'] = $memberAccount;
        
        return $this->makeRequest('/provider/shan/report-transactions', $data);
    }
    
    public function getTodayTransactions($memberAccount, $limit = 50) {
        $data = [
            'agent_code' => $this->agentCode,
            'member_account' => $memberAccount,
            'limit' => $limit
        ];
        
        return $this->makeRequest('/provider/shan/today-transactions', $data);
    }
    
    private function makeRequest($endpoint, $data) {
        $url = $this->baseUrl . $endpoint;
        
        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            return false;
        }
        
        return json_decode($result, true);
    }
}

// Usage
$provider = new ShankomeeProvider(
    'https://your-provider-domain.com/api',
    'SCT931',
    'your_secret_key'
);

// Launch game
$gameUrl = $provider->launchGame('PLAYER001', 1000.00);
if ($gameUrl) {
    header("Location: $gameUrl");
    exit;
}

// Get balance
$balance = $provider->getBalance('PLAYER001');
echo "Player balance: $balance";

// Get reports
$reports = $provider->getReportTransactions('2025-01-01', '2025-01-31');

// Get today's transactions
$todayTransactions = $provider->getTodayTransactions('PLAYER001');
?>
```

### JavaScript Integration
```javascript
class ShankomeeProvider {
    constructor(baseUrl, agentCode, secretKey) {
        this.baseUrl = baseUrl;
        this.agentCode = agentCode;
        this.secretKey = secretKey;
    }
    
    async launchGame(memberAccount, balance, callbackUrl = null) {
        const data = {
            agent_code: this.agentCode,
            product_code: 15,
            game_type: 'shan_komee',
            member_account: memberAccount,
            balance: balance
        };
        
        if (callbackUrl) {
            data.callback_url = callbackUrl;
        }
        
        try {
            const response = await fetch(`${this.baseUrl}/client/launch-game`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.code === 200) {
                return result.url;
            }
            
            throw new Error(result.message);
        } catch (error) {
            console.error('Launch game error:', error);
            return null;
        }
    }
    
    async getBalance(memberAccount) {
        const data = {
            batch_requests: [{
                member_account: memberAccount,
                product_code: 15
            }],
            currency: 'MMK'
        };
        
        try {
            const response = await fetch(`${this.baseUrl}/shan/getbalance`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.data && result.data[0]) {
                return result.data[0].balance;
            }
            
            return 0;
        } catch (error) {
            console.error('Get balance error:', error);
            return 0;
        }
    }
    
    async getReportTransactions(dateFrom = null, dateTo = null, memberAccount = null) {
        const data = {
            agent_code: this.agentCode,
            group_by: 'both'
        };
        
        if (dateFrom) data.date_from = dateFrom;
        if (dateTo) data.date_to = dateTo;
        if (memberAccount) data.member_account = memberAccount;
        
        try {
            const response = await fetch(`${this.baseUrl}/provider/shan/report-transactions`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            
            return await response.json();
        } catch (error) {
            console.error('Get reports error:', error);
            return null;
        }
    }
    
    async getTodayTransactions(memberAccount, limit = 50) {
        const data = {
            agent_code: this.agentCode,
            member_account: memberAccount,
            limit: limit
        };
        
        try {
            const response = await fetch(`${this.baseUrl}/provider/shan/today-transactions`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            
            return await response.json();
        } catch (error) {
            console.error('Get today transactions error:', error);
            return null;
        }
    }
}

// Usage
const provider = new ShankomeeProvider(
    'https://your-provider-domain.com/api',
    'SCT931',
    'your_secret_key'
);

// Launch game
provider.launchGame('PLAYER001', 1000.00)
    .then(url => {
        if (url) {
            window.location.href = url;
        }
    });

// Get balance
provider.getBalance('PLAYER001')
    .then(balance => {
        console.log('Player balance:', balance);
    });

// Get reports
provider.getReportTransactions('2025-01-01', '2025-01-31')
    .then(reports => {
        console.log('Reports:', reports);
    });

// Get today's transactions
provider.getTodayTransactions('PLAYER001')
    .then(todayTransactions => {
        console.log('Today\'s transactions:', todayTransactions);
    });
```

### Python Integration
```python
import requests
import json
from datetime import datetime

class ShankomeeProvider:
    def __init__(self, base_url, agent_code, secret_key):
        self.base_url = base_url
        self.agent_code = agent_code
        self.secret_key = secret_key
    
    def launch_game(self, member_account, balance, callback_url=None):
        data = {
            'agent_code': self.agent_code,
            'product_code': 15,
            'game_type': 'shan_komee',
            'member_account': member_account,
            'balance': balance
        }
        
        if callback_url:
            data['callback_url'] = callback_url
        
        try:
            response = requests.post(
                f"{self.base_url}/client/launch-game",
                json=data,
                headers={'Content-Type': 'application/json'}
            )
            
            result = response.json()
            
            if result.get('code') == 200:
                return result['url']
            
            raise Exception(result.get('message', 'Unknown error'))
            
        except Exception as e:
            print(f"Launch game error: {e}")
            return None
    
    def get_balance(self, member_account):
        data = {
            'batch_requests': [{
                'member_account': member_account,
                'product_code': 15
            }],
            'currency': 'MMK'
        }
        
        try:
            response = requests.post(
                f"{self.base_url}/shan/getbalance",
                json=data,
                headers={'Content-Type': 'application/json'}
            )
            
            result = response.json()
            
            if result.get('data') and result['data']:
                return result['data'][0]['balance']
            
            return 0
            
        except Exception as e:
            print(f"Get balance error: {e}")
            return 0
    
    def get_report_transactions(self, date_from=None, date_to=None, member_account=None):
        data = {
            'agent_code': self.agent_code,
            'group_by': 'both'
        }
        
        if date_from:
            data['date_from'] = date_from
        if date_to:
            data['date_to'] = date_to
        if member_account:
            data['member_account'] = member_account
        
        try:
            response = requests.post(
                f"{self.base_url}/provider/shan/report-transactions",
                json=data,
                headers={'Content-Type': 'application/json'}
            )
            
            return response.json()
            
        except Exception as e:
            print(f"Get reports error: {e}")
            return None
    
    def get_today_transactions(self, member_account, limit=50):
        data = {
            'agent_code': self.agent_code,
            'member_account': member_account,
            'limit': limit
        }
        
        try:
            response = requests.post(
                f"{self.base_url}/provider/shan/today-transactions",
                json=data,
                headers={'Content-Type': 'application/json'}
            )
            
            return response.json()
            
        except Exception as e:
            print(f"Get today transactions error: {e}")
            return None

# Usage
provider = ShankomeeProvider(
    'https://your-provider-domain.com/api',
    'SCT931',
    'your_secret_key'
)

# Launch game
game_url = provider.launch_game('PLAYER001', 1000.00)
if game_url:
    print(f"Game URL: {game_url}")

# Get balance
balance = provider.get_balance('PLAYER001')
print(f"Player balance: {balance}")

# Get reports
reports = provider.get_report_transactions('2025-01-01', '2025-01-31')
print(f"Reports: {reports}")

# Get today's transactions
today_transactions = provider.get_today_transactions('PLAYER001')
print(f"Today's transactions: {today_transactions}")
```

## Testing Checklist

### 1. Authentication Testing
- [ ] Login with valid credentials
- [ ] Login with invalid credentials
- [ ] Token expiration handling
- [ ] Register new agent

### 2. Game Launch Testing
- [ ] Launch game with valid parameters
- [ ] Launch game with invalid agent code
- [ ] Launch game with non-existent player
- [ ] Launch game with negative balance
- [ ] Launch game with callback URL

### 3. Balance Testing
- [ ] Get balance for existing player
- [ ] Get balance for non-existent player
- [ ] Batch balance requests
- [ ] Balance after game transactions

### 4. Transaction Testing
- [ ] Create valid transaction
- [ ] Create transaction with invalid player
- [ ] Create duplicate transaction
- [ ] Transaction with insufficient balance

### 5. Report Testing
- [ ] Get reports for valid date range
- [ ] Get reports for invalid date range
- [ ] Get reports for specific member
- [ ] Get member transaction details

### 6. Webhook Testing
- [ ] Receive balance update callback
- [ ] Verify callback signature
- [ ] Handle duplicate callbacks
- [ ] Respond with correct format

## Common Issues and Solutions

### 1. Authentication Issues
**Problem**: 401 Unauthorized
**Solution**: Check token validity and ensure proper Authorization header

### 2. Agent Code Issues
**Problem**: Agent not found
**Solution**: Verify `shan_agent_code` exists in database

### 3. Balance Issues
**Problem**: Insufficient balance
**Solution**: Check player balance before transactions

### 4. Callback Issues
**Problem**: Callbacks not received
**Solution**: Verify callback URL is accessible and responds correctly

### 5. Signature Issues
**Problem**: Invalid signature
**Solution**: Ensure proper HMAC-MD5 signature generation

## Security Best Practices

1. **Use HTTPS**: Always use HTTPS for API communications
2. **Validate Input**: Validate all input data on your side
3. **Store Secrets Securely**: Never expose API keys in client-side code
4. **Implement Rate Limiting**: Prevent abuse with rate limiting
5. **Log Everything**: Log all API interactions for debugging
6. **Verify Signatures**: Always verify webhook signatures
7. **Handle Errors Gracefully**: Implement proper error handling

## Support and Resources

- **Documentation**: Complete API documentation
- **Postman Collection**: Ready-to-use API collection
- **SDK Examples**: Code examples in multiple languages
- **Test Environment**: Staging environment for testing
- **Support Team**: Technical support available

For additional help, contact our support team at support@shankomee-provider.com
