<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;

class stock extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function updateTotals($isPurchase = false, $buyPrice = null, $buyLot = null)
    {
        $unsoldTransactions = $this->transactions()->whereNull('sell_date')->get();

        // Jika pembelian, update total_average
        if ($isPurchase && $buyPrice !== null && $buyLot !== null) {
            $this->total_average = $this->total_lot > 0
                ? (($this->total_average * $this->total_lot * 100) + ($buyPrice * $buyLot * 100)) / (($this->total_lot + $buyLot) * 100)
                : $buyPrice;
        }

        // Hitung ulang total lot
        $this->total_lot = $unsoldTransactions->sum('buy_lot');

        // Hitung total_invested menggunakan total_lot dan total_average
        $this->total_invested = $this->total_lot * $this->total_average * 100;

        // Total profit
        $this->total_profit = $this->transactions()->sum('total_profit');

        // Rata-rata profit (default 0 jika null)
        $averageProfit = $this->transactions()->avg('profit_percentage');
        $this->average_profit_percentage = $averageProfit ?? 0;

        $this->save();
    }

    public function paginatedTransactions($perPage = 10)
    {
        return $this->transactions()->orderBy('id', 'asc')->paginate($perPage);
    }


}
