<?php

use Svr\Core\Middleware\ApiValidationErrors;
use Illuminate\Support\Facades\Route;
use Svr\Data\Controllers\Api\ApiAnimalsController;
use Svr\Data\Controllers\Api\ApiApplicationsController;

/*
|--------------------------------------------------------------------------
| Laravel Roles API CORE Routes
|--------------------------------------------------------------------------
|
*/

Route::prefix(config('svr.api_prefix'))->group(function(){
    Route::get('applications/data', [ApiApplicationsController::class, 'applicationsData'])->middleware(['auth:svr_api', 'api']);
//    Route::post('applications/list', [ApiApplicationsController::class, 'applicationsList'])->middleware(['auth:svr_api', 'api']);
    Route::post('applications/animal_add', [ApiApplicationsController::class, 'applicationsAnimalAdd'])->middleware(['auth:svr_api', 'api']);
//    Route::post('applications/animal_delete', [ApiApplicationsController::class, 'applicationsAnimalDelete'])->middleware(['auth:svr_api', 'api']);
    Route::post('applications/status', [ApiApplicationsController::class, 'applicationsStatus'])->middleware(['auth:svr_api', 'api']);

    Route::get('animals/data', [ApiAnimalsController::class, 'animalsData'])->middleware(['auth:svr_api', 'api']);
    Route::get('animals/list', [ApiAnimalsController::class, 'animalsList'])->middleware(['auth:svr_api', 'api']);
    Route::post('animals/mark_edit', [ApiAnimalsController::class, 'animalsMarkEdit'])->middleware(['auth:svr_api', 'api']);
    Route::post('animals/mark_edit_group', [ApiAnimalsController::class, 'animalsMarkEditGroup'])->middleware(['auth:svr_api', 'api']);
//    Route::post('animals/mark_photo_edit', [ApiAnimalsController::class, 'animalsMarkPhotoEdit'])->middleware(['auth:svr_api', 'api']);
//    Route::post('animals/mark_photo_delete', [ApiAnimalsController::class, 'animalsMarkPhotoDelete'])->middleware(['auth:svr_api', 'api']);
//    Route::post('animals/animal_keeping_object_edit', [ApiAnimalsController::class, 'animalsKeepingObjectEdit'])->middleware(['auth:svr_api', 'api']);
//    Route::post('animals/animal_keeping_object_edit_group', [ApiAnimalsController::class, 'animalsKeepingObjectEditGroup'])->middleware(['auth:svr_api', 'api']);
//    Route::post('animals/animal_birth_object_edit', [ApiAnimalsController::class, 'animalsBirthObjectEdit'])->middleware(['auth:svr_api', 'api']);
});
