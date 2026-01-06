<?php

namespace App\Models;

use App\Enums\TransactionStatuses;
use App\Enums\TransactionTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\Traits\LogsActivity;

class Transaction extends Model
{
    use LogsActivity;
    use \App\Traits\LogsActivityWithOrganization;

    protected $fillable = [
        'title',
        'type',
        'amount_cents',
        'due_date',
        'paid_at',
        'cancelled_at',
        'payment_proof_path',
        'status',
        'recipient',
        'description',
        'recurring_group_id',
        'transaction_category_id',
        'installment_number',
        'total_installments',
    ];

    public function casts(): array
    {
        return [
            'type' => TransactionTypes::class,
            'status' => TransactionStatuses::class,
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (Transaction $transaction) {
            if ($transaction->isDirty('status')) {
                // If Paid -> Set paid_at
                if ($transaction->status === TransactionStatuses::COMPLETED) {
                    $transaction->paid_at = now();
                    $transaction->cancelled_at = null;
                } 
                // If Cancelled -> Set cancelled_at
                elseif ($transaction->status === TransactionStatuses::CANCELED) {
                    $transaction->cancelled_at = now();
                    $transaction->paid_at = null;
                }
                // If Pending/Overdue -> Clear dates (optional, user asked "auto update", implying sync)
                elseif ($transaction->status === TransactionStatuses::PENDING || $transaction->status === TransactionStatuses::OVERDUE) {
                    $transaction->paid_at = null;
                    $transaction->cancelled_at = null;
                }
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function counterparty(): BelongsTo
    {
        return $this->belongsTo(Counterparty::class, 'recipient');
    }

    public function recurrenceGroup()
    {
        return $this->hasMany(static::class, 'recurring_group_id', 'recurring_group_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class, 'transaction_category_id');
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty();
    }
}
