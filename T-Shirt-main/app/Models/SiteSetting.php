<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasFactory;

    protected $table = 'site_settings';

    protected $fillable = [
        'store_name',
        'admin_header_title',
        'tagline',
        'store_logo',
        'favicon',
        'show_store_logo',
        'show_store_name',
        'show_footer_logo',
        'show_footer_social',
        'show_footer_contact',
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
    ];

    protected $casts = [
        'show_store_logo' => 'boolean',
        'show_store_name' => 'boolean',
        'show_footer_logo' => 'boolean',
        'show_footer_social' => 'boolean',
        'show_footer_contact' => 'boolean',
    ];

    /**
     * Get the full URL of the store logo.
     */
    public function logoUrl(): ?string
    {
        return $this->store_logo ? asset('storage/' . $this->store_logo) : null;
    }

    /**
     * Get the full URL of the favicon.
     */
    public function faviconUrl(): ?string
    {
        return $this->favicon ? asset('storage/' . $this->favicon) : null;
    }
}
