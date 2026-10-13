<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity',
        'description',
        'module',
        'ip_address',
        'user_agent',
    ];

    /**
     * Relationship to User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Static helper to record activity log cleanly across controllers.
     */
    public static function record(string $activity, ?string $description = null, string $module = 'System', ?int $userId = null)
    {
        try {
            return self::create([
                'user_id' => $userId ?? auth()->id(),
                'activity' => $activity,
                'description' => $description,
                'module' => $module,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Silently catch error to prevent log failures from breaking main user operations
            \Illuminate\Support\Facades\Log::error('Failed recording activity log: ' . $e->getMessage());
            return null;
        }
    }
}
