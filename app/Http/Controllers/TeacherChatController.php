<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMessage;

class TeacherChatController extends Controller
{
    // Show sessions list
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

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

        return view('teacher.sessions', compact('sessions', 'q'));
    }

    // Show messages for one session
    public function show(string $sessionId)
    {
        $messages = ChatMessage::where('session_id', $sessionId)
            ->orderBy('id')
            ->get();

        return view('teacher.session_show', compact('messages', 'sessionId'));
    }
}
