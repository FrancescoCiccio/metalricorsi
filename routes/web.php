<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\DownloadController;
use App\Http\Middleware\SetLocale;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::post('language/{locale}', function (string $locale) {
    abort_unless(in_array($locale, SetLocale::SUPPORTED, true), 404);

    session(['locale' => $locale]);

    request()->user()?->forceFill(['locale' => $locale])->save();

    return back();
})->name('language.update');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::get('/video', [VideoController::class, 'index'])->name('videos.index');

Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/courses/{course}/subscribe', [CourseController::class, 'subscribe'])->name('courses.subscribe');

Route::get('/downloads', [DownloadController::class, 'index'])->name('downloads.index');

require __DIR__ . '/auth.php';
