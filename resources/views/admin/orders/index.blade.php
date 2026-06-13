<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Restaurant Orders</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('css/admin-orders.css') }}">
</head>
<body data-data-url="{{ route('admin.orders.data') }}">

<div class="page">
    <div class="header">
        <div>
            <h1>Kitchen Orders</h1>
            <p>Live order dashboard &mdash; refreshes automatically.</p>
        </div>

        <div class="header-actions">
            <a href="{{ route('admin.menu.index') }}" class="btn btn-secondary">Menu Admin</a>
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

    <div class="toolbar">
        <div class="filter-tabs" id="filterTabs">
            <button type="button" class="filter-tab is-active" data-filter="all">All <span class="count" data-count="all">0</span></button>
            <button type="button" class="filter-tab" data-filter="new">New <span class="count" data-count="new">0</span></button>
            <button type="button" class="filter-tab" data-filter="preparing">Preparing <span class="count" data-count="preparing">0</span></button>
            <button type="button" class="filter-tab" data-filter="ready">Ready <span class="count" data-count="ready">0</span></button>
            <button type="button" class="filter-tab" data-filter="completed">Completed <span class="count" data-count="completed">0</span></button>
            <button type="button" class="filter-tab" data-filter="cancelled">Cancelled <span class="count" data-count="cancelled">0</span></button>
        </div>

        <div class="toolbar-controls">
            <span class="live-status" id="liveStatus">
                <span class="live-dot"></span>
                <span id="liveStatusText">Live</span>
            </span>

            <button type="button" id="soundToggle" class="toggle-btn is-on">Sound: On</button>
            <button type="button" id="refreshToggle" class="toggle-btn is-on">Auto-refresh: On</button>
            <button type="button" id="refreshNow" class="toggle-btn">Refresh Now</button>
        </div>
    </div>

    <div class="toast-container" id="toastContainer"></div>

    <div class="orders-grid" id="ordersGrid">
        @forelse($orders as $order)
            <div class="order-card status-{{ $order->status }}"
                 data-order-id="{{ $order->id }}"
                 data-status="{{ $order->status }}"
                 data-created-at="{{ $order->created_at->toIso8601String() }}">

                <div class="order-top">
                    <div>
                        <h2>{{ $order->table?->table_name ?? 'Unknown Table' }}</h2>
                        <p>{{ $order->order_number }}</p>
                    </div>

                    <span class="status-badge badge-{{ $order->status }}" data-status-badge>{{ strtoupper(\App\Models\Order::statusLabel($order->status)) }}</span>
                </div>

                <div class="order-meta-row">
                    <span class="order-time">{{ $order->created_at->format('d M Y - h:i A') }}</span>
                    <span class="order-elapsed" data-elapsed data-created-at="{{ $order->created_at->toIso8601String() }}">just now</span>
                </div>

                <div class="items-list">
                    @foreach($order->items as $item)
                        <div class="item-row">
                            <div class="item-main">
                                <span class="item-qty">{{ $item->quantity }}&times;</span>
                                <span class="item-text">
                                    <span class="item-name">{{ $item->item_name }}</span>
                                    <span class="item-unit">MVR {{ number_format($item->unit_price, 2) }} each</span>
                                </span>
                            </div>

                            <b class="item-total">MVR {{ number_format($item->line_total, 2) }}</b>
                        </div>
                    @endforeach
                </div>

                @if($order->customer_note)
                    <div class="note-box" data-note-box>
                        <strong>Note:</strong> <span data-note-text>{{ $order->customer_note }}</span>
                    </div>
                @else
                    <div class="note-box" data-note-box style="display:none;">
                        <strong>Note:</strong> <span data-note-text></span>
                    </div>
                @endif

                <div class="total-row">
                    <span>Total</span>
                    <strong>MVR {{ number_format($order->total_amount, 2) }}</strong>
                </div>

                <div class="card-actions">
                    @php $nextStatus = \App\Models\Order::nextStatus($order->status); @endphp

                    <button type="button"
                            class="btn-next-status"
                            data-next-status-btn
                            data-order-id="{{ $order->id }}"
                            data-next-status="{{ $nextStatus }}"
                            style="{{ $nextStatus ? '' : 'display:none;' }}">
                        {{ \App\Models\Order::nextStatusLabel($order->status) }}
                    </button>

                    <div class="status-form-row">
                        <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="status-form" data-status-form data-order-id="{{ $order->id }}">
                            @csrf

                            <select name="status" data-status-select>
                                <option value="new" {{ $order->status === 'new' ? 'selected' : '' }}>New</option>
                                <option value="preparing" {{ $order->status === 'preparing' ? 'selected' : '' }}>Preparing</option>
                                <option value="ready" {{ $order->status === 'ready' ? 'selected' : '' }}>Ready</option>
                                <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>

                            <button type="submit" class="btn-update">Update</button>
                        </form>

                        <a href="{{ route('admin.orders.ticket', $order) }}" target="_blank" class="btn btn-ticket" data-ticket-link>Print Ticket</a>
                    </div>
                </div>
            </div>
        @empty
        @endforelse
    </div>

    <div class="empty" id="emptyState" style="{{ $orders->isEmpty() ? '' : 'display:none;' }}">
        No orders yet.
    </div>
</div>

<script src="{{ asset('js/admin-orders.js') }}"></script>
</body>
</html>
