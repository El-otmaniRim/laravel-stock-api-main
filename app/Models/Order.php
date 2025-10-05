<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;


    protected $fillable = [
    'user_id',
    'status',
    'delivery_id',
    'total_price',
    'scheduled_date',
    'notes'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

     // Relation to the client (user)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // Delivery person relation
    public function delivery()
    {
        return $this->belongsTo(User::class, 'delivery_id');
    }

    // In Order.php
    public function client()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
