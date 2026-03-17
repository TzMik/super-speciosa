<?php

use App\Http\Controllers\LeadNoteController;
use Illuminate\Support\Facades\Route;

Route::post('/leads/{lead}/notes', [LeadNoteController::class, 'store']);
