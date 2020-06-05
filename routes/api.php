<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::pattern('swapiId', '[0-9]+');
Route::pattern('categoryName', '[A-Za-z]+');

Route::get('/{categoryName}/{swapiId}/impressions', 'ImpressionController@index');
Route::post('/{categoryName}/{swapiId}/impressions', 'ImpressionController@store');
