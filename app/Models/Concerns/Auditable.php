<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Registra en activity_logs cada alta, cambio y baja del modelo, sin tener que
 * acordarse de llamarlo en cada controlador.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn (Model $model) => $model->audit('created'));
        static::updated(fn (Model $model) => $model->audit('updated'));
        static::deleted(fn (Model $model) => $model->audit('deleted'));
    }

    protected function audit(string $action): void
    {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return; // seeders e importador
        }

        $changes = $action === 'updated'
            ? collect($this->getChanges())->except(['updated_at', 'password', 'remember_token'])->keys()->implode(', ')
            : '';

        if ($action === 'updated' && $changes === '') {
            return;
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $this->getMorphClass(),
            'subject_id' => $this->getKey(),
            'description' => trim(class_basename($this).' '.$action.($changes ? ": $changes" : '')),
            'ip_address' => request()?->ip(),
        ]);
    }
}
