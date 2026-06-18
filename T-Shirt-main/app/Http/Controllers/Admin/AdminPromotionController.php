<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromotionBanner;
use Illuminate\Http\Request;

class AdminPromotionController extends Controller
{
    public function index()
    {
        $promotions = PromotionBanner::orderBy('display_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.promotions.index', compact('promotions'));
    }

    public function create()
    {
        return view('admin.promotions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'heading'          => 'required|string|max:255',
            'subtitle'         => 'nullable|string|max:1000',
            'coupon_code'      => 'nullable|string|max:50',
            'button_text'      => 'required|string|max:100',
            'button_link'      => 'required|string|max:255',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'gradient_color_1' => 'required|string|max:20',
            'gradient_color_2' => 'required|string|max:20',
            'start_date'       => 'nullable|date',
            'expiry_date'      => 'nullable|date|after_or_equal:start_date',
            'display_order'    => 'required|integer|min:0',
        ]);

        $imagePath = null;
        if ($request->hasFile('background_image')) {
            $imagePath = $this->uploadImage($request->file('background_image'));
        }

        PromotionBanner::create([
            'title'            => $validated['title'],
            'heading'          => $validated['heading'],
            'subtitle'         => $validated['subtitle'] ?? null,
            'coupon_code'      => $validated['coupon_code'] ?? null,
            'button_text'      => $validated['button_text'],
            'button_link'      => $validated['button_link'],
            'background_image' => $imagePath,
            'gradient_color_1' => $validated['gradient_color_1'],
            'gradient_color_2' => $validated['gradient_color_2'],
            'is_active'        => $request->has('is_active'),
            'start_date'       => $validated['start_date'] ?? null,
            'expiry_date'      => $validated['expiry_date'] ?? null,
            'display_order'    => $validated['display_order'],
        ]);

        return redirect()->route('admin.promotions.index')
            ->with('success', '🎉 Promotion offer created successfully!');
    }

    public function edit($id)
    {
        $promotion = PromotionBanner::findOrFail($id);
        return view('admin.promotions.edit', compact('promotion'));
    }

    public function update(Request $request, $id)
    {
        $promotion = PromotionBanner::findOrFail($id);

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'heading'          => 'required|string|max:255',
            'subtitle'         => 'nullable|string|max:1000',
            'coupon_code'      => 'nullable|string|max:50',
            'button_text'      => 'required|string|max:100',
            'button_link'      => 'required|string|max:255',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'gradient_color_1' => 'required|string|max:20',
            'gradient_color_2' => 'required|string|max:20',
            'start_date'       => 'nullable|date',
            'expiry_date'      => 'nullable|date|after_or_equal:start_date',
            'display_order'    => 'required|integer|min:0',
        ]);

        if ($request->hasFile('background_image')) {
            // Remove old image
            if ($promotion->background_image && file_exists(public_path($promotion->background_image))) {
                @unlink(public_path($promotion->background_image));
            }
            $validated['background_image'] = $this->uploadImage($request->file('background_image'));
        }

        $promotion->update([
            'title'            => $validated['title'],
            'heading'          => $validated['heading'],
            'subtitle'         => $validated['subtitle'] ?? null,
            'coupon_code'      => $validated['coupon_code'] ?? null,
            'button_text'      => $validated['button_text'],
            'button_link'      => $validated['button_link'],
            'background_image' => $validated['background_image'] ?? $promotion->background_image,
            'gradient_color_1' => $validated['gradient_color_1'],
            'gradient_color_2' => $validated['gradient_color_2'],
            'is_active'        => $request->has('is_active'),
            'start_date'       => $validated['start_date'] ?? null,
            'expiry_date'      => $validated['expiry_date'] ?? null,
            'display_order'    => $validated['display_order'],
        ]);

        return redirect()->route('admin.promotions.index')
            ->with('success', '✅ Promotion offer updated successfully!');
    }

    public function destroy($id)
    {
        $promotion = PromotionBanner::findOrFail($id);

        if ($promotion->background_image && file_exists(public_path($promotion->background_image))) {
            @unlink(public_path($promotion->background_image));
        }

        $promotion->delete();

        return redirect()->route('admin.promotions.index')
            ->with('success', 'Promotion offer deleted.');
    }

    /**
     * Quick toggle active/inactive via AJAX or redirect.
     */
    public function toggleActive($id)
    {
        $promotion = PromotionBanner::findOrFail($id);
        $promotion->is_active = !$promotion->is_active;
        $promotion->save();

        $status = $promotion->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.promotions.index')
            ->with('success', "Promotion offer {$status}.");
    }

    /**
     * Preview a promotion (shows the rendered frontend section).
     */
    public function preview($id)
    {
        $promotion = PromotionBanner::findOrFail($id);
        return view('admin.promotions.preview', compact('promotion'));
    }

    /**
     * Upload image helper.
     */
    private function uploadImage($file): string
    {
        $name = time() . '_' . $file->getClientOriginalName();
        $dest = public_path('images/promotions');

        if (!file_exists($dest)) {
            mkdir($dest, 0755, true);
        }

        $file->move($dest, $name);
        return 'images/promotions/' . $name;
    }
}
