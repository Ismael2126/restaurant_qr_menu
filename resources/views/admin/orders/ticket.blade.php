<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ticket - {{ $order->order_number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/order-ticket.css') }}">
</head>
<body>

<div class="top-actions no-print">
    <a href="{{ route('admin.orders.index') }}" class="btn">Back</a>
    <button onclick="window.print()" class="btn btn-print">Print</button>
</div>

<div class="ticket">
    <div class="ticket-header">
        <div class="brand">Restaurant QR Menu</div>
        <div class="ticket-title">Kitchen Ticket</div>
    </div>

    <div class="ticket-line ticket-dashed"></div>

    <div class="ticket-row">
        <span>Table</span>
        <strong>{{ $order->table?->table_name ?? 'Unknown Table' }}</strong>
    </div>

    <div class="ticket-row">
        <span>Order #</span>
        <strong>{{ $order->order_number }}</strong>
    </div>

    <div class="ticket-row">
        <span>Date</span>
        <strong>{{ $order->created_at->format('d M Y - h:i A') }}</strong>
    </div>

    <div class="ticket-line ticket-dashed"></div>

    <div class="ticket-items">
        @foreach($order->items as $item)
            <div class="ticket-item">
                <div class="ticket-item-main">
                    <span class="ticket-item-qty">{{ $item->quantity }}&times;</span>
                    <span class="ticket-item-name">{{ $item->item_name }}</span>
                </div>
                <div class="ticket-item-total">MVR {{ number_format($item->line_total, 2) }}</div>
            </div>
        @endforeach
    </div>

    <div class="ticket-line ticket-dashed"></div>

    @if($order->customer_note)
        <div class="ticket-note">
            <strong>Note:</strong> {{ $order->customer_note }}
        </div>

        <div class="ticket-line ticket-dashed"></div>
    @endif

    <div class="ticket-row ticket-total">
        <span>Total</span>
        <strong>MVR {{ number_format($order->total_amount, 2) }}</strong>
    </div>

    <div class="ticket-footer">
        Status: {{ strtoupper(\App\Models\Order::statusLabel($order->status)) }}
    </div>
</div>

</body>
</html>
