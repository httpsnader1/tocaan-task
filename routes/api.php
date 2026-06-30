<?php

use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {

    Route::post('login', App\Actions\Api\Auth\LoginAction::class);
    Route::post('register', App\Actions\Api\Auth\RegisterAction::class);

    Route::middleware('auth:api')->group(function () {

        Route::get('profile', App\Actions\Api\Auth\ProfileAction::class);
        Route::get('logout', App\Actions\Api\Auth\LogoutAction::class);

    });

});

Route::middleware('auth:api')->group(function () {

    Route::prefix('orders')->group(function () {

        Route::get('', App\Actions\Api\Orders\IndexAction::class);
        Route::post('', App\Actions\Api\Orders\CreateAction::class);

        Route::prefix('{order}')->group(function () {

            Route::get('', App\Actions\Api\Orders\ShowAction::class);
            Route::patch('', App\Actions\Api\Orders\UpdateAction::class);
            Route::delete('', App\Actions\Api\Orders\DeleteAction::class);
            Route::post('pay', App\Actions\Api\Orders\PayAction::class);
            Route::get('confirm', App\Actions\Api\Orders\ConfirmAction::class);

        });

    });

});
