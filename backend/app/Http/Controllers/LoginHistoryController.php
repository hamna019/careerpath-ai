<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use App\Support\RelativeTime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LoginHistoryController extends Controller
{
    private function sweepStaleSessions(): void
    {
        $cutoff = Carbon::now()->subSeconds(LoginHistory::SESSION_TIMEOUT_SECONDS);

        LoginHistory::where('role', 'student')
            ->where('status', 'success')
            ->whereNull('logout_at')
            ->where(function ($query) use ($cutoff) {
                $query->where('last_seen_at', '<', $cutoff)
                    ->orWhere(function ($q) use ($cutoff) {
                        $q->whereNull('last_seen_at')->where('login_at', '<', $cutoff);
                    });
            })
            ->update(['logout_at' => DB::raw('COALESCE(last_seen_at, login_at)')]);
    }

    public function index(Request $request)
    {
        $query = LoginHistory::with('user:id,name,email,role')->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        $histories = $query->paginate(15);

        return response()->json([
            'status' => 'success',
            'data'   => $histories
        ], 200);
    }

    public function studentActivity()
    {
        $this->sweepStaleSessions();

        $histories = LoginHistory::where('role', 'student')
            ->with('user:id,name')
            ->latest('login_at')
            ->take(300)
            ->get();

        $data = $histories->map(function (LoginHistory $entry) {
            $isSuccess = $entry->status === 'success';
            $hasLoggedOut = $isSuccess && $entry->logout_at !== null;

            $durationSeconds = null;
            if ($isSuccess) {
                $end = $entry->logout_at ?? now();
                $durationSeconds = max(0, $end->getTimestamp() - $entry->login_at->getTimestamp());
            }

            return [
                'id'            => $entry->id,
                'name'          => $entry->user->name ?? $entry->email,
                'email'         => $entry->email,
                'avatar'        => $entry->user?->getFirstMediaUrl('avatar') ?: null,
                'ipAddress'     => $entry->ip_address,
                'deviceType'    => $entry->device_type,
                'browser'       => $entry->browser,
                'platform'      => $entry->platform,
                'status'        => $isSuccess ? 'Completed' : 'Error',
                'failureReason' => $entry->failure_reason,
                'loginAt'       => $entry->login_at?->toIso8601String(),
                'loginTime'     => $entry->login_at ? RelativeTime::format($entry->login_at) : null,
                'logoutAt'      => $entry->logout_at?->toIso8601String(),
                'logoutTime'    => $entry->logout_at ? RelativeTime::format($entry->logout_at) : null,
                'isActive'      => $isSuccess && !$hasLoggedOut,
                'durationLabel' => $durationSeconds !== null ? RelativeTime::duration($durationSeconds, $entry->login_at) : null,
            ];
        });

        return response()->json(['activity' => $data]);
    }
}