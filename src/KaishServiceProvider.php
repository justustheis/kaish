<?php

namespace JustusTheis\Kaish;

use Blade;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class KaishServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @param \Illuminate\Contracts\Http\Kernel $kernel
     *
     * @return void
     */
    public function boot(Kernel $kernel) :void
    {
        if ($this->app->isLocal()) {
            $kernel->pushMiddleware('JustusTheis\Kaish\FlushKaishViews');
        }

        Blade::directive('cache', function ($expression) {
            return "<?php if (! app('JustusTheis\Kaish\BladeDirective')->setUp({$expression})) : ?>";
        });

        Blade::directive('endcache', function () {
            return "<?php endif; echo app('JustusTheis\Kaish\BladeDirective')->tearDown() ?>";
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() :void
    {
        $this->app->singleton(BladeDirective::class);
    }
}
