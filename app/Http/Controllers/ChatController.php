<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\ChatMessage;

class ChatController extends Controller
{
    // Show chat page + history
    public function index(Request $request)
    {
        $sessionId = $request->session()->get('chat_session_id');

        $messages = $sessionId
            ? ChatMessage::where('session_id', $sessionId)->orderBy('id')->get()
            : collect();

        return view('chat', compact('messages'));
    }

    // Send message to OLLAMA + save to DB
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        // 1) Get / create session id
        $sessionId = $request->session()->get('chat_session_id');
        if (!$sessionId) {
            $sessionId = (string) Str::uuid();
            $request->session()->put('chat_session_id', $sessionId);
        }

        $userMessage = $request->message;

        // 2) Save user message
        ChatMessage::create([
            'session_id' => $sessionId,
            'role'       => 'user',
            'content'    => $userMessage,
        ]);

        // 3) Get last 10 messages for memory
        $history = ChatMessage::where('session_id', $sessionId)
            ->orderBy('id', 'desc')
            ->take(10)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($m) {
                return [
                    'role' => $m->role === 'assistant' ? 'assistant' : 'user',
                    'content' => $m->content,
                ];
            })
            ->toArray();

        // 4) Tutor instruction (system)
        array_unshift($history, [
            'role' => 'system',
            'content' => 'You are a helpful tutor for students. Explain simply, step-by-step, with short examples.'
        ]);

        $ollamaUrl = rtrim(env('OLLAMA_URL', 'http://127.0.0.1:11434'), '/');
        $model     = env('OLLAMA_MODEL', 'llama3.2');

        try {
            // 5) Call Ollama API
            $response = Http::timeout(120)->post($ollamaUrl . '/api/chat', [
                'model' => $model,
                'messages' => $history,
                'stream' => false,
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'reply' => 'Ollama error: ' . $response->status() . ' - ' . $response->body(),
                ], 500);
            }

            $reply = $response->json('message.content') ?? 'No reply from Ollama.';

            // 6) Save AI reply
            ChatMessage::create([
                'session_id' => $sessionId,
                'role'       => 'assistant',
                'content'    => $reply,
            ]);

            return response()->json([
                'reply' => $reply,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'reply' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Clear chat history
    public function clear(Request $request)
    {
        $sessionId = $request->session()->get('chat_session_id');

        if ($sessionId) {
            ChatMessage::where('session_id', $sessionId)->delete();
        }

        $request->session()->forget('chat_session_id');

        return redirect('/chat')->with('success', 'Chat cleared!');
    }
}
