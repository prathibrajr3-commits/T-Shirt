<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: {{ $promotion->heading }} — AuraWear Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #8b5cf6;
            --primary-glow: rgba(139, 92, 246, 0.15);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #0d0d0f;
            font-family: 'Outfit', sans-serif;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ========= Admin Preview Bar ========= */
        .preview-bar {
            background: linear-gradient(90deg, #1a1a2e, #16213e);
            border-bottom: 1px solid rgba(139,92,246,0.3);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }
        .preview-bar .badge-preview {
            background: rgba(139,92,246,0.2);
            color: #a78bfa;
            border: 1px solid rgba(139,92,246,0.4);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 1px;
        }
        .preview-bar .btn-close-preview {
            background: rgba(255,255,255,0.07);
            color: #94a3b8;
            border: 1px solid rgba(255,255,255,0.1);
            padding: 6px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .preview-bar .btn-close-preview:hover {
            background: rgba(255,255,255,0.12);
            color: #fff;
        }

        /* ========= Promo Section ========= */
        .promo-section-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .promo-container {
            width: 100%;
            max-width: 960px;
        }
        .promo-label {
            font-size: 0.7rem;
            letter-spacing: 3px;
            font-weight: 700;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 12px;
        }
        .promo-panel {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 56px 48px;
            text-align: center;
            box-shadow:
                0 0 80px rgba(0,0,0,0.6),
                inset 0 1px 0 rgba(255,255,255,0.06);
        }
        .promo-panel-bg {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            opacity: 0.18;
            z-index: 0;
        }
        .promo-panel-overlay {
            position: absolute;
            inset: 0;
            z-index: 1;
        }
        .promo-panel-content {
            position: relative;
            z-index: 2;
        }
        .promo-eyebrow {
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 16px;
        }
        .promo-heading {
            font-size: clamp(1.6rem, 4vw, 2.6rem);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.5px;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #fff 0%, #c4b5fd 60%, #93c5fd 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .promo-subtitle {
            font-size: 1rem;
            color: #94a3b8;
            max-width: 540px;
            margin: 0 auto 28px;
            line-height: 1.7;
        }
        .promo-coupon {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,0.04);
            border: 1px dashed rgba(139,92,246,0.5);
            border-radius: 12px;
            padding: 10px 22px;
            margin-bottom: 28px;
            cursor: pointer;
            transition: all 0.25s;
            position: relative;
        }
        .promo-coupon:hover {
            background: rgba(139,92,246,0.1);
            border-color: rgba(139,92,246,0.8);
            transform: translateY(-1px);
        }
        .promo-coupon-label {
            font-size: 0.7rem;
            letter-spacing: 1px;
            color: #64748b;
            text-transform: uppercase;
        }
        .promo-coupon-code {
            font-size: 1.15rem;
            font-weight: 800;
            letter-spacing: 3px;
            background: linear-gradient(90deg, #a78bfa, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .promo-coupon-copy {
            font-size: 0.7rem;
            color: #64748b;
            font-weight: 600;
        }
        .promo-coupon-copied {
            position: absolute;
            inset: 0;
            background: rgba(139,92,246,0.15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            color: #a78bfa;
            font-weight: 600;
            opacity: 0;
            transition: opacity 0.3s;
            pointer-events: none;
        }
        .promo-coupon.copied .promo-coupon-copied { opacity: 1; }
        .promo-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 36px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }
        .promo-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0.12);
            opacity: 0;
            transition: opacity 0.2s;
        }
        .promo-btn:hover::before { opacity: 1; }
        .promo-btn:hover {
            transform: translateY(-3px) scale(1.04);
            box-shadow: 0 12px 40px rgba(0,0,0,0.4);
        }

        /* Glow orbs */
        .glow-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            pointer-events: none;
        }

        /* Meta info panel */
        .meta-panel {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 14px;
            padding: 20px 24px;
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 16px;
        }
        .meta-item label { font-size: 0.7rem; letter-spacing: 1px; color: #475569; text-transform: uppercase; display: block; margin-bottom: 4px; }
        .meta-item span { font-size: 0.88rem; color: #94a3b8; }
        .meta-item .badge-status { font-size: 0.75rem; padding: 3px 10px; border-radius: 20px; }

        @media (max-width: 600px) {
            .promo-panel { padding: 36px 24px; }
            .promo-heading { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

{{-- Admin Preview Top Bar --}}
<div class="preview-bar">
    <div class="d-flex align-items-center gap-3">
        <span class="badge-preview"><i class="fa-solid fa-eye me-1"></i> PREVIEW MODE</span>
        <span style="color: #475569; font-size: 0.82rem;">This is how the offer appears on your homepage</span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.promotions.edit', $promotion->id) }}" class="btn-close-preview">
            <i class="fa-solid fa-pen-to-square me-1"></i> Edit
        </a>
        <a href="{{ route('admin.promotions.index') }}" class="btn-close-preview">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>

{{-- Preview Section --}}
<div class="promo-section-wrapper">
    <div class="promo-container">

        <div class="promo-label">
            <i class="fa-solid fa-eye me-1"></i> Homepage Promotional Banner Preview
        </div>

        @php
            $c1 = $promotion->gradient_color_1;
            $c2 = $promotion->gradient_color_2;
            $gradientBg = "linear-gradient(135deg, {$c1}18 0%, {$c2}18 100%)";
        @endphp

        <div class="promo-panel" style="background: {{ $gradientBg }};">

            {{-- Background image (if any) --}}
            @if($promotion->background_image)
                <div class="promo-panel-bg" style="background-image: url('{{ asset($promotion->background_image) }}');"></div>
            @endif

            {{-- Gradient overlay --}}
            <div class="promo-panel-overlay" style="background: linear-gradient(135deg, {{ $c1 }}12 0%, {{ $c2 }}12 100%);"></div>

            {{-- Glow orbs --}}
            <div class="glow-orb" style="width: 300px; height: 300px; top: -80px; left: -60px; background: {{ $c1 }};"></div>
            <div class="glow-orb" style="width: 250px; height: 250px; bottom: -60px; right: -40px; background: {{ $c2 }};"></div>

            <div class="promo-panel-content">
                <div class="promo-eyebrow">{{ $promotion->title }}</div>

                <h2 class="promo-heading">{{ $promotion->heading }}</h2>

                @if($promotion->subtitle)
                    <p class="promo-subtitle">{{ $promotion->subtitle }}</p>
                @endif

                @if($promotion->coupon_code)
                    <div class="d-flex justify-content-center mb-4">
                        <div class="promo-coupon" id="couponBox" onclick="copyCoupon('{{ $promotion->coupon_code }}')">
                            <div>
                                <div class="promo-coupon-label">Coupon Code</div>
                                <div class="promo-coupon-code">{{ $promotion->coupon_code }}</div>
                            </div>
                            <div class="promo-coupon-copy">
                                <i class="fa-regular fa-copy"></i> Click to copy
                            </div>
                            <div class="promo-coupon-copied"><i class="fa-solid fa-check me-1"></i> Copied!</div>
                        </div>
                    </div>
                @endif

                <a href="{{ url($promotion->button_link) }}"
                   class="promo-btn"
                   style="background: linear-gradient(135deg, {{ $c1 }}, {{ $c2 }}); color: white; box-shadow: 0 6px 30px {{ $c1 }}55;">
                    {{ $promotion->button_text }}
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- Meta Info --}}
        <div class="meta-panel">
            <div class="meta-item">
                <label>Status</label>
                @if($promotion->is_expired)
                    <span class="badge bg-danger badge-status">Expired</span>
                @elseif(!$promotion->is_active)
                    <span class="badge bg-secondary badge-status">Inactive</span>
                @elseif(!$promotion->is_started)
                    <span class="badge bg-warning text-dark badge-status">Scheduled</span>
                @else
                    <span class="badge bg-success badge-status">Live</span>
                @endif
            </div>
            <div class="meta-item">
                <label>Display Order</label>
                <span>#{{ $promotion->display_order }}</span>
            </div>
            <div class="meta-item">
                <label>Start Date</label>
                <span>{{ $promotion->start_date ? $promotion->start_date->format('d M Y') : '—' }}</span>
            </div>
            <div class="meta-item">
                <label>Expiry Date</label>
                <span>{{ $promotion->expiry_date ? $promotion->expiry_date->format('d M Y') : 'No expiry' }}</span>
            </div>
            <div class="meta-item">
                <label>Button Link</label>
                <span>{{ $promotion->button_link }}</span>
            </div>
            <div class="meta-item">
                <label>Coupon Code</label>
                <span>{{ $promotion->coupon_code ?: '—' }}</span>
            </div>
        </div>
    </div>
</div>

<script>
    function copyCoupon(code) {
        navigator.clipboard.writeText(code).then(() => {
            const box = document.getElementById('couponBox');
            box.classList.add('copied');
            setTimeout(() => box.classList.remove('copied'), 1800);
        });
    }
</script>
</body>
</html>
