<?php

use App\Http\Livewire\AllCompletedWorks;
use App\Http\Livewire\ModeratorPage;
use App\Http\Livewire\WorkPage;
use App\Http\Livewire\SubmitWord;
use App\Http\Livewire\SubmitWork;
use App\Http\Livewire\SubmitAdvice;
use App\Http\Livewire\SubmitQuestion;
use Illuminate\Support\Facades\Route;
use App\Http\Livewire\SubmitDefinition;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/work/{work}', WorkPage::class)->name('work-page');
    Route::get('/submit-word', SubmitWord::class)->name('submit-word');
    Route::get('/submit-question', SubmitQuestion::class)->name('submit-question');
    Route::get('/define-word-of-the-day', SubmitDefinition::class)->name('submit-definition');
    Route::get('/give-advice', SubmitAdvice::class)->name('give-advice');
    Route::get('/all', AllCompletedWorks::class)->name('all');
    Route::get('/super-secret-moderator-page', ModeratorPage::class)->name('super-secret-moderator-page');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';
