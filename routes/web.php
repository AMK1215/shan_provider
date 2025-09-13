<?php

use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\SubAccountController;
use App\Http\Controllers\TelegramBotController;
use App\Http\Controllers\TransferLogController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

require_once __DIR__.'/admin.php';

Auth::routes();
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/profile', [HomeController::class, 'profile'])->name('profile');

// auth routes
Route::get('/login', [LoginController::class, 'showLogin'])->name('showLogin');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('get-change-password', [LoginController::class, 'changePassword'])->name('getChangePassword');
Route::post('update-password/{user}', [LoginController::class, 'updatePassword'])->name('updatePassword');

// telegram routes
Route::get('/telegram-home', [App\Http\Controllers\TelegramBotController::class, 'index']);
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Route::get('sendMessage', [App\Http\Controllers\TelegramBotController::class, 'sendMessage']);
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Route::get('sendPhoto', [App\Http\Controllers\TelegramBotController::class, 'sendPhoto']);
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Route::get('sendAudio', [App\Http\Controllers\TelegramBotController::class, 'sendAudio']);
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Route::get('sendVideo', [App\Http\Controllers\TelegramBotController::class, 'sendVideo']);
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Route::get('sendVoice', [App\Http\Controllers\TelegramBotController::class, 'sendVoice']);
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Route::get('sendDocument', [App\Http\Controllers\TelegramBotController::class, 'sendDocument']);
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Route::get('sendLocation', [App\Http\Controllers\TelegramBotController::class, 'sendLocation']);
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Route::get('sendVenue', [App\Http\Controllers\TelegramBotController::class, 'sendVenue']);
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Route::get('sendContact', [App\Http\Controllers\TelegramBotController::class, 'sendContact']);
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Route::get('sendPoll', [App\Http\Controllers\TelegramBotController::class, 'sendPoll']);
// ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
Route::post('telegram-message-webhook', [App\Http\Controllers\TelegramBotController::class, 'telegram_webhook']);

// TeleBot Webhook Route - Using our custom controller instead
// Route::post('telegram/webhook', [WeStacks\TeleBot\Laravel\Controllers\WebhookController::class, 'handle']);

// Webhook Management Routes
Route::get('telegram/webhook/info', [App\Http\Controllers\TelegramBotController::class, 'getWebhookInfo']);
Route::get('telegram/webhook/set', [App\Http\Controllers\TelegramBotController::class, 'setWebhook']);
Route::get('telegram/webhook/delete', [App\Http\Controllers\TelegramBotController::class, 'deleteWebhook']);

// Test Panel Route
Route::get('telegram/test', [App\Http\Controllers\TelegramBotController::class, 'testPanel']);

Route::post('/web-chat/send', [TelegramBotController::class, 'send'])->name('web.telegram.send');

// Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
//     Route::get('/transfer-logs', [TransferLogController::class, 'index'])->name('transfer-logs.index');
// });

Route::get('admin/product/game-list', [\App\Http\Controllers\Admin\ProductController::class, 'GameListFetch'])->name('admin.product.game-list');

// Sub-Agent Permission Routes
// Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
//     // Player management routes
//     Route::middleware(['permission:player_view'])->group(function () {
//         Route::get('players', [PlayerController::class, 'index'])->name('player.index');
//         Route::get('players/{player}', [PlayerController::class, 'show'])->name('player.show');
//     });

//     // Player creation routes
//     Route::middleware(['permission:player_create'])->group(function () {
//         Route::get('players/create', [PlayerController::class, 'create'])->name('player.create');
//         Route::post('players', [PlayerController::class, 'store'])->name('player.store');
//     });

//     // Player edit routes
//     Route::middleware(['permission:player_edit'])->group(function () {
//         Route::get('players/{player}/edit', [PlayerController::class, 'edit'])->name('player.edit');
//         Route::put('players/{player}', [PlayerController::class, 'update'])->name('player.update');
//     });

//     // Deposit/Withdraw routes
//     Route::middleware(['permission:deposit_withdraw'])->group(function () {
//         Route::get('players/{player}/deposit', [PlayerController::class, 'deposit'])->name('player.deposit');
//         Route::post('players/{player}/deposit', [PlayerController::class, 'processDeposit'])->name('player.process-deposit');
//         Route::get('players/{player}/withdraw', [PlayerController::class, 'withdraw'])->name('player.withdraw');
//         Route::post('players/{player}/withdraw', [PlayerController::class, 'processWithdraw'])->name('player.process-withdraw');
//     });

//     // Sub-agent management routes
//     Route::get('subacc/{id}/permissions', [SubAccountController::class, 'viewPermissions'])->name('subacc.permissions.view');
//     Route::put('subacc/{id}/permissions', [SubAccountController::class, 'updatePermissions'])->name('subacc.permissions.update');
// });
