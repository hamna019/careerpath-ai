<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoginHistoryController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminStudentController;
use App\Http\Controllers\QuizQuestionController;
use App\Http\Controllers\CareerDetailsController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\UserProfilesController;
use App\Http\Controllers\RecentStudentActivityController;
use App\Http\Controllers\StudentResultController;
use App\Http\Controllers\StudentStatsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\CareerRoadmapController;
use App\Http\Controllers\CareerMatchController;

Route::get('/test-connection', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Laravel Backend Connected Successfully!'
    ]);
});

Route::get('/countries', [AuthController::class, 'getCountries']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'googleLogin'])->middleware('throttle:20,1');
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/session/ping', [SessionController::class, 'ping']);

Route::post('/admin/otp/request', [AuthController::class, 'requestAdminLoginOtp']);
Route::post('/admin/otp/verify', [AuthController::class, 'verifyAdminLoginOtp']);

Route::post('/student/otp/request', [AuthController::class, 'requestStudentLoginOtp']);
Route::post('/student/otp/verify', [AuthController::class, 'verifyStudentLoginOtp']);

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/admin/login-history', [LoginHistoryController::class, 'index']);
Route::get('/admin/student-login-activity', [LoginHistoryController::class, 'studentActivity']);
Route::get('/admin/dashboard-stats', [AdminDashboardController::class, 'getStats']);
Route::get('/admin/students', [AdminStudentController::class, 'index']);
Route::post('/admin/students/{student}/toggle-suspension', [AdminStudentController::class, 'toggleSuspension']);
Route::post('/admin/students/{student}/send-password-reset', [AdminStudentController::class, 'sendPasswordReset']);
Route::get('/admin/student-results', [StudentResultController::class, 'index']);
Route::get('/admin/student-results/{user}/history', [StudentResultController::class, 'history']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (\Illuminate\Http\Request $request) {
        return response()->json($request->user());
    });

    Route::get('/admin/notifications', [RecentStudentActivityController::class, 'notifications']);
    Route::post('/admin/student-results/{result}/approve-certificate', [StudentResultController::class, 'approveCertificate']);
    Route::post('/notifications/mark-read', [NotificationController::class, 'markRead']);

    Route::get('/admin/profile', [AdminProfileController::class, 'show']);
    Route::post('/admin/profile/update', [AdminProfileController::class, 'update']);

    Route::get('/profile', [UserProfilesController::class, 'show']);
    Route::get('/profile/completion', [UserProfilesController::class, 'completion']);
    Route::put('/profile', [UserProfilesController::class, 'update']);
    Route::post('/profile/email/verify', [UserProfilesController::class, 'verifyEmailChange']);
    Route::post('/profile/email/resend', [UserProfilesController::class, 'resendEmailChange']);
    Route::post('/profile/avatar', [UserProfilesController::class, 'uploadAvatar']);
    Route::delete('/profile/avatar', [UserProfilesController::class, 'deleteAvatar']);
    Route::put('/profile/password', [UserProfilesController::class, 'updatePassword']);

    Route::post('/quiz/start', [RecentStudentActivityController::class, 'start']);
    Route::post('/quiz/ping', [RecentStudentActivityController::class, 'ping']);
    Route::post('/quiz/complete', [RecentStudentActivityController::class, 'complete']);
    Route::post('/quiz/submit', [CareerMatchController::class, 'submit']);
    Route::post('/quiz/feedback', [CareerMatchController::class, 'feedback']);
    Route::get('/quiz/latest-result', [CareerMatchController::class, 'latest']);
    Route::get('/quiz/results/{id}', [CareerMatchController::class, 'show'])->whereNumber('id');

    Route::get('/student/activity', [RecentStudentActivityController::class, 'mine']);

    Route::get('/student/dashboard-stats', [StudentStatsController::class, 'index']);
    Route::post('/student/activity-heartbeat', [StudentStatsController::class, 'heartbeat']);

    Route::get('/student/notifications', [StudentStatsController::class, 'notifications']);
    Route::post('/careers/{career}/view', [StudentStatsController::class, 'trackCareerView']);

    Route::get('/career-roadmap/{career}', [CareerRoadmapController::class, 'show']);
    Route::post('/career-roadmap/{career}/toggle', [CareerRoadmapController::class, 'toggle']);
    Route::get('/student/roadmap-progress', [CareerRoadmapController::class, 'mine']);
    Route::get('/student/assessment-journey', [CareerRoadmapController::class, 'journey']);

    Route::get('/student/assessment-history', [StudentResultController::class, 'mine']);
});

Route::get('/quiz/assessment', [QuizQuestionController::class, 'assessment']);

Route::post('/quiz/abandon', [RecentStudentActivityController::class, 'abandon']);

Route::prefix('admin/questions')->group(function () {
    Route::get('/', [QuizQuestionController::class, 'index']);
    Route::post('/', [QuizQuestionController::class, 'store']);
    Route::get('/{id}', [QuizQuestionController::class, 'show']);
    Route::put('/{id}', [QuizQuestionController::class, 'update']);
    Route::delete('/{id}', [QuizQuestionController::class, 'destroy']);
});

Route::apiResource('career-details', CareerDetailsController::class);