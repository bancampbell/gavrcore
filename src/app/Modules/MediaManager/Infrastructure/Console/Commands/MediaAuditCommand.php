<?php

namespace Modules\MediaManager\Infrastructure\Console\Commands;

use Illuminate\Console\Command;
use Modules\MediaManager\Infrastructure\Models\MediaAuditLog;

class MediaAuditCommand extends Command
{
    protected $signature = 'media:audit {--limit=50 : Количество последних записей}';
    protected $description = 'Просмотр аудит-лога медиа-менеджера';

    public function handle(): int
    {
        $logs = MediaAuditLog::query()
            ->with('user:id,name')
            ->latest('created_at')
            ->limit((int) $this->option('limit'))
            ->get();

        if ($logs->isEmpty()) {
            $this->warn('Аудит-лог пуст.');
            return self::SUCCESS;
        }

        $rows = $logs->map(fn ($log) => [
            $log->created_at?->format('Y-m-d H:i:s'),
            $log->action,
            $log->user?->name ?? 'N/A',
            $log->path,
            $log->old_path ?? '-',
            $log->ip ?? '-',
        ]);

        $this->table(
            ['Дата', 'Действие', 'Пользователь', 'Путь', 'Старый путь', 'IP'],
            $rows
        );

        return self::SUCCESS;
    }
}
