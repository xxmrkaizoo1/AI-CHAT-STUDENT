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

        // session id
        $sessionId = $request->session()->get('chat_session_id');
        if (!$sessionId) {
            $sessionId = (string) Str::uuid();
            $request->session()->put('chat_session_id', $sessionId);
        }

        $userMessage = $request->message;
        $question = strtolower(trim($userMessage));

        // ✅ Save user message first (so greetings/blocked also saved)
        ChatMessage::create([
            'session_id' => $sessionId,
            'role'       => 'user',
            'content'    => $userMessage,
        ]);

        // ✅ Greetings
        $greetings = [
            'hi',
            'hello',
            'hey',
            'hey there',
            'hye',
            'yo',
            'good morning',
            'good afternoon',
            'good evening',
            'good night',
            'hai',
            'helo',
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
            'hey ai',
        ];

        foreach ($greetings as $greet) {
            if ($question === $greet || str_starts_with($question, $greet)) {
                $text =
                    "👋 Hi! I’m your study assistant.\n\n" .
                    "You can ask me about:\n" .
                    "- Programming (PHP, Java, Laravel)\n" .
                    "- Math & Statistics\n" .
                    "- IT & Computer concepts\n" .
                    "- Assignments & exams";

                return $this->streamInstantText($text, $sessionId);
            }
        }

        // ✅ HARD FILTER (block out-of-topic BEFORE Ollama)
        $CurseWords = [
            'Bodoh',
            'stupid',
            'idiot',
            'dumb',
            'fool',
            'moron',
            'silly',
            'brainless',
            'dimwit',
            'imbecile',
            'cretin',
            'loser',
            'twit',
            'nitwit',
            'blockhead',
            'birdbrain',
            'dunce',
            'ignoramus',
            'halfwit',
            'simpleton',
            'thickhead',
            'asshole',
            'bastard',
            'damn',
            'crap'



        ];

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
            'java',
            'php',
            'laravel',
            'javascript',
            'html',
            'css',
            'database',
            'sql',
            'mysql',
            'mariadb',
            'algorithm',
            'oop',
            'computer',
            'it',
            'network',
            'software',
            'hardware',
            'data structure',
            'function',
            'variable',
            'loop',
            'array',
            'string',
            'integer',
            'float',
            'boolean',
            'conditional',
            'array',
            'object',
            'class',
            'method',
            'debugging',
            'development',
            'framework',
            'api',
            'version control',
            'git',
            'github',
            'problem solving',
            'logic',
            'flowchart',
            'pseudocode',
            'binary',
            'hexadecimal',
            'compiler',
            'interpreter',
            'syntax',
            'program',
            'software engineering',
            'operating system',
            'cloud computing',
            'cybersecurity',
            'artificial intelligence',
            'machine learning',
            'data science',
            'computer vision',
            'natural language processing',
            'deep learning',
            'neural network',
            'big data',
            'data analysis',
            'data visualization',
            'statistics',
            'probability',
            'linear algebra',
            'discrete mathematics',
            'calculus',
            'geometry',
            'trigonometry',
            'number theory',
            'combinatorics',
            'graph theory',
            'set theory',
            'logic',
            'cryptography',
            'information theory',
            'automata theory',
            'computational theory',
            'explain'


        ];

        $blockedKeywords = [
            'game',
            'gaming',
            'fortnite',
            'pubg',
            'valorant',
            'minecraft',
            'download',
            'install crack',
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

        foreach ($CurseWords as $word) {
            if (str_contains($question, strtolower($word))) {
                return $this->streamInstantText(
                    "❌ Please avoid using inappropriate language. Let's keep our conversation respectful and focused on learning.",
                    "❌ Else will get Auto Report ! please behave yourself ",
                    $sessionId

                );
            }
        }

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

        // ✅ Build memory (last 10)
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

        // ✅ Tutor system rules
        array_unshift($history, [
            'role' => 'system',
            'content' =>
            "You are a strict study assistant for students. " .
                "ONLY answer questions related to education, programming, math, IT, or assignments. " .
                "Explain simply, step-by-step, with short examples. " .
                "If out of topic, refuse.",
            "I only Able talk about study related  topics."

        ]);

        $ollamaUrl = rtrim(env('OLLAMA_URL', 'http://127.0.0.1:11434'), '/');
        $model     = env('OLLAMA_MODEL', 'llama3.2');

        // ✅ STREAM from Ollama to browser
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
                    'Ollama error: ' . $res->status() . ' - ' . $res->body(),
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

                    // Ollama streams JSON per line
                    while (($pos = strpos($buffer, "\n")) !== false) {
                        $line = substr($buffer, 0, $pos);
                        $buffer = substr($buffer, $pos + 1);

                        $line = trim($line);
                        if ($line === '') continue;

                        $data = json_decode($line, true);
                        if (!is_array($data)) continue;

                        if (isset($data['message']['content'])) {
                            $text = $data['message']['content'];
                            $fullReply .= $text;

                            echo $text; // stream to frontend
                            @ob_flush();
                            @flush();
                        }
                    }
                }

                // Save full assistant reply once finished
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
            return $this->streamInstantText('Server error: ' . $e->getMessage(), $sessionId);
        }
    }

    // ✅ Helper: return a “stream-like” reply instantly + save it
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
