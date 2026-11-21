<?php

namespace App\Http\Controllers;

use App\Models\CareerDetails;
use App\Models\CareerView;
use App\Models\DailyActiveTime;
use App\Models\QuizQuestion;
use App\Models\StudentResult;
use App\Support\NotificationReadState;
use App\Support\RelativeTime;
use Illuminate\Http\Request;

class StudentStatsController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $quizzesCompleted = StudentResult::where('user_id', $userId)
            ->where('status', 'completed')
            ->count();

        $careersExplored = CareerView::where('user_id', $userId)->count();

        $matchedCareerIds = StudentResult::where('user_id', $userId)
            ->where('status', 'completed')
            ->get(['career_id', 'recommendations'])
            ->flatMap(fn ($r) => array_merge([$r->career_id], array_column($r->recommendations ?? [], 'career_id')))
            ->filter()
            ->unique()
            ->values();

        $skillsAssessed = CareerDetails::whereIn('id', $matchedCareerIds)
            ->pluck('skills')
            ->flatten()
            ->filter()
            ->unique()
            ->count();

        $activeSecondsToday = (int) DailyActiveTime::where('user_id', $userId)
            ->where('activity_date', now()->toDateString())
            ->value('seconds');

        $lastAttempt = StudentResult::where('user_id', $userId)
            ->where('status', 'completed')
            ->whereNotNull('score')
            ->latest('completed_at')
            ->latest('id')
            ->first(['score', 'recommendations']);
        $lastQuizScore = $lastAttempt?->score;

        $lastMatchingCareers = CareerDetails::whereIn(
            'id',
            array_filter(array_column($lastAttempt?->recommendations ?? [], 'career_id'))
        )->count();

        $averageScore = StudentResult::where('user_id', $userId)
            ->where('status', 'completed')
            ->whereNotNull('score')
            ->avg('score');

        $matchingCareers = $matchedCareerIds->count();

        return response()->json([
            'quizzesCompleted'   => $quizzesCompleted,
            'careersExplored'    => $careersExplored,
            'skillsAssessed'     => $skillsAssessed,
            'activeSecondsToday' => $activeSecondsToday,
            'lastQuizScore'      => $lastQuizScore !== null ? (int) $lastQuizScore : null,
            'averageScore'       => $averageScore !== null ? (int) round($averageScore) : null,
            'matchingCareers'    => $matchingCareers,
            'lastMatchingCareers' => $lastMatchingCareers,
        ]);
    }

    public function trackCareerView(Request $request, CareerDetails $career)
    {
        CareerView::firstOrCreate([
            'user_id'   => $request->user()->id,
            'career_id' => $career->id,
        ]);

        $careersExplored = CareerView::where('user_id', $request->user()->id)->count();

        return response()->json(['careersExplored' => $careersExplored]);
    }

    public function heartbeat(Request $request)
    {
        $request->validate([
            'seconds' => ['required', 'integer', 'min:0', 'max:60'],
        ]);

        $seconds = (int) $request->input('seconds');

        $row = DailyActiveTime::firstOrCreate(
            ['user_id' => $request->user()->id, 'activity_date' => now()->toDateString()],
            ['seconds' => 0]
        );

        if ($seconds > 0) {
            $row->increment('seconds', $seconds);
        }

        return response()->json(['activeSecondsToday' => $row->fresh()->seconds]);
    }

    public function notifications(Request $request)
    {
        $limit = 8;

        $careerEvents = CareerDetails::orderByDesc('created_at')
            ->take($limit)
            ->get(['title', 'created_at'])
            ->map(fn ($career) => [
                'type'   => 'career_added',
                'text'   => "New career added: {$career->title}",
                'detail' => 'Explore role details, skills, and salary insights.',
                'at'     => $career->created_at,
            ]);

        $quizEvents = QuizQuestion::orderByDesc('created_at')
            ->take($limit)
            ->get(['category', 'created_at'])
            ->map(fn ($question) => [
                'type'   => 'quiz_updated',
                'text'   => 'New assessment question added',
                'detail' => $question->category ? "Category: {$question->category}" : 'Retake the assessment to see what changed.',
                'at'     => $question->created_at,
            ]);

        $numbers = StudentResult::where('user_id', $request->user()->id)->where('status', 'completed')
            ->orderBy('id')->pluck('id')->flip();
        $certificateEvents = StudentResult::where('user_id', $request->user()->id)
            ->whereNotNull('certificate_approved_at')
            ->orderByDesc('certificate_approved_at')
            ->take($limit)
            ->get(['id', 'certificate_approved_at'])
            ->map(fn ($r) => [
                'type'   => 'certificate_approved',
                'text'   => 'Certificate approved: Assessment #' . (($numbers[$r->id] ?? 0) + 1),
                'detail' => 'Your certificates are ready. Download them from Results.',
                'at'     => $r->certificate_approved_at,
            ]);

        $feed = $certificateEvents->concat($careerEvents)->concat($quizEvents)
            ->sortByDesc(fn ($item) => $item['at'])
            ->take($limit)
            ->values()
            ->map(fn ($item) => [
                'type'      => $item['type'],
                'text'      => $item['text'],
                'detail'    => $item['detail'],
                'time'      => RelativeTime::format($item['at']),
                'timestamp' => $item['at']->toIso8601String(),
            ]);

        $readUntil = NotificationReadState::readUntil($request->user());

        return response()->json([
            'notifications' => $feed,
            'read_until'    => $readUntil->toIso8601String(),
            'unread_count'  => CareerDetails::where('created_at', '>', $readUntil)->count()
                + QuizQuestion::where('created_at', '>', $readUntil)->count()
                + StudentResult::where('user_id', $request->user()->id)->where('certificate_approved_at', '>', $readUntil)->count(),
        ]);
    }
}
