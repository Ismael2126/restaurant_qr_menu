<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AuditHelper;
use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Setting;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Purchase::query();

        if ($startDate) {
            $query->whereDate('purchase_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('purchase_date', '<=', $endDate);
        }

        $purchases = $query->orderBy('purchase_date', 'desc')->orderBy('id', 'desc')->get();

        $gstRate = (float) Setting::get('gst_rate', '8');

        return view('admin.purchases.index', compact('purchases', 'startDate', 'endDate', 'gstRate'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePurchase($request);

        $purchase = Purchase::create([
            ...$validated,
            'total_amount' => $validated['amount_excl_gst'] + $validated['gst_amount'],
            'created_by' => $request->user()->id,
        ]);

        AuditHelper::log(
            'Create',
            'Purchase',
            'Recorded purchase from ' . $purchase->vendor_name . ' / Invoice ' . $purchase->invoice_number . ' / Total MVR ' . number_format($purchase->total_amount, 2)
        );

        return back()->with('success', 'Purchase recorded successfully.');
    }

    public function edit(Purchase $purchase)
    {
        $gstRate = (float) Setting::get('gst_rate', '8');

        return view('admin.purchases.edit', compact('purchase', 'gstRate'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $validated = $this->validatePurchase($request);

        $purchase->update([
            ...$validated,
            'total_amount' => $validated['amount_excl_gst'] + $validated['gst_amount'],
        ]);

        AuditHelper::log(
            'Update',
            'Purchase',
            'Updated purchase from ' . $purchase->vendor_name . ' / Invoice ' . $purchase->invoice_number
        );

        return redirect()->route('admin.purchases.index')->with('success', 'Purchase updated successfully.');
    }

    public function destroy(Purchase $purchase)
    {
        $description = $purchase->vendor_name . ' / Invoice ' . $purchase->invoice_number;

        $purchase->delete();

        AuditHelper::log('Delete', 'Purchase', 'Deleted purchase from ' . $description);

        return back()->with('success', 'Purchase deleted.');
    }

    private function validatePurchase(Request $request): array
    {
        return $request->validate([
            'purchase_date' => 'required|date',
            'vendor_name' => 'required|string|max:150',
            'vendor_tin' => 'nullable|string|max:50',
            'invoice_number' => 'required|string|max:100',
            'amount_excl_gst' => 'required|numeric|min:0',
            'gst_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);
    }
}
