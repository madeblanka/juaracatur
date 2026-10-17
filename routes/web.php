<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RedemptionController as AdminRedemptionController;
use App\Http\Controllers\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Admin\TournamentController as AdminTournamentController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VenueController as AdminVenueController;
use App\Http\Controllers\Admin\VoucherController as AdminVoucherController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VenueRegisterController;
use App\Http\Controllers\Vendor\TournamentController as VendorTournamentController;
use App\Http\Controllers\VenueOwner\DashboardController as VenueOwnerDashboardController;
use App\Http\Controllers\VenueOwner\PhotoController as VenueOwnerPhotoController;
use App\Http\Controllers\ExchangeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PointHistoryController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TournamentController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\Vendor\ItemController as VendorItemController;
use App\Http\Controllers\Vendor\RedemptionController as VendorRedemptionController;
use App\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/wilayah/children', [WilayahController::class, 'children'])->name('wilayah.children');

Route::middleware('guest')->group(function () {
    Route::get('/daftar-venue', [VenueRegisterController::class, 'create'])->name('venue.register');
    Route::post('/daftar-venue', [VenueRegisterController::class, 'store'])->middleware('throttle:6,1')->name('venue.register.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:6,1');
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->middleware('throttle:6,1')->name('password.update');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');

    Route::get('/dashboard', fn () => redirect()->route('landing'))->name('dashboard');

    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
    Route::middleware('player.verified')->get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
    Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    Route::post('/rooms/{room}/cancel', [RoomController::class, 'cancel'])->name('rooms.cancel');
    Route::post('/rooms/{room}/report', [RoomController::class, 'report'])->name('rooms.report');
    Route::get('/rooms/{room}/messages', [RoomController::class, 'messages'])->name('rooms.messages.index');
    Route::post('/rooms/{room}/messages', [RoomController::class, 'sendMessage'])->name('rooms.messages.store');

    Route::middleware('player.verified')->group(function () {
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::post('/rooms/{room}/accept', [RoomController::class, 'accept'])->name('rooms.accept');
        Route::post('/rooms/{room}/invite', [RoomController::class, 'invite'])->name('rooms.invite');
        Route::post('/tournaments/{tournament}/daftar', [TournamentController::class, 'join'])->name('tournaments.join');
        Route::post('/tournaments/{tournament}/keluar', [TournamentController::class, 'leave'])->name('tournaments.leave');
        Route::post('/tournaments/{tournament}/undangan',[TournamentController::class, 'respondInvite'])->name('tournaments.invite.respond');
        Route::post('/tournaments/{tournament}/pertandingan/{match}/lapor', [TournamentController::class, 'reportMatch'])->name('tournaments.matches.report');
    });

    Route::get('/tournaments', [TournamentController::class, 'index'])->name('tournaments.index');
    Route::get('/tournaments/{tournament}', [TournamentController::class, 'show'])->name('tournaments.show');

    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');

    Route::get('/profile', [PlayerController::class, 'me'])->name('profile.show');
    Route::get('/players', [PlayerController::class, 'index'])->name('players.index');
    Route::get('/players/{user}', [PlayerController::class, 'show'])->name('players.show');

    Route::get('/venues', [VenueController::class, 'index'])->name('venues.index');
    Route::get('/venues/{venue}', [VenueController::class, 'show'])->name('venues.show');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');

    Route::get('/tukar-poin', [ExchangeController::class, 'index'])->name('exchange.index');
    Route::post('/tukar-poin/{item}', [ExchangeController::class, 'redeem'])->name('exchange.redeem');
    Route::get('/riwayat-poin', [PointHistoryController::class, 'index'])->name('points.history');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
});

Route::middleware(['auth', 'venue'])->prefix('venue')->name('venue.')->group(function () {
    Route::get('/', [VenueOwnerDashboardController::class, 'index'])->name('dashboard');
    Route::put('/profil', [VenueOwnerDashboardController::class, 'update'])->name('update');
    Route::post('/foto', [VenueOwnerPhotoController::class, 'store'])->name('photos.store');
    Route::delete('/foto/{photo}', [VenueOwnerPhotoController::class, 'destroy'])->name('photos.destroy');
});

Route::middleware(['auth', 'vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/', fn () => redirect()->route('vendor.items.index'))->name('dashboard');
    Route::resource('barang', VendorItemController::class)->parameters(['barang' => 'item'])->names('items')->except(['show']);
    Route::get('/penukaran', [VendorRedemptionController::class, 'index'])->name('redemptions.index');
    Route::post('/penukaran/{redemption}/selesai', [VendorRedemptionController::class, 'complete'])->name('redemptions.complete');

    Route::prefix('turnamen')->name('tournaments.')->group(function () {
        Route::get('/', [VendorTournamentController::class, 'index'])->name('index');
        Route::get('/create', [VendorTournamentController::class, 'create'])->name('create');
        Route::post('/', [VendorTournamentController::class, 'store'])->name('store');

        Route::middleware('owns.tournament')->group(function () {
            Route::get('/{tournament}', [VendorTournamentController::class, 'show'])->name('show');
            Route::put('/{tournament}/bagan', [VendorTournamentController::class, 'updateBagan'])->name('bagan.update');
            Route::delete('/{tournament}/bagan', [VendorTournamentController::class, 'destroyBagan'])->name('bagan.destroy');
            Route::put('/{tournament}/lokasi', [VendorTournamentController::class, 'updateVenue'])->name('venue.update');
            Route::put('/{tournament}/biaya-daftar', [VendorTournamentController::class, 'updateEntryPoints'])->name('entry.update');
            Route::put('/{tournament}/deskripsi', [VendorTournamentController::class, 'updateDescription'])->name('description.update');
            Route::post('/{tournament}/flyer', [VendorTournamentController::class, 'updateFlyer'])->name('flyer.update');
            Route::post('/{tournament}/undang', [VendorTournamentController::class, 'invite'])->name('invite');
            Route::post('/{tournament}/participants', [VendorTournamentController::class, 'addParticipant'])->name('participants.store');
            Route::delete('/{tournament}/participants/{user}', [VendorTournamentController::class, 'removeParticipant'])->name('participants.destroy');
            Route::post('/{tournament}/generate', [VendorTournamentController::class, 'generate'])->name('generate');
            Route::post('/{tournament}/matches/{match}/result', [VendorTournamentController::class, 'setMatchResult'])->name('matches.result');
        });
    });
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::resource('venues', AdminVenueController::class)->except(['show']);

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/aktivitas', [AdminUserController::class, 'activity'])->name('users.activity');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::delete('/users/{user}/foto', [AdminUserController::class, 'destroyPhoto'])->name('users.photo.destroy');
    Route::post('/users/{user}/adjust-points', [AdminUserController::class, 'adjustPoints'])->name('users.adjust-points');

    Route::get('/penukaran', [AdminRedemptionController::class, 'index'])->name('redemptions.index');
    Route::resource('vouchers', AdminVoucherController::class)->parameters(['vouchers' => 'voucher'])->except(['show']);

    Route::get('/rooms', [AdminRoomController::class, 'index'])->name('rooms.index');
    Route::post('/rooms/{room}/resolve', [AdminRoomController::class, 'resolve'])->name('rooms.resolve');

    Route::get('/tournaments', [AdminTournamentController::class, 'index'])->name('tournaments.index');
    Route::get('/tournaments/create', [AdminTournamentController::class, 'create'])->name('tournaments.create');
    Route::post('/tournaments', [AdminTournamentController::class, 'store'])->name('tournaments.store');
    Route::get('/tournaments/{tournament}', [AdminTournamentController::class, 'show'])->name('tournaments.show');
    Route::put('/tournaments/{tournament}/bagan', [AdminTournamentController::class, 'updateBagan'])->name('tournaments.bagan.update');
    Route::delete('/tournaments/{tournament}/bagan', [AdminTournamentController::class, 'destroyBagan'])->name('tournaments.bagan.destroy');
    Route::put('/tournaments/{tournament}/lokasi', [AdminTournamentController::class, 'updateVenue'])->name('tournaments.venue.update');
    Route::put('/tournaments/{tournament}/biaya-daftar', [AdminTournamentController::class, 'updateEntryPoints'])->name('tournaments.entry.update');
    Route::put('/tournaments/{tournament}/deskripsi',[AdminTournamentController::class, 'updateDescription'])->name('tournaments.description.update');
    Route::post('/tournaments/{tournament}/flyer',[AdminTournamentController::class, 'updateFlyer'])->name('tournaments.flyer.update');
    Route::post('/tournaments/{tournament}/undang', [AdminTournamentController::class, 'invite'])->name('tournaments.invite');
    Route::post('/tournaments/{tournament}/participants', [AdminTournamentController::class, 'addParticipant'])->name('tournaments.participants.store');
    Route::delete('/tournaments/{tournament}/participants/{user}', [AdminTournamentController::class, 'removeParticipant'])->name('tournaments.participants.destroy');
    Route::post('/tournaments/{tournament}/generate', [AdminTournamentController::class, 'generate'])->name('tournaments.generate');
    Route::post('/tournaments/{tournament}/matches/{match}/result', [AdminTournamentController::class, 'setMatchResult'])->name('tournaments.matches.result');
});
