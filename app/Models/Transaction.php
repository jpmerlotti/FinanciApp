<?php

namespace App\Models;

use App\Enums\TransactionStatuses;
use App\Enums\TransactionTypes;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'title',
        'type',
        'amount_cents',
        'transaction_date',
        'payment_proof_path',
        'status',
        'recipient',
        'description',
        'recurring_group_id',
        'installment_number',
        'total_installments',
    ];

    public function casts(): array
    {
        return [
            'type' => TransactionTypes::class,
            'status' => TransactionStatuses::class,
            'transaction_date' => 'date',
        ];
    }
}
