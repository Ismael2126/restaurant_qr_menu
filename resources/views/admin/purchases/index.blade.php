<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchases &amp; Bills</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/admin-purchases.css') }}">
</head>
<body>

<div class="page">
    <div class="header">
        <div>
            <h1>Purchases &amp; Bills</h1>
            <p>Record vendor bills and GST paid on purchases for Input Tax reporting.</p>
        </div>

        <div class="header-actions">
            @include('admin.partials.nav', ['current' => 'purchases'])
        </div>
    </div>

    @if(session('success'))
        <div class="alert">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card">
        <h2>Record Purchase</h2>

        <form method="POST" action="{{ route('admin.purchases.store') }}" class="form-row">
            @csrf

            <div>
                <label>Date</label>
                <input type="date" name="purchase_date" value="{{ old('purchase_date', now()->toDateString()) }}" required>
            </div>

            <div>
                <label>Vendor Name</label>
                <input type="text" name="vendor_name" value="{{ old('vendor_name') }}" placeholder="Shop / supplier name" required>
            </div>

            <div>
                <label>Vendor TIN</label>
                <input type="text" name="vendor_tin" value="{{ old('vendor_tin') }}" placeholder="Vendor's TIN number">
            </div>

            <div>
                <label>Invoice Number</label>
                <input type="text" name="invoice_number" value="{{ old('invoice_number') }}" placeholder="INV-0001" required>
            </div>

            <div>
                <label>Amount Excl. GST (MVR)</label>
                <input type="number" step="0.01" min="0" name="amount_excl_gst" id="amountExclGst" value="{{ old('amount_excl_gst') }}" required>
            </div>

            <div>
                <label>GST Amount (MVR)</label>
                <input type="number" step="0.01" min="0" name="gst_amount" id="gstAmount" value="{{ old('gst_amount') }}" required>
            </div>

            <div class="span-2">
                <label>Notes</label>
                <textarea name="notes" placeholder="Optional notes">{{ old('notes') }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit">Add Purchase</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2 class="section-title">All Purchases</h2>

        <form method="GET" action="{{ route('admin.purchases.index') }}" class="filter-form">
            <div>
                <label>Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}">
            </div>

            <div>
                <label>End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}">
            </div>

            <button type="submit">Filter</button>
            <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary">Clear</a>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Vendor</th>
                    <th>TIN</th>
                    <th>Invoice #</th>
                    <th>Excl. GST</th>
                    <th>GST</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->purchase_date->format('d M Y') }}</td>
                        <td>{{ $purchase->vendor_name }}</td>
                        <td>{{ $purchase->vendor_tin ?: '—' }}</td>
                        <td>{{ $purchase->invoice_number }}</td>
                        <td>MVR {{ number_format($purchase->amount_excl_gst, 2) }}</td>
                        <td>MVR {{ number_format($purchase->gst_amount, 2) }}</td>
                        <td>MVR {{ number_format($purchase->total_amount, 2) }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.purchases.edit', $purchase) }}" class="btn btn-small btn-secondary">Edit</a>

                                <form method="POST" action="{{ route('admin.purchases.destroy', $purchase) }}" onsubmit="return confirm('Delete this purchase record?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-small btn-danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No purchases recorded yet.</td>
                    </tr>
                @endforelse
                </tbody>
                @if($purchases->isNotEmpty())
                    <tfoot>
                    <tr>
                        <td colspan="4">Totals</td>
                        <td>MVR {{ number_format($purchases->sum('amount_excl_gst'), 2) }}</td>
                        <td>MVR {{ number_format($purchases->sum('gst_amount'), 2) }}</td>
                        <td>MVR {{ number_format($purchases->sum('total_amount'), 2) }}</td>
                        <td></td>
                    </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var rate = {{ $gstRate }};
        var exclInput = document.getElementById('amountExclGst');
        var gstInput = document.getElementById('gstAmount');

        exclInput.addEventListener('input', function () {
            if (!gstInput.dataset.touched) {
                var value = parseFloat(exclInput.value) || 0;
                gstInput.value = (value * rate / 100).toFixed(2);
            }
        });

        gstInput.addEventListener('input', function () {
            gstInput.dataset.touched = 'true';
        });
    });
</script>

</body>
</html>
