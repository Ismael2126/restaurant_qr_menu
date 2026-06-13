<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="{{ asset('css/admin-reports.css') }}">
</head>
<body>

<div class="page">
    <div class="header">
        <div>
            <h1>Reports</h1>
            <p>Sales summary and GST output/input tax for MIRA filing.</p>
        </div>

        <div class="header-actions">
            @include('admin.partials.nav', ['current' => 'reports'])
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="filter-form">
            <div>
                <label>Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}">
            </div>

            <div>
                <label>End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}">
            </div>

            <button type="submit">Run Report</button>
        </form>

        @php
            $today = now()->toDateString();
            $monthStart = now()->startOfMonth()->toDateString();
            $monthEnd = now()->endOfMonth()->toDateString();
            $lastMonth = now()->startOfMonth()->subMonth();
            $lastMonthStart = $lastMonth->toDateString();
            $lastMonthEnd = $lastMonth->copy()->endOfMonth()->toDateString();
            $yearStart = now()->startOfYear()->toDateString();
            $yearEnd = now()->endOfYear()->toDateString();
        @endphp

        <div class="preset-links">
            <a class="btn btn-small btn-secondary" href="{{ route('admin.reports.index', ['start_date' => $today, 'end_date' => $today]) }}">Today</a>
            <a class="btn btn-small btn-secondary" href="{{ route('admin.reports.index', ['start_date' => $monthStart, 'end_date' => $monthEnd]) }}">This Month</a>
            <a class="btn btn-small btn-secondary" href="{{ route('admin.reports.index', ['start_date' => $lastMonthStart, 'end_date' => $lastMonthEnd]) }}">Last Month</a>
            <a class="btn btn-small btn-secondary" href="{{ route('admin.reports.index', ['start_date' => $yearStart, 'end_date' => $yearEnd]) }}">This Year</a>
        </div>

        <p class="note">Showing {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} &mdash; {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}. GST rate: {{ $gstRate }}%.</p>
    </div>

    <div class="card">
        <h2 class="section-title">Sales Summary</h2>

        <div class="summary-grid">
            <div class="stat-card">
                <div class="stat-label">Orders</div>
                <div class="stat-value">{{ $orders->count() }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Total Sales (incl. GST)</div>
                <div class="stat-value">MVR {{ number_format($salesTotal, 2) }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Sales Excl. GST</div>
                <div class="stat-value">MVR {{ number_format($salesExclGst, 2) }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Output GST</div>
                <div class="stat-value">MVR {{ number_format($outputGst, 2) }}</div>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Orders</th>
                    <th>Total Sales</th>
                </tr>
                </thead>
                <tbody>
                @forelse($dailySales as $day)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($day['date'])->format('d M Y') }}</td>
                        <td>{{ $day['orders'] }}</td>
                        <td>MVR {{ number_format($day['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No sales in this date range.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header-row">
            <h2 class="section-title">GST Output Tax (Sales)</h2>
            <a class="btn btn-small btn-secondary" href="{{ route('admin.reports.output-tax.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}">Export CSV</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Vendor</th>
                    <th>TIN Number</th>
                    <th>Invoice Number</th>
                    <th>Excl. GST</th>
                    <th>GST Amount</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                @forelse($orders as $order)
                    @php
                        $exclGst = round($order->total_amount / (1 + $gstRate / 100), 2);
                        $gstAmount = round($order->total_amount - $exclGst, 2);
                    @endphp
                    <tr>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td>{{ $restaurantName }}</td>
                        <td>{{ $restaurantTin ?: '—' }}</td>
                        <td>{{ $order->order_number }}</td>
                        <td>MVR {{ number_format($exclGst, 2) }}</td>
                        <td>MVR {{ number_format($gstAmount, 2) }}</td>
                        <td>MVR {{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No sales in this date range.</td>
                    </tr>
                @endforelse
                </tbody>
                @if($orders->isNotEmpty())
                    <tfoot>
                    <tr>
                        <td colspan="4">Totals</td>
                        <td>MVR {{ number_format($salesExclGst, 2) }}</td>
                        <td>MVR {{ number_format($outputGst, 2) }}</td>
                        <td>MVR {{ number_format($salesTotal, 2) }}</td>
                    </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header-row">
            <h2 class="section-title">GST Input Tax (Purchases)</h2>
            <a class="btn btn-small btn-secondary" href="{{ route('admin.reports.input-tax.export', ['start_date' => $startDate, 'end_date' => $endDate]) }}">Export CSV</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Vendor</th>
                    <th>TIN Number</th>
                    <th>Invoice Number</th>
                    <th>Excl. GST</th>
                    <th>GST Amount</th>
                    <th>Total</th>
                </tr>
                </thead>
                <tbody>
                @forelse($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->purchase_date->format('d M Y') }}</td>
                        <td>{{ $purchase->vendor_name }}</td>
                        <td>{{ $purchase->vendor_tin ?: '—' }}</td>
                        <td>{{ $purchase->invoice_number }}</td>
                        <td>MVR {{ number_format($purchase->amount_excl_gst, 2) }}</td>
                        <td>MVR {{ number_format($purchase->gst_amount, 2) }}</td>
                        <td>MVR {{ number_format($purchase->total_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">No purchases in this date range.</td>
                    </tr>
                @endforelse
                </tbody>
                @if($purchases->isNotEmpty())
                    <tfoot>
                    <tr>
                        <td colspan="4">Totals</td>
                        <td>MVR {{ number_format($purchasesExclGst, 2) }}</td>
                        <td>MVR {{ number_format($inputGst, 2) }}</td>
                        <td>MVR {{ number_format($purchasesTotal, 2) }}</td>
                    </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="card">
        <h2 class="section-title">Net GST Summary</h2>

        <div class="summary-grid summary-grid-3">
            <div class="stat-card">
                <div class="stat-label">Output GST (Sales)</div>
                <div class="stat-value">MVR {{ number_format($outputGst, 2) }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Input GST (Purchases)</div>
                <div class="stat-value">MVR {{ number_format($inputGst, 2) }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">{{ $netGst >= 0 ? 'Net GST Payable' : 'Net GST Refundable' }}</div>
                <div class="stat-value {{ $netGst >= 0 ? 'amount-due' : 'amount-credit' }}">MVR {{ number_format(abs($netGst), 2) }}</div>
            </div>
        </div>

        <p class="note">Net GST Payable = Output GST &minus; Input GST for the selected period &mdash; the figure typically reported to MIRA.</p>
    </div>
</div>

</body>
</html>
