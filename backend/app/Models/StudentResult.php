<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class StudentResult extends Model
{
    private const ABANDONED_AFTER_SECONDS = 15;

    protected $fillable = [
        'user_id',
        'status',
        'score',
        'career_id',
        'career_title',
        'match_percent',
        'answers',
        'recommendations',
        'started_at',
        'completed_at',
        'last_activity_at',
    ];

    protected $casts = [
        'answers'          => 'array',
        'recommendations'  => 'array',
        'started_at'       => 'datetime',
        'completed_at'     => 'datetime',
        'certificate_approved_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function career()
    {
        return $this->belongsTo(CareerDetails::class, 'career_id');
    }

    public static function logStart(User $user): self
    {
        $now = now();

        return self::create([
            'user_id'          => $user->id,
            'status'           => 'in_progress',
            'started_at'       => $now,
            'last_activity_at' => $now,
        ]);
    }

    public static function logPing(User $user): void
    {
        $attempt = self::openAttemptFor($user);

        if ($attempt) {
            $attempt->update(['status' => 'in_progress', 'last_activity_at' => now()]);
        }
    }

    public static function logComplete(User $user, int $score, ?int $careerId, string $careerTitle): self
    {
        $now = now();
        $attempt = self::openAttemptFor($user) ?? self::create([
            'user_id'          => $user->id,
            'status'           => 'in_progress',
            'started_at'       => $now,
            'last_activity_at' => $now,
        ]);

        $attempt->update([
            'status'           => 'completed',
            'score'            => $score,
            'career_id'        => $careerId,
            'career_title'     => $careerTitle,
            'match_percent'    => $score,
            'completed_at'     => $now,
            'last_activity_at' => $now,
        ]);

        return $attempt;
    }

    public static function logAbandon(User $user): void
    {
        $attempt = self::where('user_id', $user->id)->where('status', 'in_progress')->latest('id')->first();

        if ($attempt) {
            $attempt->update(['status' => 'error', 'last_activity_at' => now()]);
        }
    }

    public static function sweepAbandoned(): void
    {
        self::where('status', 'in_progress')
            ->where('last_activity_at', '<', Carbon::now()->subSeconds(self::ABANDONED_AFTER_SECONDS))
            ->update(['status' => 'error']);
    }

    private static function openAttemptFor(User $user): ?self
    {
        return self::where('user_id', $user->id)
            ->whereIn('status', ['in_progress', 'error'])
            ->latest('id')
            ->first();
    }

    public function certificateId(): string
    {
        return 'CPAI-' . (1000 + $this->id);
    }

    public function suggestedCareers(): array
    {
        if ($this->recommendations) {
            return collect($this->recommendations)
                ->map(fn ($r) => ['careerId' => $r['career_id'] ?? null, 'match' => $r['match'] ?? null])
                ->filter(fn ($r) => $r['careerId'])
                ->values()
                ->all();
        }

        return $this->career_id ? [['careerId' => $this->career_id, 'match' => $this->match_percent ?? $this->score]] : [];
    }
}
