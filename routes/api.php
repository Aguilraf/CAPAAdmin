<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CommunityController;
use App\Http\Controllers\Api\FirefighterController;
use App\Http\Controllers\Api\CaptureController;
use App\Http\Controllers\FirefighterSettingController;
use App\Http\Controllers\FirefighterReportPdfController;
use App\Http\Controllers\Api\CommunityImportController;
use App\Http\Controllers\Api\FirefighterImportController;
use App\Http\Controllers\Api\CaptureImportController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->group(function () {

    // Communities
    Route::apiResource('communities', CommunityController::class);
    Route::post('communities/import', [CommunityImportController::class, 'import']);
    Route::get('communities/import/template', [CommunityImportController::class, 'downloadTemplate']);

    // Firefighters
    Route::apiResource('firefighters', FirefighterController::class);
    Route::post('firefighters/import', [FirefighterImportController::class, 'import']);
    Route::get('firefighters/import/template', [FirefighterImportController::class, 'downloadTemplate']);

    // Captures
    Route::post('captures/assign-requirement', [CaptureController::class, 'assignRequirement']);
    Route::get('captures/requirements', [CaptureController::class, 'getRequirements']);
    Route::get('captures/next-requirement', [CaptureController::class, 'getNextRequirementNumber']);
    Route::apiResource('captures', CaptureController::class);

    // Import Captures
    Route::post('/import/captures', [CaptureImportController::class, 'import']);

    // Settings
    Route::get('/firefighter-settings', [FirefighterSettingController::class, 'index']);
    Route::post('/firefighter-settings', [FirefighterSettingController::class, 'update']);

});
