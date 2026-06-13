<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Purchase</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/admin-purchases.css') }}">
</head>
<body>

<div class="page">
    <div class="header">
        <div>
            <h1>Edit Purchase</h1>
            <p>Update the vendor bill details for invoice {{ $purchase->invoice_number }}.</p>
        </div>

        <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary">Back to Purchases</a>
    </div>

    @if($errors->any())
        <div class="error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card">
        <h2>Purchase Details</h2>

        <form method="POST" action="{{ route('admin.purchases.update', $purchase) }}" class="form-row">
            @csrf
            @method('PUT')

            <div>
                <label>Date</label>
                <input type="date" name="purchase_date" value="{{ old('purchase_date', $purchase->purchase_date->toDateString()) }}" required>
            </div>

            <div>
                <label>Vendor Name</label>
                <input type="text" name="vendor_name" value="{{ old('vendor_name', $purchase->vendor_name) }}" required>
            </div>

            <div>
                <label>Vendor TIN</label>
                <input type="text" name="vendor_tin" value="{{ old('vendor_tin', $purchase->vendor_tin) }}">
            </div>

            <div>
                <label>Invoice Number</label>
                <input type="text" name="invoice_number" value="{{ old('invoice_number', $purchase->invoice_number) }}" required>
            </div>

            <div>
                <label>Amount Excl. GST (MVR)</label>
                <input type="number" step="0.01" min="0" name="amount_excl_gst" value="{{ old('amount_excl_gst', $purchase->amount_excl_gst) }}" required>
            </div>

            <div>
                <label>GST Amount (MVR)</label>
                <input type="number" step="0.01" min="0" name="gst_amount" value="{{ old('gst_amount', $purchase->gst_amount) }}" required>
            </div>

            <div class="span-2">
                <label>Notes</label>
                <textarea name="notes" placeholder="Optional notes">{{ old('notes', $purchase->notes) }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
