<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'plan',
        'omise_customer_id',
        'omise_subscription_id',
        'current_period_end',
        'invoice_count_this_month',
    ];

    protected $casts = [
        'current_period_end' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
