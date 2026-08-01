<?php

namespace Modules\MediaManager\Infrastructure\Listeners;

use Illuminate\Support\Facades\Request;
use Modules\MediaManager\Domain\Events\FolderCreated;
use Modules\MediaManager\Domain\Events\MediaCopied;
use Modules\MediaManager\Domain\Events\MediaDeleted;
use Modules\MediaManager\Domain\Events\MediaRenamed;
use Modules\MediaManager\Domain\Events\MediaUploaded;
use Modules\MediaManager\Infrastructure\Models\MediaAuditLog;

class LogMediaActivityListener
{
    public function handle(object $event): void
    {
        $log = new MediaAuditLog();

        match (true) {
            $event instanceof MediaUploaded => $log->fill([
                'user_id' => $event->userId,
                'action' => 'upload',
                'path' => $event->path,
            ]),
            $event instanceof MediaRenamed => $log->fill([
                'user_id' => $event->userId,
                'action' => 'rename',
                'path' => $event->newPath,
                'old_path' => $event->oldPath,
            ]),
            $event instanceof MediaDeleted => $log->fill([
                'user_id' => $event->userId,
                'action' => 'delete',
                'path' => $event->path,
            ]),
            $event instanceof MediaCopied => $log->fill([
                'user_id' => $event->userId,
                'action' => 'copy',
                'path' => $event->path,
            ]),
            $event instanceof FolderCreated => $log->fill([
                'user_id' => $event->userId,
                'action' => 'folder_created',
                'path' => $event->path,
            ]),
        };

        $log->ip = Request::ip();
        $log->user_agent = Request::userAgent();
        $log->save();
    }
}
