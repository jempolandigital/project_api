<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\ModulController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\UserProgressController;
use App\Http\Controllers\Api\AnswerController;
use App\Http\Controllers\Api\AnswerSessionController;
use App\Http\Controllers\Api\IssueTrackerController;

Route::post('/login', [AuthController::class, 'login']);



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/tenant', [TenantController::class, 'myTenant']);
});



Route::middleware('auth:sanctum')->get('/tenant/{tenantId}/moduls', [ModulController::class, 'getModulByTenant']);


Route::middleware('auth:sanctum')->get('/user-progress', [UserProgressController::class, 'index']);

use Illuminate\Http\Middleware\HandleCors;

Route::middleware([HandleCors::class])->group(function () {

    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/tenant', [TenantController::class, 'myTenant']);
        Route::get('/tenant/{tenantId}/moduls', [ModulController::class, 'getModulByTenant']);
        Route::get('/modul/{modulId}/questions', [QuestionController::class, 'getQuestionsByModul']);
       // Route::get('/user-progress', [UserProgressController::class, 'index']);
        Route::get('/progress', [UserProgressController::class, 'index']);
//        
    });

});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/modul/{modulId}/questions', [QuestionController::class, 'getQuestionsByModul']);
});




Route::middleware('auth:sanctum')->group(function () {
    Route::post('/answers', [AnswerController::class, 'store']);
    Route::post('/start-session', [AnswerSessionController::class, 'store']);
    Route::get('/sessions', [AnswerSessionController::class, 'index']);
    Route::get('/sessions/{id}', [AnswerSessionController::class, 'show']);
});



Route::middleware('auth:sanctum')->post('/session/start', [AnswerSessionController::class, 'start']);



Route::middleware('auth:sanctum')->group(function () {
    Route::post('/answer-session', [AnswerSessionController::class, 'store']);
    Route::get('/answer-session', [AnswerSessionController::class, 'index']);
    Route::get('/answer-session/{id}', [AnswerSessionController::class, 'show']);
});





Route::middleware('auth:sanctum')->group(function () {
    Route::get('/issues', [IssueTrackerController::class, 'index']);
    Route::get('/issues/{id}', [IssueTrackerController::class, 'show']);

    Route::post('/issues/{id}/start', [IssueTrackerController::class, 'startWork']);
    Route::post('/issues/{id}/close-request', [IssueTrackerController::class, 'closeRequest']);
    Route::post('/issues/{id}/proof', [IssueTrackerController::class, 'addProof']);
    Route::post('/issues/{id}/approve', [IssueTrackerController::class, 'approve']);
    Route::post('/issues/{id}/reject', [IssueTrackerController::class, 'reject']);
});

// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/fcm/token', [\App\Http\Controllers\Api\UserDeviceController::class, 'store']);
    Route::delete('/fcm/token', [\App\Http\Controllers\Api\UserDeviceController::class, 'destroy']);
});
