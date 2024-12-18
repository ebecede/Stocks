<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class stock extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function updateTotals()
    {
        $unsoldTransactions = $this->transactions()->whereNull('sell_date')->get();

        $this->total_invested = $unsoldTransactions->sum(function ($transaction) {
            return $transaction->buy_price * $transaction->buy_lot * 100;
        });

        $this->total_lot = $unsoldTransactions->sum('buy_lot');

        $this->total_average = $this->total_lot > 0
            ? $this->total_invested / ($this->total_lot * 100)
            : 0;

        $this->total_profit = $this->transactions()->sum('total_profit');

        // Tambahkan default 0 jika avg() menghasilkan null
        $averageProfit = $this->transactions()->avg('profit_percentage');
        $this->average_profit_percentage = $averageProfit ?? 0;

        $this->save();
    }


}
