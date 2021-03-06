<?php

namespace App\Providers;

use App\Auth\AppUserProvider;
use App\Models\Bookmark;
use App\Models\Expedition;
use App\Models\Mission;
use App\Models\Planet;
use App\Models\User;
use App\Policies\BookmarkPolicy;
use App\Policies\ExpeditionPolicy;
use App\Policies\MissionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Bookmark::class => BookmarkPolicy::class,
        Expedition::class => ExpeditionPolicy::class,
        Mission::class => MissionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        Auth::provider('app', function ($app, array $config) {
            return $this->userProvider($config);
        });

        $this->registerPolicies();

        Gate::define('friendly', function (User $user, Planet $planet) {
            return $user->id == $planet->user_id;
        });

        Gate::define('hostile', function (User $user, Planet $planet) {
            return $user->id != $planet->user_id;
        });

        Gate::define('building', function (User $user, $building, $type) {
            return ! empty($building) && $building->type == $type;
        });

        Gate::define('viewDeveloperSetting', function ($user) {
            return in_array($user->email, Config::get('debug.emails'));
        });

        Gate::define('viewWebSocketsDashboard', function ($user) {
            return in_array($user->email, Config::get('debug.emails'));
        });
    }

    /**
     * Create the user provider instance.
     *
     * @throws \Exception|\Throwable
     *
     * @return AppUserProvider
     */
    protected function userProvider(array $config)
    {
        return new AppUserProvider(
            $this->app->make('hash'), $config['model']
        );
    }
}
