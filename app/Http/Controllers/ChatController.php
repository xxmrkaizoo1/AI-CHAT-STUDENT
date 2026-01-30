<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\ChatMessage;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $sessionId = $request->session()->get('chat_session_id');

        $messages = $sessionId
            ? ChatMessage::where('session_id', $sessionId)->orderBy('id')->get()
            : collect();

        return view('chat', compact('messages'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        // 🎓 Student level (default beginner)
        $level = $request->input('level', 'beginner');

        // session id
        $sessionId = $request->session()->get('chat_session_id');
        if (!$sessionId) {
            $sessionId = (string) Str::uuid();
            $request->session()->put('chat_session_id', $sessionId);
        }

        $userMessage = $request->message;
        $question = strtolower(trim($userMessage));

        // Save user message
        ChatMessage::create([
            'session_id' => $sessionId,
            'role'       => 'user',
            'content'    => $userMessage,
        ]);

        /* ================= GREETINGS ================= */
        $greetings = [
            'hi',
            'hello',
            'hey',
            'hey there',
            'hye',
            'yo',
            'hai',
            'helo',
            'good morning',
            'good afternoon',
            'good evening',
            'good night',
            'assalamualaikum',
            'salam',
            'selamat pagi',
            'selamat petang',
            'selamat malam',
            'hi teacher',
            'hello teacher',
            'hey sir',
            'hey miss',
            'hi ai',
            'hello ai',
            'hey ai'
        ];

        foreach ($greetings as $greet) {
            if ($question === $greet || str_starts_with($question, $greet)) {
                return $this->streamInstantText(
                    "👋 Hi! I’m your study assistant.\n\n" .
                        "You can ask me about:\n" .
                        "- Programming (PHP, Java, Laravel)\n" .
                        "- Math & Statistics\n" .
                        "- IT & Computer concepts\n" .
                        "- Assignments & exams",
                    $sessionId
                );
            }
        }

        /* ================= CURSE WORD FILTER ================= */
        $curseWords = [
            'bodoh',
            'stupid',
            'idiot',
            'dumb',
            'fool',
            'moron',
            'asshole',
            'bastard',
            'damn',
            'crap'
        ];

        foreach ($curseWords as $word) {
            if (str_contains($question, $word)) {
                return $this->streamInstantText(
                    "❌ Please avoid using inappropriate language. This chatbot is for learning.",
                    $sessionId
                );
            }
        }

        /* ================= BLOCKED TOPICS ================= */
        $blockedKeywords = [
            'game',
            'gaming',
            'fortnite',
            'pubg',
            'valorant',
            'minecraft',
            'download',
            'crack',
            'cheat',
            'hack',
            'movie',
            'song',
            'music',
            'anime',
            'tiktok',
            'instagram',
            'facebook',
            'twitter',
            'youtube',
            'netflix',
            'spotify',
            'torrent'
        ];

        foreach ($blockedKeywords as $word) {
            if (str_contains($question, $word)) {
                return $this->streamInstantText(
                    "❌ Sorry, I can only help with study-related questions.",
                    $sessionId
                );
            }
        }

        /* ================= ALLOWED TOPICS ================= */
        $allowedKeywords = [
            'study',
            'exam',
            'assignment',
            'homework',
            'revision',
            'math',
            'mathematics',
            'algebra',
            'calculus',
            'statistics',
            'programming',
            'coding',
            'code',
            'php',
            'java',
            'laravel',
            'javascript',
            'html',
            'css',
            'sql',
            'mysql',
            'database',
            'oop',
            'class',
            'object',
            'function',
            'variable',
            'loop',
            'array',
            'computer',
            'it',
            'network',
            'software',
            'hardware',
            'algorithm',
            'data',
            'ai',
            'machine learning',
            'data science',
            'explain',
            'example'
        ];

        $allowed = false;
        foreach ($allowedKeywords as $word) {
            if (str_contains($question, $word)) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            return $this->streamInstantText(
                "❌ This chatbot is for learning only. Please ask a study-related question.",
                $sessionId
            );
        }

        /* ================= MEMORY (LAST 10) ================= */
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

        /* ================= LEVEL PROMPT ================= */
        $levelPrompt = match ($level) {
            'beginner' => "Explain very simply, step-by-step, using easy words and simple examples.",
            'intermediate' => "Explain clearly with some technical terms and examples.",
            'advanced' => "Explain in detail using technical language, theory, and best practices.",
            default => "Explain simply."
        };

        array_unshift($history, [
            'role' => 'system',
            'content' =>
            "You are a strict study assistant. " .
                "ONLY answer education-related questions. " .
                $levelPrompt . " " .
                "If out of topic, politely refuse."
        ]);

        /* ================= OLLAMA STREAM ================= */
        $ollamaUrl = rtrim(env('OLLAMA_URL', 'http://127.0.0.1:11434'), '/');
        $model     = env('OLLAMA_MODEL', 'llama3.2');

        try {
            $res = Http::withOptions(['stream' => true])
                ->timeout(0)
                ->post($ollamaUrl . '/api/chat', [
                    'model' => $model,
                    'messages' => $history,
                    'stream' => true,
                ]);

            if (!$res->successful()) {
                return $this->streamInstantText(
                    'Ollama error: ' . $res->status(),
                    $sessionId
                );
            }

            $psr = $res->toPsrResponse();
            $body = $psr->getBody();

            return response()->stream(function () use ($body, $sessionId) {
                $buffer = '';
                $fullReply = '';

                while (!$body->eof()) {
                    $chunk = $body->read(1024);
                    if ($chunk === '') continue;

                    $buffer .= $chunk;

                    while (($pos = strpos($buffer, "\n")) !== false) {
                        $line = trim(substr($buffer, 0, $pos));
                        $buffer = substr($buffer, $pos + 1);

                        if ($line === '') continue;

                        $data = json_decode($line, true);
                        if (!is_array($data)) continue;

                        if (isset($data['message']['content'])) {
                            $text = $data['message']['content'];
                            $fullReply .= $text;

                            echo $text;
                            @ob_flush();
                            @flush();
                        }
                    }
                }

                ChatMessage::create([
                    'session_id' => $sessionId,
                    'role'       => 'assistant',
                    'content'    => trim($fullReply) ?: 'No reply from Ollama.',
                ]);
            }, 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        } catch (\Exception $e) {
            return $this->streamInstantText(
                'Server error: ' . $e->getMessage(),
                $sessionId
            );
        }
    }

    /* ================= INSTANT STREAM HELPER ================= */
    private function streamInstantText(string $text, string $sessionId)
    {
        ChatMessage::create([
            'session_id' => $sessionId,
            'role'       => 'assistant',
            'content'    => $text,
        ]);

        return response()->stream(function () use ($text) {
            echo $text;
            @ob_flush();
            @flush();
        }, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

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
