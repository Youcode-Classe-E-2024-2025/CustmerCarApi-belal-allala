<?php

use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\ResponseController; // Importez ResponseController
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| ... (routes existantes) ...
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('tickets', TicketController::class);

Route::get('/tickets/{ticket}/responses', [ResponseController::class, 'index'])->name('responses.index'); 
Route::post('/tickets/{ticket}/responses', [ResponseController::class, 'store'])->name('responses.store'); 
Route::get('/responses/{response}', [ResponseController::class, 'show'])->name('responses.show');      
Route::put('/responses/{response}', [ResponseController::class, 'update'])->name('responses.update');    
Route::delete('/responses/{response}', [ResponseController::class, 'destroy'])->name('responses.destroy');