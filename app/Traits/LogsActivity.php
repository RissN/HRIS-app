<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

trait LogsActivity
{
    /**
     * Log an activity performed by the currently authenticated user.
     *
     * @param  array<string, mixed>|null  $changes
     */
    protected function logActivity(
        string $action,
        string $description,
        ?Model $model = null,
        ?array $changes = null,
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $model?->getMorphClass(),
            'model_id' => $model?->getKey(),
            'description' => $description,
            'changes' => $changes,
            'ip_address' => Request::ip(),
            'user_agent' => Str::limit(Request::userAgent(), 200),
        ]);
    }
}
