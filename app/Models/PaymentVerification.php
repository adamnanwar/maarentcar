<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentVerification extends Model
{
    public $timestamps = false;

    const ACTION_VERIFY = 'verify';
    const ACTION_REJECT = 'reject';

    protected $fillable = ['payment_id', 'verified_by', 'action', 'reason'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
