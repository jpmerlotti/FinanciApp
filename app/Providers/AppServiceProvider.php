<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Actions\EditAction as PageEditAction;

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
        PageEditAction::configureUsing(function (PageEditAction $action) {
            $action->color('warning');
        });

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            \App\Listeners\CheckDueTransactions::class,
        );
    }
}
