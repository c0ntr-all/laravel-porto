<?php

/**
 * @apiGroup           User
 *
 * @apiName            GetUserProfile
 *
 * @api                {GET} /v1/profile Get Profile
 *
 * @apiDescription     Get the authenticated user profile data.
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated ['permissions' => null, 'roles' => null]
 *
 * @apiUse             UserSuccessSingleResponse
 */

use App\Containers\User\Actions\GetUserProfileAction;
use Illuminate\Support\Facades\Route;

Route::get('profile', GetUserProfileAction::class)
    ->middleware(['auth:api']);
