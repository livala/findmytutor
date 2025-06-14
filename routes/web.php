<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TutorController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SessionDashboardController;
use App\Http\Controllers\FavoriteTutorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AvailabilitySlotController;
use App\Http\Controllers\ReviewController;
use Illuminate\Http\Request;
use App\Models\TutorProfile;

Route::get('/', function () {
    return view('welcome');
});

// Public Tutor Routes
Route::get('/tutors', [TutorController::class, 'index'])->name('tutors.index');
Route::get('/tutors/{id}', [TutorController::class, 'show'])->name('tutors.show');
Route::get('/tutors/subject/{subject}', [TutorController::class, 'showBySubject'])->name('tutors.bySubject');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/dashboard/sessions', [SessionDashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('sessions.dashboard');

// Auth-Protected Routes
Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Favorites
    Route::post('/tutors/{tutorProfile}/favorite', [FavoriteTutorController::class, 'store'])->name('tutors.favorite');
    Route::delete('/tutors/{tutorProfile}/unfavorite', [FavoriteTutorController::class, 'destroy'])->name('tutors.unfavorite');

    // Messages
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{user}', [MessageController::class, 'store'])->name('messages.store');

    // Availability
    Route::get('/availability', [AvailabilitySlotController::class, 'index'])->name('availability.index');
    Route::post('/availability', [AvailabilitySlotController::class, 'store'])->name('availability.store');
    Route::delete('/availability/{availabilitySlot}', [AvailabilitySlotController::class, 'destroy'])->name('availability.destroy');

    // Reviews
    Route::post('/tutors/{tutor}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Become a Tutor
    Route::get('/become-a-tutor', [TutorController::class, 'create'])->name('tutor.become');
    Route::post('/become-a-tutor', [TutorController::class, 'store'])->name('tutor.save');

    // Session Actions (moved inside auth middleware group)
    Route::post('/sessions/{session}/approve', [SessionController::class, 'approve'])->name('sessions.approve');
    Route::post('/sessions/{session}/reject', [SessionController::class, 'reject'])->name('sessions.reject');
    Route::post('/sessions/{session}/cancel', [SessionController::class, 'cancel'])->name('sessions.cancel');

    // Book a session
    Route::post('/sessions/book/{tutor}', [SessionController::class, 'book'])->name('sessions.book');

    Route::get('/tutor-suggestions', function (Request $request) {
        $query = $request->input('name');

        return TutorProfile::with('user')
            ->whereHas('user', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%"); // ← fix is here
            })
            ->take(5)
            ->get()
            ->map(function ($tutor) {
                return [
                    'id' => $tutor->id,
                    'name' => $tutor->user->name ?? 'No name',
                    'bio' => $tutor->bio ?? 'No bio',
                ];
            });
    });

});

require __DIR__.'/auth.php';
