<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AdminId;
use App\Models\Country;
use App\Models\LoginHistory;
use App\Models\RecentStudentActivity;
use App\Models\StudentOtp;
use App\Models\AdminOtp;
use App\Models\PasswordResetToken;
use App\Mail\StudentOtpMail;
use App\Mail\AdminOtpMail;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const OTP_RESEND_COOLDOWN_SECONDS = 120;
    private const OTP_TTL_MINUTES = 10;
    private const RESET_TOKEN_TTL_MINUTES = 60;

    public function getCountries()
    {
        $countries = Country::orderBy('name')
            ->get(['name', 'code', 'flag', 'placeholder', 'max_len'])
            ->map(fn ($country) => [
                'name'        => $country->name,
                'code'        => $country->code,
                'flag'        => $country->flag,
                'placeholder' => $country->placeholder,
                'maxLen'      => $country->max_len,
            ]);

        return response()->json($countries, 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users,email',
            'country_name' => 'nullable|string|max:255',
            'country_code' => 'nullable|string|max:10',
            'phone'        => 'nullable|string|max:20|unique:users,phone', 
            'password'     => 'required|string|min:6',
            'role'         => 'required|in:student,admin',
        ]);

        if ($request->role === 'admin') {
            if (!$request->admin_id) {
                return response()->json(['message' => 'Admin ID enter karna zaroori hai!'], 422);
            }
            if (!$request->security_passcode) {
                return response()->json(['message' => 'Security Passcode enter karna zaroori hai!'], 422);
            }
            if ($request->security_passcode !== env('ADMIN_SECRET_KEY')) {
                return response()->json(['message' => 'Invalid Security Passcode! Access Denied.'], 403);
            }

            if (!AdminId::where('admin_id', $request->admin_id)->exists()) {
                return response()->json(['message' => 'Ye Admin ID company system mein authorized nahi hai!'], 403);
            }
            if (User::where('admin_id', $request->admin_id)->exists()) {
                return response()->json(['message' => 'Ye Admin ID pehle se registered hai!'], 409);
            }
        }

        $user = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'country_name' => $request->country_name,
            'country_code' => $request->country_code,
            'phone'        => $request->phone,
            'password'     => Hash::make($request->password),
            'role'         => $request->role,
            'admin_id'     => $request->role === 'admin' ? $request->admin_id : null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        if ($user->role === 'student') {
            RecentStudentActivity::updateOrCreate(
                ['user_id' => $user->id],
                ['status' => 'new_student', 'last_activity_at' => now()]
            );
        }

        if ($user->role === 'admin') {
            AdminId::where('admin_id', $user->admin_id)
                ->update(['email' => $user->email]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Account successfully create ho gaya hai!',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    private function parseUserAgent($userAgent)
    {
        $browser = 'Unknown'; $platform = 'Unknown'; $deviceType = 'Desktop';
        if (preg_match('/windows|win32/i', $userAgent)) $platform = 'Windows';
        elseif (preg_match('/macintosh|mac os x/i', $userAgent)) $platform = 'macOS';
        elseif (preg_match('/linux/i', $userAgent)) $platform = 'Linux';
        elseif (preg_match('/android/i', $userAgent)) $platform = 'Android';
        elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) $platform = 'iOS';

        if (preg_match('/mobile|iphone|android/i', $userAgent)) $deviceType = 'Mobile';
        elseif (preg_match('/ipad|tablet/i', $userAgent)) $deviceType = 'Tablet';

        if (preg_match('/chrome|crios/i', $userAgent) && !preg_match('/edg/i', $userAgent)) $browser = 'Chrome';
        elseif (preg_match('/firefox|fxios/i', $userAgent)) $browser = 'Firefox';
        elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) $browser = 'Safari';
        elseif (preg_match('/edg/i', $userAgent)) $browser = 'Edge';

        return compact('browser', 'platform', 'deviceType');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
            'role'     => 'required|in:student,admin',
            'admin_id' => 'nullable|string|max:255',
        ]);

        $agentInfo = $this->parseUserAgent($request->header('User-Agent'));
        $user = User::where('email', $request->email)->first();

        if ($user && !$user->has_password) {
            return response()->json([
                'message' => 'This account was created with Google. Use "Sign in with Google", or set a password with "Forgot password".',
            ], 422);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            LoginHistory::create([
                'user_id'        => $user ? $user->id : null,
                'email'          => $request->email,
                'role'           => $request->role,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->header('User-Agent'),
                'device_type'    => $agentInfo['deviceType'],
                'browser'        => $agentInfo['browser'],
                'platform'       => $agentInfo['platform'],
                'status'         => 'failed',
                'failure_reason' => 'Invalid email or password',
            ]);
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        if ($user->role !== $request->role) {
            LoginHistory::create([
                'user_id'        => $user->id,
                'email'          => $request->email,
                'role'           => $request->role,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->header('User-Agent'),
                'device_type'    => $agentInfo['deviceType'],
                'browser'        => $agentInfo['browser'],
                'platform'       => $agentInfo['platform'],
                'status'         => 'failed',
                'failure_reason' => "Role mismatch. Attempted: {$request->role}",
            ]);
            return response()->json([
                'message' => "Access Denied! Aapka account '{$user->role}' hai. Aap '{$request->role}' portal par login nahi kar sakte."
            ], 403);
        }

        if ($user->role === 'admin') {
            $submittedId = trim((string) $request->input('admin_id', ''));
            $idValid = $submittedId !== ''
                && $submittedId === $user->admin_id
                && AdminId::where('admin_id', $submittedId)->exists();

            if (!$idValid) {
                LoginHistory::create([
                    'user_id'        => $user->id,
                    'email'          => $user->email,
                    'role'           => $user->role,
                    'ip_address'     => $request->ip(),
                    'user_agent'     => $request->header('User-Agent'),
                    'device_type'    => $agentInfo['deviceType'],
                    'browser'        => $agentInfo['browser'],
                    'platform'       => $agentInfo['platform'],
                    'status'         => 'failed',
                    'failure_reason' => 'Invalid or missing Admin ID',
                ]);
                return response()->json(['message' => 'Invalid Admin ID for this account.'], 403);
            }
        }

        return $this->completeLogin($user, $request, $agentInfo);
    }

    private function blockIfAlreadyActiveElsewhere(User $user): ?LoginHistory
    {
        if ($user->role !== 'student') {
            return null;
        }

        $openSession = LoginHistory::findOpenFor($user->id);
        if (!$openSession) {
            return null;
        }

        if (!$openSession->isStale()) {
            return $openSession;
        }

        $openSession->update([
            'logout_at' => $openSession->last_seen_at ?? $openSession->login_at,
        ]);
        return null;
    }

    public function googleLogin(Request $request)
    {
        $request->validate(['access_token' => ['required', 'string', 'max:4096']]);

        $clientId = config('services.google.client_id');
        if (!$clientId) {
            return response()->json(['message' => 'Google sign-in is not configured yet.'], 503);
        }

        try {
            $httpClient = app()->isLocal() ? Http::withoutVerifying() : Http::asJson();

            $tokenInfo = $httpClient->timeout(10)->get('https://oauth2.googleapis.com/tokeninfo', [
                'access_token' => $request->input('access_token'),
            ]);
            if (!$tokenInfo->ok() || ($tokenInfo->json('aud') !== $clientId && $tokenInfo->json('azp') !== $clientId)) {
                return response()->json(['message' => 'Google sign-in could not be verified. Please try again.'], 401);
            }

            $profile = $httpClient->timeout(10)->withToken($request->input('access_token'))
                ->get('https://openidconnect.googleapis.com/v1/userinfo');
            if (!$profile->ok() || !$profile->json('sub') || !$profile->json('email')) {
                return response()->json(['message' => 'Google did not share your account details. Please try again.'], 401);
            }
        } catch (\Throwable $e) {
            Log::warning('Google sign-in failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Could not reach Google. Please try again.'], 502);
        }

        if (!filter_var($profile->json('email_verified'), FILTER_VALIDATE_BOOLEAN)) {
            return response()->json(['message' => 'Your Google email address is not verified.'], 403);
        }

        $googleId = (string) $profile->json('sub');
        $email = strtolower($profile->json('email'));
        $user = User::where('google_id', $googleId)->first() ?? User::where('email', $email)->first();

        if ($user && $user->role !== 'student') {
            return response()->json(['message' => 'Google sign-in is only available for student accounts. Admins sign in with their Admin ID.'], 403);
        }

        if (!$user) {
            $user = User::create([
                'name'         => $profile->json('name') ?: Str::before($email, '@'),
                'email'        => $email,
                'password'     => Hash::make(Str::random(40)), 
                'has_password' => false,
                'role'         => 'student',
                'google_id'    => $googleId,
            ]);
            RecentStudentActivity::updateOrCreate(
                ['user_id' => $user->id],
                ['status' => 'new_student', 'last_activity_at' => now()]
            );
        } elseif (!$user->google_id) {
            $user->google_id = $googleId;
            $user->save();
        }

        $picture = $profile->json('picture');
        if ($picture && !$user->getFirstMediaUrl('avatar')) {
            try {
                $user->addMediaFromUrl(preg_replace('/=s\d+-c$/', '=s400-c', $picture))->toMediaCollection('avatar');
            } catch (\Throwable $e) {
                Log::info('Google photo could not be saved', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }
        }

        return $this->completeLogin($user->fresh(), $request, $this->parseUserAgent($request->header('User-Agent')));
    }

    private function completeLogin(User $user, Request $request, array $agentInfo)
    {
        if ($user->role === 'student' && $user->is_suspended) {
            LoginHistory::create([
                'user_id'        => $user->id,
                'email'          => $user->email,
                'role'           => $user->role,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->header('User-Agent'),
                'device_type'    => $agentInfo['deviceType'],
                'browser'        => $agentInfo['browser'],
                'platform'       => $agentInfo['platform'],
                'status'         => 'failed',
                'failure_reason' => 'Account suspended',
            ]);

            return response()->json([
                'message' => 'Account suspended. Contact support.',
            ], 403);
        }

        $activeElsewhere = $this->blockIfAlreadyActiveElsewhere($user);
        if ($activeElsewhere) {
            LoginHistory::create([
                'user_id'        => $user->id,
                'email'          => $user->email,
                'role'           => $user->role,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->header('User-Agent'),
                'device_type'    => $agentInfo['deviceType'],
                'browser'        => $agentInfo['browser'],
                'platform'       => $agentInfo['platform'],
                'status'         => 'failed',
                'failure_reason' => 'Already logged in on another device',
            ]);

            return response()->json([
                'message' => 'This account is already logged in on another device. Please log out there first.',
            ], 409);
        }

        $history = LoginHistory::create([
            'user_id'     => $user->id,
            'email'       => $user->email,
            'role'        => $user->role,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->header('User-Agent'),
            'device_type' => $agentInfo['deviceType'],
            'browser'     => $agentInfo['browser'],
            'platform'    => $agentInfo['platform'],
            'status'       => 'success',
            'login_at'     => now(),
            'last_seen_at' => now(),
        ]);

        $user->tokens()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'           => 'success',
            'message'          => 'Login successful!',
            'user'             => $user,
            'token'            => $token,
            'login_history_id' => $history->id
        ], 200);
    }

    public function requestStudentLoginOtp(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $agentInfo = $this->parseUserAgent($request->header('User-Agent'));
        $user = User::where('email', $request->email)->first();

        if ($user && !$user->has_password) {
            return response()->json([
                'message' => 'This account was created with Google. Use "Sign in with Google", or set a password with "Forgot password".',
            ], 422);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            LoginHistory::create([
                'user_id'        => $user ? $user->id : null,
                'email'          => $request->email,
                'role'           => 'student',
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->header('User-Agent'),
                'device_type'    => $agentInfo['deviceType'],
                'browser'        => $agentInfo['browser'],
                'platform'       => $agentInfo['platform'],
                'status'         => 'failed',
                'failure_reason' => 'Invalid email or password',
            ]);
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        if ($user->role !== 'student') {
            LoginHistory::create([
                'user_id'        => $user->id,
                'email'          => $request->email,
                'role'           => 'student',
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->header('User-Agent'),
                'device_type'    => $agentInfo['deviceType'],
                'browser'        => $agentInfo['browser'],
                'platform'       => $agentInfo['platform'],
                'status'         => 'failed',
                'failure_reason' => "Role mismatch. Attempted: student",
            ]);
            return response()->json([
                'message' => "Access Denied! Aapka account '{$user->role}' hai. Aap 'student' portal par login nahi kar sakte."
            ], 403);
        }

        if ($user->is_suspended) {
            return response()->json([
                'message' => 'Account suspended. Contact support.',
            ], 403);
        }

        $existing = StudentOtp::where('email', $user->email)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
        if ($existing) {
            $secondsSinceLastSend = (int) $existing->created_at->diffInSeconds(now());
            if ($secondsSinceLastSend < self::OTP_RESEND_COOLDOWN_SECONDS) {
                return response()->json([
                    'message'     => 'A verification code was already sent to your email. Please enter it or wait before requesting another.',
                    'retry_after' => self::OTP_RESEND_COOLDOWN_SECONDS - $secondsSinceLastSend,
                    'otp_active'  => true,
                ], 429);
            }
        }

        $code = str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);

        StudentOtp::create([
            'email'      => $user->email,
            'code'       => $code,
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
            'is_used'    => false,
        ]);

        Mail::to($user->email)->send(new StudentOtpMail($code));

        return response()->json([
            'message'     => 'Verification code sent.',
            'retry_after' => self::OTP_RESEND_COOLDOWN_SECONDS,
            'expires_in'  => self::OTP_TTL_MINUTES * 60,
        ]);
    }

    public function verifyStudentLoginOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code'  => ['required', 'string'],
        ]);

        $otp = StudentOtp::where('email', $request->input('email'))
            ->where('code', $request->input('code'))
            ->where('is_used', false)
            ->where('expires_at', '>=', now())
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json(['message' => 'Invalid or expired verification code.'], 422);
        }

        $otp->update([
            'is_used' => true,
            'used_at' => now(),
        ]);

        $user = User::where('email', $request->input('email'))->where('role', 'student')->first();
        if (!$user) {
            return response()->json(['message' => 'Invalid or expired verification code.'], 422);
        }

        $agentInfo = $this->parseUserAgent($request->header('User-Agent'));

        return $this->completeLogin($user, $request, $agentInfo);
    }

    public function requestAdminLoginOtp(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
            'admin_id' => ['required', 'string'],
        ]);

        $agentInfo = $this->parseUserAgent($request->header('User-Agent'));
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password) || $user->role !== 'admin') {
            LoginHistory::create([
                'user_id'        => $user ? $user->id : null,
                'email'          => $request->email,
                'role'           => 'admin',
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->header('User-Agent'),
                'device_type'    => $agentInfo['deviceType'],
                'browser'        => $agentInfo['browser'],
                'platform'       => $agentInfo['platform'],
                'status'         => 'failed',
                'failure_reason' => 'Invalid email or password',
            ]);
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        $submittedId = trim($request->input('admin_id', ''));
        $idValid = $submittedId !== ''
            && $submittedId === $user->admin_id
            && AdminId::where('admin_id', $submittedId)->exists();

        if (!$idValid) {
            LoginHistory::create([
                'user_id'        => $user->id,
                'email'          => $user->email,
                'role'           => 'admin',
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->header('User-Agent'),
                'device_type'    => $agentInfo['deviceType'],
                'browser'        => $agentInfo['browser'],
                'platform'       => $agentInfo['platform'],
                'status'         => 'failed',
                'failure_reason' => 'Invalid or missing Admin ID',
            ]);
            return response()->json(['message' => 'Invalid Admin ID for this account.'], 403);
        }

        $existing = AdminOtp::where('email', $user->email)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
        if ($existing) {
            $secondsSinceLastSend = (int) $existing->created_at->diffInSeconds(now());
            if ($secondsSinceLastSend < self::OTP_RESEND_COOLDOWN_SECONDS) {
                return response()->json([
                    'message'     => 'A verification code was already sent to your email. Please enter it or wait before requesting another.',
                    'retry_after' => self::OTP_RESEND_COOLDOWN_SECONDS - $secondsSinceLastSend,
                    'otp_active'  => true,
                ], 429);
            }
        }

        $code = str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);

        AdminOtp::create([
            'email'      => $user->email,
            'code'       => $code,
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
            'is_used'    => false,
        ]);

        Mail::to($user->email)->send(new AdminOtpMail($code));

        return response()->json([
            'message'     => 'Verification code sent.',
            'retry_after' => self::OTP_RESEND_COOLDOWN_SECONDS,
            'expires_in'  => self::OTP_TTL_MINUTES * 60,
        ]);
    }

    public function verifyAdminLoginOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'code'  => ['required', 'string'],
        ]);

        $otp = AdminOtp::where('email', $request->input('email'))
            ->where('code', $request->input('code'))
            ->where('is_used', false)
            ->where('expires_at', '>=', now())
            ->latest()
            ->first();

        if (!$otp) {
            return response()->json(['message' => 'Invalid or expired verification code.'], 422);
        }

        $otp->update([
            'is_used' => true,
            'used_at' => now(),
        ]);

        $user = User::where('email', $request->input('email'))->where('role', 'admin')->first();
        if (!$user) {
            return response()->json(['message' => 'Invalid or expired verification code.'], 422);
        }

        $agentInfo = $this->parseUserAgent($request->header('User-Agent'));

        return $this->completeLogin($user, $request, $agentInfo);
    }

    private function resetTokenIsValid(string $email, string $token): ?PasswordResetToken
    {
        $records = PasswordResetToken::where('email', $email)
            ->where('is_used', false)
            ->latest()
            ->get();

        foreach ($records as $record) {
            if ($record->created_at && $record->created_at->diffInMinutes(now()) <= self::RESET_TOKEN_TTL_MINUTES) {
                if (Hash::check($token, $record->token)) {
                    return $record;
                }
            }
        }

        return null;
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $existing = PasswordResetToken::where('email', $user->email)
                ->where('is_used', false)
                ->latest()
                ->first();

            if ($existing && $existing->created_at && $existing->created_at->diffInSeconds(now()) < 60) {
                return response()->json([
                    'message' => 'Please wait a moment before requesting another reset link.',
                ], 429);
            }

            $rawToken = Str::random(64);

            PasswordResetToken::create([
                'email'      => $user->email,
                'token'      => Hash::make($rawToken),
                'is_used'    => false,
                'created_at' => now(),
            ]);

            $resetUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/')
                . '/?reset_email=' . urlencode($user->email)
                . '&reset_token=' . urlencode($rawToken);

            Mail::to($user->email)->send(new PasswordResetMail($resetUrl));
        }

        return response()->json([
            'message' => 'If that email is registered, a password reset link has been sent.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'        => ['required', 'email'],
            'token'        => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        $tokenRecord = $this->resetTokenIsValid($request->email, $request->token);

        if (!$tokenRecord) {
            return response()->json(['message' => 'This reset link is invalid or has expired.'], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'This reset link is invalid or has expired.'], 422);
        }

        $user->update(['password' => Hash::make($request->new_password), 'has_password' => true]);
        $user->tokens()->delete();

        $tokenRecord->update([
            'is_used' => true,
            'used_at' => now(),
        ]);

        return response()->json(['message' => 'Password reset successfully.']);
    }

    public function logout(Request $request)
    {
        $historyId = $request->input('login_history_id');

        if (!$historyId) {
            return response()->json(['message' => 'Login History ID enter karna zaroori hai!'], 422);
        }

        $history = LoginHistory::find($historyId);

        if ($history && is_null($history->logout_at)) {
            $history->update([
                'logout_at' => now()
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Logged out successfully!'
        ], 200);
    }
}