<?php

namespace App\Services;

use App\Models\CareerDetails;
use App\Models\CareerRoadmapProgress;
use App\Models\StudentResult;
use Illuminate\Support\Collection;

class AssessmentCertificates
{
    public const IN_PROGRESS = 'in_progress';
    public const PENDING_APPROVAL = 'pending_approval';
    public const APPROVED = 'approved';

    public static function forAttempts(int $userId, Collection $attempts): array
    {
        $suggested = $attempts->mapWithKeys(fn (StudentResult $a) => [$a->id => $a->suggestedCareers()]);
        $careerIds = $suggested->flatten(1)->pluck('careerId')->unique()->values();

        $careers = CareerDetails::whereIn('id', $careerIds)->get(['id', 'title', 'roadmap'])->keyBy('id');
        $progress = CareerRoadmapProgress::where('user_id', $userId)
            ->whereIn('career_id', $careerIds)
            ->whereNotNull('completed_at')
            ->get(['career_id', 'completed_at'])
            ->groupBy('career_id');

        $numbers = StudentResult::where('user_id', $userId)->where('status', 'completed')
            ->orderBy('id')->pluck('id')->flip()->map(fn ($i) => $i + 1);

        $result = [];
        foreach ($attempts as $attempt) {
            $list = collect($suggested[$attempt->id])
                ->filter(fn ($s) => $careers->has($s['careerId']))
                ->values()
                ->map(function ($s) use ($careers, $progress) {
                    $career = $careers[$s['careerId']];
                    $total = count($career->roadmap ?? []);
                    $rows = $progress[$career->id] ?? collect();
                    $done = min($rows->count(), $total);

                    return [
                        'careerId'       => $career->id,
                        'careerTitle'    => $career->title,
                        'match'          => $s['match'],
                        'completedSteps' => $done,
                        'totalSteps'     => $total,
                        'percent'        => $total > 0 ? (int) round($done / $total * 100) : 100,
                        'finishedAt'     => $done >= $total ? $rows->max('completed_at') : null,
                    ];
                });

            $completed = $list->filter(fn ($c) => $c['completedSteps'] >= $c['totalSteps'])->count();
            $roadmapsDone = $list->isNotEmpty() && $completed === $list->count();
            $approved = $roadmapsDone && $attempt->certificate_approved_at !== null;
            $status = $approved ? self::APPROVED : ($roadmapsDone ? self::PENDING_APPROVAL : self::IN_PROGRESS);

            $result[$attempt->id] = [
                'status'           => $status,
                'eligible'         => $approved,
                'number'           => $numbers[$attempt->id] ?? null,
                'completedCareers' => $completed,
                'totalCareers'     => $list->count(),
                'careers'          => $list->map(fn ($c) => collect($c)->except('finishedAt')->all())->all(),

                'readyAt'          => $roadmapsDone ? optional($list->max('finishedAt'))->toIso8601String() : null,
                'approvedAt'       => $approved ? $attempt->certificate_approved_at->toIso8601String() : null,

                'certificates'     => $approved
                    ? $list->map(fn ($c, $i) => [
                        'certificateId' => $attempt->certificateId() . '-' . ($i + 1),
                        'careerTitle'   => $c['careerTitle'],
                        'score'         => (int) ($c['match'] ?? $attempt->score),
                    ])->all()
                    : [],
            ];
        }

        return $result;
    }
}
