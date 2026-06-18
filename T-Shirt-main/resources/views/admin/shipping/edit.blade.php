@extends('layouts.admin')

@section('title', 'Shipping Settings')

@section('content')
<div class="d-flex align-items-center mb-4">
    <i class="fa-solid fa-truck-fast text-primary fs-4 me-3"></i>
    <h2 class="brand-font mb-0">Shipping Settings</h2>
</div>

<form action="{{ route('admin.shipping.update') }}" method="POST" id="shipping-form">
    @csrf
    @method('PUT')

    <div class="row g-4">

        {{-- ── LEFT COLUMN: Settings ── --}}
        <div class="col-lg-7">

            {{-- Shipping Type --}}
            <div class="glass-panel p-4 mb-4">
                <h5 class="brand-font mb-4"><i class="fa-solid fa-sliders me-2 text-primary"></i>Shipping Mode</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-check p-3 rounded border border-secondary border-opacity-25 cursor-pointer"
                               style="background: rgba(255,255,255,0.03);">
                            <input class="form-check-input me-2" type="radio" name="shipping_type"
                                   value="free_above_threshold" id="type_threshold"
                                   {{ $settings->shipping_type === 'free_above_threshold' ? 'checked' : '' }}>
                            <span class="fw-bold text-white">Free above threshold</span>
                            <div class="text-secondary small mt-1">
                                Charge flat fee; waive it once cart exceeds the minimum.
                            </div>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <label class="form-check p-3 rounded border border-secondary border-opacity-25 cursor-pointer"
                               style="background: rgba(255,255,255,0.03);">
                            <input class="form-check-input me-2" type="radio" name="shipping_type"
                                   value="fixed" id="type_fixed"
                                   {{ $settings->shipping_type === 'fixed' ? 'checked' : '' }}>
                            <span class="fw-bold text-white">Always fixed</span>
                            <div class="text-secondary small mt-1">
                                Always charge the flat fee regardless of cart total.
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Amounts --}}
            <div class="glass-panel p-4 mb-4">
                <h5 class="brand-font mb-4"><i class="fa-solid fa-indian-rupee-sign me-2 text-warning"></i>Amounts</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="estimated_shipping" class="form-label form-label-custom">Estimated Shipping (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-secondary">₹</span>
                            <input type="number" name="estimated_shipping" id="estimated_shipping"
                                   class="form-control form-control-custom @error('estimated_shipping') is-invalid @enderror"
                                   value="{{ old('estimated_shipping', $settings->estimated_shipping) }}"
                                   min="0" step="0.01" required>
                        </div>
                        @error('estimated_shipping')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6" id="threshold-field">
                        <label for="free_shipping_minimum" class="form-label form-label-custom">Free Shipping Minimum (₹)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-secondary">₹</span>
                            <input type="number" name="free_shipping_minimum" id="free_shipping_minimum"
                                   class="form-control form-control-custom @error('free_shipping_minimum') is-invalid @enderror"
                                   value="{{ old('free_shipping_minimum', $settings->free_shipping_minimum) }}"
                                   min="0" step="0.01" required>
                        </div>
                        @error('free_shipping_minimum')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Messages --}}
            <div class="glass-panel p-4 mb-4">
                <h5 class="brand-font mb-4"><i class="fa-solid fa-message me-2 text-info"></i>Messages</h5>

                <div class="mb-3">
                    <label for="custom_message" class="form-label form-label-custom">
                        Promo Message
                        <span class="text-secondary small">&nbsp;(use <code>:amount</code> for the remaining amount)</span>
                    </label>
                    <input type="text" name="custom_message" id="custom_message"
                           class="form-control form-control-custom @error('custom_message') is-invalid @enderror"
                           value="{{ old('custom_message', $settings->custom_message) }}" required>
                    @error('custom_message')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-0">
                    <label for="free_shipping_message" class="form-label form-label-custom">Free Shipping Unlocked Message</label>
                    <input type="text" name="free_shipping_message" id="free_shipping_message"
                           class="form-control form-control-custom @error('free_shipping_message') is-invalid @enderror"
                           value="{{ old('free_shipping_message', $settings->free_shipping_message) }}" required>
                    @error('free_shipping_message')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Appearance --}}
            <div class="glass-panel p-4 mb-4">
                <h5 class="brand-font mb-4"><i class="fa-solid fa-palette me-2 text-purple"></i>Appearance</h5>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="icon_class" class="form-label form-label-custom">Icon (FontAwesome Class)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary" id="icon-preview-wrap">
                                <i id="icon-preview" class="{{ $settings->icon_class }}"></i>
                            </span>
                            <input type="text" name="icon_class" id="icon_class"
                                   class="form-control form-control-custom"
                                   value="{{ old('icon_class', $settings->icon_class) }}"
                                   placeholder="fa-solid fa-truck-fast">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="border_radius" class="form-label form-label-custom">Border Radius</label>
                        <input type="text" name="border_radius" id="border_radius"
                               class="form-control form-control-custom"
                               value="{{ old('border_radius', $settings->border_radius) }}"
                               placeholder="0.5rem">
                    </div>
                    <div class="col-md-6">
                        <label for="background_color" class="form-label form-label-custom">Background Color</label>
                        <div class="input-group">
                            <input type="color" name="background_color" id="background_color_picker"
                                   class="form-control form-control-color bg-dark border-secondary"
                                   style="max-width: 48px;"
                                   value="{{ $settings->background_color === 'transparent' ? '#1a1a2e' : $settings->background_color }}">
                            <input type="text" name="background_color" id="background_color"
                                   class="form-control form-control-custom"
                                   value="{{ old('background_color', $settings->background_color) }}"
                                   placeholder="transparent or #hex">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="text_color" class="form-label form-label-custom">Text Color</label>
                        <div class="input-group">
                            <input type="color" name="_text_color_picker" id="text_color_picker"
                                   class="form-control form-control-color bg-dark border-secondary"
                                   style="max-width: 48px;"
                                   value="{{ $settings->text_color }}">
                            <input type="text" name="text_color" id="text_color"
                                   class="form-control form-control-custom"
                                   value="{{ old('text_color', $settings->text_color) }}"
                                   placeholder="#ffffff">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Toggles --}}
            <div class="glass-panel p-4 mb-4">
                <h5 class="brand-font mb-4"><i class="fa-solid fa-toggle-on me-2 text-success"></i>Visibility</h5>

                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded"
                     style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                    <div>
                        <div class="fw-bold text-white">Enable Shipping Section</div>
                        <div class="text-secondary small">Show the shipping row in cart & checkout summaries.</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" role="switch"
                               style="width: 3rem; height: 1.5rem;"
                               {{ $settings->is_active ? 'checked' : '' }}>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 rounded"
                     style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                    <div>
                        <div class="fw-bold text-white">Show Free Shipping Promotion</div>
                        <div class="text-secondary small">Display promo banner when free shipping is not yet unlocked.</div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="show_free_shipping_promo" id="show_free_shipping_promo"
                               role="switch" style="width: 3rem; height: 1.5rem;"
                               {{ $settings->show_free_shipping_promo ? 'checked' : '' }}>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-premium py-3 px-5 w-100">
                <i class="fa-solid fa-floppy-disk me-2"></i> Save Shipping Settings
            </button>
        </div>

        {{-- ── RIGHT COLUMN: Live Preview ── --}}
        <div class="col-lg-5">
            <div class="glass-panel p-4 sticky-md-top" style="top: 100px;">
                <h5 class="brand-font mb-4"><i class="fa-solid fa-eye me-2 text-info"></i>Live Preview</h5>

                <div class="mb-3">
                    <label class="form-label form-label-custom">Test Subtotal (₹)</label>
                    <input type="number" id="preview_subtotal" class="form-control form-control-custom"
                           value="699" min="0" step="1" placeholder="e.g. 699">
                </div>

                {{-- Cart Summary Preview --}}
                <div id="preview-card" class="p-4 rounded mb-3"
                     style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.09);">
                    <h6 class="text-secondary small text-uppercase mb-3" style="letter-spacing: 0.5px;">Cart Summary</h6>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Subtotal</span>
                        <span class="fw-bold" id="prev-subtotal">₹699.00</span>
                    </div>

                    <div id="prev-shipping-row" class="d-flex justify-content-between mb-3">
                        <span class="text-secondary d-flex align-items-center gap-2">
                            <i id="prev-icon" class="{{ $settings->icon_class }}"></i>
                            Estimated Shipping
                        </span>
                        <span class="fw-bold" id="prev-shipping-value">₹150.00</span>
                    </div>

                    <div id="prev-promo-banner" class="alert alert-info py-2 px-3 small mb-3"
                         style="border-radius: 0.5rem;">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        <span id="prev-promo-text">Add ₹1,801.00 more for FREE shipping!</span>
                    </div>

                    <hr class="border-secondary opacity-25 my-2">

                    <div class="d-flex justify-content-between">
                        <span class="text-white fw-bold">Total</span>
                        <span class="text-success fw-bold fs-5" id="prev-total">₹849.00</span>
                    </div>
                </div>

                <div class="text-secondary small text-center">
                    <i class="fa-solid fa-info-circle me-1"></i>
                    Preview updates live as you change settings above.
                </div>
            </div>
        </div>

    </div>
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ─── Helpers ───────────────────────────────────────────────
    const $ = id => document.getElementById(id);
    const fmt = n => '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function getFloat(id) { return parseFloat($(id)?.value || 0) || 0; }
    function getString(id) { return $(id)?.value?.trim() || ''; }
    function isChecked(id) { return $(id)?.checked; }
    function getRadio(name) { return document.querySelector(`input[name="${name}"]:checked`)?.value; }

    // ─── Live Preview ───────────────────────────────────────────
    function updatePreview() {
        const subtotal    = getFloat('preview_subtotal');
        const estShipping = getFloat('estimated_shipping');
        const minimum     = getFloat('free_shipping_minimum');
        const shippingType = getRadio('shipping_type');
        const isActive    = isChecked('is_active');
        const showPromo   = isChecked('show_free_shipping_promo');
        const customMsg   = getString('custom_message');
        const freeMsg     = getString('free_shipping_message');
        const iconCls     = getString('icon_class');
        const bgColor     = getString('background_color');
        const txtColor    = getString('text_color');
        const radius      = getString('border_radius');

        // Subtotal display
        $('prev-subtotal').textContent = fmt(subtotal);

        // Shipping cost
        let shippingCost;
        if (!isActive) {
            shippingCost = null;
        } else if (shippingType === 'fixed') {
            shippingCost = estShipping;
        } else {
            shippingCost = subtotal >= minimum ? 0 : estShipping;
        }

        // Shipping row
        const shippingRow = $('prev-shipping-row');
        if (!isActive) {
            shippingRow.style.display = 'none';
            $('prev-total').textContent = fmt(subtotal);
        } else {
            shippingRow.style.display = '';
            $('prev-shipping-value').textContent = shippingCost === 0 ? 'FREE' : fmt(shippingCost);
            $('prev-total').textContent = fmt(subtotal + shippingCost);
        }

        // Promo banner
        const promoBanner = $('prev-promo-banner');
        const isFree = (shippingType === 'free_above_threshold' && subtotal >= minimum);
        if (!isActive || !showPromo) {
            promoBanner.style.display = 'none';
        } else if (isFree) {
            promoBanner.style.display = '';
            promoBanner.className = 'alert alert-success py-2 px-3 small mb-3';
            $('prev-promo-text').textContent = freeMsg;
        } else {
            const remaining = Math.max(0, minimum - subtotal);
            const msg = customMsg.replace(':amount', fmt(remaining));
            promoBanner.style.display = '';
            promoBanner.className = 'alert alert-info py-2 px-3 small mb-3';
            $('prev-promo-text').textContent = msg;
        }

        // Icon update
        const iconEl = $('prev-icon');
        iconEl.className = iconCls;

        // Appearance
        const card = $('preview-card');
        card.style.borderRadius = radius;
        card.style.backgroundColor = bgColor === 'transparent' ? 'rgba(255,255,255,0.04)' : bgColor;
        card.style.color = txtColor;

        // Icon_class preview in input group
        $('icon-preview').className = iconCls;
    }

    // Sync colour pickers → text inputs (bg)
    $('background_color_picker')?.addEventListener('input', function () {
        $('background_color').value = this.value;
        updatePreview();
    });
    $('background_color')?.addEventListener('input', function () {
        if (/^#[0-9a-fA-F]{6}$/.test(this.value)) {
            $('background_color_picker').value = this.value;
        }
        updatePreview();
    });

    // Sync colour pickers → text inputs (text)
    $('text_color_picker')?.addEventListener('input', function () {
        $('text_color').value = this.value;
        updatePreview();
    });
    $('text_color')?.addEventListener('input', function () {
        if (/^#[0-9a-fA-F]{6}$/.test(this.value)) {
            $('text_color_picker').value = this.value;
        }
        updatePreview();
    });

    // Toggle threshold field visibility
    function syncThresholdField() {
        const type = getRadio('shipping_type');
        const field = $('threshold-field');
        if (field) field.style.opacity = type === 'fixed' ? '0.4' : '1';
    }

    // Watch all inputs
    document.querySelectorAll('input, textarea, select').forEach(el => {
        el.addEventListener('input', () => { updatePreview(); syncThresholdField(); });
        el.addEventListener('change', () => { updatePreview(); syncThresholdField(); });
    });

    // Initial render
    syncThresholdField();
    updatePreview();
});
</script>
@endsection
