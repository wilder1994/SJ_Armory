<?php

namespace App\Listeners;

use App\Events\AssignmentChanged;
use App\Events\ClientChanged;
use App\Events\PortfolioAssignmentsChanged;
use App\Events\PostChanged;
use App\Events\TransferChanged;
use App\Events\WeaponChanged;
use App\Events\WorkerChanged;
use App\Services\DomainActivityNotificationService;

class DispatchDomainActivityNotifications
{
    public function __construct(private DomainActivityNotificationService $notificationService) {}

    public function handle(PostChanged|WorkerChanged|WeaponChanged|ClientChanged|AssignmentChanged|TransferChanged|PortfolioAssignmentsChanged $event): void
    {
        $this->notificationService->notifyFromDomainEvent($event);
    }
}
