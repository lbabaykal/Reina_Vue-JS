<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MainAnimesResource;
use App\Http\Resources\MainDoramasResource;
use App\Models\Anime;
use App\Models\Dorama;
use App\Reina;
use Illuminate\Http\Response;

class MainController extends Controller
{
    public function __invoke(): Response
    {
        $animes = cache()->store('redis_animes')->flexible('main_animes', [1200,1800], function () {
            return Anime::query()
                ->select(['id', 'slug', 'poster', 'title_ru', 'rating', 'episodes_released', 'episodes_total'])
                ->limit(Reina::COUNT_ARTICLES_MAIN)
                ->latest('updated_at')
                ->get();
        });

        $doramas = cache()->store('redis_doramas')->flexible('main_doramas', [1200,1800], function () {
            return Dorama::query()
                ->select(['id', 'slug', 'poster', 'title_ru', 'rating', 'episodes_released', 'episodes_total'])
                ->limit(Reina::COUNT_ARTICLES_MAIN)
                ->latest('updated_at')
                ->get();
        });

        return response([
            'animes' => MainAnimesResource::collection($animes),
            'doramas' => MainDoramasResource::collection($doramas),
        ]);
    }
}
