<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'total_price',
        'address',
        'user_id',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot(['quantity','price']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payment()
    {
        return $this->hasOne(OrderPayment::class);
    }
}
