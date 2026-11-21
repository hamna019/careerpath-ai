<?php

namespace App\Http\Controllers;

use App\Mail\EmailChangeOtpMail;
use App\Models\StudentOtp;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\ProfileCompletion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserProfilesController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile ?? new UserProfile();

        return response()->json([
            'user' => [
                'id'           => $user->id,
                'fullName'     => $user->name,
                'email'        => $user->email,
                'phone'        => $user->phone,
                'countryCode'  => $user->country_code,
                'country'      => $user->country_name,
                'avatar'       => $user->getFirstMediaUrl('avatar') ?: null,
                'hasPassword'  => (bool) $user->has_password,
                'googleLinked' => (bool) $user->google_id,
            ],
            'profile'    => $this->formatProfile($profile),
            'completion' => ProfileCompletion::for($user),
        ]);
    }

    public function completion(Request $request)
    {
        return response()->json(ProfileCompletion::for($request->user()));
    }

    private const PROFILE_COLUMNS = [
        'city'               => 'city',
        'dob'                => 'date_of_birth',
        'address'            => 'residential_address',
        'gender'             => 'gender',
        'currentEducation'   => 'current_education',
        'fieldOfStudy'       => 'field_of_study',
        'university'         => 'university',
        'cgpa'               => 'cgpa',
        'expectedGraduation' => 'graduation_year',
        'interests'          => 'career_interests',
        'targetRoles'        => 'target_job_roles',
        'skills'             => 'core_skills',
        'workPreference'     => 'preferred_work_mode',
        'bio'                => 'bio',
        'linkedin'           => 'linkedin',
        'github'             => 'github',
        'portfolio'          => 'portfolio',
        'twitter'            => 'twitter',
    ];

    private const LIST_FIELDS = ['interests', 'targetRoles', 'skills', 'workPreference'];
    private const MAX_TAGS = 15;
    private const EMAIL_CODE_MINUTES = 10;
    private const EMAIL_RESEND_SECONDS = 60;

    public function update(Request $request)
    {
        $user = $request->user();
        $link = 'regex:/^(https?:\/\/)?[\w.-]+\.[a-z]{2,}(\/\S*)?$/i';

        $validated = $request->validate([
            'fullName' => ['sometimes', 'required', 'string', 'max:255'],
            'email'    => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'    => ['sometimes', 'nullable', 'string', 'max:20', 'regex:/^[0-9+\s\-()]{7,20}$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'country'  => ['sometimes', 'nullable', 'string', 'max:255'],

            'city'                => ['sometimes', 'nullable', 'string', 'max:255'],
            'dob'                 => ['sometimes', 'nullable', 'date', 'before:today'],
            'address'             => ['sometimes', 'nullable', 'string', 'max:500'],
            'gender'              => ['sometimes', 'nullable', 'string', 'max:50'],
            'currentEducation'    => ['sometimes', 'nullable', 'string', 'max:255'],
            'fieldOfStudy'        => ['sometimes', 'nullable', 'string', 'max:255'],
            'university'          => ['sometimes', 'nullable', 'string', 'max:255'],
            'cgpa'                => ['sometimes', 'nullable', 'string', 'max:20', 'regex:/^\d{1,3}(\.\d{1,2})?\s*(%|\/\s*\d{1,3}(\.\d{1,2})?)?$/'],
            'expectedGraduation'  => ['sometimes', 'nullable', 'digits:4', 'integer', 'between:1990,2100'],
            'interests'           => ['sometimes', 'nullable', 'array', 'max:' . self::MAX_TAGS],
            'interests.*'         => ['string', 'max:60', 'distinct:ignore_case'],
            'targetRoles'         => ['sometimes', 'nullable', 'array', 'max:' . self::MAX_TAGS],
            'targetRoles.*'       => ['string', 'max:60', 'distinct:ignore_case'],
            'skills'              => ['sometimes', 'nullable', 'array', 'max:' . self::MAX_TAGS],
            'skills.*'            => ['string', 'max:60', 'distinct:ignore_case'],
            'workPreference'      => ['sometimes', 'nullable', 'array'],
            'workPreference.*'    => ['string', 'in:On-site,Remote,Hybrid'],
            'bio'                 => ['sometimes', 'nullable', 'string', 'max:2000'],
            'linkedin'            => ['sometimes', 'nullable', 'string', 'max:255', $link],
            'github'              => ['sometimes', 'nullable', 'string', 'max:255', $link],
            'portfolio'           => ['sometimes', 'nullable', 'string', 'max:255', $link],
            'twitter'             => ['sometimes', 'nullable', 'string', 'max:255'],
        ], [
            'email.unique'             => 'This email address is already used by another account.',
            'phone.unique'             => 'This phone number is already used by another account.',
            'phone.regex'              => 'Enter a valid phone number (digits only, 7-20 characters).',
            'cgpa.regex'               => 'Enter your CGPA like 3.4 or 3.4/4, or a percentage like 85%.',
            'expectedGraduation.*'     => 'Enter a 4-digit graduation year, like 2027.',
            'dob.before'               => 'Date of birth must be in the past.',
            'interests.*.distinct'     => 'This career interest is already in the list.',
            'targetRoles.*.distinct'   => 'This target role is already in the list.',
            'skills.*.distinct'        => 'This skill is already in the list.',
            'interests.max'            => 'You can add up to ' . self::MAX_TAGS . ' career interests.',
            'targetRoles.max'          => 'You can add up to ' . self::MAX_TAGS . ' target roles.',
            'skills.max'               => 'You can add up to ' . self::MAX_TAGS . ' skills.',
            'linkedin.regex'           => 'Enter a valid link, like linkedin.com/in/your-name.',
            'github.regex'             => 'Enter a valid link, like github.com/your-name.',
            'portfolio.regex'          => 'Enter a valid website link.',
        ], [
            'fullName' => 'full name',
        ]);

        if (array_key_exists('fullName', $validated)) {
            $user->name = $validated['fullName'];
        }
        if (array_key_exists('phone', $validated)) {
            $user->phone = $validated['phone'];
        }
        if (array_key_exists('country', $validated)) {
            $user->country_name = $validated['country'];
        }
        $user->save();

        $profileData = [];
        foreach (self::PROFILE_COLUMNS as $key => $column) {
            if (array_key_exists($key, $validated)) {
                $value = $validated[$key];
                $profileData[$column] = in_array($key, self::LIST_FIELDS, true) ? array_values($value ?? []) : $value;
            }
        }
        $profile = UserProfile::updateOrCreate(['user_id' => $user->id], $profileData);
        $user->setRelation('profile', $profile);

        $pendingEmail = null;
        $message = 'Profile updated successfully!';
        if (array_key_exists('email', $validated) && strcasecmp($validated['email'], $user->email) !== 0) {
            $pendingEmail = strtolower($validated['email']);
            try {
                $this->sendEmailChangeCode($user, $pendingEmail);
                $message = "Profile saved. Enter the code we sent to {$pendingEmail} to confirm your new email.";
            } catch (\Throwable $e) {
                Log::warning('Email change code could not be sent', ['user_id' => $user->id, 'error' => $e->getMessage()]);
                $pendingEmail = null;
                $message = 'Profile saved, but we could not send a code to the new email address. Please try again.';
            }
        }

        return response()->json([
            'message'      => $message,
            'user'         => $this->formatUser($user),
            'profile'      => $this->formatProfile($profile),
            'completion'   => ProfileCompletion::for($user),
            'pendingEmail' => $pendingEmail,
        ]);
    }

    public function verifyEmailChange(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'size:5']]);
        $user = $request->user();

        $email = Cache::get($this->emailChangeKey($user));
        if (!$email) {
            return response()->json(['message' => 'Your email change request has expired. Please save your new email again.'], 422);
        }
        if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
            Cache::forget($this->emailChangeKey($user));
            return response()->json(['message' => 'This email address is already used by another account.'], 422);
        }

        $otp = StudentOtp::where('email', $email)
            ->where('code', $request->input('code'))
            ->where('is_used', false)
            ->where('expires_at', '>=', now())
            ->latest()
            ->first();
        if (!$otp) {
            return response()->json(['message' => 'Invalid or expired verification code.'], 422);
        }

        $otp->update(['is_used' => true, 'used_at' => now()]);
        $user->email = $email;
        $user->save();
        Cache::forget($this->emailChangeKey($user));

        return response()->json([
            'message'    => 'Email address updated successfully!',
            'user'       => $this->formatUser($user),
            'completion' => ProfileCompletion::for($user),
        ]);
    }

    public function resendEmailChange(Request $request)
    {
        $user = $request->user();
        $email = Cache::get($this->emailChangeKey($user));
        if (!$email) {
            return response()->json(['message' => 'Your email change request has expired. Please save your new email again.'], 422);
        }

        $last = StudentOtp::where('email', $email)->latest()->first();
        $waited = $last ? (int) $last->created_at->diffInSeconds(now()) : self::EMAIL_RESEND_SECONDS;
        if ($waited < self::EMAIL_RESEND_SECONDS) {
            return response()->json([
                'message'     => 'Please wait before requesting another code.',
                'retry_after' => self::EMAIL_RESEND_SECONDS - $waited,
            ], 429);
        }

        try {
            $this->sendEmailChangeCode($user, $email);
        } catch (\Throwable $e) {
            Log::warning('Email change code could not be resent', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'We could not send the code right now. Please try again.'], 500);
        }

        return response()->json(['message' => "A new code was sent to {$email}.", 'retry_after' => self::EMAIL_RESEND_SECONDS]);
    }

    private function sendEmailChangeCode(User $user, string $email): void
    {
        $code = str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        StudentOtp::create([
            'email'      => $email,
            'code'       => $code,
            'expires_at' => now()->addMinutes(self::EMAIL_CODE_MINUTES),
            'is_used'    => false,
        ]);
        Cache::put($this->emailChangeKey($user), $email, now()->addMinutes(self::EMAIL_CODE_MINUTES));
        Mail::to($email)->send(new EmailChangeOtpMail($code));
    }

    private function emailChangeKey(User $user): string
    {
        return "profile-email-change:{$user->id}";
    }

    private function formatUser(User $user): array
    {
        return [
            'id'          => $user->id,
            'fullName'    => $user->name,
            'email'       => $user->email,
            'phone'       => $user->phone,
            'countryCode' => $user->country_code,
            'country'     => $user->country_name,
            'avatar'      => $user->getFirstMediaUrl('avatar') ?: null,
            'hasPassword' => (bool) $user->has_password,
            'googleLinked' => (bool) $user->google_id,
        ];
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        $user = $request->user();
        $user->addMediaFromRequest('avatar')->toMediaCollection('avatar');

        return response()->json([
            'message'    => 'Profile picture updated successfully!',
            'avatar'     => $user->fresh()->getFirstMediaUrl('avatar'),
            'completion' => ProfileCompletion::for($user->fresh()),
        ]);
    }

    public function deleteAvatar(Request $request)
    {
        $user = $request->user();
        $user->clearMediaCollection('avatar');

        return response()->json([
            'message'    => 'Profile picture removed successfully!',
            'avatar'     => null,
            'completion' => ProfileCompletion::for($user->fresh()),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'currentPassword' => [$user->has_password ? 'required' : 'nullable', 'string'],
            'newPassword'     => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if ($user->has_password && !Hash::check($request->input('currentPassword'), $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $user->password = Hash::make($request->input('newPassword'));
        $user->has_password = true;
        $user->save();

        return response()->json(['message' => 'Password updated successfully!']);
    }

    private function formatProfile(UserProfile $profile): array
    {
        return [
            'city'               => $profile->city,
            'dob'                => optional($profile->date_of_birth)->format('Y-m-d'),
            'address'            => $profile->residential_address,
            'gender'             => $profile->gender,
            'currentEducation'   => $profile->current_education,
            'fieldOfStudy'       => $profile->field_of_study,
            'university'         => $profile->university,
            'cgpa'               => $profile->cgpa,
            'expectedGraduation' => $profile->graduation_year,
            'interests'          => $profile->career_interests ?? [],
            'targetRoles'        => $profile->target_job_roles ?? [],
            'skills'             => $profile->core_skills ?? [],
            'workPreference'     => $profile->preferred_work_mode ?? [],
            'bio'                => $profile->bio,
            'linkedin'           => $profile->linkedin,
            'github'             => $profile->github,
            'portfolio'          => $profile->portfolio,
            'twitter'            => $profile->twitter,
        ];
    }
}
