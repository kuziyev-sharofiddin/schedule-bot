<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/setwebhook', function () {
    $response = \Telegram\Bot\Laravel\Facades\Telegram::setWebhook([
        'url' => 'https://2fdc-91-196-77-120.ngrok-free.app/api/schedule-message-bot'
    ]);
    return $response;
});
