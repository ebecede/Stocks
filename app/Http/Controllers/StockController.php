<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $stocks = Stock::with('transactions')->get();

        // Tambahkan data perhitungan total di Controller
        $stocks->each(function ($stock) {
            $stock->total_invested = $stock->transactions->sum(function ($transaction) {
                return $transaction->buy_price * $transaction->buy_lot * 100; // Harga * Lot * 100
            });

            $stock->total_lot = $stock->transactions->sum('buy_lot');

            $stock->total_average = $stock->total_lot > 0
                ? $stock->total_invested / ($stock->total_lot * 100)
                : 0;

            $stock->total_profit = $stock->transactions->sum('total_profit');

            $stock->average_profit_percentage = $stock->transactions->count() > 0
                ? $stock->transactions->avg('profit_percentage')
                : 0;
        });

        return view('layout', compact('stocks'));
    }

    public function store(Request $request)
    {
        $stock = Stock::create($request->only('name'));
        return redirect()->back()->with('success', 'Stock created successfully.');
    }

    public function addTransaction(Request $request, Stock $stock)
    {
        $validated = $request->validate([
            'buy_date' => 'required|date',
            'buy_price' => 'required|numeric',
            'buy_lot' => 'required|integer',
        ]);

        // Hitung total invested dengan Harga * Lot * 100
        $totalInvested = $validated['buy_price'] * $validated['buy_lot'] * 100;

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

        foreach ($validated['transaction_ids'] as $transactionId) {
            $transaction = $stock->transactions()->find($transactionId);

            if ($transaction && !$transaction->sell_date) {
                $totalSellValue = $validated['sell_price'] * $validated['sell_lot'] * 100;
                $totalProfit = $totalSellValue - $transaction->total_invested;

                $transaction->update([
                    'sell_date' => $validated['sell_date'],
                    'sell_price' => $validated['sell_price'],
                    'sell_lot' => $validated['sell_lot'],
                    'total_profit' => $totalProfit,
                    'profit_percentage' => ($totalProfit / $transaction->total_invested) * 100,
                ]);
            }
        }

        // Update perhitungan total
        $stock->total_invested = $stock->transactions->whereNull('sell_date')->sum(function ($transaction) {
            return $transaction->buy_price * $transaction->buy_lot * 100;
        });

        $stock->total_lot = $stock->transactions->whereNull('sell_date')->sum('buy_lot');

        $stock->total_average = $stock->total_lot > 0
            ? $stock->total_invested / ($stock->total_lot * 100)
            : 0;

        $stock->total_profit = $stock->transactions->sum('total_profit');

        $stock->average_profit_percentage = $stock->transactions->count() > 0
            ? $stock->transactions->avg('profit_percentage')
            : 0;

        $stock->save();

        return redirect()->back()->with('success', 'Selected transactions sold successfully.');
    }


    // public function sellSelectedTransaction(Request $request, Stock $stock)
    // {
    //     $validated = $request->validate([
    //         'transaction_ids' => 'required|array',
    //         'sell_date' => 'required|date',
    //         'sell_price' => 'required|numeric',
    //         'sell_lot' => 'required|integer',
    //     ]);

    //     foreach ($validated['transaction_ids'] as $transactionId) {
    //         $transaction = $stock->transactions()->find($transactionId);

    //         if ($transaction) {
    //             $totalSellValue = $validated['sell_price'] * $validated['sell_lot'] * 100;
    //             $totalProfit = $totalSellValue - $transaction->total_invested;

    //             $transaction->update([
    //                 'sell_date' => $validated['sell_date'],
    //                 'sell_price' => $validated['sell_price'],
    //                 'sell_lot' => $validated['sell_lot'],
    //                 'total_profit' => $totalProfit,
    //                 'profit_percentage' => ($totalProfit / $transaction->total_invested) * 100,
    //             ]);
    //         }
    //     }

    //     return redirect()->back()->with('success', 'Selected transactions sold successfully.');
    // }

    public function destroy($id)
    {
        $stock = Stock::findOrFail($id); // Cari saham berdasarkan ID
        $stock->delete(); // Hapus saham

        return redirect()->back()->with('success', 'Stock deleted successfully.');
    }
}
