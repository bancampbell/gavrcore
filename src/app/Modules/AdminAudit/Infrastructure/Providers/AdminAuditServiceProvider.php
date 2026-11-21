<?php

namespace App\Modules\AdminAudit\Infrastructure\Providers;

use App\Modules\AdminAudit\Infrastructure\Listeners\LogAdminAuditListener;
use App\Modules\FormBuilder\Domain\Events\FormCreated;
use App\Modules\FormBuilder\Domain\Events\FormDeleted;
use App\Modules\FormBuilder\Domain\Events\FormUpdated;
use App\Modules\FormBuilder\Domain\Events\SubmissionDeleted;
use App\Modules\MaterialManager\Domain\Events\MaterialCreated;
use App\Modules\MaterialManager\Domain\Events\MaterialDeleted;
use App\Modules\MaterialManager\Domain\Events\MaterialForceDeleted;
use App\Modules\MaterialManager\Domain\Events\MaterialUpdated;
use App\Modules\UserManager\Domain\Events\AccessLevelCreated;
use App\Modules\UserManager\Domain\Events\AccessLevelDeleted;
use App\Modules\UserManager\Domain\Events\AccessLevelUpdated;
use App\Modules\UserManager\Domain\Events\GroupCreated;
use App\Modules\UserManager\Domain\Events\GroupDeleted;
use App\Modules\UserManager\Domain\Events\GroupUpdated;
use App\Modules\UserManager\Domain\Events\PermissionCreated;
use App\Modules\UserManager\Domain\Events\PermissionDeleted;
use App\Modules\UserManager\Domain\Events\PermissionUpdated;
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
        Event::listen(MaterialForceDeleted::class, LogAdminAuditListener::class);
        Event::listen(UserCreated::class, LogAdminAuditListener::class);
        Event::listen(UserUpdated::class, LogAdminAuditListener::class);
        Event::listen(UserDeleted::class, LogAdminAuditListener::class);
        Event::listen(FormCreated::class, LogAdminAuditListener::class);
        Event::listen(FormUpdated::class, LogAdminAuditListener::class);
        Event::listen(FormDeleted::class, LogAdminAuditListener::class);
        Event::listen(SubmissionDeleted::class, LogAdminAuditListener::class);
        Event::listen(GroupCreated::class, LogAdminAuditListener::class);
        Event::listen(GroupUpdated::class, LogAdminAuditListener::class);
        Event::listen(GroupDeleted::class, LogAdminAuditListener::class);
        Event::listen(AccessLevelCreated::class, LogAdminAuditListener::class);
        Event::listen(AccessLevelUpdated::class, LogAdminAuditListener::class);
        Event::listen(AccessLevelDeleted::class, LogAdminAuditListener::class);
        Event::listen(PermissionCreated::class, LogAdminAuditListener::class);
        Event::listen(PermissionUpdated::class, LogAdminAuditListener::class);
        Event::listen(PermissionDeleted::class, LogAdminAuditListener::class);
    }
}
