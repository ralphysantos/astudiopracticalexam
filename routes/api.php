<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\ProjectController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\UserController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('/register',[AuthController::class,'register']);    
Route::post('/login',[AuthController::class,'login']);

Route::middleware('auth:api')->group(function(){
    Route::post('/logout',[AuthController::class,'logout']);

    Route::prefix('project')->group(function(){
        Route::get('/',[ProjectController::class,'get']);
        Route::get('/{id}',[ProjectController::class,'getById']);
        Route::post('/',[ProjectController::class,'create']);
        Route::put('/{id}',[ProjectController::class,'update']);
        Route::delete('/{id}',[ProjectController::class,'delete']);
    });
    
    Route::prefix('attribute')->group(function(){
        Route::get('/',[AttributeController::class,'get']);
        Route::get('/{id}',[AttributeController::class,'getById']);
        Route::post('/',[AttributeController::class,'create']);
        Route::put('/{id}',[AttributeController::class,'update']);
        Route::delete('/{id}',[AttributeController::class,'delete']);
    });

    Route::prefix('timesheet')->group(function(){
        Route::get('/',[TimesheetController::class,'get']);
        Route::get('/{id}',[TimesheetController::class,'getById']);
        Route::post('/',[TimesheetController::class,'create']);
        Route::put('/{id}',[TimesheetController::class,'update']);
        Route::delete('/{id}',[TimesheetController::class,'delete']);
    });

    Route::prefix('user')->group(function(){
        Route::get('/',[UserController::class,'get']);
        Route::get('/{id}',[UserController::class,'getById']);
        Route::post('/',[UserController::class,'create']);
        Route::put('/{id}',[UserController::class,'update']);
        Route::delete('/{id}',[UserController::class,'delete']);
    });

});





