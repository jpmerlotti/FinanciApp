<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;

class Counterparty extends Model
{
    use LogsActivity;
    use \App\Traits\LogsActivityWithOrganization;

    protected $fillable = [
        'name',
        'document',
        'email',
        'company_name',
        'mobile_phone',
        'phone',
        'zip_code',
        'street',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'municipal_registration',
        'state_registration',
        'should_send_boleto',
        'additional_emails',
        'observations',
    ];

    public function casts(): array
    {
        return [
            'should_send_boleto' => 'boolean',
            'additional_emails' => 'array',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'recipient', 'id');
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}
