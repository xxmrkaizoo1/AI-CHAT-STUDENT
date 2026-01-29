<?php
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiController;
use App\Http\Controllers\ChatController;


Route::get('/chat', [ChatController::class, 'index']);
Route::post('/chat', [ChatController::class, 'send']);
Route::post('/chat/clear', [ChatController::class, 'clear']);


Route::get('/ai-test', function () {
    $res = Http::withOptions(['force_ip_resolve' => 'v4'])
        ->connectTimeout( 100)
        ->timeout(120)
        ->asJson()
        ->post(env('OLLAMA_URL').'/api/generate', [
            'model' => env('OLLAMA_MODEL'),
            'prompt' => 'can u  speak  100 words ',
            'stream' => false,
            'options' => [
                'num_predict' => 100, // VERY SMALL → fastest test
            ],
        ]);

    return $res->json();
});





// use App\Http\Controllers\ChatController;
// use Illuminate\Support\Facades\Route;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Http;

// Route::get('/chat', [ChatController::class, 'index']);
// Route::post('/chat', [ChatController::class, 'send']);


// Route::get('/ask', function () {
//     $q = request('q', 'Explain Laravel in simple words');

//     try {
//         $res = Http::connectTimeout(10)
//             ->timeout(180) // 3 minutes
//             ->post(env('OLLAMA_URL') . '/api/generate', [
//                 'model' => env('OLLAMA_MODEL'),
//                 'prompt' => $q,
//                 'stream' => false,
//             ]);

//         if (!$res->successful()) {
//             return response("Ollama error: " . $res->body(), 500);
//         }

//         $json = $res->json();
//         return $json['response'] ?? 'No response field';
//     } catch (\Throwable $e) {
//         return response("Request failed: " . $e->getMessage(), 500);
//     }
// });
