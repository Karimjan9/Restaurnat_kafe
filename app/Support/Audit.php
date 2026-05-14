<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Audit
{
    public static function record(string $action, ?Model $auditable = null, array $metadata = [], ?Request $request = null): void
    {
        $request ??= request();
        $actor = $request?->user();

        AuditLog::create([
            'actor_user_id' => $actor?->id,
            'user_id' => $metadata['user_id'] ?? ($auditable instanceof \App\Models\User ? $auditable->id : null),
            'branch_id' => $metadata['branch_id'] ?? ($actor?->branch_id),
            'action' => $action,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
