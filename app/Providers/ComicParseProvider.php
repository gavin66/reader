<?php

namespace App\Providers;

use App\Services\ComicCatalogParse;
use App\Services\ComicChapterParse;
use App\Services\ComicSearchParse;
use Illuminate\Support\ServiceProvider;

class ComicParseProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton( ComicSearchParse::class, function ( $app ) {
            return new ComicSearchParse();
        } );

        $this->app->singleton( ComicCatalogParse::class, function ( $app ) {
            return new ComicCatalogParse();
        } );

        $this->app->singleton( ComicChapterParse::class, function ( $app ) {
            return new ComicChapterParse();
        } );
    }
}
