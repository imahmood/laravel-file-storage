<?php
declare(strict_types=1);

namespace Imahmood\FileStorage;

use DateTimeInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Imahmood\FileStorage\Config\Configuration;
use Imahmood\FileStorage\Config\ConfigurationFactory;

class FileStorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(Configuration::class, function () {
            return ConfigurationFactory::create(config('file-storage'));
        });

        $this->mergeConfigFrom(__DIR__.'/../config/file-storage.php', 'file-storage');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'file-storage');

        $this->publishes([
            __DIR__.'/../config/file-storage.php' => config_path('file-storage.php'),
        ]);

        $this->registerTemporaryUrlCallbacks();
    }

    protected function registerTemporaryUrlCallbacks(): void
    {
        foreach (config('filesystems.disks') as $disk => $options) {
            if ($options['driver'] !== 'local') {
                continue;
            }

            Storage::disk($disk)->buildTemporaryUrlsUsing(
                fn (string $path, DateTimeInterface $expiration) => URL::signedRoute(
                    name: 'file-storage:private',
                    parameters: [$disk, $path],
                    expiration: $expiration,
                ),
            );
        }
    }
}
