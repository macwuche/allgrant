<?php

namespace App\Providers;

use App\Models\LandingPage;
use App\Models\Navigation;
use App\Models\Page;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Jenssegers\Agent\Agent;
use Remotelywork\Installer\Repository\App;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register modules.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap modules.
     *
     * @return void
     */
    public function boot()
    {

        if (App::dbConnectionCheck()) {
            View::composer(['backend.include.__side_nav', 'backend.setting.site_setting.include.__global'], function ($view) {
                $view->with([
                    'landingSections' => cache()->remember('landingSections', 60 * 60 * 24, function () {
                        return LandingPage::where('locale', 'en')->whereNot('code', 'footer')->orderBy('short')->get(['name', 'code']);
                    }),
                    'pages' => cache()->remember('pages', 60 * 60 * 24, function () {
                        return Page::where('locale', 'en')->get(['title', 'code', 'status', 'url']);
                    }),
                ]);
            });


            View::composer(['frontend::include.__header'], function ($view) {
                $view->with([
                    'navigations' => cache()->remember('nav_header', 300, fn() =>
                        Navigation::where('status', 1)->where(function ($q) {
                            $q->where('type', 'header')->orWhere('type', 'both');
                        })->orderBy('header_position')->get()
                    ),
                ]);
            });

            View::composer(['frontend::include.__footer'], function ($view) {
                $view->with([
                    'navigations' => cache()->remember('nav_footer', 300, fn() =>
                        Navigation::where('status', 1)->where(function ($q) {
                            $q->where('type', 'footer')->orWhere('type', 'both');
                        })->orderBy('footer_position')->get()->chunk(5)
                    ),
                ]);
            });

            // Share socials once across all views — avoids 4 separate DB queries
            \Illuminate\Support\Facades\View::share(
                'socials',
                cache()->remember('socials_all', 300, fn() => \App\Models\Social::all())
            );

            View::composer(['*'], function ($view) {
                $view->with([
                    'currencySymbol' => setting('currency_symbol', 'global'),
                    'currency' => setting('site_currency', 'global'),
                ]);
            });
            if (auth('web')) {
                $agent = new Agent;
                View::composer(['frontend*'], function ($view) use ($agent) {
                    $view->with([
                        'user' => auth()->user(),
                        'isMobile' => $agent->isMobile(),
                    ]);
                });
            }
        }
    }
}
