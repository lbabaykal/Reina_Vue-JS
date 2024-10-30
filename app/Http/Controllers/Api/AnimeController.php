<?php

namespace App\Http\Controllers\Api;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Filters\Fields\CountryFilter;
use App\Http\Filters\Fields\GenreFilter;
use App\Http\Filters\Fields\SortingFilter;
use App\Http\Filters\Fields\StudioFilter;
use App\Http\Filters\Fields\TitleFilter;
use App\Http\Filters\Fields\TypeFilter;
use App\Http\Filters\Fields\YearFromFilter;
use App\Http\Filters\Fields\YearToFilter;
use App\Http\Requests\FavoriteAnimesRequest;
use App\Http\Requests\RatingRequest;
use App\Http\Resources\AnimesIndexResource;
use App\Http\Resources\MainAnimesResource;
use App\Http\Resources\MainDoramasResource;
use App\Models\Anime;
use App\Models\Country;
use App\Models\FavoriteAnime;
use App\Models\FolderAnime;
use App\Models\Genre;
use App\Models\Studio;
use App\Models\Type;
use App\Reina;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\View\View;
use function Amp\Dns\query;

class AnimeController extends Controller
{

    public function index()
    {
        request()->merge(['sorting' => request()->input('sorting', 1)]);

        $query = Anime::query()->select(['id', 'slug', 'poster', 'title_ru', 'rating', 'episodes_released', 'episodes_total']);

        $animes = Pipeline::send($query)
            ->through([
                TitleFilter::class,
                TypeFilter::class,
                GenreFilter::class,
                CountryFilter::class,
                StudioFilter::class,
                YearFromFilter::class,
                YearToFilter::class,
                SortingFilter::class,
            ])
            ->thenReturn();

        return AnimesIndexResource::collection($animes->paginate(Reina::COUNT_ARTICLES_FULL, ['*'], 'page', request()->input('page', 1)));

//        return view('layouts.anime.index')
//            ->with('types', Type::all())
//            ->with('genres', Genre::all())
//            ->with('studios', Studio::all())
//            ->with('countries', Country::all())
//            ->with('animes', $animes->paginate(Reina::COUNT_ARTICLES_FULL)->withQueryString());
    }

    public function show($slug): View
    {
        $anime = Cache::store('redis_animes')->remember('anime:'.$slug, 600, function () use ($slug) {
            return Anime::query()
                ->where('slug', $slug)
                ->with('type')
                ->with('country')
                ->with('studios')
                ->with('genres')
                ->firstOrFail();
        });

        $ratingUser = $anime->ratings()
            ->where('user_id', auth()->id())
            ->value('assessment');

        $favoriteUser = $anime->favorites()
            ->where('user_id', auth()->id())
            ->value('folder_anime_id');

        $foldersUser = FolderAnime::query()
            ->where('user_id', auth()->id())
            ->orWhere('user_id', 0)
            ->orderBy('id')
            ->get();

        return view('layouts.anime.show')
            ->with('anime', $anime)
            ->with('favoriteUser', $favoriteUser)
            ->with('ratingUser', $ratingUser)
            ->with('foldersUser', $foldersUser);
    }

    public function watch($slug): View
    {
        $anime = Cache::store('redis_animes')->remember('anime_watch:'.$slug, 600, function () use ($slug) {
            return Anime::query()
                ->where('slug', $slug)
                ->with('type')
                ->with('genres')
                ->firstOrFail();
        });

        $ratingUser = $anime->ratings()
            ->where('user_id', auth()->id())
            ->value('assessment');

        $favoriteUser = $anime->favorites()
            ->where('user_id', auth()->id())
            ->value('folder_anime_id');

        $foldersUser = FolderAnime::query()
            ->where('user_id', auth()->id())
            ->orWhere('user_id', 0)
            ->orderBy('id')
            ->get();

        $episodes = $anime->animeEpisodes()
            ->where('status', StatusEnum::PUBLISHED)
            ->orderBy('number')
            ->get();

        return view('layouts.anime.watch')
            ->with('anime', $anime)
            ->with('favoriteUser', $favoriteUser)
            ->with('ratingUser', $ratingUser)
            ->with('foldersUser', $foldersUser)
            ->with('episodes', $episodes);
    }

}
