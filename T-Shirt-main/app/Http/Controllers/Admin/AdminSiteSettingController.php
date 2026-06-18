<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\FooterLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AdminSiteSettingController extends Controller
{
    /**
     * Display the Site Settings admin page.
     */
    public function index()
    {
        $settings = SiteSetting::firstOrCreate([]);
        $links = FooterLink::orderBy('sort_order')->get();

        return view('admin.site-settings.index', compact('settings', 'links'));
    }

    /**
     * Update the Site Settings configuration.
     */
    public function update(Request $request)
    {
        $settings = SiteSetting::firstOrCreate([]);

        $request->validate([
            'store_name' => 'required|string|max:255',
            'admin_header_title' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            
            'store_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'favicon' => [
                'nullable',
                'file',
                'mimes:png,ico',
                'max:2048',
            ],
            
            'footer_title' => 'nullable|string|max:255',
            'footer_description' => 'nullable|string|max:1000',
            'copyright_text' => 'nullable|string|max:255',

            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',

            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'whatsapp_url' => 'nullable|url|max:255',
        ], [
            'store_logo.max' => 'The logo must not exceed 2 MB.',
            'store_logo.image' => 'The logo must be an image file.',
            'favicon.mimes' => 'Only PNG and ICO files are allowed.',
            'favicon.max' => 'Favicon must not exceed 2 MB.',
        ]);

        // Capture all fillable inputs
        $settings->fill($request->only([
            'store_name',
            'admin_header_title',
            'tagline',
            'footer_title',
            'footer_description',
            'copyright_text',
            'phone',
            'email',
            'address',
            'facebook_url',
            'instagram_url',
            'twitter_url',
            'youtube_url',
            'linkedin_url',
            'whatsapp_url',
        ]));

        // Handle checkboxes/toggles
        $settings->show_store_logo = $request->boolean('show_store_logo');
        $settings->show_store_name = $request->boolean('show_store_name');
        $settings->show_footer_logo = $request->boolean('show_footer_logo');
        $settings->show_footer_social = $request->boolean('show_footer_social');
        $settings->show_footer_contact = $request->boolean('show_footer_contact');

        // Handle Store Logo Upload
        if ($request->hasFile('store_logo')) {
            if ($settings->store_logo) {
                Storage::disk('public')->delete($settings->store_logo);
            }
            $settings->store_logo = $request->file('store_logo')->store('site-settings', 'public');
        }

        // Handle Favicon Upload
        if ($request->hasFile('favicon')) {
            if ($settings->favicon) {
                Storage::disk('public')->delete($settings->favicon);
            }
            $settings->favicon = $request->file('favicon')->store('site-settings', 'public');
        }

        $settings->save();

        // Clear caching for views
        Cache::forget('site_settings');

        return redirect()->route('admin.site-settings.index')
            ->with('success', 'Site settings updated successfully!');
    }

    /**
     * Store a new footer link.
     */
    public function storeLink(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:100',
            'url' => 'required|string|max:255',
        ]);

        $maxSort = FooterLink::max('sort_order') ?? 0;

        FooterLink::create([
            'text' => $request->text,
            'url' => $request->url,
            'sort_order' => $maxSort + 1,
        ]);

        return redirect()->route('admin.site-settings.index')
            ->with('success', 'Footer link added successfully!');
    }

    /**
     * Update an existing footer link.
     */
    public function updateLink(Request $request, $id)
    {
        $link = FooterLink::findOrFail($id);

        $request->validate([
            'text' => 'required|string|max:100',
            'url' => 'required|string|max:255',
        ]);

        $link->update([
            'text' => $request->text,
            'url' => $request->url,
        ]);

        return redirect()->route('admin.site-settings.index')
            ->with('success', 'Footer link updated successfully!');
    }

    /**
     * Delete a footer link.
     */
    public function destroyLink($id)
    {
        $link = FooterLink::findOrFail($id);
        $link->delete();

        return redirect()->route('admin.site-settings.index')
            ->with('success', 'Footer link deleted successfully!');
    }

    /**
     * Reorder footer links via AJAX.
     */
    public function reorderLinks(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:footer_links,id',
        ]);

        foreach ($request->ids as $idx => $id) {
            FooterLink::where('id', $id)->update(['sort_order' => $idx]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Footer links reordered successfully.'
        ]);
    }
}
