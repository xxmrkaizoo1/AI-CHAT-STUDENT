<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Http;

// class AiController extends Controller
// {
//     public function ask(Request $request)
//     {
//         $q = $request->input('q', 'Hi');

//         $res = Http::withOptions(['force_ip_resolve' => 'v4'])
//             ->connectTimeout(30)
//             ->timeout(300)
//             ->asJson()
//             ->post(env('OLLAMA_URL') . '/api/generate', [
//                 'model' => env('OLLAMA_MODEL'),
//                 'prompt' => $q,
//                 'stream' => false,
//                 'options' => [
//                     'num_predict' => 10,
//                     'temperature' => 0.2,
//                 ],
//             ]);



//         return response()->json([
//             'question' => $q,
//             'answer' => $res->json('response') ?? $res->body(),
//         ]);
//     }
// }
