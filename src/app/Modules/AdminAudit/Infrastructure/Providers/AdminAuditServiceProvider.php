<?php

namespace App\Modules\AdminAudit\Infrastructure\Providers;

use App\Modules\AdminAudit\Infrastructure\Listeners\LogAdminAuditListener;
use App\Modules\FormBuilder\Domain\Events\FormCreated;
use App\Modules\FormBuilder\Domain\Events\FormDeleted;
use App\Modules\FormBuilder\Domain\Events\FormUpdated;
use App\Modules\MaterialManager\Domain\Events\MaterialCreated;
use App\Modules\MaterialManager\Domain\Events\MaterialDeleted;
use App\Modules\MaterialManager\Domain\Events\MaterialUpdated;
use App\Modules\UserManager\Domain\Events\UserCreated;
use App\Modules\UserManager\Domain\Events\UserDeleted;
use App\Modules\UserManager\Domain\Events\UserUpdated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AdminAuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../Database/Migrations');

        $this->registerAuditListeners();
    }

    protected function registerAuditListeners(): void
    {
        Event::listen(MaterialCreated::class, LogAdminAuditListener::class);
        Event::listen(MaterialUpdated::class, LogAdminAuditListener::class);
        Event::listen(MaterialDeleted::class, LogAdminAuditListener::class);
        Event::listen(UserCreated::class, LogAdminAuditListener::class);
        Event::listen(UserUpdated::class, LogAdminAuditListener::class);
        Event::listen(UserDeleted::class, LogAdminAuditListener::class);
        Event::listen(FormCreated::class, LogAdminAuditListener::class);
        Event::listen(FormUpdated::class, LogAdminAuditListener::class);
        Event::listen(FormDeleted::class, LogAdminAuditListener::class);
    }
}
