<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Remotelywork\Installer\Repository\App as MainApp;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (! MainApp::dbConnectionCheck()) {
            return $next($request);
        }

        if (session()->has('locale')) {
            App::setLocale(session()->get('locale'));
        } else {
            App::setLocale(defaultLocale());
        }

        return $next($request);
    }
}
