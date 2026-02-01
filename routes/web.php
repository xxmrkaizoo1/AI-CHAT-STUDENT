<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ChatController;
use App\Http\Controllers\TeacherChatController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\TeacherDashboardController;

/*
|--------------------------------------------------------------------------
| Public / test routes
|--------------------------------------------------------------------------
*/

Route::get('/ai-test', function () {
    $res = Http::withOptions(['force_ip_resolve' => 'v4'])
        ->connectTimeout(100)
        ->timeout(120)
        ->asJson()
        ->post(rtrim(env('OLLAMA_URL'), '/') . '/api/generate', [
            'model' => env('OLLAMA_MODEL'),
            'prompt' => 'can u speak 100 words',
            'stream' => false,
            'options' => [
                'num_predict' => 100, // fast test
            ],
        ]);

    return $res->json();
});

/*
|--------------------------------------------------------------------------
| Redirect after login (student vs teacher)
|--------------------------------------------------------------------------
| Make sure RouteServiceProvider::HOME = '/redirect'
*/

Route::get('/', function () {
    // If not logged in, show landing dashboard (with login buttons)
    return view('dashboard');
});




Route::get('/redirect', function () {
    if (!auth()->check()) return redirect('/login');

    return auth()->user()->role === 'teacher'
        ? redirect('/teacher/dashboard')
        : redirect('/student/dashboard');
})->middleware('auth');

/*
|--------------------------------------------------------------------------
| Student routes (auth + role student)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:student'])->group(function () {

    // Student dashboard
    Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])
        ->name('student.dashboard');

    // Chatbot (student only)
    Route::get('/chat', [ChatController::class, 'index']);
    Route::post('/chat', [ChatController::class, 'send']);
    Route::post('/chat/clear', [ChatController::class, 'clear']);
});

/*
|--------------------------------------------------------------------------
| Teacher routes (auth + role teacher)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:teacher'])->group(function () {

    // Teacher dashboard
    Route::get('/teacher/dashboard', [TeacherDashboardController::class, 'index'])
        ->name('teacher.dashboard');

    // Teacher chat monitoring
    Route::get('/teacher/chats', [TeacherChatController::class, 'index']);
    Route::get('/teacher/chats/{sessionId}', [TeacherChatController::class, 'show']);
});
