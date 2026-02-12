<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Post;
use App\Models\Weapon;
use App\Models\Worker;
use App\Policies\ClientPolicy;
use App\Policies\PostPolicy;
use App\Policies\WeaponPolicy;
use App\Policies\WorkerPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Client::class => ClientPolicy::class,
        Post::class => PostPolicy::class,
        Weapon::class => WeaponPolicy::class,
        Worker::class => WorkerPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}

