<?php


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;


Route::get('/ask', function () {
    $q = request('q', 'Explain Laravel in simple words');

    $response = Http::timeout(300) // 5 minutes
        ->post(env('OLLAMA_URL') . '/api/generate', [
            'model' => env('OLLAMA_MODEL'),
            'prompt' => $q,
            'stream' => false,
            'num_predict' => 100, // shorter answer = faster
        ]);

    return $response->json()['response'] ?? 'No response';
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
