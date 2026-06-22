<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogger
{
    public static function record(
        string $action,
        string $auditableType,
        int|string|null $auditableId = null,
        array|null $before = null,
        array|null $after = null,
        ?Request $request = null,
    ): void {
        $request ??= request();
        $user = $request?->user();

        DB::table('audit_logs')->insert([
            'user_id' => $user?->id,
            'actor_name' => $user?->name,
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => is_numeric($auditableId) ? (int) $auditableId : null,
            'before' => $before ? json_encode($before) : null,
            'after' => $after ? json_encode($after) : null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
