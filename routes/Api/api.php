<?php

use Svr\Core\Middleware\ApiValidationErrors;
use Illuminate\Support\Facades\Route;
use Svr\Data\Controllers\Api\ApiApplicationsController;

/*
|--------------------------------------------------------------------------
| Laravel Roles API CORE Routes
|--------------------------------------------------------------------------
|
*/

Route::prefix(config('svr.api_prefix'))->group(function(){
    Route::get('applications/data', [ApiApplicationsController::class, 'applicationsData'])->middleware(['auth:svr_api', 'api']);
    Route::post('applications/list', [ApiApplicationsController::class, 'applicationsList'])->middleware(['auth:svr_api', 'api']);
    Route::post('applications/animal_add', [ApiApplicationsController::class, 'applicationsAnimalAdd'])->middleware(['auth:svr_api', 'api']);
    Route::post('applications/animal_delete', [ApiApplicationsController::class, 'applicationsAnimalDelete'])->middleware(['auth:svr_api', 'api']);
    Route::post('applications/status', [ApiApplicationsController::class, 'applicationsStatus'])->middleware(['auth:svr_api', 'api']);
});
