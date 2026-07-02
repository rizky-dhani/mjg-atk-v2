<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard/login');
Route::get('docs', function () {
    return view('public.tutorial');
});
