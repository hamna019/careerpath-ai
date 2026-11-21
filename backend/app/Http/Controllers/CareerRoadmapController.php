<?php

namespace App\Http\Controllers;

use App\Models\CareerDetails;
use App\Models\CareerRoadmapProgress;
use App\Models\StudentResult;
use Illuminate\Http\Request;

class CareerRoadmapController extends Controller
{
    public function show(Request $request, $careerId)
    {
        $career = CareerDetails::findOrFail($careerId);
        $roadmap = $career->roadmap ?? [];

        $completedIndexes = CareerRoadmapProgress::where('user_id', $request->user()->id)
            ->where('career_id', $career->id)
            ->whereNotNull('completed_at')
            ->pluck('step_index')
            ->all();

        $steps = collect($roadmap)->values()->map(function ($step, $index) use ($completedIndexes) {
            return [
                'index'     => $index,
                'title'     => $step['title'] ?? '',
                'desc'      => $step['desc'] ?? '',
                'duration'  => $step['duration'] ?? '',
                'tags'      => $step['tags'] ?? [],
                'completed' => in_array($index, $completedIndexes, true),
            ];
        })->values();

        $total = $steps->count();
        $completed = $steps->where('completed', true)->count();

        return response()->json([
            'careerId'       => $career->id,
            'careerTitle'    => $career->title,
            'steps'          => $steps,
            'totalSteps'     => $total,
            'completedSteps' => $completed,
            'percent'        => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
        ]);
    }

    public function toggle(Request $request, $careerId)
    {
        $data = $request->validate([
            'step_index' => 'required|integer|min:0',
        ]);

        $career = CareerDetails::findOrFail($careerId);
        $roadmap = $career->roadmap ?? [];

        if ($data['step_index'] >= count($roadmap)) {
            return response()->json(['message' => 'Invalid roadmap step.'], 422);
        }

        $progress = CareerRoadmapProgress::firstOrNew([
            'user_id'    => $request->user()->id,
            'career_id'  => $career->id,
            'step_index' => $data['step_index'],
        ]);

        if (!$progress->completed_at) {
            $progress->completed_at = now();
            $progress->save();
        }

        return $this->show($request, $careerId);
    }

    public function mine(Request $request)
    {
        $userId = $request->user()->id;

        $careerIds = CareerRoadmapProgress::where('user_id', $userId)
            ->distinct()
            ->pluck('career_id');

        $careers = CareerDetails::whereIn('id', $careerIds)->get()->keyBy('id');

        $rows = CareerRoadmapProgress::where('user_id', $userId)
            ->whereIn('career_id', $careerIds)
            ->get()
            ->groupBy('career_id');

        $summary = $rows->map(function ($progressRows, $careerId) use ($careers) {
            $career = $careers->get($careerId);
            if (!$career) {
                return null;
            }

            $totalSteps = count($career->roadmap ?? []);
            $completedSteps = $progressRows->whereNotNull('completed_at')->count();
            $lastUpdated = $progressRows->max('updated_at');

            return [
                'careerId'       => $career->id,
                'careerTitle'    => $career->title,
                'icon'           => $career->icon,
                'totalSteps'     => $totalSteps,
                'completedSteps' => $completedSteps,
                'percent'        => $totalSteps > 0 ? (int) round(($completedSteps / $totalSteps) * 100) : 0,
                'lastUpdatedAt'  => optional($lastUpdated)->toIso8601String(),
            ];
        })->filter()->values()->sortByDesc('lastUpdatedAt')->values();

        return response()->json(['roadmaps' => $summary]);
    }

    public function journey(Request $request)
    {
        $userId = $request->user()->id;

        $attempts = StudentResult::where('user_id', $userId)
            ->where('status', 'completed')
            ->orderBy('completed_at')
            ->orderBy('id')
            ->get(['id', 'score', 'career_id', 'career_title', 'match_percent', 'recommendations', 'completed_at']);

        $suggestions = $attempts->mapWithKeys(fn (StudentResult $a) => [
            $a->id => $a->recommendations
                ? collect($a->recommendations)->map(fn ($r) => ['careerId' => $r['career_id'] ?? null, 'match' => $r['match'] ?? null])
                : collect([['careerId' => $a->career_id, 'match' => $a->match_percent]]),
        ]);

        $careerIds = $suggestions->flatten(1)->pluck('careerId')->filter()->unique();
        $careers = CareerDetails::whereIn('id', $careerIds)->get()->keyBy('id');
        $doneSteps = CareerRoadmapProgress::where('user_id', $userId)
            ->whereIn('career_id', $careerIds)
            ->whereNotNull('completed_at')
            ->get()
            ->countBy('career_id');

        $assessments = $attempts->values()->map(function (StudentResult $a, int $i) use ($suggestions, $careers, $doneSteps) {
            $list = $suggestions[$a->id]
                ->filter(fn ($s) => $careers->has($s['careerId']))
                ->values()
                ->map(function ($s, int $rank) use ($careers, $doneSteps) {
                    $career = $careers[$s['careerId']];
                    $total = count($career->roadmap ?? []);
                    $done = min($doneSteps[$career->id] ?? 0, $total);

                    return [
                        'rank'           => $rank + 1,
                        'careerId'       => $career->id,
                        'careerTitle'    => $career->title,
                        'icon'           => $career->icon,
                        'match'          => $s['match'] !== null ? (int) $s['match'] : null,
                        'totalSteps'     => $total,
                        'completedSteps' => $done,
                        'percent'        => $total > 0 ? (int) round($done / $total * 100) : 0,
                    ];
                });

            return [
                'id'          => $a->id,
                'number'      => $i + 1,
                'score'       => $a->score,
                'completedAt' => optional($a->completed_at)->toIso8601String(),
                'date'        => optional($a->completed_at)->format('M j, Y'),
                'time'        => optional($a->completed_at)->format('g:i A'),
                'careers'     => $list,
            ];
        })->reverse()->values();

        return response()->json(['assessments' => $assessments]);
    }
}
