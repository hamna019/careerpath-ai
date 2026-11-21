<?php

namespace App\Http\Controllers;

use App\Support\NotificationReadState;
use App\Models\CareerDetails;
use App\Models\RecentStudentActivity;
use App\Models\StudentCareerResult;
use App\Models\StudentResult;
use App\Services\AssessmentCertificates;
use App\Services\ProfileCompletion;
use App\Support\RelativeTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class RecentStudentActivityController extends Controller
{
    private const ABANDONED_AFTER_SECONDS = 15; 

    public function start(Request $request)
    {
        $user = $request->user();
        $now = now();

        $completion = ProfileCompletion::for($user);
        if (!$completion['eligible']) {
            return ProfileCompletion::blockedResponse($completion);
        }

        RecentStudentActivity::updateOrCreate(
            ['user_id' => $user->id],
            [
                'status'           => 'in_progress',
                'score'            => null,
                'started_at'       => $now,
                'completed_at'     => null,
                'last_activity_at' => $now,
            ]
        );

        StudentResult::logStart($user);

        return response()->json(['status' => 'ok']);
    }

    public function complete(Request $request)
    {
        $request->validate([
            'score'  => ['required', 'integer', 'min:0', 'max:100'],
            'career' => ['nullable', 'string', 'max:255'],
        ]);

        $rawTitle = trim((string) $request->input('career', ''));
        $career   = self::resolveCareer($rawTitle);

        self::recordCompletion(
            $request->user(),
            $request->integer('score'),
            $career,
            $career?->title ?: ($rawTitle !== '' ? $rawTitle : 'Unspecified')
        );

        return response()->json(['status' => 'ok']);
    }

    public static function recordCompletion($user, int $score, ?CareerDetails $career, string $careerTitle): ?StudentResult
    {
        $now = now();

        RecentStudentActivity::updateOrCreate(
            ['user_id' => $user->id],
            [
                'status'           => 'completed',
                'score'            => $score,
                'completed_at'     => $now,
                'last_activity_at' => $now,
            ]
        );

        try {
            StudentCareerResult::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'career_id'    => $career?->id,
                    'career_title' => $careerTitle,
                    'score'        => $score,
                    'completed_at' => $now,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('StudentCareerResult write failed on quiz complete', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        try {
            return StudentResult::logComplete($user, $score, $career?->id, $careerTitle);
        } catch (\Throwable $e) {
            Log::warning('StudentResult write failed on quiz complete', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        return null;
    }

    private static function resolveCareer(string $title): ?CareerDetails
    {
        $title = trim($title);
        if ($title === '') {
            return null;
        }

        $lower = mb_strtolower($title);

        $exact = CareerDetails::whereRaw('LOWER(title) = ?', [$lower])->first();
        if ($exact) {
            return $exact;
        }

        $aliases = [
            'data scientist'   => 'Data Analyst',
            'digital marketer' => 'Digital Marketing Specialist',
        ];
        if (isset($aliases[$lower])) {
            $aliased = CareerDetails::whereRaw('LOWER(title) = ?', [mb_strtolower($aliases[$lower])])->first();
            if ($aliased) {
                return $aliased;
            }
        }

        return CareerDetails::where('title', 'like', '%' . $title . '%')
            ->orWhereRaw('? like concat("%", title, "%")', [$title])
            ->first();
    }
    public function ping(Request $request)
    {
        $activity = RecentStudentActivity::where('user_id', $request->user()->id)->first();

        if ($activity && in_array($activity->status, ['in_progress', 'error'], true)) {
            $activity->update([
                'status'           => 'in_progress',
                'last_activity_at' => now(),
            ]);
        }

        StudentResult::logPing($request->user());

        return response()->json(['status' => 'ok']);
    }

    public function abandon(Request $request)
    {
        $token = $request->bearerToken() ?: (string) $request->input('token');
        $user  = $token ? optional(PersonalAccessToken::findToken($token))->tokenable : null;

        if ($user) {
            RecentStudentActivity::where('user_id', $user->id)
                ->where('status', 'in_progress')
                ->update(['status' => 'error', 'last_activity_at' => now()]);

            StudentResult::logAbandon($user);
        }

        return response()->json(['status' => 'ok']);
    }
    public function mine(Request $request)
    {
        self::flagAbandonedAttempts();

        $user = $request->user();
        $activity = RecentStudentActivity::where('user_id', $user->id)->first();

        $items = [];

        $joinedAt = $user->created_at ?? $activity?->created_at;
        if ($joinedAt) {
            $items[] = ['type' => 'account_created', 'text' => 'Joined CareerPath', 'at' => $joinedAt];
        }

        if ($activity && $activity->started_at) {
            $items[] = ['type' => 'assessment_started', 'text' => 'Started Career Assessment', 'at' => $activity->started_at];
        }

        if ($activity && $activity->status === 'completed' && $activity->completed_at) {
            $items[] = [
                'type' => 'assessment_completed',
                'text' => $activity->score !== null
                    ? "Completed Career Assessment ({$activity->score}%)"
                    : 'Completed Career Assessment',
                'at'   => $activity->completed_at,
            ];
        }

        if ($activity && $activity->status === 'error') {
            $items[] = [
                'type' => 'assessment_incomplete',
                'text' => 'Career Assessment left incomplete',
                'at'   => $activity->last_activity_at ?? $activity->started_at,
            ];
        }

        usort($items, fn ($a, $b) => $b['at']->getTimestamp() <=> $a['at']->getTimestamp());

        $feed = array_map(fn ($item) => [
            'type'      => $item['type'],
            'text'      => $item['text'],
            'time'      => RelativeTime::format($item['at']),
            'timestamp' => $item['at']->toIso8601String(),
        ], $items);

        return response()->json(['activity' => $feed]);
    }

    public static function recentActivityForAdmin(int $limit = 200)
    {
        return self::buildAdminActivityFeed($limit, restrictToLast24Hours: true);
    }

    private static function pendingCertificateNotifications()
    {
        return StudentResult::with('user')
            ->where('status', 'completed')
            ->whereNull('certificate_approved_at')
            ->get()
            ->groupBy('user_id')
            ->flatMap(function ($attempts, $userId) {
                $user = $attempts->first()->user;
                if (!$user) {
                    return [];
                }
                return collect(AssessmentCertificates::forAttempts((int) $userId, $attempts))
                    ->filter(fn ($c) => $c['status'] === AssessmentCertificates::PENDING_APPROVAL && $c['readyAt'])
                    ->map(fn ($c) => [
                        'name'       => $user->name,
                        'email'      => $user->email,
                        'avatar'     => $user->getFirstMediaUrl('avatar') ?: null,
                        'score'      => null,
                        'status'     => 'Certificate Pending',
                        'assessment' => $c['number'],
                        'time'       => RelativeTime::format(Carbon::parse($c['readyAt'])),
                        'timestamp'  => $c['readyAt'],
                    ])
                    ->values();
            })
            ->values();
    }

    public static function notificationFeedForAdmin(int $limit = 8)
    {
        return self::buildAdminActivityFeed($limit, restrictToLast24Hours: false);
    }

    private static function buildAdminActivityFeed(int $limit, bool $restrictToLast24Hours)
    {
        self::flagAbandonedAttempts();

        $statusLabels = [
            'new_student' => 'New Student',
            'in_progress' => 'In Progress',
            'completed'   => 'Completed',
            'error'       => 'Error',
        ];

        $query = RecentStudentActivity::with('user:id,name,email');

        if ($restrictToLast24Hours) {
            $query->where('last_activity_at', '>=', Carbon::now()->subHours(24));
        }

        return $query
            ->orderByDesc('last_activity_at')
            ->take($limit)
            ->get()
            ->filter(fn ($activity) => $activity->user)
            ->map(function ($activity) use ($statusLabels) {
                $eventTime = self::eventTimeFor($activity);

                return [
                    'name'      => $activity->user->name,
                    'email'     => $activity->user->email,
                    'avatar'    => $activity->user->getFirstMediaUrl('avatar') ?: null,
                    'score'     => $activity->score,
                    'status'    => $statusLabels[$activity->status] ?? 'New Student',
                    'time'      => RelativeTime::format($eventTime),
                    'timestamp' => $eventTime->toIso8601String(),
                ];
            })
            ->values();
    }

    public function notifications(Request $request)
    {
        $readUntil = NotificationReadState::readUntil($request->user());
        $all = self::notificationFeedForAdmin(200)
            ->concat(self::pendingCertificateNotifications())
            ->sortByDesc(fn ($item) => $item['timestamp'])
            ->values();

        return response()->json([
            'activity'     => $all->take(8)->values(),
            'read_until'   => $readUntil->toIso8601String(),
            'unread_count' => $all->filter(fn ($item) => Carbon::parse($item['timestamp'])->greaterThan($readUntil))->count(),
        ]);
    }

    private static function eventTimeFor(RecentStudentActivity $activity): Carbon
    {
        $time = match ($activity->status) {
            'completed'   => $activity->completed_at,
            'in_progress' => $activity->started_at,
            default       => $activity->last_activity_at,
        };

        return $time ?? $activity->last_activity_at;
    }

    private static function flagAbandonedAttempts(): void
    {
        RecentStudentActivity::where('status', 'in_progress')
            ->where('last_activity_at', '<', Carbon::now()->subSeconds(self::ABANDONED_AFTER_SECONDS))
            ->update(['status' => 'error']);

        StudentResult::sweepAbandoned();
    }
}
