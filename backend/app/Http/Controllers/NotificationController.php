<?php

namespace App\Http\Controllers;

use App\Support\NotificationReadState;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markRead(Request $request)
    {
        $request->validate(['until' => ['required', 'date']]);

        $readUntil = NotificationReadState::markReadUntil(
            $request->user(),
            Carbon::parse($request->input('until'))
        );

        return response()->json(['read_until' => $readUntil->toIso8601String()]);
    }
}
