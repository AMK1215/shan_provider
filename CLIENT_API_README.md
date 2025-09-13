# Provider Launch Game API Documentation

This document explains how to use the provider launch game API to integrate game launching functionality into your client site.

## Overview

The provider launch game API allows external client sites to launch games without requiring authentication. This is a provider site endpoint that receives requests and responds with launch game URLs. This is useful for integrating game functionality into partner sites or white-label solutions.

## API Endpoint

```
POST /api/client/launch-game
```

This endpoint receives launch game requests and responds with a launch game URL.

## Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `agent_code` | string | Yes | Your agent code |
| `product_code` | integer | Yes | Product code for the game provider |
| `game_type` | string | Yes | Type of game (e.g., 'slot', 'live', 'table') |
| `member_account` | string | Yes | User's member account/username |
| `nickname` | string | No | Optional nickname for the user |
| `balance` | decimal | Yes |

### Product Codes

| Product Code | Provider | Currency |
|--------------|----------|----------|
| 100200 | Shankomee | MMK |


## Response Format

### Success Response

```json
{
    "code": 200,
    "message": "Game launched successfully",
    "url": "https://goldendragon7.pro/?user_name=player123&balance=1000&product_code=1007&game_type=slot"
}
```

The API builds and returns a launch game URL with user information and game parameters.

### Error Response

```json
{
    "code": 422,
    "message": "Validation failed",
    "errors": {
        "agent_code": ["The agent code field is required."]
    }
}
```

```json
{
    "code": 500,
    "message": "Launch failed"
}
```

## Usage Examples

### JavaScript Example

```javascript
async function launchGame(gameData) {
    const response = await fetch('/api/client/launch-game', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify(gameData)
    });

    const result = await response.json();
    
    if (result.code === 200) {
        // Open game in new window
        window.open(result.url, '_blank');
        return result;
    } else {
        throw new Error(result.message);
    }
}

// Launch a slot game
launchGame({
    agent_code: 'your_agent_code',
    product_code: 1007,
    game_type: 'slot',
    member_account: 'player123',
    nickname: 'Player 123'
});
```

### PHP Example

```php
$data = [
    'agent_code' => 'your_agent_code',
    'product_code' => 1007,
    'game_type' => 'slot',
    'member_account' => 'player123',
    'nickname' => 'Player 123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://your-domain.com/api/client/launch-game');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$result = json_decode($response, true);

if ($result['code'] === 200) {
    // Redirect to game URL
    header('Location: ' . $result['url']);
    exit;
}
```

### cURL Example

```bash
curl -X POST https://your-domain.com/api/client/launch-game \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "agent_code": "your_agent_code",
    "product_code": 1007,
    "game_type": "slot",
    "member_account": "player123",
    "nickname": "Player 123"
  }'
```

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 422 | Validation failed |
| 500 | Server error or provider API error |

## Security Considerations

1. **Provider Site**: This is a provider site endpoint that receives requests and responds with launch game URLs
2. **User Creation**: Automatically creates users in the database if they don't exist
3. **Input Validation**: All input parameters are validated
4. **Logging**: All requests are logged for monitoring and debugging
5. **Rate Limiting**: Consider implementing rate limiting for production use

## Integration Steps

1. **Get Your Agent Code**: Contact the system administrator to get your agent code
2. **Test the API**: Use the demo page at `/client-demo.html` to test the API
3. **Implement in Your Site**: Use the provided examples to integrate the API into your site
4. **Handle Errors**: Implement proper error handling for failed requests
5. **Monitor Usage**: Monitor API usage and implement appropriate rate limiting

## Demo Page

A demo page is available at `/client-demo.html` that allows you to test the API with different parameters.

## Support

For technical support or questions about the API, please contact the system administrator.

## Changelog

- **v1.0**: Initial release with basic game launch functionality
- Support for multiple product codes and game types
- MMK2 currency support for specific providers 