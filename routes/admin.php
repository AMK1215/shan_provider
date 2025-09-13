<?php

use App\Http\Controllers\Admin\AdsVedioController;
use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\BannerAdsController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BannerTextController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DepositRequestController;
use App\Http\Controllers\Admin\GameListController;
use App\Http\Controllers\Admin\LocalWagerController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Controllers\Admin\PaymentTypeController;
use App\Http\Controllers\Admin\PlayerController;
use App\Http\Controllers\Admin\PlayerReportController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\Shan\ShanPlayerReportController;
use App\Http\Controllers\Admin\SubAccountController;
use App\Http\Controllers\Admin\TopTenWithdrawController;
use App\Http\Controllers\Admin\TransferLogController;
use App\Http\Controllers\Admin\TwoD\TwoDigitController;
use App\Http\Controllers\Admin\WagerListController;
use App\Http\Controllers\Admin\WinnerTextController;
use App\Http\Controllers\Admin\WithDrawRequestController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => ['auth', 'checkBanned'],
], function () {

    Route::post('balance-up', [HomeController::class, 'balanceUp'])->name('balanceUp');

    Route::get('logs/{id}', [HomeController::class, 'logs'])->name('logs');

    // to do
    Route::get('/changePassword/{user}', [HomeController::class, 'changePassword'])->name('changePassword');
    Route::post('/updatePassword/{user}', [HomeController::class, 'updatePassword'])->name('updatePassword');

    Route::get('/changeplayersite/{user}', [HomeController::class, 'changePlayerSite'])->name('changeSiteName');

    Route::post('/updatePlayersite/{user}', [HomeController::class, 'updatePlayerSiteLink'])->name('updateSiteLink');

    Route::get('/player-list', [HomeController::class, 'playerList'])->name('playerList');

    // banner etc start

    Route::resource('video-upload', AdsVedioController::class);
    Route::resource('winner_text', WinnerTextController::class);
    Route::resource('top-10-withdraws', TopTenWithdrawController::class);
    Route::resource('banners', BannerController::class);
    Route::resource('adsbanners', BannerAdsController::class);
    Route::resource('text', BannerTextController::class);
    Route::resource('/promotions', PromotionController::class);
    Route::resource('contact', ContactController::class);
    Route::resource('paymentTypes', PaymentTypeController::class);
    Route::resource('bank', BankController::class);
    // Route::resource('product', ProductController::class);

    // agent start
    Route::resource('agent', AgentController::class);
    Route::get('agent-player-report/{id}', [AgentController::class, 'getPlayerReports'])->name('agent.getPlayerReports');
    Route::get('agent-cash-in/{id}', [AgentController::class, 'getCashIn'])->name('agent.getCashIn');
    Route::post('agent-cash-in/{id}', [AgentController::class, 'makeCashIn'])->name('agent.makeCashIn');
    Route::get('agent/cash-out/{id}', [AgentController::class, 'getCashOut'])->name('agent.getCashOut');
    Route::post('agent/cash-out/update/{id}', [AgentController::class, 'makeCashOut'])
        ->name('agent.makeCashOut');
    Route::put('agent/{id}/ban', [AgentController::class, 'banAgent'])->name('agent.ban');
    Route::get('agent-changepassword/{id}', [AgentController::class, 'getChangePassword'])->name('agent.getChangePassword');
    Route::post('agent-changepassword/{id}', [AgentController::class, 'makeChangePassword'])->name('agent.makeChangePassword');
    Route::get('agent-profile/{id}', [AgentController::class, 'agentProfile'])->name('agent.profile');
    // agent end

    // sub-agent start
    Route::resource('subacc', SubAccountController::class);
    Route::put('subacc/{id}/ban', [SubAccountController::class, 'banSubAcc'])->name('subacc.ban');
    Route::get('subacc-changepassword/{id}', [SubAccountController::class, 'getChangePassword'])->name('subacc.getChangePassword');
    Route::post('subacc-changepassword/{id}', [SubAccountController::class, 'makeChangePassword'])->name('subacc.makeChangePassword');
    Route::get('subacc-permission/{id}', [SubAccountController::class, 'permission'])->name('subacc.permission');
    Route::post('subacc-permission/update/{id}', [SubAccountController::class, 'updatePermission'])->name('subacc.permission.update');
    Route::get('subacc/{id}/permissions', [SubAccountController::class, 'viewPermissions'])->name('subacc.permissions.view');
    Route::put('subacc/{id}/permissions', [SubAccountController::class, 'updatePermissions'])->name('subacc.permissions.update');

    Route::get('subacc-profile/{id}', [SubAccountController::class, 'subAgentProfile'])
        ->name('subacc.profile');
    Route::get('subacc-agent-players', [SubAccountController::class, 'agentPlayers'])
        ->name('subacc.agent_players');
    Route::get('subacc/player/{id}/report', [SubAccountController::class, 'playerReport'])->name('subacc.player.report_detail');
    Route::middleware(['permission:process_deposit'])->group(function () {
        Route::get('subacc/player-cash-in/{player}', [SubAccountController::class, 'getCashIn'])->name('subacc.player.getCashIn');
        Route::post('subacc/player-cash-in/{player}', [SubAccountController::class, 'makeCashIn'])->name('subacc.player.makeCashIn');

    });

    Route::middleware(['permission:process_withdraw'])->group(function () {
        Route::get('/subacc/player/cash-out/{player}', [SubAccountController::class, 'getCashOut'])->name('subacc.player.getCashOut');
        Route::post('/subacc/player/cash-out/update/{player}', [SubAccountController::class, 'makeCashOut'])
            ->name('subacc.player.makeCashOut');
    });

    Route::get('/subagentacc/tran-logs', [SubAccountController::class, 'SubAgentTransferLog'])->name('subacc.tran.logs');

    // sub-agent end
    // agent create player start
    // Route::resource('player', PlayerController::class);
    // sub-agent permission start
    // Player management routes
    Route::middleware(['permission:player_view'])->group(function () {
        Route::get('players', [PlayerController::class, 'index'])->name('player.index');
        Route::get('players/{player}', [PlayerController::class, 'show'])->name('player.show');
    });

    Route::middleware(['permission:edit_player'])->group(function () {
        Route::get('players/{player}/edit', [PlayerController::class, 'edit'])->name('player.edit');
        Route::put('players/{player}', [PlayerController::class, 'update'])->name('player.update');
    });

    // Player creation routes
    Route::get('agent/players/create', [PlayerController::class, 'create'])->name('agent.player.create');
    Route::post('agent/players', [PlayerController::class, 'store'])->name('agent.player.store');
    Route::middleware(['permission:create_player'])->group(function () {

        Route::get('/subagentacc/player/create', [SubAccountController::class, 'PlayerCreate'])->name('subacc.player.create');
        Route::post('/subagentacc/player/store', [SubAccountController::class, 'PlayerStore'])->name('subacc.player.store');
    });

    // Withdraw routes (for process_withdraw permission)
    Route::middleware(['permission:process_withdraw'])->group(function () {
        Route::get('finicialwithdraw', [WithDrawRequestController::class, 'index'])->name('agent.withdraw');
        Route::post('finicialwithdraw/{withdraw}', [WithDrawRequestController::class, 'statusChangeIndex'])->name('agent.withdrawStatusUpdate');
        Route::post('finicialwithdraw/reject/{withdraw}', [WithDrawRequestController::class, 'statusChangeReject'])->name('agent.withdrawStatusreject');
        Route::get('finicialwithdraw/{withdraw}', [WithDrawRequestController::class, 'WithdrawShowLog'])->name('agent.withdrawLog');
    });

    // Deposit routes (for both parent agents and sub-agents)
    Route::middleware(['permission:process_deposit|view_deposit_requests'])->group(function () {
        Route::get('finicialdeposit', [DepositRequestController::class, 'index'])->name('agent.deposit');
        Route::get('finicialdeposit/{deposit}', [DepositRequestController::class, 'view'])->name('agent.depositView');
        Route::post('finicialdeposit/{deposit}', [DepositRequestController::class, 'statusChangeIndex'])->name('agent.depositStatusUpdate');
        Route::post('finicialdeposit/reject/{deposit}', [DepositRequestController::class, 'statusChangeReject'])->name('agent.depositStatusreject');
        Route::get('finicialdeposit/{deposit}/log', [DepositRequestController::class, 'DepositShowLog'])->name('agent.depositLog');
    });

    // Cash-in/cash-out routes (still using deposit_withdraw permission)
    Route::middleware(['permission:deposit_withdraw'])->group(function () {
        Route::get('player-cash-in/{player}', [PlayerController::class, 'getCashIn'])->name('player.getCashIn');
        Route::post('player-cash-in/{player}', [PlayerController::class, 'makeCashIn'])->name('player.makeCashIn');
        Route::get('player/cash-out/{player}', [PlayerController::class, 'getCashOut'])->name('player.getCashOut');
        Route::post('player/cash-out/update/{player}', [PlayerController::class, 'makeCashOut'])->name('player.makeCashOut');
    });

    // Player ban route
    Route::middleware(['permission:ban_player'])->group(function () {
        Route::put('player/{id}/ban', [PlayerController::class, 'banUser'])->name('player.ban');
    });

    // Player change password routes
    Route::middleware(['permission:change_player_password'])->group(function () {
        Route::get('player-changepassword/{id}', [PlayerController::class, 'getChangePassword'])->name('player.getChangePassword');
        Route::post('player-changepassword/{id}', [PlayerController::class, 'makeChangePassword'])->name('player.makeChangePassword');
    });

    // sub-agent permission end
    // Route::put('player/{id}/ban', [PlayerController::class, 'banUser'])->name('player.ban');
    // Route::get('player-changepassword/{id}', [PlayerController::class, 'getChangePassword'])->name('player.getChangePassword');
    // Route::post('player-changepassword/{id}', [PlayerController::class, 'makeChangePassword'])->name('player.makeChangePassword');
    Route::get('/players-list', [PlayerController::class, 'player_with_agent'])->name('playerListForAdmin');
    // agent create player end
    // report log

    Route::get('/agent-report/{id}', [AgentController::class, 'agentReportIndex'])->name('agent.report');
    Route::get('/player-report/{id}', [PlayerController::class, 'playerReportIndex'])->name('player.report');

    // Shan Report
    Route::get('/shan-report', [ReportController::class, 'shanReportIndex'])->name('shan_report');

    // master, agent sub-agent end
    Route::get('/transfer-logs', [TransferLogController::class, 'index'])->name('transfer-logs.index');

    // Route::get('transer-log', [TransferLogController::class, 'index'])->name('transferLog');
    Route::get('playertransferlog/{id}', [TransferLogController::class, 'PlayertransferLog'])->name('PlayertransferLogDetail');

    Route::get('wager-list', [WagerListController::class, 'index'])->name('wager-list');
    Route::get('wager-list/fetch', [WagerListController::class, 'fetch'])->name('wager-list.fetch');
    Route::get('wager-list/export-csv', [WagerListController::class, 'exportCsv'])->name('wager-list.export-csv');
    Route::get('wager-list/{id}', [WagerListController::class, 'show'])->name('wager-list.show');
    Route::get('wager-list/{wager_code}/game-history', [WagerListController::class, 'gameHistory'])->name('wager-list.game-history');

    Route::get('local-wager', [LocalWagerController::class, 'index'])->name('local-wager.index');
    Route::get('local-wager/{id}', [LocalWagerController::class, 'show'])->name('local-wager.show');

    Route::get('report', [ReportController::class, 'index'])->name('report.index');
    Route::get('report/{member_account}', [ReportController::class, 'show'])->name('report.detail');
    Route::get('player-report', [PlayerReportController::class, 'summary'])->name('player_report.summary');
    Route::get('reports/daily-win-loss', [ReportController::class, 'dailyWinLossReport'])->name('reports.daily_win_loss');
    Route::get('reports/game-log-report', [ReportController::class, 'gameLogReport'])->name('reports.game_log_report');
    // Route::get('report/detail/{member_account}', [\App\Http\Controllers\Admin\ReportController::class, 'getReportDetails'])->name('admin.report.detail');
    // provider start
    Route::get('gametypes', [ProductController::class, 'index'])->name('gametypes.index');
    Route::post('/game-types/{productId}/toggle-status', [ProductController::class, 'toggleStatus'])->name('gametypes.toggle-status');
    Route::get('gametypes/{game_type_id}/product/{product_id}', [ProductController::class, 'edit'])->name('gametypes.edit');
    Route::post('gametypes/{game_type_id}/product/{product_id}', [ProductController::class, 'update'])->name('gametypes.update');
    Route::post('admin/gametypes/{gameTypeId}/{productId}/update', [ProductController::class, 'update'])
        ->name('gametypesproduct.update');

    // game list start
    Route::get('all-game-lists', [GameListController::class, 'GetGameList'])->name('gameLists.index');
    Route::get('all-game-lists/{id}', [GameListController::class, 'edit'])->name('gameLists.edit');
    Route::post('all-game-lists/{id}', [GameListController::class, 'update'])->name('gameLists.update');

    Route::patch('gameLists/{id}/toggleStatus', [GameListController::class, 'toggleStatus'])->name('gameLists.toggleStatus');

    Route::patch('hotgameLists/{id}/toggleStatus', [GameListController::class, 'HotGameStatus'])->name('HotGame.toggleStatus');

    // pp hot

    Route::patch('pphotgameLists/{id}/toggleStatus', [GameListController::class, 'PPHotGameStatus'])->name('PPHotGame.toggleStatus');
    Route::get('game-list/{gameList}/edit', [GameListController::class, 'edit'])->name('game_list.edit');
    Route::post('/game-list/{id}/update-image-url', [GameListController::class, 'updateImageUrl'])->name('game_list.update_image_url');
    Route::get('game-list-order/{gameList}/edit', [GameListController::class, 'GameListOrderedit'])->name('game_list_order.edit');
    Route::post('/game-lists/{id}/update-order', [GameListController::class, 'updateOrder'])->name('GameListOrderUpdate');

    // two digit start
    Route::get('twod/settings', [TwoDigitController::class, 'headCloseDigit'])->name('twod.settings');
    Route::get('choose-close-digit', [TwoDigitController::class, 'chooseCloseDigit'])->name('choose-close-digit');
    Route::post('head-close-digit/toggle-status', [TwoDigitController::class, 'toggleStatus'])->name('head-close-digit.toggle-status');
    Route::post('choose-close-digit/toggle-status', [TwoDigitController::class, 'toggleStatus'])->name('choose-close-digit.toggle-status');
    Route::post('choose-close-digit/toggle-status', [TwoDigitController::class, 'toggleChooseDigitStatus'])->name('choose-close-digit.toggle-status');
    Route::post('battle/toggle-status', [TwoDigitController::class, 'toggleBattleStatus'])->name('battle.toggle-status');
    Route::post('two-d-limit/store', [TwoDigitController::class, 'storeTwoDLimit'])->name('two-d-limit.store');
    // 2d bet slip list
    Route::get('twod/bet-slip-list', [TwoDigitController::class, 'betSlipList'])->name('twod.bet-slip-list');
    Route::get('twod/bet-slip-details/{slip_id}', [TwoDigitController::class, 'betSlipDetails'])->name('twod.bet-slip-details');
    Route::post('two-d-result/store', [TwoDigitController::class, 'storeTwoDResult'])->name('two-d-result.store');
    Route::get('twod/daily-ledger', [TwoDigitController::class, 'dailyLedger'])->name('twod.daily-ledger');
    // Route::get('twod/daily-ledger-morning', [TwoDigitController::class, 'dailyLedgerMorning'])->name('twod.daily-ledger-morning');
    // Route::get('twod/daily-ledger-evening', [TwoDigitController::class, 'dailyLedgerEvening'])->name('twod.daily-ledger-evening');
    Route::get('twod/daily-winners', [TwoDigitController::class, 'dailyWinners'])->name('twod.daily-winners');
    // two digit end
    // shan player report
    Route::get('/shan-player-report', [ShanPlayerReportController::class, 'index'])->name('shan.player.report');
    Route::get('/shan-player-report/grouped', [ShanPlayerReportController::class, 'groupByMemberAccount'])->name('shan.player.report.grouped');
    Route::get('/shan-player-report/detail/{memberAccount}', [ShanPlayerReportController::class, 'playerDetail'])->name('shan.player.report.detail');
    
    // Laravel Log Viewer Routes
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', [LogController::class, 'index'])->name('index');
        Route::get('/simple', function () {
            return view('admin.logs.simple');
        })->name('simple');
        Route::get('/download', [LogController::class, 'download'])->name('download');
        Route::delete('/clear', [LogController::class, 'clear'])->name('clear');
        Route::get('/stats', [LogController::class, 'stats'])->name('stats');
    });
});
