<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Restaurant Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/admin-settings.css') }}">
</head>
<body>

<div class="page">
    <div class="header">
        <div>
            <h1>Restaurant Settings</h1>
            <p>Business details used on GST reports filed with MIRA.</p>
        </div>

        <div class="header-actions">
            @include('admin.partials.nav', ['current' => 'settings'])
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
        <h2>Business Details</h2>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="form-stack">
            @csrf
            @method('PUT')

            <div>
                <label>Restaurant Name</label>
                <input type="text" name="restaurant_name" value="{{ old('restaurant_name', $settings['restaurant_name']) }}" placeholder="Your Restaurant Name" required>
            </div>

            <div>
                <label>TIN Number</label>
                <input type="text" name="restaurant_tin" value="{{ old('restaurant_tin', $settings['restaurant_tin']) }}" placeholder="GST/TIN registration number">
            </div>

            <div>
                <label>GST Rate (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="gst_rate" value="{{ old('gst_rate', $settings['gst_rate']) }}" required>
                <p class="note">Used to split GST out of sales totals on the Reports page (e.g. 8 for standard GST, 16 for Tourism GST).</p>
            </div>

            <button type="submit">Save Settings</button>
        </form>
    </div>
</div>

</body>
</html>
