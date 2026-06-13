<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Staff Account</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
</head>
<body>

<div class="page">
    <div class="header">
        <div>
            <h1>Edit Staff Account</h1>
            <p>Update name, email, role, or reset the password for {{ $user->name }}.</p>
        </div>

        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Back to Staff</a>
    </div>

    @if($errors->any())
        <div class="error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card">
        <h2>Edit Details</h2>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="form-row">
            @csrf
            @method('PUT')

            <div>
                <label>Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </div>

            <div>
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>

            <div>
                <label>New Password</label>
                <input type="password" name="password" placeholder="Leave blank to keep current password">
            </div>

            <div>
                <label>Role</label>
                <select name="role" required>
                    @foreach(\App\Models\User::ROLES as $value => $label)
                        <option value="{{ $value }}" {{ old('role', $user->role) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-actions">
                <button type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
