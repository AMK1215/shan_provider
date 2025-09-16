<?php

use App\Http\Controllers\Api\Player\GameLogController;
use App\Http\Controllers\Api\Player\TransactionController;
use App\Http\Controllers\Api\V1\Auth\AuthController;

use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\Dashboard\AdminLoginController;
use App\Http\Controllers\Api\V1\DepositRequestController;
use App\Http\Controllers\Api\V1\DigitGame\DigitBetController;
use App\Http\Controllers\Api\V1\DigitGame\DigitSlotController;
use App\Http\Controllers\Api\V1\Game\GameController;
use App\Http\Controllers\Api\V1\Game\GSCPlusProviderController;
use App\Http\Controllers\Api\V1\Game\LaunchGameController;
use App\Http\Controllers\Api\V1\Game\ProviderTransactionCallbackController;
use App\Http\Controllers\Api\V1\Game\ShanLaunchGameController;
use App\Http\Controllers\Api\V1\Game\ShanPlayerHistoryController;
use App\Http\Controllers\Api\V1\gplus\Webhook\DepositController;
use App\Http\Controllers\Api\V1\gplus\Webhook\GameListController;
use App\Http\Controllers\Api\V1\gplus\Webhook\GetBalanceController;
use App\Http\Controllers\Api\V1\gplus\Webhook\ProductListController;
use App\Http\Controllers\Api\V1\gplus\Webhook\PushBetDataController;
use App\Http\Controllers\Api\V1\gplus\Webhook\WithdrawController;

use App\Http\Controllers\Api\V1\PromotionController;
use App\Http\Controllers\Api\V1\Shan\ShanApiTransactionController;
use App\Http\Controllers\Api\V1\Shan\ShanAgentController;
use App\Http\Controllers\Api\V1\Shan\ShanAgentReportTransactionController;
use App\Http\Controllers\Api\V1\Shan\ShanGetBalanceController;
use App\Http\Controllers\Api\V1\Shan\ShanTransactionController;
use App\Http\Controllers\Api\V1\TwoDigit\TwoDigitBetController;
use App\Http\Controllers\Api\V1\Wallet\WalletController;
use App\Http\Controllers\Api\V1\WithDrawRequestController;
use App\Http\Controllers\Api\V2\Shan\ShankomeeGetBalanceController;
use App\Http\Controllers\Api\V1\Shan\BalanceUpdateCallbackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Game\ProviderLaunchGameController;
use App\Http\Controllers\Api\PoneWine\PoneWineBetController;
use App\Http\Controllers\Api\PoneWine\ProviderPoneWineLaunchGameController;
use App\Http\Controllers\Api\PoneWine\PoneWineClientBalanceUpdateController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// admin login
//Route::post('/admin/login', [AdminLoginController::class, 'login']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/player-change-password', [AuthController::class, 'playerChangePassword']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('product-list', [ProductListController::class, 'index']);
Route::get('operators/provider-games', [GameListController::class, 'index']);

Route::prefix('v1/api/seamless')->group(function () {
    Route::post('balance', [GetBalanceController::class, 'getBalance']);
    Route::post('withdraw', [WithdrawController::class, 'withdraw']);
    Route::post('deposit', [DepositController::class, 'deposit']);
    Route::post('pushbetdata', [PushBetDataController::class, 'pushBetData']);
});

Route::post('/transactions', [ShanTransactionController::class, 'ShanTransactionCreate'])->middleware('transaction');

Route::post('ponewine/bet', [PoneWineBetController::class, 'index'])->middleware('transaction');

Route::post('/client/ponewine/launch-game', [ProviderPoneWineLaunchGameController::class, 'PoneWinelaunchGameForClient']);

// Client launch game endpoint (no authentication required)
// Route::post('/client/launch-game', [LaunchGameController::class, 'launchGameForClient']);
Route::post('/client/launch-game', [ProviderLaunchGameController::class, 'launchGameForClient']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/seamless/launch-game', [LaunchGameController::class, 'launchGame']);

    // main balance
    Route::post('exchange-main-to-game', [TransactionController::class, 'MainToGame']);
    Route::post('exchange-game-to-main', [TransactionController::class, 'GameToMain']);
    Route::get('exchange-transactions-log', [TransactionController::class, 'exchangeTransactionLog']);

    // user api
    Route::get('user', [AuthController::class, 'getUser']);
    Route::get('/banks', [GSCPlusProviderController::class, 'banks']);

    // fanicial api
    //Route::get('agentfinicialPaymentType', [BankController::class, 'all']);
    Route::post('depositfinicial', [DepositRequestController::class, 'FinicialDeposit']);
    Route::get('depositlogfinicial', [DepositRequestController::class, 'log']);
    Route::get('paymentTypefinicial', [GSCPlusProviderController::class, 'paymentType']);
    Route::post('withdrawfinicial', [WithDrawRequestController::class, 'FinicalWithdraw']);
    Route::get('withdrawlogfinicial', [WithDrawRequestController::class, 'log']);

    Route::get('contact', [ContactController::class, 'get']);
    Route::get('promotion', [PromotionController::class, 'index']);
    Route::get('winnerText', [BannerController::class, 'winnerText']);
    Route::get('banner_Text', [BannerController::class, 'bannerText']);
    Route::get('popup-ads-banner', [BannerController::class, 'AdsBannerIndex']);
    Route::get('banner', [BannerController::class, 'index']);
    Route::get('videoads', [BannerController::class, 'ApiVideoads']);
    Route::get('toptenwithdraw', [BannerController::class, 'TopTen']);

    // Player game logs
    Route::get('/player/game-logs', [GameLogController::class, 'index']);
    Route::get('user', [AuthController::class, 'getUser']);

});

// games
Route::get('/game_types', [GSCPlusProviderController::class, 'gameTypes']);
Route::get('/providers/{type}', [GSCPlusProviderController::class, 'providers']);
Route::get('/game_lists/{type}/{provider}', [GSCPlusProviderController::class, 'gameLists']);

Route::get('/game_lists/{type}/{productcode}', [GSCPlusProviderController::class, 'NewgameLists']);
Route::get('/hot_game_lists', [GSCPlusProviderController::class, 'hotGameLists']);

// Route::group(['prefix' => 'shanreport', 'middleware' => ['auth:sanctum']], function () {
//     Route::get('player-history', [ShanPlayerHistoryController::class, 'getPlayerHistory']);
// });

Route::group(['prefix' => 'shan'], function () {
    Route::post('getbalance', [ShanGetBalanceController::class, 'getBalance']);
    Route::post('/client/balance-update', [BalanceUpdateCallbackController::class, 'handleBalanceUpdate']); 
});

// Pone Wine client callback route
Route::post('/pone-wine/client-report', [PoneWineClientBalanceUpdateController::class, 'PoneWineClientReport']);

// provider shan api
Route::group(['prefix' => 'provider/shan'], function () {
    Route::post('ShanGetBalances', [ShankomeeGetBalanceController::class, 'shangetbalance']);
    // Route::post('ShanLaunchGame', [ShankomeeGetBalanceController::class, 'LaunchGame']);

    // Agent management routes
    Route::post('users-by-agent', [ShanAgentController::class, 'getUsersByAgent']);
    Route::get('users-by-agent-one', [ShanAgentController::class, 'getUsersByAgentOne']);
    Route::get('agents', [ShanAgentController::class, 'getAllAgentsWithUserCounts']);
    Route::post('agent-details', [ShanAgentController::class, 'getAgentById']);
    
    
});

// Report transaction routes
Route::post('report-transactions', [ShanAgentReportTransactionController::class, 'getReportTransactions']);
Route::post('member-transactions', [ShanAgentReportTransactionController::class, 'getMemberTransactions']);
Route::post('today-transactions', [ShanAgentReportTransactionController::class, 'getTodayTransactions']);



// Route::post('/auth/login', [LoginController::class, 'login']);
// Route::post('/auth/logout', [LoginController::class, 'logout']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    //Route::get('/promotions', [PromotionControllerAlias::class, 'index']);
    Route::get('/launch-game', [LaunchGameController::class, 'launchGame']);
});

// DigitBet API routes, protected by sanctum middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/digitbet', [DigitBetController::class, 'store']); // Endpoint for placing a bet
    Route::get('/digitbet/history', [DigitBetController::class, 'history']); // Endpoint for getting bet history
    Route::post('/digit-slot/bet', [DigitSlotController::class, 'bet']);
});
