<?php

use App\Http\Controllers\Api\AnimeController;
use App\Http\Controllers\Api\DoramaController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\MainController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::domain(env('APP_URL'))->group(function () {
    Route::get('/user-data', function () {
        $authCheck = auth()->check();

        if ($authCheck) {
            return response()->json([
                'authenticated' => $authCheck,
                'user' => new \App\Http\Resources\UserLoginResource(auth()->user()),
            ]);
        } else {
            return response()->json([
                'authenticated' => $authCheck,
            ]);
        }
    });

    Route::get('/main', MainController::class)->name('main');
    Route::get('/search', [SearchController::class, 'index']);
    Route::get('/search-data', [SearchController::class, 'searchData']);


    Route::prefix('animes')->name('animes.')->group(function () {
        Route::get('/', [AnimeController::class, 'index']);

        Route::get('/{slug}', [AnimeController::class, 'show']);
        Route::get('/{slug}/watch', [AnimeController::class, 'watch']);

        // Rating
        Route::post('/{id}/rating', [RatingController::class, 'addForAnime']);
        Route::delete('/{id}/rating', [RatingController::class, 'removeForAnime']);

        // Favorite
        Route::post('/favorite', [FavoriteController::class, 'getForAnime']);
        Route::post('/{id}/favorite', [FavoriteController::class, 'addForAnime']);
        Route::delete('/{id}/favorite', [FavoriteController::class, 'removeForAnime']);
    });

    Route::prefix('doramas')->name('doramas.')->group(function () {
        Route::get('/', [DoramaController::class, 'index']);

        Route::get('/{slug}', [DoramaController::class, 'show']);
        Route::get('/{slug}/watch', [DoramaController::class, 'watch']);

        // Rating
        Route::post('/{id}/rating', [RatingController::class, 'addForDorama']);
        Route::delete('/{id}/rating', [RatingController::class, 'removeForDorama']);

        // Favorite
        Route::post('/favorite', [FavoriteController::class, 'getForDorama']);
        Route::post('/{id}/favorite', [FavoriteController::class, 'addForDorama']);
        Route::delete('/{id}/favorite', [FavoriteController::class, 'removeForDorama']);
    });
});

