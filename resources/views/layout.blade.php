<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Transactions</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-lightblue">
        <div class="container-fluid">
            {{-- <a class="navbar-brand" href="#">Home</a> --}}
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav ms-auto">
                    @if (Route::has('login'))
                        <a class="btn-darkblue me-3" href="{{ route('login') }}">{{ __('Sign In') }}</a>
                        @if (Route::has('register'))
                            <a class="btn-darkblue" href="{{ route('register') }}">{{ __('Sign Up') }}</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </nav>

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
            <form action="{{ route('stocks.index') }}" method="GET" style="width: 22%;">
                <div class="row g-3 align-items-center">
                    <div class="col-md-9">
                        <select id="stock-filter" name="stock_id" class="form-select">
                            <option value="">Semua Saham</option> <!-- Opsi untuk melihat semua saham -->
                            @foreach ($allStocks as $stock)
                                <option value="{{ $stock->id }}" {{ $selectedStockId == $stock->id ? 'selected' : '' }}>
                                    {{ $stock->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn-lightblue">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        @if ($stocks->isEmpty())
            <div class="alert alert-info">Tidak ada data saham yang ditemukan.</div>
        @else
            <!-- Loop untuk Stock -->
            @foreach ($stocks as $stock)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h3>{{ $stock->name }}</h3>
                    <form action="{{ route('stocks.destroy', $stock->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this stock?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn-red" type="submit">Delete Stock</button>
                    </form>
                </div>

                <!-- Form Tambah Transaksi Beli -->
                <form action="{{ route('stocks.addTransaction', $stock->id) }}" method="POST" class="mb-3">
                    @csrf
                    <div class="row g-3 align-items-center">
                        <div class="col-md-2"><input type="date" name="buy_date" class="form-control" required></div>
                        <div class="col-md-2"><input type="number" step="0.01" name="buy_price" class="form-control" placeholder="Buy Price" required></div>
                        <div class="col-md-2"><input type="number" name="buy_lot" class="form-control" placeholder="Buy Lot" required></div>
                        <div class="col"><button class="btn-lightblue" type="submit">Buy Stock</button></div>
                    </div>
                </form>

                <!-- Form Jual Transaksi -->
                <form action="{{ route('stocks.sellSelectedTransaction', $stock->id) }}" method="POST" class="mb-4">
                    @csrf
                    @method('PATCH')

                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-md-2"><input type="date" name="sell_date" class="form-control" required></div>
                        <div class="col-md-2"><input type="number" step="0.01" name="sell_price" class="form-control" placeholder="Sell Price" required></div>
                        <div class="col-md-2"><input type="number" name="sell_lot" class="form-control" placeholder="Sell Lot" required></div>
                        <div class="col"><button class="btn-darkblue" type="submit">Sell Stock</button></div>
                    </div>

                    <!-- Tabel Transaksi -->
                    <table class="table table-bordered">
                        <thead class="table-info">
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
                                    <td>{{ $transaction->total_profit ? number_format($transaction->total_profit, 0) : '-' }}</td>
                                    <td>{{ $transaction->profit_percentage ? number_format($transaction->profit_percentage, 2) . '%' : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>

                <!-- Ringkasan Total -->
                <div class="d-flex" style="gap: 20px;">
                    <!-- Tabel 1 -->
                    <table class="table table-bordered" style="width: 30%">
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
                    <table class="table table-bordered" style="width: 30%">
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
                <br>
            @endforeach
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#stock-filter').select2({
                placeholder: "Pilih Saham",
                allowClear: true
            });
        });
    </script>

</body>
</html>
