<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer('layouts.app', function (\Illuminate\View\View $view): void {
            $user = auth()->user();
            if ($user === null || $user->isAuditor()) {
                $view->with('notificationBellEnabled', false);
                $view->with('unreadNotificationCount', 0);

                return;
            }

            $view->with('notificationBellEnabled', true);
            $view->with('unreadNotificationCount', $user->unreadNotifications()->count());
        });
    }
}
