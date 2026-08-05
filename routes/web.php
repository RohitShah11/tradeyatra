<?php

use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminContactController;
use App\Http\Controllers\Admin\AdminContributionController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminPlatformSettingsController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminSupportController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\AnalyticsEventController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrokerGuideController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CryptoIntelligenceController;
use App\Http\Controllers\DeltaExchangeController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\SharkExchangeController;
use App\Http\Controllers\SupportContributionController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\TradeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->middleware('analytics.visit')->name('home');

Route::get('/sitemap.xml', function () {
    $urls = [
        ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'weekly'],
        ['loc' => route('broker.guide'), 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => route('resources.index'), 'priority' => '0.8', 'changefreq' => 'weekly'],
        ['loc' => route('resources.delta'), 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => route('resources.shark'), 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => route('resources.crypto-india'), 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => route('founder'), 'priority' => '0.7', 'changefreq' => 'monthly'],
        ['loc' => route('support-fund.index'), 'priority' => '0.6', 'changefreq' => 'weekly'],
        ['loc' => route('legal.risk'), 'priority' => '0.5', 'changefreq' => 'yearly'],
        ['loc' => route('legal.terms'), 'priority' => '0.4', 'changefreq' => 'yearly'],
        ['loc' => route('legal.privacy'), 'priority' => '0.4', 'changefreq' => 'yearly'],
    ];

    return response()->view('sitemap', compact('urls'))->header('Content-Type', 'application/xml; charset=UTF-8');
})->name('sitemap');

Route::get('/robots.txt', function () {
    $content = implode("\n", [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin/',
        'Disallow: /dashboard',
        'Disallow: /trades',
        'Disallow: /analysis',
        'Disallow: /analytics',
        'Disallow: /calendar',
        'Disallow: /news',
        'Disallow: /crypto-intelligence',
        'Disallow: /shark/',
        'Disallow: /delta/',
        'Disallow: /profile',
        'Disallow: /ai-chat',
        'Disallow: /support/',
        '',
        'Sitemap: '.route('sitemap'),
        '',
    ]);

    return response($content)->header('Content-Type', 'text/plain; charset=UTF-8');
})->name('robots');

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::post('/analytics/events', [AnalyticsEventController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('analytics.events.store');

Route::get('/support-tradeyatra', [SupportContributionController::class, 'index'])
    ->middleware('analytics.visit')
    ->name('support-fund.index');
Route::post('/support-tradeyatra', [SupportContributionController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('support-fund.store');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::patch('/settings/automatic-trade-sync', [AdminPlatformSettingsController::class, 'updateAutomaticTradeSync'])
            ->name('settings.automatic-trade-sync');
        Route::get('/analytics', AdminAnalyticsController::class)->name('analytics');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::get('/contacts', [AdminContactController::class, 'index'])->name('contacts.index');
        Route::get('/contacts/{contactMessage}', [AdminContactController::class, 'show'])->name('contacts.show');
        Route::patch('/contacts/{contactMessage}', [AdminContactController::class, 'update'])->name('contacts.update');
        Route::get('/support', [AdminSupportController::class, 'index'])->name('support.index');
        Route::get('/support/{supportTicket}', [AdminSupportController::class, 'show'])->name('support.show');
        Route::post('/support/{supportTicket}/reply', [AdminSupportController::class, 'reply'])->middleware('throttle:30,1')->name('support.reply');
        Route::patch('/support/{supportTicket}', [AdminSupportController::class, 'update'])->name('support.update');
        Route::get('/contributions', [AdminContributionController::class, 'index'])->name('contributions.index');
        Route::patch('/contributions/{supportContribution}', [AdminContributionController::class, 'update'])->name('contributions.update');
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    });
});

Route::get('/terms', [LegalController::class, 'terms'])->middleware('analytics.visit')->name('legal.terms');
Route::get('/privacy', [LegalController::class, 'privacy'])->middleware('analytics.visit')->name('legal.privacy');
Route::get('/risk-disclaimer', [LegalController::class, 'risk'])->middleware('analytics.visit')->name('legal.risk');
Route::get('/broker-connection-guide', [BrokerGuideController::class, 'show'])->middleware('analytics.visit')->name('broker.guide');
Route::view('/founder', 'founder')->middleware('analytics.visit')->name('founder');
Route::get('/guides', [ResourceController::class, 'index'])->middleware('analytics.visit')->name('resources.index');
Route::get('/delta-exchange-trading-journal', [ResourceController::class, 'show'])
    ->defaults('slug', 'delta-exchange-trading-journal')->middleware('analytics.visit')->name('resources.delta');
Route::get('/shark-exchange-trading-journal', [ResourceController::class, 'show'])
    ->defaults('slug', 'shark-exchange-trading-journal')->middleware('analytics.visit')->name('resources.shark');
Route::get('/crypto-trading-journal-india', [ResourceController::class, 'show'])
    ->defaults('slug', 'crypto-trading-journal-india')->middleware('analytics.visit')->name('resources.crypto-india');
Route::redirect('/resources', '/guides', 301);
Route::redirect('/resources/delta-exchange-trading-journal', '/delta-exchange-trading-journal', 301);
Route::redirect('/resources/shark-exchange-trading-journal', '/shark-exchange-trading-journal', 301);
Route::redirect('/resources/crypto-trading-journal-india', '/crypto-trading-journal-india', 301);

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->middleware('analytics.visit')->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->middleware('analytics.visit')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'analytics.visit'])->group(function () {
    Route::get('/ai-chat', [AiChatController::class, 'index'])->name('ai-chat.index');
    Route::post('/ai-chat/conversations', [AiChatController::class, 'createConversation'])->name('ai-chat.conversations.create');
    Route::post('/ai-chat/messages', [AiChatController::class, 'message'])->middleware('throttle:20,1')->name('ai-chat.messages.store');
    Route::get('/dashboard', [TradeController::class, 'dashboard'])->name('dashboard');
    Route::get('/news', [NewsController::class, 'index'])->middleware('throttle:60,1')->name('news.index');
    Route::get('/crypto-intelligence', [CryptoIntelligenceController::class, 'index'])->middleware('throttle:30,1')->name('crypto-intelligence.index');
    Route::get('/dashboard/daily-plan', [TradeController::class, 'dailyPlan'])->middleware('throttle:60,1')->name('dashboard.daily-plan.show');
    Route::post('/dashboard/daily-plan', [TradeController::class, 'saveDailyPlan'])->middleware('throttle:30,1')->name('dashboard.daily-plan.save');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/trades-export', [TradeController::class, 'export'])->name('trades.export');
    Route::get('/trades/shark', [TradeController::class, 'sharkTrades'])->name('trades.shark');
    Route::get('/trades/delta', [TradeController::class, 'deltaTrades'])->name('trades.delta');
    Route::get('/trades/{trade}/chart', [TradeController::class, 'chart'])->name('trades.chart');
    Route::get('/trades/{trade}/candles', [TradeController::class, 'candles'])->middleware('throttle:60,1')->name('trades.candles');
    Route::post('/trades/{trade}/notes', [TradeController::class, 'updateNotes'])->name('trades.notes.update');
    Route::get('/trades/{trade}/screenshots/{filename}', [TradeController::class, 'screenshot'])->where('filename', '[^/]+')->name('trades.screenshot');
    Route::resource('trades', TradeController::class)->except('show');
    Route::get('/analytics', [TradeController::class, 'analysis'])->name('trades.analytics');
    Route::get('/analysis', [TradeController::class, 'analysis'])->name('trades.analysis');
    Route::get('/calendar', [TradeController::class, 'calendar'])->name('trades.calendar');
    Route::get('/shark/settings', [SharkExchangeController::class, 'settings'])->name('shark.settings');
    Route::post('/shark/settings', [SharkExchangeController::class, 'saveSettings'])->name('shark.settings.save');
    Route::get('/shark/sync', [SharkExchangeController::class, 'syncPage'])->name('shark.sync');
    Route::post('/shark/sync', [SharkExchangeController::class, 'sync'])->middleware('throttle:5,1')->name('shark.sync.run');
    Route::get('/shark/market', [SharkExchangeController::class, 'market'])->name('shark.market');
    Route::get('/delta/settings', [DeltaExchangeController::class, 'settings'])->name('delta.settings');
    Route::post('/delta/settings', [DeltaExchangeController::class, 'saveSettings'])->name('delta.settings.save');
    Route::post('/delta/test-connection', [DeltaExchangeController::class, 'testConnection'])->middleware('throttle:10,1')->name('delta.connection.test');
    Route::get('/delta/sync', [DeltaExchangeController::class, 'syncPage'])->name('delta.sync');
    Route::post('/delta/sync', [DeltaExchangeController::class, 'sync'])->middleware('throttle:5,1')->name('delta.sync.run');
    Route::get('/support', [SupportTicketController::class, 'index'])->name('support.index');
    Route::get('/support/create', [SupportTicketController::class, 'create'])->name('support.create');
    Route::post('/support', [SupportTicketController::class, 'store'])->middleware('throttle:10,1')->name('support.store');
    Route::get('/support/{supportTicket}', [SupportTicketController::class, 'show'])->name('support.show');
    Route::post('/support/{supportTicket}/reply', [SupportTicketController::class, 'reply'])->middleware('throttle:30,1')->name('support.reply');
    Route::patch('/support/{supportTicket}/status', [SupportTicketController::class, 'updateStatus'])->name('support.status');
});
