@extends('layout')

@section('content')
{{-- <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}
                </div>
            </div>
        </div>
    </div>
</div> --}}
<!-- Container -->
<div class="container mt-4">
    <h1 class="mb-4">Stock Transactions</h1>
    <div class="d-flex justify-content-between align-items-start mb-4">
        <!-- Form Add Stock -->
        <form action="{{ route('stocks.store') }}" method="POST" style="width: 20%;">
            @csrf
            <div class="input-group">
                <input type="text" name="name" class="form-control" placeholder="Stock Name" required>
                <button class="btn-darkblue" type="submit">Add Stock</button>
            </div>
        </form>

        <!-- Filter Stock -->
        <form action="{{ route('stocks.index') }}" method="GET" style="width: 15%;">
            <div class="row g-3 align-items-center">
                <div class="col-md-12">
                    <select name="name" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Saham</option> <!-- Opsi untuk melihat semua saham -->
                        @foreach ($allStocks as $stock)
                            <option value="{{ $stock->id }}" {{ $selectedStockId == $stock->id ? 'selected' : '' }}>
                                {{ $stock->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>

    @if ($stocks->isEmpty())
        <div class="alert alert-info">Tidak ada data saham yang ditemukan.</div>
    @else
    @foreach ($stocks as $stock)
        <div class="card mb-4 shadow">
            <!-- Card Header -->
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">{{ $stock->name }}</h3>
                <form action="{{ route('stocks.destroy', $stock->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this stock?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn-red btn-sm" type="submit">Delete Stock</button>
                </form>
            </div>

            <!-- Card Body & Footer Combined -->
            <div class="card-body">
                <!-- Form Tambah Transaksi Beli -->
                <form action="{{ route('stocks.addTransaction', $stock->id) }}" method="POST" class="mb-3">
                    @csrf
                    <div class="row g-3 align-items-center">
                        <div class="col-md-3"><input type="date" name="buy_date" class="form-control" required></div>
                        <div class="col-md-3"><input type="number" step="0.01" name="buy_price" class="form-control" placeholder="Buy Price" required></div>
                        <div class="col-md-3"><input type="number" name="buy_lot" class="form-control" placeholder="Buy Lot" required></div>
                        <div class="col"><button class="btn-darkblue btn-sm" type="submit">Buy Stock</button></div>
                    </div>
                </form>

                <!-- Form Jual Transaksi -->
                <form action="{{ route('stocks.sellSelectedTransaction', $stock->id) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-md-3"><input type="date" name="sell_date" class="form-control" required></div>
                        <div class="col-md-3"><input type="number" step="0.01" name="sell_price" class="form-control" placeholder="Sell Price" required></div>
                        <div class="col-md-3"><input type="number" name="sell_lot" class="form-control" placeholder="Sell Lot" required></div>
                        <div class="col"><button class="btn-lightblue btn-sm" type="submit">Sell Stock</button></div>
                    </div>

                    <!-- Tabel Transaksi -->
                    <table class="table table-bordered">
                        <thead>
                            <tr class="text-center">
                                <th colspan="6" style="background-color: #cce5ff;">BUY</th>
                                <th colspan="6" style="background-color: #f8d7da;">SELL</th>
                            </tr>
                            <tr class="table-info">
                                <th>Select</th>
                                <th>Buy Date</th>
                                <th>Buy Price</th>
                                <th>Buy Lot</th>
                                <th>Shares</th>
                                <th>Total Invested</th>
                                <th>Sell Date</th>
                                <th>Sell Price</th>
                                <th>Sell Lot</th>
                                <th>Total Sell</th>
                                <th>Total Profit</th>
                                <th>Profit (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stock->paginatedTransactions() as $transaction)
                                <tr class="{{ $transaction->sell_date ? 'table-danger' : '' }}">
                                    <td>
                                        @if (!$transaction->sell_date)
                                            <input type="checkbox" name="transaction_ids[]" value="{{ $transaction->id }}">
                                        @else
                                            Sold
                                        @endif
                                    </td>
                                    <td>{{ $transaction->buy_date }}</td>
                                    <td>{{ number_format($transaction->buy_price, 2) }}</td>
                                    <td>{{ $transaction->buy_lot }}</td>
                                    <td>{{ number_format($transaction->buy_lot * 100, 0) }}</td>
                                    <td>{{ number_format($transaction->total_invested, 0) }}</td>
                                    <td>{{ $transaction->sell_date ?? '-' }}</td>
                                    <td>{{ $transaction->sell_price ? number_format($transaction->sell_price, 2) : '-' }}</td>
                                    <td>{{ $transaction->sell_lot ?? '-' }}</td>
                                    <td>{{ number_format($transaction->total_sell, 0) }}</td>
                                    <td>{{ $transaction->total_profit ? number_format($transaction->total_profit, 0) : '-' }}</td>
                                    <td>{{ $transaction->profit_percentage ? number_format($transaction->profit_percentage, 2) . '%' : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex">
                        {{ $stock->paginatedTransactions()->links() }}
                    </div>

                </form>


                <!-- Ringkasan Total -->
                <div class="d-flex justify-content-between" style="gap: 20px;">
                    <!-- Tabel 1 -->
                    <table class="table table-bordered" style="width: 48%">
                        <tr>
                            <td>TOTAL LOT</td>
                            <td class="fw-bold">{{ $stock->total_lot }}</td>
                        </tr>
                        <tr>
                            <td>TOTAL INVESTED</td>
                            <td class="fw-bold">{{ number_format($stock->total_invested, 2) }}</td>
                        </tr>
                        <tr>
                            <td>AVERAGE PRICE</td>
                            <td class="fw-bold">{{ number_format($stock->total_average, 2) }}</td>
                        </tr>
                    </table>

                    <!-- Tabel 2 -->
                    <table class="table table-bordered" style="width: 48%">
                        <tr>
                            <td>TOTAL PROFIT</td>
                            <td class="text-success fw-bold">{{ number_format($stock->total_profit, 0) }}</td>
                        </tr>
                        <tr>
                            <td>AVERAGE PROFIT (%)</td>
                            <td class="text-primary fw-bold">{{ number_format($stock->average_profit_percentage, 2) }}%</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    @endforeach

    @endif
</div>
@endsection
