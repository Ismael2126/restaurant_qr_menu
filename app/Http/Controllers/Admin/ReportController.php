<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Setting;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->loadReportData($request);

        $orders = $data['orders'];
        $purchases = $data['purchases'];
        $gstRate = $data['gstRate'];

        $salesTotal = (float) $orders->sum('total_amount');
        $salesExclGst = round($salesTotal / (1 + $gstRate / 100), 2);
        $outputGst = round($salesTotal - $salesExclGst, 2);

        $dailySales = $orders
            ->groupBy(fn ($order) => $order->created_at->format('Y-m-d'))
            ->map(fn ($group, $date) => [
                'date' => $date,
                'orders' => $group->count(),
                'total' => (float) $group->sum('total_amount'),
            ])
            ->sortKeys()
            ->values();

        $purchasesExclGst = (float) $purchases->sum('amount_excl_gst');
        $inputGst = (float) $purchases->sum('gst_amount');
        $purchasesTotal = (float) $purchases->sum('total_amount');

        $netGst = round($outputGst - $inputGst, 2);

        return view('admin.reports.index', [
            ...$data,
            'salesTotal' => $salesTotal,
            'salesExclGst' => $salesExclGst,
            'outputGst' => $outputGst,
            'dailySales' => $dailySales,
            'purchasesExclGst' => $purchasesExclGst,
            'inputGst' => $inputGst,
            'purchasesTotal' => $purchasesTotal,
            'netGst' => $netGst,
        ]);
    }

    public function exportOutputTax(Request $request): StreamedResponse
    {
        $data = $this->loadReportData($request);

        $filename = 'gst-output-tax-' . $data['startDate'] . '-to-' . $data['endDate'] . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Vendor', 'TIN Number', 'Invoice Number', 'Amount Excl. GST', 'GST Amount', 'Total']);

            foreach ($data['orders'] as $order) {
                $exclGst = round($order->total_amount / (1 + $data['gstRate'] / 100), 2);
                $gstAmount = round($order->total_amount - $exclGst, 2);

                fputcsv($handle, [
                    $order->created_at->format('Y-m-d'),
                    $data['restaurantName'],
                    $data['restaurantTin'],
                    $order->order_number,
                    number_format($exclGst, 2, '.', ''),
                    number_format($gstAmount, 2, '.', ''),
                    number_format($order->total_amount, 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportInputTax(Request $request): StreamedResponse
    {
        $data = $this->loadReportData($request);

        $filename = 'gst-input-tax-' . $data['startDate'] . '-to-' . $data['endDate'] . '.csv';

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Vendor', 'TIN Number', 'Invoice Number', 'Amount Excl. GST', 'GST Amount', 'Total']);

            foreach ($data['purchases'] as $purchase) {
                fputcsv($handle, [
                    $purchase->purchase_date->format('Y-m-d'),
                    $purchase->vendor_name,
                    $purchase->vendor_tin,
                    $purchase->invoice_number,
                    number_format($purchase->amount_excl_gst, 2, '.', ''),
                    number_format($purchase->gst_amount, 2, '.', ''),
                    number_format($purchase->total_amount, 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function loadReportData(Request $request): array
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $orders = Order::where('status', '!=', 'cancelled')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at')
            ->get();

        $purchases = Purchase::whereDate('purchase_date', '>=', $startDate)
            ->whereDate('purchase_date', '<=', $endDate)
            ->orderBy('purchase_date')
            ->get();

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'gstRate' => (float) Setting::get('gst_rate', '8'),
            'restaurantName' => Setting::get('restaurant_name', 'Restaurant'),
            'restaurantTin' => Setting::get('restaurant_tin', ''),
            'orders' => $orders,
            'purchases' => $purchases,
        ];
    }
}
