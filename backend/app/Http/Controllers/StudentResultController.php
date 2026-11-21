<?php

namespace App\Http\Controllers;

use App\Models\StudentResult;
use App\Models\User;
use App\Services\AssessmentCertificates;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudentResultController extends Controller
{
    private const STATUS_LABELS = [
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
        'error'       => 'Error',
    ];

    public function index()
    {
        StudentResult::sweepAbandoned();

        $latestPerUser = StudentResult::with('user:id,name,email')
            ->orderBy('id', 'desc')
            ->get()
            ->filter(fn ($row) => $row->user)
            ->unique('user_id')
            ->values();

        $sorted = $latestPerUser
            ->sortByDesc(fn (StudentResult $row) => $this->eventTimeFor($row)->getTimestamp())
            ->values();

        $data = $sorted->map(function (StudentResult $row, int $index) {
            return $this->serializeAttempt($row, $index + 1);
        })->values();

        return response()->json([
            'total'   => $data->count(),
            'results' => $data,
        ]);
    }

    public function history(User $user)
    {
        return response()->json($this->buildHistoryPayload($user));
    }

    public function approveCertificate(Request $request, StudentResult $result)
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json(['message' => 'Only admins can approve certificates.'], 403);
        }
        if ($result->status !== 'completed') {
            return response()->json(['message' => 'Only completed assessments can be approved.'], 422);
        }

        $status = AssessmentCertificates::forAttempts($result->user_id, collect([$result]))[$result->id];
        if ($status['status'] === AssessmentCertificates::APPROVED) {
            return response()->json(['message' => 'This certificate is already approved.', 'certificate' => $status], 409);
        }
        if ($status['status'] !== AssessmentCertificates::PENDING_APPROVAL) {
            return response()->json([
                'message'     => "The student has completed {$status['completedCareers']} of {$status['totalCareers']} career roadmaps. All must be 100% before approval.",
                'certificate' => $status,
            ], 422);
        }

        $updated = StudentResult::whereKey($result->id)
            ->whereNull('certificate_approved_at')
            ->update(['certificate_approved_at' => now(), 'certificate_approved_by' => $request->user()->id]);
        if (!$updated) {
            return response()->json(['message' => 'This certificate is already approved.'], 409);
        }

        $result->refresh();

        return response()->json([
            'message'     => "Certificate approved for Assessment #{$status['number']}.",
            'certificate' => AssessmentCertificates::forAttempts($result->user_id, collect([$result]))[$result->id],
        ]);
    }

    public function mine(Request $request)
    {
        return response()->json($this->buildHistoryPayload($request->user()));
    }

    private function buildHistoryPayload(User $user): array
    {
        StudentResult::sweepAbandoned();

        $rows = StudentResult::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();

        $certificates = AssessmentCertificates::forAttempts($user->id, $rows->where('status', 'completed'));

        $attempts = $rows->values()->map(function (StudentResult $row, int $index) use ($certificates) {
            return $this->serializeAttempt($row, $index + 1) + ['certificate' => $certificates[$row->id] ?? null];
        })->values();

        $completed = $rows->where('status', 'completed');

        return [
            'user' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'avatar' => $user->getFirstMediaUrl('avatar') ?: null,
            ],
            'summary' => [
                'totalAttempts'   => $rows->count(),
                'completedCount'  => $completed->count(),
                'errorCount'      => $rows->where('status', 'error')->count(),
                'inProgressCount' => $rows->where('status', 'in_progress')->count(),
                'avgScore'        => $completed->count() ? (int) round($completed->avg('score')) : null,
                'bestScore'       => $completed->count() ? (int) $completed->max('score') : null,
            ],
            'attempts' => $attempts,
        ];
    }

    private function serializeAttempt(StudentResult $row, int $serial): array
    {
        $eventAt = $this->eventTimeFor($row);
        $isCompleted = $row->status === 'completed';

        return [
            'id'           => $row->id,
            'serial'       => $serial,
            'userId'       => $row->user_id,
            'name'         => $row->user->name ?? null,
            'email'        => $row->user->email ?? null,
            'avatar'       => $row->user?->getFirstMediaUrl('avatar') ?: null,
            'status'       => self::STATUS_LABELS[$row->status] ?? 'In Progress',
            'score'        => $isCompleted ? $row->score : null,
            'careerMatch'  => $isCompleted ? $row->career_title : null,
            'matchPercent' => $isCompleted ? $row->match_percent : null,
            'startedAt'    => optional($row->started_at)->toIso8601String(),
            'completedAt'  => optional($row->completed_at)->toIso8601String(),
            'date'         => $eventAt->format('Y-m-d'),
            'time'         => $eventAt->format('g:i A'),
            'eventAt'      => $eventAt->toIso8601String(),
            'certificateId' => $isCompleted ? $row->certificateId() : null,
        ];
    }

    private function eventTimeFor(StudentResult $row): Carbon
    {
        if ($row->status === 'completed' && $row->completed_at) {
            return $row->completed_at;
        }

        return $row->started_at ?? $row->last_activity_at;
    }
}
