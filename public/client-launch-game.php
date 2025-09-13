<?php
/**
 * Client Launch Game Library (PHP)
 *
 * This library provides easy integration with the provider launch game API
 * for client sites to launch games without authentication.
 */
class ClientLaunchGame
{
    private $apiUrl;

    private $agentCode;

    private $timeout;

    public function __construct($config = [])
    {
        $this->apiUrl = $config['apiUrl'] ?? 'https://luckymillion.pro/api/client/launch-game';
        $this->agentCode = $config['agentCode'] ?? 'A3H4';
        $this->timeout = $config['timeout'] ?? 30;
    }

    /**
     * Launch a game with the given parameters
     *
     * @param  array  $gameData  Game launch parameters
     * @param  int  $gameData['product_code']  Product code
     * @param  string  $gameData['game_type']  Game type
     * @param  string  $gameData['member_account']  Member account
     * @param  float  $gameData['balance']  User balance
     * @param  string|null  $gameData['nickname']  Optional nickname
     * @return array Response with game URL
     *
     * @throws Exception
     */
    public function launchGame($gameData)
    {
        $payload = [
            'agent_code' => $this->agentCode,
            'product_code' => $gameData['product_code'],
            'game_type' => $gameData['game_type'],
            'member_account' => $gameData['member_account'],
            'balance' => $gameData['balance'],
        ];

        if (isset($gameData['nickname'])) {
            $payload['nickname'] = $gameData['nickname'];
        }

        return $this->makeApiRequest($payload);
    }

    /**
     * Launch a slot game
     *
     * @param  string  $memberAccount  Member account
     * @param  float  $balance  User balance
     * @param  int  $productCode  Product code (default: 1007)
     * @param  string|null  $nickname  Optional nickname
     * @return array Response with game URL
     */
    public function launchSlotGame($memberAccount, $balance, $productCode = 1007, $nickname = null)
    {
        return $this->launchGame([
            'product_code' => $productCode,
            'game_type' => 'slot',
            'member_account' => $memberAccount,
            'balance' => $balance,
            'nickname' => $nickname,
        ]);
    }

    /**
     * Launch a live casino game
     *
     * @param  string  $memberAccount  Member account
     * @param  float  $balance  User balance
     * @param  int  $productCode  Product code (default: 1221)
     * @param  string|null  $nickname  Optional nickname
     * @return array Response with game URL
     */
    public function launchLiveGame($memberAccount, $balance, $productCode = 1221, $nickname = null)
    {
        return $this->launchGame([
            'product_code' => $productCode,
            'game_type' => 'live',
            'member_account' => $memberAccount,
            'balance' => $balance,
            'nickname' => $nickname,
        ]);
    }

    /**
     * Launch a table game
     *
     * @param  string  $memberAccount  Member account
     * @param  float  $balance  User balance
     * @param  int  $productCode  Product code (default: 1040)
     * @param  string|null  $nickname  Optional nickname
     * @return array Response with game URL
     */
    public function launchTableGame($memberAccount, $balance, $productCode = 1040, $nickname = null)
    {
        return $this->launchGame([
            'product_code' => $productCode,
            'game_type' => 'table',
            'member_account' => $memberAccount,
            'balance' => $balance,
            'nickname' => $nickname,
        ]);
    }

    /**
     * Launch a fishing game
     *
     * @param  string  $memberAccount  Member account
     * @param  float  $balance  User balance
     * @param  int  $productCode  Product code (default: 1046)
     * @param  string|null  $nickname  Optional nickname
     * @return array Response with game URL
     */
    public function launchFishingGame($memberAccount, $balance, $productCode = 1046, $nickname = null)
    {
        return $this->launchGame([
            'product_code' => $productCode,
            'game_type' => 'fishing',
            'member_account' => $memberAccount,
            'balance' => $balance,
            'nickname' => $nickname,
        ]);
    }

    /**
     * Make API request to the provider
     *
     * @param  array  $data  Request data
     * @return array Response data
     *
     * @throws Exception
     */
    private function makeApiRequest($data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
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
$gameClient = new ClientLaunchGame([
    'apiUrl' => 'https://luckymillion.pro/api/client/launch-game',
    'agentCode' => 'A3H4',
    'timeout' => 30
]);

try {
    // Launch different types of games
    $result = $gameClient->launchSlotGame('AG10726478', 8000, 1007, 'TestShanAgent');
    echo "Slot game launched: " . $result['url'] . "\n";

    $result = $gameClient->launchLiveGame('AG10726478', 8000, 1221, 'TestShanAgent');
    echo "Live game launched: " . $result['url'] . "\n";

    $result = $gameClient->launchTableGame('AG10726478', 8000, 1040, 'TestShanAgent');
    echo "Table game launched: " . $result['url'] . "\n";

    $result = $gameClient->launchFishingGame('AG10726478', 8000, 1046, 'TestShanAgent');
    echo "Fishing game launched: " . $result['url'] . "\n";

    // Or launch with custom parameters
    $result = $gameClient->launchGame([
        'product_code' => 1007,
        'game_type' => 'slot',
        'member_account' => 'AG10726478',
        'balance' => 8000,
        'nickname' => 'TestShanAgent'
    ]);
    echo "Custom game launched: " . $result['url'] . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
*/

// Web application integration example:
/*
<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$gameClient = new ClientLaunchGame([
    'apiUrl' => 'https://luckymillion.pro/api/client/launch-game',
    'agentCode' => 'A3H4'
]);

if ($_POST['action'] === 'launch_game') {
    try {
        $memberAccount = $_SESSION['username'];
        $productCode = (int)$_POST['product_code'];
        $gameType = $_POST['game_type'];
        $nickname = $_SESSION['display_name'] ?? null;

        $result = $gameClient->launchGame([
            'product_code' => $productCode,
            'game_type' => $gameType,
            'member_account' => $memberAccount,
            'nickname' => $nickname
        ]);

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
        <option value="fishing">Fishing</option>
    </select>

    <button type="submit">Launch Game</button>
</form>
*/
?> 