//The shortcode tikva heart main product

function TH_Subscription_shortcode2($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(array(
        'product_id' => get_the_ID()
    ), $atts);

    $product_id = intval($atts['product_id']);
    $product = wc_get_product($product_id);

    // Validate product
    if (!$product || !is_object($product)) {
        return '<p style="color: red;">Error: Product not found. Please specify a valid product_id in the shortcode.</p>';
    }

    if (!$product->is_type('variable')) {
        return '<p style="color: red;">Error: This shortcode only works with variable products. Product ID: ' . $product_id . '</p>';
    }

    // Get variations
    $available_variations = $product->get_available_variations();

    if (empty($available_variations)) {
        return '<p style="color: red;">Error: No variations found for this product.</p>';
    }

    $flavors = array();

    foreach ($available_variations as $variation) {
        $variation_obj = wc_get_product($variation['variation_id']);

        if (!$variation_obj) continue;

        $attributes = $variation_obj->get_variation_attributes();

        // Look for flavor attribute
        $flavor_slug = '';
        if (isset($attributes['attribute_pa_flavours'])) {
            $flavor_slug = $attributes['attribute_pa_flavours'];
        } elseif (isset($attributes['attribute_flavours'])) {
            $flavor_slug = $attributes['attribute_flavours'];
        }

        if ($flavor_slug) {
            $flavor_name = ucwords(str_replace('-', ' ', $flavor_slug));

            // Get variation image
            $image_id = $variation_obj->get_image_id();
            $image_url = '';

            if ($image_id) {
                $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
            } else {
                $parent_product = wc_get_product($variation_obj->get_parent_id());
                if ($parent_product) {
                    $parent_image_id = $parent_product->get_image_id();
                    if ($parent_image_id) {
                        $image_url = wp_get_attachment_image_url($parent_image_id, 'thumbnail');
                    }
                }
            }

            $flavors[] = array(
                'slug' => $flavor_slug,
                'name' => $flavor_name,
                'variation_id' => $variation['variation_id'],
                'price' => $variation_obj->get_price(),
                'image_url' => $image_url
            );
        }
    }

    if (empty($flavors)) {
        return '<p style="color: red;">Error: No flavor variations found. Make sure your product has a "flavours" attribute.</p>';
    }

    ob_start();
    ?>

    <style>
    /* ===== RESET & BASE ===== */
    .th-subscription-wrapper * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    .th-subscription-wrapper {
        font-family: 'Gilory', serif;
        max-width: 670px;
        margin: 0 auto;
        padding: 0;
        background: transparent;
    }

    /* ===== TOP BANNER ===== */
    /* .th-bf-gift-banner {
        background-image: url("https://mtr3swr11p.wpdns.site/wp-content/uploads/2025/12/New-Year-Sale-Label.png");
        width: 100%;
        height: 283px;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    } */

    /* ===== MOST POPULAR BADGE ===== */
    .th-popular-badge-top {
        display: inline-block;
        background: #FF8D28;
        color: #fff;
        font-family: 'Gilory', sans-serif;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 7px 18px;
        border-radius: 20px;
        margin: -45px 0 10px 0;
        position: absolute;
    }

    /* ===== SUBSCRIPTION CARDS WRAPPER ===== */
    .th-subscription-grid {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 18px;
        padding: 0 0;
    }

    /* ===== BASE CARD ===== */
    .th-subscription-card,
    .th-onetime-card {
        background: #fff;
        border: 1.5px solid #E8DDD5;
        border-radius: 12px;
        padding: 30px 20px;
        cursor: pointer;
        transition: all 0.25s ease;
        position: relative;
    }

    /* ===== ACTIVE / SELECTED CARD ===== */
    .th-subscription-card.active,
    .th-onetime-card.active {
        background: #702C44;
        border-color: #702C44;
        color: #fff;
    }

    .th-subscription-card:hover:not(.active),
    .th-onetime-card:hover:not(.active) {
        border-color: #702C44;
        box-shadow: 0 2px 10px rgba(112,44,68,0.10);
    }

    /* ===== CARD INNER LAYOUT ===== */
    .th-card-top-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 4px;
    }

    .th-card-title {
        font-family: 'Gilory', serif;
        font-size: 30px;
        font-weight: 700;
        color: #1a1a1a;
        line-height: 1.2;
    }

    .th-subscription-card.active .th-card-title,
    .th-onetime-card.active .th-onetime-title {
        color: #fff;
    }

    /* Per-day price (right side top) */
    .th-card-per-day {
        font-family: 'Gilory', sans-serif;
        font-size: 30px;
        font-weight: 900;
        color: #702C44;
        text-align: right;
        white-space: nowrap;
    }

    .th-subscription-card.active .th-card-per-day {
        color: #fff;
    }

    .th-card-per-day span.perday-label {
        font-size: 14px;
        font-weight: 600;
    }

    /* Ships info */
    .th-card-ships {
        font-family: 'Gilory', sans-serif;
        font-size: 20px;
        font-weight: 600;
        color: #FF8D28;
        margin-bottom: 8px;
    }

    .th-subscription-card.active .th-card-ships {
        color: #FF8D28;
    }

    /* Reg / sale price row */
    .th-card-price-row {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 6px;
        margin-bottom: 6px;
    }

    .th-card-reg-price {
        font-family: 'Gilory', sans-serif;
        font-size: 20px;
        color: #F57300;
        text-decoration: line-through;
    }

    .th-card-sale-price {
        font-family: 'Gilory', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: #F57300;
    }

    .th-subscription-card.active .th-card-sale-price,
    .th-subscription-card.active .th-card-reg-price {
        color: #F57300;
    }

    /* Description text */
    .th-card-desc {
        font-family: 'Gilory', sans-serif;
        font-size: 13px;
        color: #555;
        margin-bottom: 10px;
        font-weight: 500;
    }

    .th-subscription-card.active .th-card-desc {
        color: rgba(255,255,255,0.80);
    }

    /* Bonus highlight box */
    .th-card-bonus-box {
        background: #FFF5EC;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 10px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .th-subscription-card.active .th-card-bonus-box {
        background: rgba(255,255,255,0.12);
    }

    .th-card-bonus-box .bonus-icon {
        font-size: 18px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .th-card-bonus-box .bonus-text {
        font-family: 'Gilory', sans-serif;
        font-size: 12.5px;
        color: #702C44;
        font-weight: 600;
        line-height: 1.4;
    }

    .th-subscription-card.active .th-card-bonus-box .bonus-text {
        color: #FFC182;
    }

    /* Bullet list */
    .th-card-bullets {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .th-card-bullets li {
        font-family: 'Gilory', sans-serif;
        font-size: 18px;
        color: #444;
        padding: 2px 0 2px 30px;
        position: relative;
        line-height: 1.5;
    }

    .th-card-bullets li:before {
        content: "";
        position: absolute;
        left: 0;
        top: 6px;
        width: 20px;
        height: 20px;
        background: #702C44;
        border-radius: 50%;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3E%3Cpath d='M2 6l3 3 5-5' stroke='white' stroke-width='2' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-size: 10px;
        background-repeat: no-repeat;
        background-position: center;
    }

    .th-subscription-card.active .th-card-bullets li {
        color: rgba(255,255,255,0.90);
    }

    .th-subscription-card.active .th-card-bullets li:before {
        background-color: rgba(255,255,255,0.25);
    }

    /* ===== ONE-TIME CARD ===== */
    .th-onetime-card {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .th-onetime-title {
        font-family: 'Gilory', serif;
        font-size: 30px;
        font-weight: 700;
        color: #702C44;
        margin-bottom: 4px;
    }

    .th-onetime-subtitle {
        font-family: 'Gilory', sans-serif;
        font-size: 20px;
        font-weight: 600;
        color: #FF8D28;
        margin-bottom: 8px;
    }

    .th-onetime-card.active .th-onetime-subtitle {
        color: #FFC182;
    }

    .th-onetime-bullets {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .th-onetime-bullets li {
        font-family: 'Gilory', sans-serif;
        font-size: 18px;
        color: #444;
        padding: 2px 0 2px 22px;
        position: relative;
        line-height: 1.5;
    }

    .th-onetime-bullets li:before {
        content: "";
        position: absolute;
        left: 0;
        top: 6px;
        width: 20px;
        height: 20px;
        background: #702C44;
        border-radius: 50%;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12'%3E%3Cpath d='M2 6l3 3 5-5' stroke='white' stroke-width='2' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-size: 10px;
        background-repeat: no-repeat;
        background-position: center;
    }

    .th-onetime-card.active .th-onetime-bullets li {
        color: rgba(255,255,255,0.90);
    }

    .th-onetime-card.active .th-onetime-bullets li:before {
        background-color: rgba(255,255,255,0.25);
    }

    .th-onetime-price-wrap {
        text-align: right;
        flex-shrink: 0;
        margin-left: 16px;
    }

    .th-onetime-price {
        font-family: 'Gilory', sans-serif;
        font-size: 30px;
        font-weight: 900;
        color: #702C44;
    }

    .th-onetime-card.active .th-onetime-price {
        color: #FF8D28;
    }

    /* ===== FLAVOR SECTION ===== */
    .th-flavor-section-title {
        font-family: 'Gilory', serif;
        font-size: 24px;
        font-weight: 700;
        color: #702C44;
        margin: 20px 0 12px 20px;
        line-height: 1.3;
    }

    .th-flavor-grid {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 18px;
    }

    .th-flavor-card {
        background: #fff;
        border: 1.5px solid #E8DDD5;
        border-radius: 10px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        transition: border-color 0.2s;
    }

    .th-flavor-card.selected {
        border-color: #702C44;
        background: #FDF8F5;
    }

    .th-flavor-left {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1;
    }

    /* Product image in flavor card */
    .th-flavor-img {
        width: 44px;
        height: 44px;
        object-fit: contain;
        border-radius: 6px;
        flex-shrink: 0;
        background: #F5EDE5;
    }

    .th-flavor-img-placeholder {
        width: 44px;
        height: 44px;
        border-radius: 6px;
        flex-shrink: 0;
        background: #F0E5DC;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .th-flavor-name {
        font-family: 'Gilory', sans-serif;
        font-size: 20px;
        font-weight: 600;
        color: #1a1a1a;
        line-height: 1.3;
    }

    .th-flavor-name-note {
        font-size: 18px;
        font-weight: 400;
        color: #702C44;
    }

    /* QTY Controls */
    .th-qty-controls {
        display: flex;
        align-items: center;
        gap: 0;
        flex-shrink: 0;
    }

    .th-qty-btn {
        width: 34px;
        height: 34px;
        background: #F5EDE5;
        color: #702C44;
        font-size: 20px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        border: 1.5px solid #E8DDD5;
        line-height: 1;
    }

    .th-qty-btn.th-qty-minus {
        border-radius: 6px 0 0 6px;
        border-right: none;
    }

    .th-qty-btn.th-qty-plus {
        border-radius: 0 6px 6px 0;
        border-left: none;
    }

    .th-qty-btn:hover {
        background: #702C44;
        color: #fff;
        border-color: #702C44;
    }

    .th-qty-input {
        width: 40px !important;
        height: 34px;
        border: 1.5px solid #E8DDD5 !important;
        border-left: none !important;
        border-right: none !important;
        border-radius: 0 !important;
        text-align: center;
        font-size: 16px;
        font-weight: 700;
        color: #1a1a1a;
        padding: 0 !important;
        background: white;
        -moz-appearance: textfield;
    }

    .th-qty-input::-webkit-outer-spin-button,
    .th-qty-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* ===== ADD TO CART BUTTON ===== */
    .th-cart-section {
        text-align: center;
        margin: 18px 0 10px 0;
    }

    .th-add-to-cart-btn {
        background: #702C44;
        color: white;
        font-family: 'Gilory', sans-serif;
        font-size: 18px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        padding: 18px 30px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
    }

    .th-add-to-cart-btn:hover:not(:disabled) {
        background: #8B3555;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(112,44,68,0.30);
    }

    .th-add-to-cart-btn:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    /* ===== DELIVERY INFO ===== */
    .th-delivery-info {
        text-align: center;
        margin-top: 14px;
    }

    .th-delivery-guarantee {
        font-family: 'Arial', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 4px;
    }

    .th-delivery-ships {
        font-family: 'Arial', sans-serif;
        font-size: 14px;
        font-weight: 400;
        color: #777;
    }

    /* ===== MESSAGES ===== */
    .th-message {
        padding: 12px 18px;
        border-radius: 8px;
        margin: 14px 0;
        text-align: center;
        font-weight: 700;
        font-size: 14px;
        display: none;
    }

    .th-error {
        background: #fce4e4;
        border: 2px solid #fcc2c3;
        color: #cc0033;
    }

    .th-success {
        background: #d4edda;
        border: 2px solid #c3e6cb;
        color: #155724;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 600px) {
        /* .th-bf-gift-banner {
            background-image: url("https://mtr3swr11p.wpdns.site/wp-content/uploads/2025/12/New-Year-Label-Mobile-Responsive.png");
            height: 350px;
        } */
        .th-card-title,
        .th-onetime-title {
            font-size: 18px;
        }
        .th-card-per-day {
            font-size: 19px;
        }
        .th-flavor-section-title {
            font-size: 16px;
        }
    }
    </style>

    <div class="th-subscription-wrapper">

        <!-- Banner Image -->
        <!-- <div class="th-bf-gift-banner"></div> -->

        <!-- Subscription Cards -->
        <div class="th-subscription-grid">

            <!-- 3 Month Subscription - Most Popular -->
            <div class="th-subscription-card th-popular-card" data-type="threemonth">
                <span class="th-popular-badge-top">Most Popular Buy 2, Get 1 FREE</span>
                <div class="th-card-top-row">
                    <div class="th-card-title">3-Month Supply</div>
                    <div class="th-card-per-day">$2.22<span class="perday-label">/Day</span></div>
                </div>
                <div class="th-card-top-row">
                  <div class="th-card-ships">Ships Every 90 Days</div>
                  <div class="th-card-price-row">
                      <div class="th-card-reg-price">Reg. $299.97</div>
                      <div class="th-card-sale-price">$199.97</div>
                  </div>
                </div>

                <div class="th-card-desc">Most customers choose this for best results and savings</div>
                <div class="th-card-bonus-box">
                    <span class="bonus-icon">🎁</span>
                    <div class="bonus-text">FREE 3 Heart Beet ultra ($14.97 Value On First Shipment)<br>Pairs with Tikva heart for added circulation support.</div>
                </div>
                <ul class="th-card-bullets">
                    <li>Includes 3 Containers</li>
                    <li>Choose Any Flavor Combination</li>
                    <li>Easy pause, skip, or cancel anytime</li>
                    <li>90-Day Money-Back Guarantee</li>
                </ul>
            </div>

            <!-- 1 Month Subscription -->
            <div class="th-subscription-card" data-type="monthly">
                <div class="th-card-top-row">
                    <div class="th-card-title">1-Month Supply</div>
                    <div class="th-card-per-day">$2.66<span class="perday-label">/Day</span></div>
                </div>
                <div class="th-card-top-row">
                  <div class="th-card-ships">Ships Every 30 Days</div>
                  <div class="th-card-price-row">
                      <div class="th-card-reg-price">Reg. $99.99</div>
                      <div class="th-card-sale-price">$79.99</div>
                  </div>
                </div>

                <div class="th-card-desc">Flexible monthly option – save more with multi-month plans</div>
                <ul class="th-card-bullets">
                    <li>Includes 1 Container</li>
                    <li>Choose Your Flavor</li>
                    <li>Easy pause, skip, or cancel anytime</li>
                </ul>
            </div>

            <!-- One-Time Purchase -->
            <div class="th-onetime-card" data-type="onetime">
                <div class="th-card-left">
                    <div class="th-onetime-title">One-Time Purchase</div>
                    <div class="th-onetime-subtitle">Prefer not to subscribe? No problem.</div>
                    <ul class="th-onetime-bullets">
                        <li>Includes 1 Container</li>
                    </ul>
                </div>
                <div class="th-onetime-price-wrap">
                    <div class="th-onetime-price">$99.99</div>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <div id="th-errorMessage" class="th-message th-error"></div>
        <div id="th-successMessage" class="th-message th-success"></div>

        <!-- Flavor Selection -->
        <h3 class="th-flavor-section-title">Choose Your 3 Containers / Flavors</h3>

        <div class="th-flavor-grid">
            <?php
            $flavor_notes = array(
                'strawberry-watermelon' => '(no sweetener - a bit tart)'
            );

            foreach ($flavors as $flavor):
                $note = isset($flavor_notes[strtolower($flavor['slug'])]) ? ' <span class="th-flavor-name-note">' . $flavor_notes[strtolower($flavor['slug'])] . '</span>' : '';
            ?>
                <div class="th-flavor-card">
                    <div class="th-flavor-left">
                        <?php if (!empty($flavor['image_url'])): ?>
                            <img src="<?php echo esc_url($flavor['image_url']); ?>" alt="<?php echo esc_attr($flavor['name']); ?>" class="th-flavor-img">
                        <?php else: ?>
                            <div class="th-flavor-img-placeholder">🫐</div>
                        <?php endif; ?>
                        <div class="th-flavor-name"><?php echo esc_html($flavor['name']); ?><?php echo $note; ?></div>
                    </div>
                    <div class="th-qty-controls">
                        <button class="th-qty-btn th-qty-minus" data-variation-id="<?php echo esc_attr($flavor['variation_id']); ?>">−</button>
                        <input type="number"
                               class="th-qty-input"
                               value="0"
                               min="0"
                               readonly
                               data-variation-id="<?php echo esc_attr($flavor['variation_id']); ?>">
                        <button class="th-qty-btn th-qty-plus" data-variation-id="<?php echo esc_attr($flavor['variation_id']); ?>">+</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Add to Cart -->
        <div class="th-cart-section">
            <button id="th-addToCartBtn" class="th-add-to-cart-btn" disabled>
                ADD TO CART - $0.00
            </button>
            <div class="th-delivery-info">
                <div class="th-delivery-guarantee" id="th-delivery-guarantee">90 Day – 100% No Risk Money Back Guarantee</div>
                <div class="th-delivery-ships">Ships Next Business Day</div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentType = 'monthly';

        const prices = {
            monthly: { price: 79.99, qty: 1 },
            threemonth: { price: 199.97, qty: 3 },
            onetime: { price: 99.99, qty: 1 }
        };

        const content = {
            monthly: {
                title: 'Choose Your 1 Container / Flavor'
            },
            threemonth: {
                title: 'Pick Your 3 Containers Choose any combination — adjust anytime'
            },
            onetime: {
                title: 'Choose Any Quantity'
            }
        };

        const deliveryText = {
           monthly:    { guarantee: '90 Day – 100% No Risk Money Back Guarantee', ships: 'Ships Next Business Day' },
           threemonth: { guarantee: '180 Day – 100% No Risk Money Back Guarantee', ships: 'Ships Next Business Day' },
           onetime:    { guarantee: '90 Day – 100% No Risk Money Back Guarantee', ships: 'Ships Next Business Day' }
       };

        // Subscription card selection
        document.querySelectorAll('.th-subscription-card, .th-onetime-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.th-subscription-card, .th-onetime-card').forEach(c => {
                    c.classList.remove('active');
                });

                this.classList.add('active');
                currentType = this.dataset.type;

                resetQuantities();
                updateContent();
                updatePricing();
            });
        });

        // Set default active card (threemonth - most popular)
        document.querySelector('.th-subscription-card[data-type="threemonth"]').classList.add('active');
        currentType = 'threemonth';
        updateContent();

        // Quantity controls
        document.querySelectorAll('.th-qty-minus').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const variationId = this.dataset.variationId;
                const input = document.querySelector(`.th-qty-input[data-variation-id="${variationId}"]`);
                const currentVal = parseInt(input.value) || 0;

                if (currentVal > 0) {
                    input.value = currentVal - 1;
                    updateFlavorCardStyle(input);
                    updatePricing();
                }
            });
        });

        document.querySelectorAll('.th-qty-plus').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const variationId = this.dataset.variationId;
                const input = document.querySelector(`.th-qty-input[data-variation-id="${variationId}"]`);
                const currentVal = parseInt(input.value) || 0;

                input.value = currentVal + 1;
                updateFlavorCardStyle(input);
                updatePricing();
            });
        });

        function updateFlavorCardStyle(input) {
            const card = input.closest('.th-flavor-card');
            if (parseInt(input.value) > 0) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        }

        // Add to cart button
        document.getElementById('th-addToCartBtn').addEventListener('click', function() {
            const totalQty = getTotalQuantity();

            if (currentType === 'monthly' && totalQty !== 1) {
                showError('Please select exactly 1 container for the 1-month subscription.');
                return;
            }

            if (currentType === 'threemonth' && totalQty !== 3) {
                showError('Please select exactly 3 containers for the 3-month subscription.');
                return;
            }

            if (totalQty === 0) {
                showError('Please select at least one container.');
                return;
            }

            addToCartAjax();
        });



        function updateContent() {
            const data = content[currentType];
            document.querySelector('.th-flavor-section-title').textContent = data.title;

            const dl = deliveryText[currentType];
            document.getElementById('th-delivery-guarantee').textContent = dl.guarantee;
            document.querySelector('.th-delivery-ships').textContent = dl.ships;
        }

        function resetQuantities() {
            document.querySelectorAll('.th-qty-input').forEach(input => {
                input.value = '0';
                input.closest('.th-flavor-card').classList.remove('selected');
            });
        }

        function getTotalQuantity() {
            let total = 0;
            document.querySelectorAll('.th-qty-input').forEach(input => {
                total += parseInt(input.value) || 0;
            });
            return total;
        }

        function updatePricing() {
            const totalQty = getTotalQuantity();
            const basePrice = prices[currentType].price;
            const totalPrice = basePrice;
            const btn = document.getElementById('th-addToCartBtn');

            btn.textContent = `ADD TO CART - $${totalPrice.toFixed(2)}`;

            let isValid = false;
            if (currentType === 'onetime') {
                isValid = totalQty > 0;
            } else if (currentType === 'monthly') {
                isValid = totalQty === 1;
            } else if (currentType === 'threemonth') {
                isValid = totalQty === 3;
            }

            btn.disabled = !isValid;
        }

        function showError(message) {
            hideMessages();
            const errorEl = document.getElementById('th-errorMessage');
            errorEl.textContent = message;
            errorEl.style.display = 'block';

            setTimeout(() => {
                errorEl.style.display = 'none';
            }, 5000);
        }

        function showSuccess(message) {
            hideMessages();
            const successEl = document.getElementById('th-successMessage');
            successEl.textContent = message;
            successEl.style.display = 'block';

            setTimeout(() => {
                successEl.style.display = 'none';
            }, 5000);
        }

        function hideMessages() {
            document.getElementById('th-errorMessage').style.display = 'none';
            document.getElementById('th-successMessage').style.display = 'none';
        }

        function addToCartAjax() {
            const btn = document.getElementById('th-addToCartBtn');
            const originalText = btn.textContent;
            btn.textContent = 'Adding to Cart...';
            btn.disabled = true;
            hideMessages();

            const quantities = {};
            document.querySelectorAll('.th-qty-input').forEach(input => {
                const qty = parseInt(input.value) || 0;
                if (qty > 0) {
                    quantities[input.dataset.variationId] = qty;
                }
            });

            const formData = new FormData();
            formData.append('action', 'th_subscription_add_to_cart_ajax');
            formData.append('nonce', '<?php echo wp_create_nonce("th_subscription_ajax_cart"); ?>');
            formData.append('subscription_type', currentType);
            formData.append('quantities', JSON.stringify(quantities));

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data.data.message || 'Products added to cart successfully!');

                    if (typeof jQuery !== 'undefined') {
                        jQuery(document.body).trigger('wc_fragment_refresh');

                        setTimeout(() => {
                            if (jQuery('.wfacp_mini_cart_open, .fkcart-trigger, [data-flyout-class]').length > 0) {
                                jQuery('.wfacp_mini_cart_open, .fkcart-trigger, [data-flyout-class]').first().trigger('click');
                            }

                            jQuery(document.body).trigger('wc_add_to_cart');
                            jQuery(document.body).trigger('added_to_cart');

                            if (typeof window.fkcart !== 'undefined' && typeof window.fkcart.openSideCart === 'function') {
                                window.fkcart.openSideCart();
                            }

                            const fkCartWrapper = document.querySelector('.wfacp-mini-cart-wrapper, .fkcart-wrapper, #wfacp_qv_div');
                            if (fkCartWrapper) {
                                fkCartWrapper.classList.add('wfacp-active', 'open');
                                document.body.classList.add('wfacp-mini-cart-opened');
                            }
                        }, 300);
                    }

                    setTimeout(() => {
                        resetQuantities();
                        updatePricing();
                    }, 2000);
                } else {
                    showError(data.data.message || 'Error adding products to cart');
                }

                btn.textContent = originalText;
                btn.disabled = false;
            })
            .catch(error => {
                console.error('Error:', error);
                showError('An error occurred. Please try again.');
                btn.textContent = originalText;
                btn.disabled = false;
            });
        }

        // Initialize
        updateContent();
        updatePricing();
    });
    </script>

    <?php
    return ob_get_clean();
}

add_shortcode('TH_Subscription', 'TH_Subscription_shortcode2');add_action('wp_ajax_th_subscription_add_to_cart_ajax', 'handle_th_subscription_add_to_cart_ajax');
add_action('wp_ajax_nopriv_th_subscription_add_to_cart_ajax', 'handle_th_subscription_add_to_cart_ajax');

function handle_th_subscription_add_to_cart_ajax() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'th_subscription_ajax_cart')) {
        wp_send_json_error(array('message' => 'Security verification failed'));
        return;
    }

    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array('message' => 'WooCommerce is not available'));
        return;
    }

    $subscription_type = sanitize_text_field($_POST['subscription_type']);
    $quantities = json_decode(stripslashes($_POST['quantities']), true);

    if (empty($quantities)) {
        wp_send_json_error(array('message' => 'Please select at least one product'));
        return;
    }

    try {
        WC()->cart->empty_cart();

        $added_count = 0;

        $first_variation_id = array_key_first($quantities);
        $first_variation = wc_get_product($first_variation_id);
        $parent_id = $first_variation ? $first_variation->get_parent_id() : 0;

        if ($subscription_type === 'monthly') {
            $_POST['convert_to_sub_' . $parent_id] = '1_month';
        } elseif ($subscription_type === 'threemonth') {
            $_POST['convert_to_sub_' . $parent_id] = '3_month';
        } else {
            $_POST['convert_to_sub_' . $parent_id] = '0';
        }

        foreach ($quantities as $variation_id => $qty) {
            $variation_id = intval($variation_id);
            $qty = intval($qty);

            if ($qty <= 0) continue;

            $variation = wc_get_product($variation_id);
            if (!$variation) continue;

            $parent_id = $variation->get_parent_id();
            $attributes = $variation->get_variation_attributes();

            $result = WC()->cart->add_to_cart($parent_id, $qty, $variation_id, $attributes);

            if ($result) {
                $added_count++;
            }
        }

        if ($added_count > 0) {
            if ($subscription_type === 'monthly') {
                $coupon_code = 'cshs30';
            } elseif ($subscription_type === 'threemonth') {
                $coupon_code = 'cshs50';
            } else {
                $coupon_code = 'cshs10';
            }

            if (!empty($coupon_code)) {
                $coupon = new WC_Coupon($coupon_code);

                if ($coupon && $coupon->get_id()) {
                    $applied = WC()->cart->apply_coupon($coupon_code);

                    if (!$applied) {
                        error_log('TH Subscription: Failed to apply coupon ' . $coupon_code);
                    }
                } else {
                    error_log('TH Subscription: Coupon ' . $coupon_code . ' does not exist');
                }
            }

            WC()->cart->calculate_totals();

            $total_qty = array_sum($quantities);
            wp_send_json_success(array(
                'message' => $total_qty . ' product(s) added to cart successfully!',
                'cart_count' => WC()->cart->get_cart_contents_count(),
                'cart_total' => WC()->cart->get_cart_total()
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to add products to cart'));
        }

    } catch (Exception $e) {
        wp_send_json_error(array('message' => 'Error: ' . $e->getMessage()));
    }
}

// Filter to ensure WCSATT applies subscription scheme to cart items
add_filter('wcsatt_set_subscription_scheme_id', 'apply_th_subscription_scheme', 10, 3);
function apply_th_subscription_scheme($scheme_id, $cart_item, $cart_item_key) {
    $product_id = isset($cart_item['product_id']) ? $cart_item['product_id'] : 0;

    if ($product_id && isset($_POST['convert_to_sub_' . $product_id])) {
        $custom_scheme = sanitize_text_field($_POST['convert_to_sub_' . $product_id]);
        if ($custom_scheme !== '0') {
            return $custom_scheme;
        }
    }

    return $scheme_id;
}

// Apply subscription pricing based on scheme
add_filter('woocommerce_add_cart_item_data', 'add_th_subscription_scheme_to_cart_item', 10, 3);
function add_th_subscription_scheme_to_cart_item($cart_item_data, $product_id, $variation_id) {
    if (isset($_POST['convert_to_sub_' . $product_id])) {
        $scheme = sanitize_text_field($_POST['convert_to_sub_' . $product_id]);
        $cart_item_data['subscription_scheme'] = $scheme;
    }

    return $cart_item_data;
}

// Add custom cart item data to identify subscription items
add_filter('woocommerce_add_cart_item_data', 'mark_subscription_item_in_cart', 20, 3);
function mark_subscription_item_in_cart($cart_item_data, $product_id, $variation_id) {
    if (isset($_POST['convert_to_sub_' . $product_id])) {
        $scheme = sanitize_text_field($_POST['convert_to_sub_' . $product_id]);

        if ($scheme !== '0') {
            $cart_item_data['is_subscription'] = 'yes';
            $cart_item_data['subscription_type'] = $scheme;
        } else {
            $cart_item_data['is_subscription'] = 'no';
        }
    }

    return $cart_item_data;
}

