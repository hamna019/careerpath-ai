<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $activities = DB::table('recent_student_activities')
            ->whereIn('status', ['completed', 'in_progress', 'error'])
            ->get();

        foreach ($activities as $activity) {
            $alreadyBackfilled = DB::table('student_results')
                ->where('user_id', $activity->user_id)
                ->exists();

            if ($alreadyBackfilled) {
                continue;
            }

            $careerId = null;
            $careerTitle = null;
            $matchPercent = null;

            if ($activity->status === 'completed') {
                $career = DB::table('students_careers_analytics')
                    ->where('user_id', $activity->user_id)
                    ->first();

                if ($career) {
                    $careerId = $career->career_id;
                    $careerTitle = $career->career_title;
                    $matchPercent = $career->score;
                }
            }

            DB::table('student_results')->insert([
                'user_id'          => $activity->user_id,
                'status'           => $activity->status,
                'score'            => $activity->status === 'completed' ? $activity->score : null,
                'career_id'        => $careerId,
                'career_title'     => $careerTitle,
                'match_percent'    => $matchPercent,
                'started_at'       => $activity->started_at ?? $activity->last_activity_at,
                'completed_at'     => $activity->completed_at,
                'last_activity_at' => $activity->last_activity_at,
                'created_at'       => $activity->created_at,
                'updated_at'       => $activity->updated_at,
            ]);
        }
    }

    public function down(): void
    {
    }
};
