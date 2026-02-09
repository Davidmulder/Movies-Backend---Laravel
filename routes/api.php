<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MovieController;

Route::get('/titles', [MovieController::class, 'index']);
