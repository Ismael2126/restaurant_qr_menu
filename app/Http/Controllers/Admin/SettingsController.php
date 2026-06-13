<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AuditHelper;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        $settings = [
            'restaurant_name' => Setting::get('restaurant_name', ''),
            'restaurant_tin' => Setting::get('restaurant_tin', ''),
            'gst_rate' => Setting::get('gst_rate', '8'),
        ];

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'restaurant_name' => 'required|string|max:150',
            'restaurant_tin' => 'nullable|string|max:50',
            'gst_rate' => 'required|numeric|min:0|max:100',
        ]);

        Setting::set('restaurant_name', $validated['restaurant_name']);
        Setting::set('restaurant_tin', $validated['restaurant_tin'] ?? '');
        Setting::set('gst_rate', (string) $validated['gst_rate']);

        AuditHelper::log('Update', 'Settings', 'Updated restaurant settings (GST rate: ' . $validated['gst_rate'] . '%)');

        return back()->with('success', 'Settings updated successfully.');
    }
}
