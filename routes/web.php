<?php
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
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
                'num_predict' => 100,

                // VERY HIGH → slower test
                // VERY SMALL → fastest test
            ],
        ]);

    return $res->json();
});
