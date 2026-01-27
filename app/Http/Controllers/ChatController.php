<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ChatMessage;
use Symfony\Component\Process\Process;

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

    // Send message to YOUR OWN AI + save to DB
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        // 1) Get / create session id
        $sessionId = $request->session()->get('chat_session_id');
        if (!$sessionId) {
            $sessionId = (string) \Illuminate\Support\Str::uuid();
            $request->session()->put('chat_session_id', $sessionId);
        }

        $userMessage = $request->message;

        // 2) Save user message
        \App\Models\ChatMessage::create([
            'session_id' => $sessionId,
            'role'       => 'user',
            'content'    => $userMessage,
        ]);

        try {
            // ✅ Force Laravel to use the SAME python from .venv (no mixing)
            $python = base_path('.venv\\Scripts\\python.exe');
            $script = base_path('storage\\ai\\predict.py');

            $process = new \Symfony\Component\Process\Process([$python, $script, $userMessage]);
            $process->setTimeout(30);
            $process->run();

            if (!$process->isSuccessful()) {
                return response()->json([
                    'reply' => 'AI error: ' . $process->getErrorOutput(),
                ], 500);
            }

            $label = trim($process->getOutput()); // positive/negative/neutral

            // 3) Make reply based on label
            $reply = $this->makeReply($label, $userMessage);

            // 4) Save AI reply
            \App\Models\ChatMessage::create([
                'session_id' => $sessionId,
                'role'       => 'assistant',
                'content'    => $reply,
            ]);

            return response()->json([
                'label' => $label,
                'reply' => $reply,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'reply' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }


    // Reply logic based on your ML label
    private function makeReply(string $label, string $userMessage): string
    {
        $label = strtolower(trim($label));

        if ($label === 'positive') {
            return "🙂 (positive)\nI understand.\n\nTell me your question/topic, I will explain step-by-step.";
        }

        if ($label === 'negative') {
            return "😕 (negative)\nLooks like you might be stressed/confused.\n\nTell me which part you don’t understand (1 sentence).";
        }

        // neutral / unknown
        return "👌 (neutral)\nOkay.\n\nAsk your question clearly (example: 'Explain OOP encapsulation').";
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
