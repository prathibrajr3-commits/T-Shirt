<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingSetting;
use Illuminate\Http\Request;

class AdminShippingController extends Controller
{
    public function edit()
    {
        $settings = ShippingSetting::getSettings();
        return view('admin.shipping.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'estimated_shipping'      => 'required|numeric|min:0',
            'free_shipping_minimum'   => 'required|numeric|min:0',
            'shipping_type'           => 'required|in:fixed,free_above_threshold',
            'custom_message'          => 'required|string|max:255',
            'free_shipping_message'   => 'required|string|max:255',
            'icon_class'              => 'required|string|max:100',
            'background_color'        => 'required|string|max:50',
            'text_color'              => 'required|string|max:50',
            'border_radius'           => 'required|string|max:20',
            'is_active'               => 'sometimes|boolean',
            'show_free_shipping_promo' => 'sometimes|boolean',
        ]);

        // Checkboxes — missing means false
        $validated['is_active']               = $request->boolean('is_active');
        $validated['show_free_shipping_promo'] = $request->boolean('show_free_shipping_promo');

        $settings = ShippingSetting::first();

        if ($settings) {
            $settings->update($validated);
        } else {
            ShippingSetting::create($validated);
        }

        return redirect()->route('admin.shipping.edit')
            ->with('success', 'Shipping settings updated successfully!');
    }
}
