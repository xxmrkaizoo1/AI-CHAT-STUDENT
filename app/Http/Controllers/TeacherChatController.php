<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMessage;

class TeacherChatController extends Controller
{
    /* =========================
       KEYWORDS
    ========================== */

    private function getBadKeywords()
    {
        return [
            'bodoh',
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
    }

    private function getBlockedKeywords()
    {
        return [
            'game',
            'gaming',
            'valorant',
            'pubg',
            'minecraft',
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
            'download',
            'crack',
            'hack',
            'cheat',
            'torrent'
        ];
    }

    /* =========================
       DASHBOARD (STATISTICS)
    ========================== */

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        // 🔹 Only student messages
        $messages = ChatMessage::where('role', 'user')->get();

        $badKeywords = $this->getBadKeywords();
        $blockedKeywords = $this->getBlockedKeywords();

        $goodCount = 0;
        $badCount = 0;
        $blockedCount = 0;

        foreach ($messages as $m) {
            $text = strtolower($m->content);

            $isBad = false;
            $isBlocked = false;

            // ❌ bad language
            foreach ($badKeywords as $word) {
                if (str_contains($text, $word)) {
                    $isBad = true;
                    break;
                }
            }

            // ⛔ blocked topics
            foreach ($blockedKeywords as $word) {
                if (str_contains($text, $word)) {
                    $isBlocked = true;
                    break;
                }
            }

            if ($isBad) {
                $badCount++;
            } elseif ($isBlocked) {
                $blockedCount++;
            } else {
                $goodCount++;
            }
        }

        $total = $goodCount + $badCount + $blockedCount;

        // 🔹 Chat sessions list
        $sessions = ChatMessage::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('content', 'like', "%{$q}%");
            })
            ->select('session_id')
            ->selectRaw('MAX(id) as last_id')
            ->selectRaw('MAX(created_at) as last_time')
            ->groupBy('session_id')
            ->orderByDesc('last_id')
            ->paginate(20);

        // ✅ PASS ALL VARIABLES TO VIEW
        return view('teacher.sessions', compact(
            'sessions',
            'q',
            'goodCount',
            'badCount',
            'blockedCount',
            'total'
        ));
    }

    /* =========================
       VIEW SINGLE SESSION
    ========================== */

    public function show(string $sessionId)
    {
        $messages = ChatMessage::where('session_id', $sessionId)
            ->orderBy('id')
            ->get();




            return view('teacher.sessions', compact('goodCount','badCount','blockedCount','total','sessions','q'));
            // return view('teacher.session_show', compact('messages', 'sessionId'));
    }
}
