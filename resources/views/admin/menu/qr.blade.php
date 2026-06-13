<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QR Code - {{ $restaurantTable->table_name }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/qr-print.css') }}">
</head>
<body>

<div class="page">
    <div class="top-actions">
        <a href="{{ route('admin.menu.index') }}" class="btn">Back</a>
        <button onclick="window.print()" class="btn btn-print">Print QR</button>
    </div>

    <div class="qr-card">
        <div class="brand">Restaurant QR Menu</div>

        <h1>{{ $restaurantTable->table_name }}</h1>
        <p>Scan to view menu</p>

        <div class="qr-box">
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data={{ urlencode($menuUrl) }}"
                alt="QR Code for {{ $restaurantTable->table_name }}"
            >
        </div>

        <div class="table-code">{{ $restaurantTable->table_code }}</div>

        <div class="link-text">
            {{ $menuUrl }}
        </div>
    </div>
</div>

</body>
</html>