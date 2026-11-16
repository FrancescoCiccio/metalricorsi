<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Video;
use App\Models\Course;
use App\Models\Download;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use App\Support\SafeUploads;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Validator;

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
        Carbon::setLocale(config('app.locale'));

        Model::unguard();

        Model::preventLazyLoading(!app()->isProduction());

        User::observe(\App\Observers\UserObserver::class);

        Course::observe(\App\Observers\CourseObserver::class);

        Download::observe(\App\Observers\DownloadObserver::class);

        Video::observe(\App\Observers\VideoObserver::class);

        Validator::extend('safe_filename', fn ($attribute, $value) => ! SafeUploads::isDangerous($value), 'Tipo di file non consentito.');

        // Safety net for every Filament upload, including fields added in the future.
        FileUpload::configureUsing(function (FileUpload $upload): void {
            $upload
                ->preserveFilenames(false)
                ->rule(SafeUploads::blockDangerousNames());
        });
    }
}
