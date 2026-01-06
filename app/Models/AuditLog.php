<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Activity;

class AuditLog extends Activity
{
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}   