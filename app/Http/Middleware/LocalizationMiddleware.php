<?php

namespace App\Http\Middleware;
use App\Utils\KurdishLanguageHelper;
use Closure;
use Illuminate\Support\Facades\App;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check header request and determine localizaton
        $local = ($request->hasHeader('X-localization')) ? (strlen($request->header('X-localization'))>0?$request->header('X-localization'): 'en'): 'en';

        // Use centralized Kurdish language normalization
        $local = KurdishLanguageHelper::normalizeKurdishToBackend($local);

        // Also normalize the incoming header so downstream code gets normalized value
        $request->headers->set('X-localization', $local);

        // set laravel localization
        App::setLocale($local);
        // continue request
        return $next($request);
    }
}
