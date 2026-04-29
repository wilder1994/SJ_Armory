<?php

namespace App\Providers;

use App\Events\AssignmentChanged;
use App\Events\ClientChanged;
use App\Events\PortfolioAssignmentsChanged;
use App\Events\PostChanged;
use App\Events\TransferChanged;
use App\Events\WeaponChanged;
use App\Events\WorkerChanged;
use App\Listeners\DispatchDomainActivityNotifications;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        PostChanged::class => [DispatchDomainActivityNotifications::class],
        WorkerChanged::class => [DispatchDomainActivityNotifications::class],
        WeaponChanged::class => [DispatchDomainActivityNotifications::class],
        ClientChanged::class => [DispatchDomainActivityNotifications::class],
        AssignmentChanged::class => [DispatchDomainActivityNotifications::class],
        TransferChanged::class => [DispatchDomainActivityNotifications::class],
        PortfolioAssignmentsChanged::class => [DispatchDomainActivityNotifications::class],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
