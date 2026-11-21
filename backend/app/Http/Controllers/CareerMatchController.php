<?php

namespace App\Http\Controllers;

use App\Models\CareerDetails;
use App\Models\StudentResult;
use App\Services\CareerMatcher;
use App\Services\MlCareerRecommender;
use App\Services\ProfileCompletion;
use Illuminate\Http\Request;

class CareerMatchController extends Controller
{
    public function submit(Request $request, CareerMatcher $matcher, MlCareerRecommender $ml)
    {
        $validated = $request->validate([
            'answers'   => ['required', 'array', 'min:1'],
            'answers.*' => ['array'],
        ]);

        $answers = collect($validated['answers'])
            ->filter(fn ($chosen, $questionId) => is_numeric($questionId))
            ->map(fn ($chosen) => array_values(array_filter(array_map('strval', $chosen), fn ($o) => trim($o) !== '')))
            ->filter(fn ($chosen) => count($chosen) > 0)
            ->all();

        if (!$answers) {
            return response()->json(['message' => 'No answers were submitted.'], 422);
        }

        $completion = ProfileCompletion::for($request->user());
        if (!$completion['eligible']) {
            return ProfileCompletion::blockedResponse($completion);
        }

        $profile = $request->user()?->profile?->only([
            'current_education', 'field_of_study', 'cgpa', 'career_interests',
            'target_job_roles', 'core_skills', 'preferred_work_mode', 'bio',
        ]);
        $ranked = $ml->rank($answers, $profile);
        $ml123 = $ranked['ranked_123_careers'] ?? [];

        if (!empty($ml123) && is_array($ml123)) {
            $recommendations = $matcher->explain($answers, $ml123, $ranked['model']['source'] ?? 'ml');
        } else {
            $mlDomainScores = $ranked['model']['domain_scores'] ?? [];
            $maxMlDomain = !empty($mlDomainScores) ? max(array_map('floatval', $mlDomainScores)) : 0.0;

            if ($ranked && $maxMlDomain >= 1.5) {
                $recommendations = $matcher->explain($answers, $ranked['recommendations'], $ranked['model']['source'] ?? 'ml');
            } else {
                $ruleMatch = $matcher->match($answers);
                $topRuleDomain = $ruleMatch['profile']['domains'][0] ?? null;
                $uncoveredDomains = ['law', 'gov', 'hospitality', 'trades', 'transport', 'defense', 'sports', 'agri', 'env'];
                $useRulesForUncovered = $topRuleDomain && in_array($topRuleDomain, $uncoveredDomains, true);

                $recommendations = ($ranked && !$useRulesForUncovered)
                    ? $matcher->explain($answers, $ranked['recommendations'], $ranked['model']['source'] ?? 'ml')
                    : $ruleMatch['recommendations'];
            }
        }

        if (!$recommendations) {
            return response()->json(['message' => 'No careers are available to match yet.'], 422);
        }

        $top    = $recommendations[0];
        $career = CareerDetails::find($top['career_id']);

        $attempt = RecentStudentActivityController::recordCompletion(
            $request->user(),
            $top['match'],
            $career,
            $top['title']
        );

        $attempt?->update([
            'answers'         => $answers,
            'recommendations' => $recommendations,
        ]);

        return response()->json([
            'recommendations' => $this->hydrate($recommendations),
            'completedAt'     => $attempt?->completed_at?->toIso8601String(),
        ]);
    }

    public function latest(Request $request)
    {
        $attempt = StudentResult::where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->whereNotNull('recommendations')
            ->latest('completed_at')
            ->first();

        $recommendations = $attempt ? $this->hydrate($attempt->recommendations ?? []) : [];

        return response()->json([
            'recommendations' => $recommendations,
            'completedAt'     => $recommendations ? $attempt->completed_at?->toIso8601String() : null,
        ]);
    }

    public function show(Request $request, CareerMatcher $matcher, int $id)
    {
        $attempt = StudentResult::where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->findOrFail($id);

        $answers = $attempt->answers ?? [];
        $recommendations = $attempt->recommendations ?? [];
        if (!$recommendations && $attempt->career_id) {
            $recommendations = $answers
                ? $matcher->explain($answers, [['career_id' => $attempt->career_id, 'match' => $attempt->match_percent ?? $attempt->score]])
                : [[
                    'career_id' => $attempt->career_id,
                    'match'     => $attempt->match_percent ?? $attempt->score,
                    'reasons'   => ['This was your top career match in this assessment. Detailed AI reasons are only recorded for assessments taken after the AI model upgrade.'],
                ]];
        }

        $number = StudentResult::where('user_id', $attempt->user_id)
            ->where('status', 'completed')
            ->where('id', '<=', $attempt->id)
            ->count();

        return response()->json([
            'id'              => $attempt->id,
            'number'          => $number,
            'score'           => $attempt->score,
            'completedAt'     => $attempt->completed_at?->toIso8601String(),
            'date'            => $attempt->completed_at?->format('M j, Y'),
            'time'            => $attempt->completed_at?->format('g:i A'),
            'certificateId'   => $attempt->certificateId(),
            'recommendations' => $this->hydrate($recommendations),

            'profileSummary'  => $answers ? $matcher->summarize($answers) : null,
        ]);
    }

    private function hydrate(array $recommendations): array
    {
        $careers = CareerDetails::whereIn('id', array_column($recommendations, 'career_id'))->get()->keyBy('id');

        return collect($recommendations)
            ->filter(fn ($r) => $careers->has($r['career_id']))
            ->values()
            ->map(fn ($r, $i) => [
                'rank'       => $i + 1,
                'match'      => (int) $r['match'],
                'matchLevel' => self::matchLevel((int) $r['match']),
                'reasons'    => $r['reasons'] ?? [],
                'career'     => $careers[$r['career_id']]->makeHidden('traits'),
            ])
            ->all();
    }

    private static function matchLevel(int $match): string
    {
        return match (true) {
            $match >= 90 => 'Excellent Match',
            $match >= 80 => 'Strong Match',
            $match >= 70 => 'Good Match',
            default      => 'Potential Match',
        };
    }

    public function feedback(Request $request)
    {
        $validated = $request->validate([
            'career_id'    => ['nullable', 'integer'],
            'career_title' => ['nullable', 'string'],
            'match_score'  => ['nullable', 'numeric'],
            'feedback'     => ['required', 'string', 'in:helpful,not_helpful'],
        ]);

        \Illuminate\Support\Facades\DB::table('recommendation_feedback')->insert([
            'user_id'      => $request->user()?->id,
            'career_id'    => $validated['career_id'] ?? null,
            'career_title' => $validated['career_title'] ?? null,
            'match_score'  => isset($validated['match_score']) ? max(0, min(100, (int) round($validated['match_score']))) : null,
            'feedback'     => $validated['feedback'],
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        \Illuminate\Support\Facades\Log::info('Career recommendation feedback', [
            'user_id' => $request->user()?->id,
            'data'    => $validated,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Thank you for your feedback! It helps improve our AI recommendations.',
        ]);
    }
}
