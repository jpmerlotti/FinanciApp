<?php

namespace App\Traits;

use Spatie\Activitylog\Models\Activity;

trait LogsActivityWithOrganization
{
    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->organization_id = $this->organization_id;
    }
}
