<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Restaurant Orders</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/admin-orders.css') }}">
</head>
<body>

<div class="page">
    <div class="header-actions">
    <a href="{{ route('admin.menu.index') }}" class="btn btn-secondary">Menu Admin</a>
    <a href="{{ route('admin.audit.index') }}" class="btn btn-secondary">Audit Logs</a>
    <a href="{{ route('admin.orders.index') }}" class="btn">Refresh</a>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-danger">Logout</button>
    </form>
</div>

    @if(session('success'))
        <div class="alert">{{ session('success') }}</div>
    @endif

    <div class="orders-grid">
        @forelse($orders as $order)
            <div class="order-card status-{{ $order->status }}">
                <div class="order-top">
                    <div>
                        <h2>{{ $order->table?->table_name ?? 'Unknown Table' }}</h2>
                        <p>{{ $order->order_number }}</p>
                    </div>

                    <span class="status-badge">{{ strtoupper($order->status) }}</span>
                </div>

                <div class="order-time">
                    {{ $order->created_at->format('d M Y - h:i A') }}
                </div>

                <div class="items-list">
                    @foreach($order->items as $item)
                        <div class="item-row">
                            <div>
                                <strong>{{ $item->quantity }} × {{ $item->item_name }}</strong>
                                <span>MVR {{ number_format($item->unit_price, 2) }} each</span>
                            </div>

                            <b>MVR {{ number_format($item->line_total, 2) }}</b>
                        </div>
                    @endforeach
                </div>

                @if($order->customer_note)
                    <div class="note-box">
                        <strong>Note:</strong> {{ $order->customer_note }}
                    </div>
                @endif

                <div class="total-row">
                    <span>Total</span>
                    <strong>MVR {{ number_format($order->total_amount, 2) }}</strong>
                </div>

                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="status-form">
                    @csrf

                    <label>Update Status</label>
                    <select name="status">
                        <option value="new" {{ $order->status === 'new' ? 'selected' : '' }}>New</option>
                        <option value="preparing" {{ $order->status === 'preparing' ? 'selected' : '' }}>Preparing</option>
                        <option value="ready" {{ $order->status === 'ready' ? 'selected' : '' }}>Ready</option>
                        <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>

                    <button type="submit">Save Status</button>
                </form>
            </div>
        @empty
            <div class="empty">
                No orders yet.
            </div>
        @endforelse
    </div>
</div>

</body>
</html>