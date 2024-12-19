<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        // Ambil ID saham yang dipilih dari request
        $selectedStockId = $request->input('name');

        if ($selectedStockId) {
            // Jika ada filter, ambil hanya saham yang dipilih
            $stocks = Stock::with(['transactions' => function ($query) {
                $query->orderBy('id', 'asc')->paginate(10);
            }])->where('id', $selectedStockId)->get();
        } else {
            // Jika tidak ada filter, ambil semua saham
            $stocks = Stock::with(['transactions' => function ($query) {
                $query->orderBy('id', 'asc')->paginate(10);
            }])->orderBy('name', 'asc')->get();
        }

        // Hitung ulang data total untuk setiap stock
        $stocks->each(function ($stock) {
            $stock->updateTotals();
        });

        // Ambil semua saham untuk dropdown, diurutkan berdasarkan nama
        $allStocks = Stock::orderBy('name', 'asc')->get();

        // Kirim data saham yang terfilter dan semua saham untuk dropdown
        return view('home', compact('stocks', 'allStocks', 'selectedStockId'));
    }

    public function store(Request $request)
    {
        $stock = Stock::create($request->only('name'));
        return redirect()->route('stocks.index')->with('success', 'Stock created successfully.');
    }

    public function addTransaction(Request $request, Stock $stock)
    {
        $validated = $request->validate([
            'buy_date' => 'required|date',
            'buy_price' => 'required|numeric',
            'buy_lot' => 'required|integer',
        ]);

        // Hitung total invested dengan Harga * Lot * 100
        $buyFee = $validated['buy_price'] * $validated['buy_lot'] * 100 * 0.0015;
        $totalInvested = ($validated['buy_price'] * $validated['buy_lot'] * 100) + $buyFee;

        $stock->transactions()->create([
            'buy_date' => $validated['buy_date'],
            'buy_price' => $validated['buy_price'],
            'buy_lot' => $validated['buy_lot'],
            'total_invested' => $totalInvested,
        ]);

        return redirect()->back()->with('success', 'Transaction added successfully.');
    }

    public function sellSelectedTransaction(Request $request, Stock $stock)
    {
        $validated = $request->validate([
            'transaction_ids' => 'required|array',
            'sell_date' => 'required|date',
            'sell_price' => 'required|numeric',
            'sell_lot' => 'required|integer',
        ]);

        $totalLotToSell = $validated['sell_lot'];
        $totalProfit = 0;

        foreach ($validated['transaction_ids'] as $transactionId) {
            $transaction = $stock->transactions()->find($transactionId);

            if ($transaction && !$transaction->sell_date) {
                $sellLot = min($transaction->buy_lot, $totalLotToSell);

                $totalSellValue = $validated['sell_price'] * $sellLot * 100;
                $sellFee = $totalSellValue * 0.0025; // Fee jual 0.25%
                $totalSell = $totalSellValue - $sellFee;

                $buyFee = $transaction->buy_price * $sellLot * 100 * 0.0015;
                $totalBuy = $transaction->buy_price * $sellLot * 100;
                $totalInvestedForSoldLot = $totalBuy + $buyFee;
                $profit = $totalSell - $totalInvestedForSoldLot;

                $totalProfit += $profit;

                $remainingLot = $transaction->buy_lot - $sellLot;

                if ($remainingLot > 0) {
                    $transaction->update([
                        'buy_lot' => $remainingLot,
                        'total_invested' => $transaction->buy_price * $remainingLot * 100,
                    ]);

                    $stock->transactions()->create([
                        'buy_date' => $transaction->buy_date,
                        'buy_price' => $transaction->buy_price,
                        'buy_lot' => $sellLot,
                        'total_invested' => $totalInvestedForSoldLot,
                        'sell_date' => $validated['sell_date'],
                        'sell_price' => $validated['sell_price'],
                        'sell_lot' => $sellLot,
                        'total_sell'=> $totalSell,
                        'total_profit' => $profit,
                        'profit_percentage' => ($profit / $totalInvestedForSoldLot) * 100,
                    ]);
                } else {
                    $transaction->update([
                        'sell_date' => $validated['sell_date'],
                        'sell_price' => $validated['sell_price'],
                        'sell_lot' => $sellLot,
                        'total_sell'=> $totalSell,
                        'total_profit' => $profit,
                        'profit_percentage' => ($profit / $totalInvestedForSoldLot) * 100,
                    ]);
                }

                $totalLotToSell -= $sellLot;
                if ($totalLotToSell <= 0) break;
            }
        }

        $stock->refresh();
        $stock->updateTotals();

        return redirect()->back()->with('success', "Transactions processed. Total Profit: " . number_format($totalProfit, 2));
    }


    public function destroy($id)
    {
        $stock = Stock::findOrFail($id); // Cari saham berdasarkan ID
        $stock->delete(); // Hapus saham

        return redirect()->route('stocks.index')->with('success', 'Stock deleted successfully.');
    }

}
