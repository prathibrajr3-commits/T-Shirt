<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Bind site setting and footer links globally to all views
        View::composer('*', function ($view) {
            $siteSetting = app()->has('siteSettingGlobal') ? app('siteSettingGlobal') : null;
            if ($siteSetting === null) {
                try {
                    $siteSetting = \App\Models\SiteSetting::first() ?? new \App\Models\SiteSetting();
                } catch (\Exception $e) {
                    $siteSetting = new \App\Models\SiteSetting();
                }
                app()->instance('siteSettingGlobal', $siteSetting);
            }

            $footerLinks = app()->has('footerLinksGlobal') ? app('footerLinksGlobal') : null;
            if ($footerLinks === null) {
                try {
                    $footerLinks = \App\Models\FooterLink::orderBy('sort_order')->get();
                } catch (\Exception $e) {
                    $footerLinks = collect();
                }
                app()->instance('footerLinksGlobal', $footerLinks);
            }

            $view->with([
                'siteSetting' => $siteSetting,
                'favicon_url' => $siteSetting->favicon ? asset('storage/' . $siteSetting->favicon) : asset('favicon.ico'),
                'footerLinks' => $footerLinks,
                // Backwards compatibility bindings
                'storeSettings' => $siteSetting,
                'storeSetting' => $siteSetting,
                'footerSettings' => $siteSetting,
            ]);
        });
    }
}
