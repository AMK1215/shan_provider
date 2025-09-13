<?php
/**
 * Client Integration Example for Game Launch API (PHP)
 *
 * This example shows how to integrate the game launch API into your PHP client site.
 * Replace the API_BASE_URL with your actual API endpoint.
 */
class GameLaunchClient
{
    private $apiBaseUrl;

    private $agentCode;

    public function __construct($apiBaseUrl, $agentCode)
    {
        $this->apiBaseUrl = $apiBaseUrl;
        $this->agentCode = $agentCode;
    }

    /**
     * Launch a game for a user
     *
     * @param  string  $memberAccount  User's member account
     * @param  int  $productCode  Product code
     * @param  string  $gameType  Game type (e.g., 'slot', 'live', 'table')
     * @param  string|null  $nickname  Optional nickname for the user
     * @return array Response with game URL
     *
     * @throws Exception
     */
    public function launchGame($memberAccount, $productCode, $gameType, $nickname = null)
    {
        $data = [
            'agent_code' => $this->agentCode,
            'product_code' => $productCode,
            'game_type' => $gameType,
            'member_account' => $memberAccount,
        ];

        if ($nickname) {
            $data['nickname'] = $nickname;
        }

        $response = $this->makeApiRequest('/client/launch-game', $data);

        if ($response['code'] === 200) {
            return $response;
        } else {
            throw new Exception('Game launch failed: '.($response['message'] ?? 'Unknown error'));
        }
    }

    /**
     * Launch a slot game
     *
     * @param  string  $memberAccount  User's member account
     * @param  string|null  $nickname  Optional nickname
     * @return array Response with game URL
     */
    public function launchSlotGame($memberAccount, $nickname = null)
    {
        return $this->launchGame($memberAccount, 1007, 'slot', $nickname);
    }

    /**
     * Launch a live casino game
     *
     * @param  string  $memberAccount  User's member account
     * @param  string|null  $nickname  Optional nickname
     * @return array Response with game URL
     */
    public function launchLiveGame($memberAccount, $nickname = null)
    {
        return $this->launchGame($memberAccount, 1221, 'live', $nickname);
    }

    /**
     * Launch a table game
     *
     * @param  string  $memberAccount  User's member account
     * @param  string|null  $nickname  Optional nickname
     * @return array Response with game URL
     */
    public function launchTableGame($memberAccount, $nickname = null)
    {
        return $this->launchGame($memberAccount, 1040, 'table', $nickname);
    }

    /**
     * Make API request to the game launch endpoint
     *
     * @param  string  $endpoint  API endpoint
     * @param  array  $data  Request data
     * @return array Response data
     *
     * @throws Exception
     */
    private function makeApiRequest($endpoint, $data)
    {
        $url = $this->apiBaseUrl.$endpoint;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('cURL error: '.$error);
        }

        if ($httpCode !== 200) {
            throw new Exception('HTTP error: '.$httpCode);
        }

        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response');
        }

        return $responseData;
    }
}

// Example usage:
/*
// Initialize the client
$gameClient = new GameLaunchClient(
    'https://your-domain.com/api', // Replace with your actual API URL
    'your_agent_code' // Replace with your actual agent code
);

try {
    // Launch a slot game
    $result = $gameClient->launchSlotGame('player123', 'Player 123');
    echo "Game launched successfully!\n";
    echo "Game URL: " . $result['url'] . "\n";

    // Launch a live game
    $result = $gameClient->launchLiveGame('player123', 'Player 123');
    echo "Live game launched successfully!\n";
    echo "Game URL: " . $result['url'] . "\n";

    // Launch a table game
    $result = $gameClient->launchTableGame('player123', 'Player 123');
    echo "Table game launched successfully!\n";
    echo "Game URL: " . $result['url'] . "\n";

    // Launch with custom parameters
    $result = $gameClient->launchGame('player123', 1046, 'slot', 'Player 123');
    echo "Custom game launched successfully!\n";
    echo "Game URL: " . $result['url'] . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
*/

// Example: Integration with a web application
/*
<?php
// In your web application
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$gameClient = new GameLaunchClient(
    'https://your-domain.com/api',
    'your_agent_code'
);

if ($_POST['action'] === 'launch_game') {
    try {
        $memberAccount = $_SESSION['username'];
        $productCode = (int)$_POST['product_code'];
        $gameType = $_POST['game_type'];
        $nickname = $_SESSION['display_name'] ?? null;

        $result = $gameClient->launchGame($memberAccount, $productCode, $gameType, $nickname);

        // Redirect to the game URL
        header('Location: ' . $result['url']);
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
        // Handle error (show error message to user)
    }
}
?>

<!-- HTML form for launching games -->
<form method="POST">
    <input type="hidden" name="action" value="launch_game">

    <label for="product_code">Product Code:</label>
    <select name="product_code" required>
        <option value="1007">1007 (PG Soft)</option>
        <option value="1221">1221</option>
        <option value="1040">1040</option>
        <option value="1046">1046</option>
        <option value="1004">1004</option>
    </select>

    <label for="game_type">Game Type:</label>
    <select name="game_type" required>
        <option value="slot">Slot</option>
        <option value="live">Live Casino</option>
        <option value="table">Table Game</option>
    </select>

    <button type="submit">Launch Game</button>
</form>
*/
?> 