<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_id',
        'buy_date',
        'buy_price',
        'buy_lot',
        'average',
        'total_invested',
        'sell_date',
        'sell_price',
        'sell_lot',
        'total_sell',
        'total_profit',
        'profit_percentage',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
