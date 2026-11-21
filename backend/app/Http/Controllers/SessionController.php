<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function ping(Request $request)
    {
        $request->validate(['login_history_id' => ['required', 'integer']]);

        $history = LoginHistory::find($request->input('login_history_id'));

        if ($history && $history->logout_at === null) {
            $history->update(['last_seen_at' => now()]);
        }

        return response()->json(['status' => 'ok']);
    }
}
