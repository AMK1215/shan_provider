# API: Shan Agent Management

## Overview
Comprehensive API endpoints for managing Shan agents and their users, including retrieval of users by agent and agent statistics.

## Endpoints Created

### 1. **Get Users by Any Agent ID** 
**POST** `/api/shan/users-by-agent`

#### Request:
```json
{
  "agent_id": 1
}
```

#### Validation:
- `agent_id`: Required, must be integer, must exist in users table

#### Response Success (200):
```json
{
  "success": true,
  "message": "Users retrieved successfully",
  "data": {
    "agent": {
      "id": 1,
      "user_name": "AG123456",
      "name": "Agent Name",
      "shan_agent_code": "AG001"
    },
    "users_count": 5,
    "users": [
      {
        "id": 10,
        "user_name": "PLAYER001",
        "name": "Player One",
        "phone": "1234567890",
        "email": "player1@example.com",
        "type": "40",
        "status": 1,
        "agent_id": 1,
        "shan_agent_code": "AG001",
        "balance": 1500.00,
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z"
      }
    ]
  }
}
```

#### Response Error (404):
```json
{
  "success": false,
  "message": "Agent not found",
  "data": "Agent with ID 1 not found or is not a valid agent"
}
```

### 2. **Get Users by Agent ID 1 (Quick Access)**
**GET** `/api/shan/users-by-agent-one`

### 3. **Get All Agents with User Counts**
**GET** `/api/shan/agents`

### 4. **Get Agent Details by ID**
**POST** `/api/shan/agent-details`

#### Request:
No parameters required.

#### Response Success (200):
```json
{
  "success": true,
  "message": "Agent 1 users retrieved successfully",
  "data": {
    "agent": {
      "id": 1,
      "user_name": "AG123456",
      "name": "Agent Name",
      "shan_agent_code": "AG001",
      "balance": 50000.00
    },
    "users_count": 5,
    "users": [
      {
        "id": 10,
        "user_name": "PLAYER001",
        "name": "Player One",
        "phone": "1234567890",
        "email": "player1@example.com",
        "type": "Player",
        "type_code": "40",
        "status": "Active",
        "status_code": 1,
        "agent_id": 1,
        "shan_agent_code": "AG001",
        "balance": 1500.00,
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z"
      }
    ]
  }
}
```

## Features

### ✅ **Comprehensive User Data:**
- User basic information (ID, username, name, contact)
- User type with human-readable labels (Owner/Agent/Player)
- Status with human-readable labels (Active/Inactive)
- Balance information from wallet
- Shan-specific data (shan_agent_code)
- Timestamps

### ✅ **Agent Validation:**
- Verifies agent exists
- Ensures user is actually an agent (type = 20)
- Returns agent information in response

### ✅ **Balance Integration:**
- Includes wallet balance for each user
- Handles users without wallets gracefully (0.00 balance)
- Rounded to 2 decimal places

### ✅ **Error Handling:**
- Proper validation messages
- 404 for agent not found
- 500 for server errors
- Detailed error messages

### ✅ **Response Formatting:**
- Consistent API response structure
- Human-readable type and status labels
- Ordered by creation date (newest first)
- Count of total users

## Usage Examples

### **cURL Examples:**

#### Get users for any agent:
```bash
curl -X POST https://yourdomain.com/api/shan/users-by-agent \
  -H "Content-Type: application/json" \
  -d '{"agent_id": 1}'
```

#### Get users for agent ID 1:
```bash
curl -X GET https://yourdomain.com/api/shan/users-by-agent-one
```

#### Get all agents with user counts:
```bash
curl -X GET https://yourdomain.com/api/shan/agents
```

#### Get specific agent details:
```bash
curl -X POST https://yourdomain.com/api/shan/agent-details \
  -H "Content-Type: application/json" \
  -d '{"agent_id": 1}'
```

### **JavaScript Examples:**

#### Using fetch for any agent:
```javascript
const response = await fetch('/api/shan/users-by-agent', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({ agent_id: 1 })
});

const data = await response.json();
console.log('Users:', data.data.users);
```

#### Using fetch for agent ID 1:
```javascript
const response = await fetch('/api/shan/users-by-agent-one');
const data = await response.json();
console.log('Agent 1 Users:', data.data.users);
```

## Database Query Details

### **Tables Used:**
- `users` - Main user table
- `wallets` - For balance information (via relationship)

### **Query Structure:**
```sql
SELECT id, user_name, name, phone, email, type, status, 
       agent_id, shan_agent_code, created_at, updated_at
FROM users 
WHERE agent_id = ? 
ORDER BY created_at DESC
```

### **Relationships:**
- Uses Laravel's `with('wallet')` for eager loading
- Handles missing wallet gracefully

## User Type Codes

| Code | Label | Description |
|------|-------|-------------|
| 10   | Owner | System owner |
| 20   | Agent | Agent user |
| 40   | Player | Regular player |

## Status Codes

| Code | Label | Description |
|------|-------|-------------|
| 1    | Active | User is active |
| 0    | Inactive | User is inactive |

## Security Considerations

### ✅ **Validation:**
- Agent ID existence validation
- Type verification (ensures is agent)
- Input sanitization

### ✅ **Data Protection:**
- Selective field exposure
- Sensitive data excluded (passwords, tokens)
- Balance information included for legitimate purposes

### ✅ **Error Handling:**
- No sensitive information in error messages
- Graceful failure handling
- Proper HTTP status codes

## Implementation Files

### **Controller:** 
`app/Http/Controllers/Api/V1/Shan/ShanAgentController.php`

### **Routes:** 
`routes/api.php` (Shan group)

### **Methods Added:**
1. `getUsersByAgent(Request $request)` - Generic agent users
2. `getUsersByAgentOne()` - Specific to agent ID 1
3. `getAllAgentsWithUserCounts()` - List all agents with user counts
4. `getAgentById(Request $request)` - Get specific agent details
5. `getUserTypeLabel(string $type)` - Helper for type labels

## Future Enhancements

### **Possible Additions:**
- Pagination for large agent user lists
- Filtering by user type or status
- Search functionality by username/name
- Export functionality
- User activity statistics
- Balance history integration

### **Performance Optimizations:**
- Database indexing on agent_id
- Caching for frequently accessed agents
- Pagination for large datasets
- Query optimization

## Testing

### **Test Cases:**
1. ✅ Valid agent ID returns users
2. ✅ Invalid agent ID returns 404
3. ✅ Agent ID that's not an agent returns 404
4. ✅ Agent with no users returns empty array
5. ✅ Balance calculation works correctly
6. ✅ Response format is consistent

### **Test Data Setup:**
```sql
-- Create test agent
INSERT INTO users (user_name, name, type, shan_agent_code) 
VALUES ('AG123456', 'Test Agent', '20', 'AG001');

-- Create test users under agent
INSERT INTO users (user_name, name, type, agent_id) 
VALUES ('PLAYER001', 'Player One', '40', 1);
```

This API provides comprehensive access to agent-user relationships with proper validation, error handling, and detailed response formatting!
