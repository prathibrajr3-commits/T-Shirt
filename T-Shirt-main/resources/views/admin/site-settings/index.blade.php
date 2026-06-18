@extends('layouts.admin')

@section('title', 'Site Settings')

@section('content')
<div class="d-flex align-items-center mb-4">
    <i class="fa-solid fa-sliders text-primary fs-4 me-3"></i>
    <div>
        <h2 class="brand-font mb-0">Site Settings</h2>
        <p class="text-secondary small mb-0">Manage your store branding, footer info, contact information, and social media presence.</p>
    </div>
</div>

<form action="{{ route('admin.site-settings.update') }}" method="POST" enctype="multipart/form-data" id="settings-form">
    @csrf
    @method('PUT')

    <div class="row g-4">

        {{-- ── LEFT COLUMN: Form Fields ── --}}
        <div class="col-lg-7">

            {{-- 1. Store Branding --}}
            <div class="glass-panel p-4 mb-4">
                <h5 class="brand-font mb-4">
                    <i class="fa-solid fa-pen-nib me-2 text-primary"></i>Store Branding
                </h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="store_name" class="form-label form-label-custom">
                            Store Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="store_name" id="store_name"
                               class="form-control form-control-custom @error('store_name') is-invalid @enderror"
                               value="{{ old('store_name', $settings->store_name) }}"
                               maxlength="100" required placeholder="e.g. AuraWear">
                        @error('store_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="admin_header_title" class="form-label form-label-custom">
                            Admin Header Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="admin_header_title" id="admin_header_title"
                               class="form-control form-control-custom @error('admin_header_title') is-invalid @enderror"
                               value="{{ old('admin_header_title', $settings->admin_header_title) }}"
                               maxlength="100" required placeholder="e.g. AURA ADMIN">
                        @error('admin_header_title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label for="tagline" class="form-label form-label-custom">Tagline</label>
                        <input type="text" name="tagline" id="tagline"
                               class="form-control form-control-custom @error('tagline') is-invalid @enderror"
                               value="{{ old('tagline', $settings->tagline) }}"
                               maxlength="255" placeholder="e.g. Premium Street Style T-Shirts">
                        @error('tagline')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Store Logo Upload --}}
                    <div class="col-12 mt-4 pt-3 border-top border-secondary border-opacity-10">
                        <label class="form-label form-label-custom fw-bold text-white mb-2">Store Logo (Max 2MB)</label>
                        <div class="d-flex align-items-center gap-3 p-3 rounded"
                             style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                            <div class="text-center bg-dark bg-opacity-40 rounded border border-secondary border-opacity-25" style="width: 80px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                @if($settings->store_logo)
                                    <img src="{{ asset('storage/' . $settings->store_logo) }}"
                                         alt="Current Logo" id="logo-preview-image"
                                         style="max-height: 40px; max-width: 70px; object-fit: contain;">
                                @else
                                    <span class="text-secondary small" id="logo-preview-placeholder">No Logo</span>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" name="store_logo" id="store_logo_input"
                                       class="form-control form-control-sm form-control-custom"
                                       accept="image/jpg,image/jpeg,image/png,image/webp,image/svg+xml">
                                <div class="text-secondary small mt-1" style="font-size: 0.75rem;">Recommended: transparent PNG or SVG · Max 2 MB</div>
                            </div>
                        </div>
                        @error('store_logo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Favicon Upload --}}
                    <div class="col-12 mt-3">
                        <label class="form-label form-label-custom fw-bold text-white mb-2">Browser Favicon (Max 2MB)</label>
                        <div class="d-flex align-items-center gap-3 p-3 rounded"
                             style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                            <div class="text-center bg-dark bg-opacity-40 rounded border border-secondary border-opacity-25" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                @if($settings->favicon)
                                    <img src="{{ asset('storage/' . $settings->favicon) }}"
                                         alt="Current Favicon" id="favicon-preview-image"
                                         style="width: 32px; height: 32px; object-fit: contain;">
                                @else
                                    <span class="text-secondary small" id="favicon-preview-placeholder">None</span>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" name="favicon" id="favicon_input"
                                       class="form-control form-control-sm form-control-custom"
                                       accept=".png,.ico,image/png,image/x-icon">
                                <div class="text-secondary small mt-1" style="font-size: 0.75rem;">PNG or ICO format • Max 2 MB • Recommended: 32×32 px</div>
                            </div>
                        </div>
                        @error('favicon')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- 2. Display Options --}}
            <div class="glass-panel p-4 mb-4">
                <h5 class="brand-font mb-4">
                    <i class="fa-solid fa-toggle-on me-2 text-success"></i>Display Options
                </h5>

                {{-- Show Store Logo --}}
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded"
                     style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                    <div>
                        <div class="fw-bold text-white">Show Store Logo</div>
                        <div class="text-secondary small">Display the uploaded logo in the storefront navbar.</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="show_store_logo" value="0">
                        <input class="form-check-input" type="checkbox" name="show_store_logo" id="show_store_logo"
                               role="switch" style="width: 3rem; height: 1.5rem;"
                               {{ $settings->show_store_logo ? 'checked' : '' }}>
                    </div>
                </div>

                {{-- Show Store Name Text --}}
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded"
                     style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                    <div>
                        <div class="fw-bold text-white">Show Store Name Text</div>
                        <div class="text-secondary small">Display the store name text beside or instead of the logo.</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="show_store_name" value="0">
                        <input class="form-check-input" type="checkbox" name="show_store_name" id="show_store_name"
                               role="switch" style="width: 3rem; height: 1.5rem;"
                               {{ $settings->show_store_name ? 'checked' : '' }}>
                    </div>
                </div>

                {{-- Show Footer Logo --}}
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded"
                     style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                    <div>
                        <div class="fw-bold text-white">Show Footer Logo</div>
                        <div class="text-secondary small">Display the logo/brand name in the storefront footer.</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="show_footer_logo" value="0">
                        <input class="form-check-input" type="checkbox" name="show_footer_logo" id="show_footer_logo"
                               role="switch" style="width: 3rem; height: 1.5rem;"
                               {{ $settings->show_footer_logo ? 'checked' : '' }}>
                    </div>
                </div>

                {{-- Show Footer Contact Info --}}
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded"
                     style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                    <div>
                        <div class="fw-bold text-white">Show Footer Contact Info</div>
                        <div class="text-secondary small">Show email, phone, and address block in the footer.</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="show_footer_contact" value="0">
                        <input class="form-check-input" type="checkbox" name="show_footer_contact" id="show_footer_contact"
                               role="switch" style="width: 3rem; height: 1.5rem;"
                               {{ $settings->show_footer_contact ? 'checked' : '' }}>
                    </div>
                </div>

                {{-- Show Footer Social Links --}}
                <div class="d-flex align-items-center justify-content-between p-3 rounded"
                     style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                    <div>
                        <div class="fw-bold text-white">Show Footer Social Links</div>
                        <div class="text-secondary small">Show the active social media icons in the footer.</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input type="hidden" name="show_footer_social" value="0">
                        <input class="form-check-input" type="checkbox" name="show_footer_social" id="show_footer_social"
                               role="switch" style="width: 3rem; height: 1.5rem;"
                               {{ $settings->show_footer_social ? 'checked' : '' }}>
                    </div>
                </div>
            </div>

            {{-- 3. Footer Information --}}
            <div class="glass-panel p-4 mb-4">
                <h5 class="brand-font mb-4">
                    <i class="fa-solid fa-paragraph me-2 text-info"></i>Footer Information
                </h5>

                <div class="row g-3">
                    <div class="col-12">
                        <label for="footer_title" class="form-label form-label-custom">Footer Title</label>
                        <input type="text" name="footer_title" id="footer_title"
                               class="form-control form-control-custom @error('footer_title') is-invalid @enderror"
                               value="{{ old('footer_title', $settings->footer_title) }}"
                               maxlength="255" placeholder="e.g. AURAWEAR">
                        @error('footer_title')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label for="footer_description" class="form-label form-label-custom">Footer Description</label>
                        <textarea name="footer_description" id="footer_description"
                                  class="form-control form-control-custom @error('footer_description') is-invalid @enderror"
                                  rows="3" placeholder="e.g. Premium streetwear and printed t-shirts...">{{ old('footer_description', $settings->footer_description) }}</textarea>
                        @error('footer_description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label for="copyright_text" class="form-label form-label-custom">Copyright Text</label>
                        <input type="text" name="copyright_text" id="copyright_text"
                               class="form-control form-control-custom @error('copyright_text') is-invalid @enderror"
                               value="{{ old('copyright_text', $settings->copyright_text) }}"
                               placeholder="e.g. © 2026 AuraWear Inc. All rights reserved.">
                        @error('copyright_text')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- 4. Contact Information --}}
            <div class="glass-panel p-4 mb-4">
                <h5 class="brand-font mb-4">
                    <i class="fa-solid fa-address-book me-2 text-primary"></i>Contact Information
                </h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label form-label-custom">Email Address</label>
                        <input type="email" name="email" id="email"
                               class="form-control form-control-custom @error('email') is-invalid @enderror"
                               value="{{ old('email', $settings->email) }}"
                               placeholder="e.g. support@aurawear.com">
                        @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="phone" class="form-label form-label-custom">Phone Number</label>
                        <input type="text" name="phone" id="phone"
                               class="form-control form-control-custom @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $settings->phone) }}"
                               placeholder="e.g. +1 (555) 123-4567">
                        @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label for="address" class="form-label form-label-custom">Physical Address</label>
                        <textarea name="address" id="address"
                                  class="form-control form-control-custom @error('address') is-invalid @enderror"
                                  rows="2" placeholder="e.g. Fashion District, NY 10001">{{ old('address', $settings->address) }}</textarea>
                        @error('address')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- 5. Social Media --}}
            <div class="glass-panel p-4 mb-4">
                <h5 class="brand-font mb-4">
                    <i class="fa-brands fa-share-nodes me-2 text-warning"></i>Social Media Links
                </h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="facebook_url" class="form-label form-label-custom">Facebook URL</label>
                        <input type="url" name="facebook_url" id="facebook_url"
                               class="form-control form-control-custom @error('facebook_url') is-invalid @enderror"
                               value="{{ old('facebook_url', $settings->facebook_url) }}"
                               placeholder="https://facebook.com/yourpage">
                        @error('facebook_url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="instagram_url" class="form-label form-label-custom">Instagram URL</label>
                        <input type="url" name="instagram_url" id="instagram_url"
                               class="form-control form-control-custom @error('instagram_url') is-invalid @enderror"
                               value="{{ old('instagram_url', $settings->instagram_url) }}"
                               placeholder="https://instagram.com/yourprofile">
                        @error('instagram_url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="twitter_url" class="form-label form-label-custom">Twitter/X URL</label>
                        <input type="url" name="twitter_url" id="twitter_url"
                               class="form-control form-control-custom @error('twitter_url') is-invalid @enderror"
                               value="{{ old('twitter_url', $settings->twitter_url) }}"
                               placeholder="https://twitter.com/yourhandle">
                        @error('twitter_url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="youtube_url" class="form-label form-label-custom">YouTube URL</label>
                        <input type="url" name="youtube_url" id="youtube_url"
                               class="form-control form-control-custom @error('youtube_url') is-invalid @enderror"
                               value="{{ old('youtube_url', $settings->youtube_url) }}"
                               placeholder="https://youtube.com/yourchannel">
                        @error('youtube_url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="linkedin_url" class="form-label form-label-custom">LinkedIn URL</label>
                        <input type="url" name="linkedin_url" id="linkedin_url"
                               class="form-control form-control-custom @error('linkedin_url') is-invalid @enderror"
                               value="{{ old('linkedin_url', $settings->linkedin_url) }}"
                               placeholder="https://linkedin.com/in/yourprofile">
                        @error('linkedin_url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="whatsapp_url" class="form-label form-label-custom">WhatsApp URL</label>
                        <input type="url" name="whatsapp_url" id="whatsapp_url"
                               class="form-control form-control-custom @error('whatsapp_url') is-invalid @enderror"
                               value="{{ old('whatsapp_url', $settings->whatsapp_url) }}"
                               placeholder="https://wa.me/yournumber">
                        @error('whatsapp_url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-premium w-100 py-3 mb-4">
                <i class="fa-solid fa-floppy-disk me-2"></i> Save Site Settings
            </button>
        </div>
</form>

        {{-- ── RIGHT COLUMN: Live Preview ── --}}
        <div class="col-lg-5">
            <div class="glass-panel p-4 sticky-md-top" style="top: 100px; z-index: 10;">
                <h5 class="brand-font mb-4">
                    <i class="fa-solid fa-eye me-2 text-info"></i>Live Preview
                </h5>

                {{-- Storefront Navbar Preview --}}
                <div class="mb-3">
                    <div class="text-secondary small text-uppercase mb-2" style="letter-spacing: 0.5px;">
                        Storefront Navbar
                    </div>
                    <div class="d-flex align-items-center gap-2 p-3 rounded"
                         style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.09);">
                        <span id="prev-nav-logo-wrap">
                            @if($settings->store_logo && $settings->show_store_logo)
                                <img id="prev-nav-logo"
                                     src="{{ asset('storage/' . $settings->store_logo) }}"
                                     alt="Logo"
                                     style="height: 28px; max-width: 100px; object-fit: contain;">
                            @else
                                <i class="fa-solid fa-shirt text-primary" id="prev-nav-icon"
                                   style="{{ $settings->store_logo && !$settings->show_store_logo ? 'display:none;' : '' }}"></i>
                            @endif
                        </span>
                        <span class="logo-text brand-font" id="prev-nav-name"
                              style="font-size: 1rem; letter-spacing: 1px; max-width: 160px;
                                     overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $settings->show_store_name ? strtoupper($settings->store_name ?? 'Store Name') : '' }}
                        </span>
                    </div>
                </div>

                {{-- Admin Sidebar Header Preview --}}
                <div class="mb-3">
                    <div class="text-secondary small text-uppercase mb-2" style="letter-spacing: 0.5px;">
                        Admin Sidebar Header
                    </div>
                    <div class="d-flex align-items-center gap-2 p-3 rounded"
                         style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.09);">
                        <i class="fa-solid fa-screwdriver-wrench text-primary fs-5"></i>
                        <span class="logo-text brand-font" id="prev-admin-title"
                              style="font-size: 1rem; letter-spacing: 1px; max-width: 160px;
                                     overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ strtoupper($settings->admin_header_title ?? 'Admin Header') }}
                        </span>
                    </div>
                </div>

                {{-- Storefront Footer Preview --}}
                <div class="mb-4">
                    <div class="text-secondary small text-uppercase mb-2" style="letter-spacing: 0.5px;">
                        Storefront Footer
                    </div>
                    <div id="mock-store-footer" class="p-3 border rounded text-start"
                         style="background: rgba(9, 13, 22, 0.95); border-color: rgba(255,255,255,0.09) !important; font-size: 0.8rem; line-height: 1.5; color: #94a3b8;">
                        <div class="row g-3">
                            
                            {{-- Brand/Description Section --}}
                            <div id="preview-about-section" class="col-12 border-bottom border-secondary border-opacity-10 pb-2 {{ $settings->show_footer_logo ? '' : 'd-none' }}">
                                <div class="d-flex align-items-center mb-1">
                                    <div id="prev-footer-logo-wrap" class="me-2">
                                        @if($settings->store_logo)
                                            <img id="prev-footer-logo-img" src="{{ asset('storage/' . $settings->store_logo) }}" alt="Logo" style="height: 25px; max-width: 100px; object-fit: contain;">
                                        @else
                                            <i class="fa-solid fa-shirt text-primary me-2"></i>
                                            <span class="brand-font text-white fw-bold" id="prev-footer-name" style="font-size: 0.95rem;">{{ $settings->store_name ?? 'AURAWEAR' }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-white fw-bold mb-1" id="prev-footer-title">{{ $settings->footer_title ?? 'AURAWEAR' }}</div>
                                <p id="prev-footer-description" class="text-secondary small mb-0" style="font-size: 0.75rem;">{{ $settings->footer_description ?? 'Street style printed t-shirts...' }}</p>
                            </div>

                            {{-- Contact Information --}}
                            <div id="preview-contact-section" class="col-12 border-bottom border-secondary border-opacity-10 pb-2 {{ $settings->show_footer_contact ? '' : 'd-none' }}">
                                <div class="text-white fw-bold mb-1" style="font-size: 0.75rem;">CONTACT</div>
                                <ul class="list-unstyled mb-0 text-secondary" style="font-size: 0.75rem;">
                                    <li class="mb-1"><i class="fa-solid fa-envelope me-2 text-primary"></i><span id="prev-email">{{ $settings->email ?? 'support@aurawear.com' }}</span></li>
                                    <li class="mb-1"><i class="fa-solid fa-phone me-2 text-primary"></i><span id="prev-phone">{{ $settings->phone ?? '+1 (555) 123-4567' }}</span></li>
                                    <li><i class="fa-solid fa-location-dot me-2 text-primary"></i><span id="prev-address">{{ $settings->address ?? 'Fashion District, NY' }}</span></li>
                                </ul>
                            </div>

                            {{-- Social Media Icons --}}
                            <div id="preview-social-section" class="col-12 d-flex justify-content-between align-items-center {{ $settings->show_footer_social ? '' : 'd-none' }}">
                                <div class="d-flex gap-2">
                                    <span id="prev-social-instagram" class="{{ $settings->instagram_url ? '' : 'd-none' }}"><i class="fa-brands fa-instagram text-secondary fs-6"></i></span>
                                    <span id="prev-social-facebook" class="{{ $settings->facebook_url ? '' : 'd-none' }}"><i class="fa-brands fa-facebook-f text-secondary fs-6"></i></span>
                                    <span id="prev-social-twitter" class="{{ $settings->twitter_url ? '' : 'd-none' }}"><i class="fa-brands fa-x-twitter text-secondary fs-6"></i></span>
                                    <span id="prev-social-youtube" class="{{ $settings->youtube_url ? '' : 'd-none' }}"><i class="fa-brands fa-youtube text-secondary fs-6"></i></span>
                                    <span id="prev-social-linkedin" class="{{ $settings->linkedin_url ? '' : 'd-none' }}"><i class="fa-brands fa-linkedin-in text-secondary fs-6"></i></span>
                                    <span id="prev-social-whatsapp" class="{{ $settings->whatsapp_url ? '' : 'd-none' }}"><i class="fa-brands fa-whatsapp text-secondary fs-6"></i></span>
                                </div>
                            </div>

                            {{-- Copyright Text --}}
                            <div class="col-12 text-center mt-2 pt-1 border-top border-secondary border-opacity-10" style="font-size: 0.7rem;">
                                <div class="text-secondary opacity-75" id="prev-copyright">{{ $settings->copyright_text ?? '© ' . date('Y') . ' AuraWear. All rights reserved.' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-secondary opacity-25 my-4">

                <div class="text-secondary small text-center">
                    <i class="fa-solid fa-bolt text-warning me-1"></i>
                    Preview updates instantly as you type.
                </div>
            </div>
        </div>

    </div>

    {{-- 6. Quick Links Section --}}
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="glass-panel p-4 mb-4">
                <h5 class="brand-font mb-4">
                    <i class="fa-solid fa-link me-2 text-primary"></i>Manage Quick Links
                </h5>

                <!-- Add Link Form -->
                <form action="{{ route('admin.site-settings.links.store') }}" method="POST" id="add-link-form" class="mb-4 bg-dark bg-opacity-20 p-3 rounded border border-secondary border-opacity-25">
                    @csrf
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label for="link_text" class="form-label form-label-custom py-0 mb-1">Link Text</label>
                            <input type="text" name="text" id="link_text" class="form-control form-control-sm form-control-custom" placeholder="e.g. Privacy Policy" required>
                        </div>
                        <div class="col-md-5">
                            <label for="link_url" class="form-label form-label-custom py-0 mb-1">URL / Relative Path</label>
                            <input type="text" name="url" id="link_url" class="form-control form-control-sm form-control-custom" placeholder="e.g. /privacy" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-premium w-100 py-2">
                                <i class="fa-solid fa-plus me-1"></i> Add
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Reorder Toast Notification -->
                <div id="links-toast" class="alert alert-custom-success py-2 d-none mb-3">
                    <i class="fa-solid fa-circle-check me-2"></i>Link order saved.
                </div>

                <!-- List of Dynamic Quick Links -->
                @if($links->isEmpty())
                    <div class="text-center py-4 text-secondary">
                        <i class="fa-solid fa-link fs-2 mb-2 opacity-50"></i>
                        <p class="mb-0">No quick links found. Add your first link above!</p>
                    </div>
                @else
                    <div class="list-group" id="links-list">
                        @foreach($links as $link)
                            <div class="list-group-item bg-dark bg-opacity-20 border-secondary border-opacity-25 text-white mb-2 rounded d-flex justify-content-between align-items-center" data-id="{{ $link->id }}">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <span class="drag-handle px-3 text-secondary cursor-move" style="cursor: grab;"><i class="fa-solid fa-grip-vertical"></i></span>
                                    <div class="row flex-grow-1 g-2 me-3">
                                        <!-- Inline Edit Form -->
                                        <form action="{{ route('admin.site-settings.links.update', $link->id) }}" method="POST" class="d-flex w-100 m-0">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="text" class="form-control form-control-sm form-control-custom bg-transparent me-2" value="{{ $link->text }}" style="max-width: 150px;" required>
                                            <input type="text" name="url" class="form-control form-control-sm form-control-custom bg-transparent flex-grow-1 me-2" value="{{ $link->url }}" required>
                                            <button type="submit" class="btn btn-sm btn-outline-success px-2" title="Save Changes"><i class="fa-solid fa-check"></i></button>
                                        </form>
                                    </div>
                                </div>
                                <div>
                                    <form action="{{ route('admin.site-settings.links.destroy', $link->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this link?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<!-- Include SortableJS via CDN for dynamic sorting -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const el = id => document.getElementById(id);

    /* ── Live Text Sync ── */
    el('store_name')?.addEventListener('input', function () {
        const show = el('show_store_name')?.checked;
        const upperVal = this.value.trim().toUpperCase() || 'STORE NAME';
        el('prev-nav-name').textContent = show ? upperVal : '';
        
        // Update footer name text if no logo image exists
        const footerLogoImg = el('prev-footer-logo-img');
        if (!footerLogoImg && el('prev-footer-name')) {
            el('prev-footer-name').textContent = upperVal;
        }
    });

    el('admin_header_title')?.addEventListener('input', function () {
        el('prev-admin-title').textContent = (this.value.trim() || 'ADMIN HEADER').toUpperCase();
    });

    el('footer_title')?.addEventListener('input', function () {
        el('prev-footer-title').textContent = this.value.trim() || 'FOOTER TITLE';
    });

    el('footer_description')?.addEventListener('input', function () {
        el('prev-footer-description').textContent = this.value.trim() || 'Footer Description';
    });

    el('copyright_text')?.addEventListener('input', function () {
        el('prev-copyright').textContent = this.value.trim() || '© Copyright Text';
    });

    el('email')?.addEventListener('input', function () {
        el('prev-email').textContent = this.value.trim() || 'support@aurawear.com';
    });

    el('phone')?.addEventListener('input', function () {
        el('prev-phone').textContent = this.value.trim() || '+1 (555) 123-4567';
    });

    el('address')?.addEventListener('input', function () {
        el('prev-address').textContent = this.value.trim() || 'Fashion District, NY';
    });

    /* ── Social Media URL toggle visibility on preview ── */
    const syncSocial = (inputId, prevId) => {
        el(inputId)?.addEventListener('input', function () {
            if (this.value.trim() !== '') {
                el(prevId)?.classList.remove('d-none');
            } else {
                el(prevId)?.classList.add('d-none');
            }
        });
    };
    syncSocial('instagram_url', 'prev-social-instagram');
    syncSocial('facebook_url', 'prev-social-facebook');
    syncSocial('twitter_url', 'prev-social-twitter');
    syncSocial('youtube_url', 'prev-social-youtube');
    syncSocial('linkedin_url', 'prev-social-linkedin');
    syncSocial('whatsapp_url', 'prev-social-whatsapp');


    /* ── Show Store Name Text toggle ── */
    el('show_store_name')?.addEventListener('change', function () {
        const name = el('store_name')?.value.trim().toUpperCase() || 'STORE NAME';
        el('prev-nav-name').textContent = this.checked ? name : '';
    });

    /* ── Show Store Logo toggle ── */
    el('show_store_logo')?.addEventListener('change', function () {
        const logoImg = el('prev-nav-logo');
        const icon    = el('prev-nav-icon');
        if (logoImg) logoImg.style.display = this.checked ? '' : 'none';
        if (icon)    icon.style.display    = this.checked ? 'none' : '';
    });

    /* ── Show Footer Logo toggle ── */
    el('show_footer_logo')?.addEventListener('change', function () {
        if (this.checked) el('preview-about-section').classList.remove('d-none');
        else el('preview-about-section').classList.add('d-none');
    });

    /* ── Show Footer Contact Info toggle ── */
    el('show_footer_contact')?.addEventListener('change', function () {
        if (this.checked) el('preview-contact-section').classList.remove('d-none');
        else el('preview-contact-section').classList.add('d-none');
    });

    /* ── Show Footer Social Links toggle ── */
    el('show_footer_social')?.addEventListener('change', function () {
        if (this.checked) el('preview-social-section').classList.remove('d-none');
        else el('preview-social-section').classList.add('d-none');
    });


    /* ── Store Logo File Upload Preview ── */
    el('store_logo_input')?.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            // Update Branding card preview thumbnail
            let previewImg = el('logo-preview-image');
            if (!previewImg) {
                const holder = el('logo-preview-placeholder');
                previewImg = document.createElement('img');
                previewImg.id = 'logo-preview-image';
                previewImg.style = 'max-height: 40px; max-width: 70px; object-fit: contain;';
                holder.parentNode.replaceChild(previewImg, holder);
            }
            previewImg.src = e.target.result;

            // Update Navbar Preview
            const wrap = el('prev-nav-logo-wrap');
            let navLogo = el('prev-nav-logo');
            if (!navLogo) {
                wrap.innerHTML = '<img id="prev-nav-logo" src="" alt="Logo" style="height:28px;max-width:100px;object-fit:contain;">';
                navLogo = el('prev-nav-logo');
            }
            navLogo.src = e.target.result;
            navLogo.style.display = el('show_store_logo')?.checked ? '' : 'none';
            const navIcon = el('prev-nav-icon');
            if (navIcon) navIcon.style.display = 'none';

            // Update Footer Preview Logo
            const footerWrap = el('prev-footer-logo-wrap');
            footerWrap.innerHTML = `<img id="prev-footer-logo-img" src="${e.target.result}" alt="Logo" style="height: 25px; max-width: 100px; object-fit: contain;">`;
        };
        reader.readAsDataURL(file);
    });

    /* ── Favicon Upload Preview ── */
    el('favicon_input')?.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            let previewFavicon = el('favicon-preview-image');
            if (!previewFavicon) {
                const holder = el('favicon-preview-placeholder');
                previewFavicon = document.createElement('img');
                previewFavicon.id = 'favicon-preview-image';
                previewFavicon.style = 'width: 32px; height: 32px; object-fit: contain;';
                holder.parentNode.replaceChild(previewFavicon, holder);
            }
            previewFavicon.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });


    /* ── Sortable Quick Links ── */
    const linksEl = document.getElementById('links-list');
    if (linksEl) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        new Sortable(linksEl, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function () {
                const ids = Array.from(linksEl.querySelectorAll('[data-id]')).map(item => item.getAttribute('data-id'));
                const toast = document.getElementById('links-toast');

                fetch("{{ route('admin.site-settings.links.reorder') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ ids: ids })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        toast.classList.remove('d-none');
                        setTimeout(() => toast.classList.add('d-none'), 3000);
                    }
                });
            }
        });
    }

});
</script>
@endsection
