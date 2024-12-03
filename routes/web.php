<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('success', function () {
    return "success";
});

Route::get("failure", function () {
    return "failure";
});

Route::get("pending", function () {
    return "pending";
});
