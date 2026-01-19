<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\SlotFreedUp;
use App\Listeners\NotifyWaitlist;

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
        // Register waitlist event listener
        Event::listen(
            SlotFreedUp::class,
            NotifyWaitlist::class,
        );
    }
}
