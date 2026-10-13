<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
        'type',
        'quantity',
        'recipient_or_provider',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'float',
    ];

    /**
     * Relationship to Item.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Relationship to User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
