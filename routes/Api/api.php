<?php

use Svr\Core\Middleware\ApiValidationErrors;
use Illuminate\Support\Facades\Route;
use Svr\Data\Controllers\Api\ApiAnimalsController;
use Svr\Data\Controllers\Api\ApiApplicationsController;
use Svr\Data\Controllers\Api\ApiCompaniesController;

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

	Route::get('companies/company_objects_list/{company_id}', [ApiCompaniesController::class, 'companyObjectList'])->middleware(['auth:svr_api', 'api']);

    Route::post('animals/data', [ApiAnimalsController::class, 'animalsData'])->middleware(['auth:svr_api', 'api']);
    Route::post('animals/list', [ApiAnimalsController::class, 'animalsList'])->middleware(['auth:svr_api', 'api']);
    Route::post('animals/mark_edit', [ApiAnimalsController::class, 'animalsMarkEdit'])->middleware(['auth:svr_api', 'api']);
    Route::post('animals/mark_edit_group', [ApiAnimalsController::class, 'animalsMarkEditGroup'])->middleware(['auth:svr_api', 'api']);
    Route::post('animals/mark_photo_edit', [ApiAnimalsController::class, 'animalsMarkPhotoEdit'])->middleware(['auth:svr_api', 'api']);
    Route::post('animals/mark_photo_delete/{mark_id}', [ApiAnimalsController::class, 'animalsMarkPhotoDelete'])->middleware(['auth:svr_api', 'api']);
    Route::post('animals/animal_keeping_object_edit', [ApiAnimalsController::class, 'animalsKeepingObjectEdit'])->middleware(['auth:svr_api', 'api']);
    Route::post('animals/animal_birth_object_edit', [ApiAnimalsController::class, 'animalsBirthObjectEdit'])->middleware(['auth:svr_api', 'api']);
    Route::post('animals/animal_object_edit_group', [ApiAnimalsController::class, 'animalsObjectEditGroup'])->middleware(['auth:svr_api', 'api']);
    Route::post('animals/animal_keeping_type_edit', [ApiAnimalsController::class, 'animalKeepingTypeEdit'])->middleware(['auth:svr_api', 'api']);
    Route::post('animals/animal_keeping_purpose_edit', [ApiAnimalsController::class, 'animalKeepingPurposeEdit'])->middleware(['auth:svr_api', 'api']);
});
