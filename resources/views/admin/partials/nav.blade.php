@php
    $role = auth()->user()->role;
    $current = $current ?? null;
@endphp

@if($role === 'admin' && $current !== 'menu')
    <a href="{{ route('admin.menu.index') }}" class="btn btn-secondary">Menu Admin</a>
@endif

@if($current !== 'orders')
    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Orders</a>
@endif

@if(in_array($role, ['admin', 'cashier']))
    @if($current !== 'purchases')
        <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary">Purchases</a>
    @endif

    @if($current !== 'reports')
        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">Reports</a>
    @endif
@endif

@if($role === 'admin')
    @if($current !== 'audit')
        <a href="{{ route('admin.audit.index') }}" class="btn btn-secondary">Audit Logs</a>
    @endif

    @if($current !== 'users')
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Staff</a>
    @endif

    @if($current !== 'settings')
        <a href="{{ route('admin.settings.edit') }}" class="btn btn-secondary">Settings</a>
    @endif
@endif

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="btn btn-danger">Logout</button>
</form>
