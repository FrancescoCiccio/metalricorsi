<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Video;
use App\Models\Course;
use App\Models\Download;
use Illuminate\Database\Eloquent\Model;
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
        //
        Model::unguard();

        Model::preventLazyLoading(!app()->isProduction());

        User::observe(\App\Observers\UserObserver::class);

        Course::observe(\App\Observers\CourseObserver::class);

        Download::observe(\App\Observers\DownloadObserver::class);

        Video::observe(\App\Observers\VideoObserver::class);
    }
}
