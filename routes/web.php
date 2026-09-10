<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\CmsDashboardController;
use App\Http\Controllers\Admin\FileManagerController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\SiteContentController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\EmailOtpChallengeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Public\ContentController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::get('/', [ContentController::class, 'home'])->name('home');

/* Email one-time-code challenge (secondary MFA for password logins) */
Route::middleware('guest')->group(function () {
    Route::get('email-otp-challenge', [EmailOtpChallengeController::class, 'create'])->name('email-otp.login');
    Route::post('email-otp-challenge', [EmailOtpChallengeController::class, 'store'])
        ->middleware('throttle:two-factor')
        ->name('email-otp.login.store');
    Route::post('email-otp-challenge/resend', [EmailOtpChallengeController::class, 'resend'])
        ->middleware('throttle:two-factor')
        ->name('email-otp.resend');
});

/* Public bilingual school website */
Route::get('/academic-calendar', [ContentController::class, 'academicCalendar'])->name('academic-calendar');
Route::get('/activities', [ContentController::class, 'activities'])->name('activities');
Route::get('/timetable', [ContentController::class, 'timetable'])->name('timetable');
Route::get('/announcements', [ContentController::class, 'announcements'])->name('announcements');
Route::get('/news', [ContentController::class, 'newsIndex'])->name('news');
Route::get('/news/{slug}', [ContentController::class, 'newsShow'])->name('news.show');
Route::get('/downloads', [ContentController::class, 'downloads'])->name('downloads');
Route::get('/team', [ContentController::class, 'team'])->name('team');
Route::get('/contact', [ContentController::class, 'contact'])->name('contact');

/* Content management (CMS) — behind auth */
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', CmsDashboardController::class)->name('dashboard');

        Route::resource('news', NewsController::class)->except(['show'])->middleware('can:manage-news');
        Route::post('uploads', [UploadController::class, 'store'])->name('uploads.store');
        Route::post('uploads/file', [UploadController::class, 'storeFile'])->name('uploads.file');

        Route::resource('announcements', AnnouncementController::class)->except(['show'])->middleware('can:manage-content');

        Route::resource('users', UserController::class)->except(['show'])->middleware('can:manage-users');

        Route::middleware('can:manage-content')->group(function () {
            Route::get('files', [FileManagerController::class, 'index'])->name('files.index');
            Route::get('files/usage', [FileManagerController::class, 'usage'])->name('files.usage');
            Route::post('files', [FileManagerController::class, 'store'])->name('files.store');
            Route::delete('files/{path}', [FileManagerController::class, 'destroy'])->where('path', '.+')->name('files.destroy');

            Route::get('content', [SiteContentController::class, 'index'])->name('content.index');
            Route::get('content/{section}/edit', [SiteContentController::class, 'edit'])->name('content.edit');
            Route::put('content/{section}', [SiteContentController::class, 'update'])->name('content.update');
            Route::post('content/{section}/publish', [SiteContentController::class, 'publish'])->name('content.publish');
            Route::delete('content/{section}/draft', [SiteContentController::class, 'discard'])->name('content.discard');
            Route::delete('content/{section}', [SiteContentController::class, 'reset'])->name('content.reset');
        });
    });

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';
