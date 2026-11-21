<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetMail;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminStudentController extends Controller
{
    public function index()
    {
        $students = User::where('role', 'student')
            ->with(['profile', 'media'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $students->map(function (User $user) {
            $profile = $user->profile;

            $phone = trim(
                ($user->country_code ? $user->country_code . ' ' : '') . ($user->phone ?? '')
            );

            return [
                'id' => $user->id,

                'name'     => $user->name,
                'email'    => $user->email,
                'phone'    => $phone !== '' ? $phone : null,
                'country'  => $user->country_name,
                'avatar'   => $user->getFirstMediaUrl('avatar') ?: null,
                'joinedAt' => optional($user->created_at)->toDateString(),
                'isSuspended'        => (bool) $user->is_suspended,
                'hasProfile'         => (bool) $profile,
                'city'               => $profile->city ?? null,
                'dob'                => optional(optional($profile)->date_of_birth)->format('Y-m-d'),
                'address'            => $profile->residential_address ?? null,
                'gender'             => $profile->gender ?? null,
                'currentEducation'   => $profile->current_education ?? null,
                'fieldOfStudy'       => $profile->field_of_study ?? null,
                'university'         => $profile->university ?? null,
                'cgpa'               => $profile->cgpa ?? null,
                'expectedGraduation' => $profile->graduation_year ?? null,
                'interests'          => $profile->career_interests ?? [],
                'targetRoles'        => $profile->target_job_roles ?? [],
                'skills'             => $profile->core_skills ?? [],
                'workPreference'     => $profile->preferred_work_mode ?? [],
                'bio'                => $profile->bio ?? null,
                'linkedin'           => $profile->linkedin ?? null,
                'github'             => $profile->github ?? null,
                'portfolio'          => $profile->portfolio ?? null,
                'twitter'            => $profile->twitter ?? null,
            ];
        });

        return response()->json([
            'total'    => $data->count(),
            'students' => $data,
        ]);
    }

    public function toggleSuspension(User $student)
    {
        if ($student->role !== 'student') {
            return response()->json(['message' => 'This action only applies to student accounts.'], 422);
        }

        $student->update(['is_suspended' => !$student->is_suspended]);

        return response()->json([
            'message'     => $student->is_suspended ? 'Student account suspended.' : 'Student account reactivated.',
            'isSuspended' => (bool) $student->is_suspended,
        ]);
    }

    public function sendPasswordReset(User $student)
    {
        if ($student->role !== 'student') {
            return response()->json(['message' => 'This action only applies to student accounts.'], 422);
        }

        $rawToken = Str::random(64);

        PasswordResetToken::create([
            'email'      => $student->email,
            'token'      => Hash::make($rawToken),
            'is_used'    => false,
            'created_at' => now(),
        ]);

        $resetUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/')
            . '/?reset_email=' . urlencode($student->email)
            . '&reset_token=' . urlencode($rawToken);

        Mail::to($student->email)->send(new PasswordResetMail($resetUrl));

        return response()->json(['message' => "Password reset link sent to {$student->email}."]);
    }
}
