<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Set Your Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

<div class="login-page">
    <div class="login-card">
        <div class="brand-badge">Restaurant QR Menu</div>

        <h1>Set Your Password</h1>
        <p>For security, please choose a new password before continuing.</p>

        @if($errors->any())
            <div class="error-box">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <label>New Password</label>
            <input type="password" name="password" placeholder="Min. 8 characters" required>

            <label>Confirm New Password</label>
            <input type="password" name="password_confirmation" placeholder="Re-enter new password" required>

            <button type="submit">Save Password</button>
        </form>
    </div>
</div>

</body>
</html>
