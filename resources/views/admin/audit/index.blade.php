<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Logs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/admin-audit.css') }}">
</head>
<body>

<div class="page">
    <div class="header">
        <div>
            <h1>Audit Logs</h1>
            <p>Track admin activity, menu changes, table changes, and order updates.</p>
        </div>

        <div class="header-actions">
            @include('admin.partials.nav', ['current' => 'audit'])
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('admin.audit.index') }}" class="filter-form">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search logs...">

            <select name="module">
                <option value="">All Modules</option>
                <option value="Authentication" {{ request('module') === 'Authentication' ? 'selected' : '' }}>Authentication</option>
                <option value="Category" {{ request('module') === 'Category' ? 'selected' : '' }}>Category</option>
                <option value="Menu Item" {{ request('module') === 'Menu Item' ? 'selected' : '' }}>Menu Item</option>
                <option value="Table" {{ request('module') === 'Table' ? 'selected' : '' }}>Table</option>
                <option value="Order" {{ request('module') === 'Order' ? 'selected' : '' }}>Order</option>
                <option value="User" {{ request('module') === 'User' ? 'selected' : '' }}>User</option>
                <option value="Purchase" {{ request('module') === 'Purchase' ? 'selected' : '' }}>Purchase</option>
                <option value="Settings" {{ request('module') === 'Settings' ? 'selected' : '' }}>Settings</option>
            </select>

            <select name="action">
                <option value="">All Actions</option>
                <option value="Login" {{ request('action') === 'Login' ? 'selected' : '' }}>Login</option>
                <option value="Logout" {{ request('action') === 'Logout' ? 'selected' : '' }}>Logout</option>
                <option value="Create" {{ request('action') === 'Create' ? 'selected' : '' }}>Create</option>
                <option value="Update" {{ request('action') === 'Update' ? 'selected' : '' }}>Update</option>
                <option value="Delete" {{ request('action') === 'Delete' ? 'selected' : '' }}>Delete</option>
                <option value="Toggle" {{ request('action') === 'Toggle' ? 'selected' : '' }}>Toggle</option>
            </select>

            <button type="submit">Filter</button>
            <a href="{{ route('admin.audit.index') }}" class="btn btn-secondary">Clear</a>
        </form>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>User</th>
                    <th>Module</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP</th>
                </tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d M Y - h:i A') }}</td>
                        <td>{{ $log->user_name ?? 'System' }}</td>
                        <td><span class="badge">{{ $log->module }}</span></td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->description }}</td>
                        <td>{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No audit logs found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $logs->links() }}
        </div>
    </div>
</div>

</body>
</html>