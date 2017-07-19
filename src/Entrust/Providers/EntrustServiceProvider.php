<?php

namespace Cloty\Entrust\Providers;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Cloty\Entrust
 */

use Cloty\Entrust\Entrust;
use Cloty\Entrust\EntrustRole;
use Cloty\Entrust\EntrustPermission;
use Cloty\Entrust\Repositories\Eloquent\EloquentPermissionRepository;
use Cloty\Entrust\Repositories\Eloquent\EloquentRoleRepository;
use Illuminate\Support\ServiceProvider;

class EntrustServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
            __DIR__ . '/../../config/cloty-entrust.php' => app()->basePath() . '/config/entrust.php',
        ]);

        // Register commands
        //$this->commands('command.entrust.migration');

        // Register blade directives
        $this->bladeDirectives();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerRepositoryInterfaces();

        $this->registerEntrust();

        $this->registerCommands();

        $this->mergeConfig();
    }

    /**
     * Register the blade directives
     *
     * @return void
     */
    private function bladeDirectives()
    {
        if (!class_exists('\Blade')) {
            return;
        }

        // Call to Entrust::hasRole
        \Blade::directive('role', function ($expression) {
            return "<?php if (\\Entrust::hasRole({$expression})) : ?>";
        });

        \Blade::directive('endrole', function ($expression) {
            return "<?php endif; // Entrust::hasRole ?>";
        });

        // Call to Entrust::can
        \Blade::directive('permission', function ($expression) {
            return "<?php if (\\Entrust::canDo({$expression})) : ?>";
        });

        \Blade::directive('endpermission', function ($expression) {
            return "<?php endif; // Entrust::canDo ?>";
        });

        // Call to Entrust::ability
        \Blade::directive('ability', function ($expression) {
            return "<?php if (\\Entrust::ability({$expression})) : ?>";
        });

        \Blade::directive('endability', function ($expression) {
            return "<?php endif; // Entrust::ability ?>";
        });
    }

    /**
     * Register the application bindings.
     *
     * @return void
     */
    private function registerEntrust()
    {
        $this->app->bind('entrust', function ($app) {
            return new Entrust($app, $app['entrust.repositories.role'], $app['entrust.repositories.permission']);
        });

        $this->app->alias('entrust', 'Cloty\Entrust\Entrust');
    }

    /**
     * Bind repositories interfaces with their implementations.
     */
    protected function registerRepositoryInterfaces()
    {
        $this->app->singleton('entrust.repositories.role', function ($app) {
            return new EloquentRoleRepository($app, new EntrustRole());
        });

        $this->app->singleton('Cloty\Entrust\Contracts\Repositories\RoleRepository', function ($app) {
            return $app['entrust.repositories.role'];
        });

        $this->app->singleton('entrust.repositories.permission', function ($app) {
            return new EloquentPermissionRepository($app, new EntrustPermission());
        });

        $this->app->singleton('Cloty\Entrust\Contracts\Repositories\PermissionRepository', function ($app) {
            return $app['entrust.repositories.permission'];
        });
    }

    /**
     * Register the artisan commands.
     *
     * @return void
     */
    private function registerCommands()
    {

    }

    /**
     * Merges user's and entrust's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/cloty-entrust.php', 'entrust'
        );
    }

    /**
     * Get the services provided.
     *
     * @return array
     */
    public function provides()
    {

    }
}
