<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Transactions</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sold-row {
            background-color: #ffeb99 !important; /* Highlight warna kuning */
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-lightblue">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Home</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav ms-auto">
                    @if (Route::has('login'))
                        <a class="btn btn-white me-3" href="{{ route('login') }}">{{ __('Sign In') }}</a>
                        @if (Route::has('register'))
                            <a class="btn btn-white" href="{{ route('register') }}">{{ __('Sign Up') }}</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Stock Transactions</h1>

        <!-- Form untuk menambahkan saham -->
        <form action="{{ route('stocks.store') }}" method="POST">
            @csrf
            <div class="input-group mb-3">
                <input type="text" name="name" class="form-control" placeholder="Stock Name" required>
                <button class="btn btn-primary" type="submit">Add Stock</button>
            </div>
        </form>

        @foreach ($stocks as $stock)
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>{{ $stock->name }}</h3>
                <!-- Tombol Hapus Stock -->
                <form action="{{ route('stocks.destroy', $stock->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this stock?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">Delete Stock</button>
                </form>
            </div>

            <!-- Form untuk menambahkan transaksi beli -->
            <form action="{{ route('stocks.addTransaction', $stock->id) }}" method="POST" class="mb-3">
                @csrf
                <div class="row g-3">
                    <div class="col">
                        <input type="date" name="buy_date" class="form-control" placeholder="Buy Date" required>
                    </div>
                    <div class="col">
                        <input type="number" step="0.01" name="buy_price" class="form-control" placeholder="Buy Price" required>
                    </div>
                    <div class="col">
                        <input type="number" name="buy_lot" class="form-control" placeholder="Buy Lot" required>
                    </div>
                    <div class="col">
                        <button class="btn btn-success" type="submit">Add Transaction</button>
                    </div>
                </div>
            </form>

            <!-- Form untuk menjual transaksi yang dipilih -->
            <form action="{{ route('stocks.sellSelectedTransaction', $stock->id) }}" method="POST" class="mb-4">
                <!-- Input untuk data jual -->
                <div class="row g-3 mb-3">
                    <div class="col">
                        <input type="date" name="sell_date" class="form-control" placeholder="Sell Date" required>
                    </div>
                    <div class="col">
                        <input type="number" step="0.01" name="sell_price" class="form-control" placeholder="Sell Price" required>
                    </div>
                    <div class="col">
                        <input type="number" name="sell_lot" class="form-control" placeholder="Sell Lot" required>
                    </div>
                    <div class="col">
                        <button class="btn btn-warning" type="submit">Sell Selected Transactions</button>
                    </div>
                </div>

                @csrf
                @method('PATCH')
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Pilih</th>
                            <th>Tanggal</th>
                            <th>Harga</th>
                            <th>Lot</th>
                            <th>Lembar</th>
                            <th>Total Invested</th>
                            <th>Tanggal Jual</th>
                            <th>Harga Jual</th>
                            <th>Lot Jual</th>
                            <th>Total Profit</th>
                            <th>Profit (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stock->transactions as $transaction)
                            <tr class="{{ $transaction->sell_date ? 'sold-row' : '' }}">
                                <!-- Checkbox hanya muncul jika belum dijual -->
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
                                <td>{{ $transaction->total_profit ? number_format($transaction->total_profit, 0) : '-' }}</td>
                                <td>{{ $transaction->profit_percentage ? number_format($transaction->profit_percentage, 2) . '%' : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </form>

            <!-- TOTAL ROW -->
            <table class="table table-bordered" style="width:40%">
                <tr>
                    <td class="fw-bold" style="width:40%">TOTAL LOT</td>
                    <td class="fw-bold">{{ $stock->total_lot }}</td>
                </tr>
                <tr>
                    <td class="fw-bold" style="width:40%">TOTAL INVESTED</td>
                    <td class="fw-bold">{{ number_format($stock->total_invested, 2) }}</td>
                </tr>
                <tr>
                    <td class="fw-bold" style="width:40%">AVERAGE PRICE</td>
                    <td class="fw-bold">{{ $stock->total_average > 0 ? number_format($stock->total_average, 2) : '0.00' }}</td>
                </tr>
                <tr>
                    <td class="fw-bold" style="width:40%">TOTAL PROFIT</td>
                    <td class="fw-bold text-success">{{ number_format($stock->total_profit, 0) }}</td>
                </tr>
                <tr>
                    <td class="fw-bold" style="width:40%">AVERAGE PROFIT (%)</td>
                    <td class="fw-bold text-primary">{{ number_format($stock->average_profit_percentage, 2) }}%</td>
                </tr>
            </table>
        @endforeach
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
