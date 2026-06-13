<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Accounts</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/admin-users.css') }}">
</head>
<body>

<div class="page">
    <div class="header">
        <div>
            <h1>Staff Accounts</h1>
            <p>Create and manage admin and kitchen staff logins.</p>
        </div>

        <div class="header-actions">
            <a href="{{ route('admin.menu.index') }}" class="btn btn-secondary">Menu Admin</a>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Orders</a>
            <a href="{{ route('admin.audit.index') }}" class="btn btn-secondary">Audit Logs</a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
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
        <h2>Add Staff Account</h2>

        <form method="POST" action="{{ route('admin.users.store') }}" class="form-row">
            @csrf

            <div>
                <label>Name</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Staff member's name" required>
            </div>

            <div>
                <label>Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required>
            </div>

            <div>
                <label>Password</label>
                <input type="password" name="password" placeholder="Min. 8 characters" required>
            </div>

            <div>
                <label>Role</label>
                <select name="role" required>
                    @foreach(\App\Models\User::ROLES as $value => $label)
                        <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-actions">
                <button type="submit">Add Staff Account</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2 class="section-title">All Staff Accounts</h2>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            {{ $user->name }}
                            @if($user->id === auth()->id())
                                <span class="badge badge-you">You</span>
                            @endif
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge badge-{{ $user->role }}">{{ $user->roleLabel() }}</span>
                        </td>
                        <td>{{ $user->created_at->format('d M Y') }}</td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-small btn-secondary">Edit</a>

                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this staff account?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-small btn-danger" type="submit">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No staff accounts found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
