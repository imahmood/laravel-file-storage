<?php
declare(strict_types=1);

namespace Imahmood\FileStorage;

use DateTimeInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Imahmood\FileStorage\Contracts\NameGeneratorInterface;

class FileStorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/file-storage.php', 'file-storage');

        $this->registerManipulator();
        $this->registerNameGenerator();
        $this->registerTemporaryUrlCallbacks();
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
    }

    protected function registerManipulator(): void
    {
        foreach (config('file-storage.modifiers') as $class => $options) {
            $this->app->when($class)->needs('$options')->give($options);
        }

        $this->app->singleton(Manipulator::class, function (Application $app) {
            $manipulator = new Manipulator;

            foreach (config('file-storage.modifiers') as $class => $options) {
                $manipulator->addModifier($app->make($class));
            }

            return $manipulator;
        });
    }

    protected function registerNameGenerator(): void
    {
        $this->app->singleton(NameGeneratorInterface::class, config('file-storage.name_generator'));
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
