<?php

namespace App\Services;

use App\Models\User;

class ProfileCompletion
{
    public const REQUIRED_PERCENT = 70;

    private const FIELDS = [
        'avatar'             => [4, 'Profile photo', 'Personal'],
        'fullName'           => [3, 'Full name', 'Personal'],
        'email'              => [3, 'Email address', 'Personal'],
        'phone'              => [5, 'Phone number', 'Personal'],
        'country'            => [5, 'Country', 'Personal'],
        'city'               => [5, 'City', 'Personal'],
        'dob'                => [5, 'Date of birth', 'Personal'],
        'address'            => [5, 'Residential address', 'Personal'],
        'currentEducation'   => [8, 'Current education', 'Academic'],
        'fieldOfStudy'       => [8, 'Field of study', 'Academic'],
        'cgpa'               => [6, 'CGPA / percentage', 'Academic'],
        'university'         => [4, 'University / institute', 'Academic'],
        'expectedGraduation' => [4, 'Expected graduation', 'Academic'],
        'interests'          => [8, 'Career interests', 'Career'],
        'skills'             => [8, 'Core skills', 'Career'],
        'targetRoles'        => [8, 'Target job roles', 'Career'],
        'workPreference'     => [6, 'Preferred work mode', 'Career'],
        'bio'                => [5, 'Professional bio', 'About'],
    ];

    public static function for(User $user): array
    {
        $profile = $user->profile;

        $values = [
            'avatar'             => $user->getFirstMediaUrl('avatar') ?: null,
            'fullName'           => $user->name,
            'email'              => $user->email,
            'phone'              => $user->phone,
            'country'            => $user->country_name,
            'city'               => $profile?->city,
            'dob'                => $profile?->date_of_birth,
            'address'            => $profile?->residential_address,
            'currentEducation'   => $profile?->current_education,
            'fieldOfStudy'       => $profile?->field_of_study,
            'cgpa'               => $profile?->cgpa,
            'university'         => $profile?->university,
            'expectedGraduation' => $profile?->graduation_year,
            'interests'          => $profile?->career_interests,
            'skills'             => $profile?->core_skills,
            'targetRoles'        => $profile?->target_job_roles,
            'workPreference'     => $profile?->preferred_work_mode,
            'bio'                => $profile?->bio,
        ];

        $percent = 0;
        $missing = [];
        $fields  = [];
        foreach (self::FIELDS as $key => [$weight, $label, $section]) {
            $done = self::filled($values[$key]);
            $percent += $done ? $weight : 0;
            if (!$done) {
                $missing[] = $label;
            }
            $fields[] = compact('key', 'label', 'section', 'weight', 'done');
        }

        return [
            'percent'  => $percent,
            'required' => self::REQUIRED_PERCENT,
            'eligible' => true,
            'missing'  => $missing,
            'fields'   => $fields,
        ];
    }

    public static function blockedResponse(array $completion): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message'    => "Please complete at least {$completion['required']}% of your profile before taking the "
                . "career assessment. Your profile is {$completion['percent']}% complete.",
            'completion' => $completion,
        ], 403);
    }

    private static function filled($value): bool
    {
        if (is_array($value)) {
            return count(array_filter($value, fn ($v) => trim((string) $v) !== '')) > 0;
        }

        return $value !== null && trim((string) $value) !== '';
    }
}
