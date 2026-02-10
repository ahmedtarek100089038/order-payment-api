<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function canBeDeleted(): bool
    {
        return $this->payments()->count() === 0;
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function calculateTotal()
    {
        return $this->items->sum('subtotal');
    }
}