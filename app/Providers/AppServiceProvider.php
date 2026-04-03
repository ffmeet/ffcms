<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Slimani\MediaManager\Models\File;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
        Livewire::component('media-browser', \App\Livewire\MediaBrowser::class);

        File::registerMediaConversionsUsing(function (File $file, ?Media $media = null): void {
            $file->addMediaConversion('thumb')
                ->fit(Fit::Crop, 320, 320)
                ->sharpen(10)
                ->nonQueued();

            $file->addMediaConversion('preview')
                ->width(960)
                ->height(960)
                ->nonQueued();
        });
    }
}
