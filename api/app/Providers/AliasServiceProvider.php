<?php

namespace App\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class AliasServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('AgentHelper', \App\Http\Helpers\AgentHelper::class);
        $loader->alias('Carbon', \Carbon\Carbon::class);
        $loader->alias('DB', \Illuminate\Support\Facades\DB::class);
        $loader->alias('Hash', \Illuminate\Support\Facades\Hash::class);
        $loader->alias('Model', \App\Models\Model::class);
        $loader->alias('Mail', \Illuminate\Support\Facades\Mail::class);
        $loader->alias('Debugbar', \Barryvdh\Debugbar\Facades\Debugbar::class);
        $loader->alias('FFMpeg', \ProtoneMedia\LaravelFFMpeg\Support\FFMpeg::class);
        $loader->alias('ResponseHelper', \App\Http\Helpers\ResponseHelper::class);
        $loader->alias('HttpResponse', \Symfony\Component\HttpFoundation\Response::class);  
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}