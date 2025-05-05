<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;

Route::post('/admin/login', [AdminAuthController::class, 'login']);

