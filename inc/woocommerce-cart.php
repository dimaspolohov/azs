<?php
/**
 * WooCommerce Cart: custom cart page, AJAX handlers, direct order placement.
 */

if (!defined('ABSPATH')) exit;

// ------------------------------------------------------------------
// Body class
// ------------------------------------------------------------------
add_filter('body_class', 'azs_cart_body_class');
function azs_cart_body_class($classes) {
    if (function_exists('is_cart') && is_cart()) {
        $classes[] = 'page-cart';
    }
    return $classes;
}

// ------------------------------------------------------------------
// Override cart page template with custom full-page layout
// ------------------------------------------------------------------
add_filter('template_include', 'azs_cart_template_override');
function azs_cart_template_override($template) {
    if (function_exists('is_cart') && is_cart()) {
        $custom = get_stylesheet_directory() . '/woocommerce/cart-page.php';
        if (file_exists($custom)) return $custom;
    }
    return $template;
}

// ------------------------------------------------------------------
// Helpers
// ------------------------------------------------------------------
function azs_format_price($price) {
    return number_format((float) $price, 2, ',', ' ');
}

function azs_cart_price_breakdown() {
    $cart     = WC()->cart;
    $subtotal = 0;
    $full     = 0;

    foreach ($cart->get_cart() as $item) {
        $product = $item['data'];
        $qty     = $item['quantity'];
        $price   = (float) $product->get_price();
        $regular = (float) $product->get_regular_price();

        $subtotal += $price * $qty;
        $full     += ($regular > 0 ? $regular : $price) * $qty;
    }

    $discount = $full - $subtotal;

    return [
        'total'        => $subtotal,
        'full_total'   => $full,
        'discount'     => $discount,
        'has_discount' => $discount > 0.01,
    ];
}

// ------------------------------------------------------------------
// Render single cart item row
// ------------------------------------------------------------------
function azs_render_cart_item($key, $cart_item) {
    $product       = $cart_item['data'];
    $product_id    = $cart_item['product_id'];
    $qty           = $cart_item['quantity'];
    $price         = (float) $product->get_price();
    $regular_price = (float) $product->get_regular_price();
    $is_on_sale    = $product->is_on_sale();
    $sku           = $product->get_sku();
    $image_url     = get_the_post_thumbnail_url($product_id, 'medium');
    $title         = $product->get_name();
    $line_total    = $price * $qty;
    $line_regular  = $regular_price > 0 ? $regular_price * $qty : $line_total;

    ob_start();
    ?>
    <div class="card" data-cart-item-key="<?php echo esc_attr($key); ?>">
        <div class="card--image">
            <img src="<?php echo esc_url($image_url ?: get_stylesheet_directory_uri() . '/assets/img/empty.jpg'); ?>"
                 alt="<?php echo esc_attr($title); ?>">
        </div>
        <div class="card--title">
            <?php if ($sku) : ?>
                <span>Артикул: <?php echo esc_html($sku); ?></span>
            <?php endif; ?>
            <p><?php echo esc_html($title); ?></p>
        </div>
        <div class="card--price">
            <div class="card--price-current">
                <span><?php echo esc_html(azs_format_price($price)); ?></span><span>₽</span>
            </div>
            <?php if ($is_on_sale && $regular_price > $price) : ?>
                <span class="card--price-old">
                    <span><?php echo esc_html(azs_format_price($regular_price)); ?></span><span>₽</span>
                </span>
            <?php endif; ?>
        </div>
        <div class="card--count">
            <div class="button button-outlined azs-qty-minus"><span class="icon-minus"></span></div>
            <div class="button button-outlined azs-qty-display"><span><?php echo esc_html($qty); ?></span></div>
            <div class="button button-outlined azs-qty-plus"><span class="icon-plus"></span></div>
        </div>
        <div class="card--total">
            <div class="card--total-current">
                <span><?php echo esc_html(azs_format_price($line_total)); ?></span><span>₽</span>
            </div>
            <?php if ($is_on_sale && $line_regular > $line_total) : ?>
                <div class="card--total-old">
                    <span><?php echo esc_html(azs_format_price($line_regular)); ?></span><span>₽</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// ------------------------------------------------------------------
// Render totals block
// ------------------------------------------------------------------
function azs_render_cart_totals() {
    $b = azs_cart_price_breakdown();
    ob_start();
    ?>
    <div class="block--price-current">
        <p>Стоимость:</p>
        <div class="price"><span><?php echo esc_html(azs_format_price($b['total'])); ?></span><span>₽</span></div>
    </div>
    <?php if ($b['has_discount']) : ?>
        <div class="block--price-sale">
            <p>Без учета скидки:</p>
            <div class="price"><span><?php echo esc_html(azs_format_price($b['full_total'])); ?></span><span>₽</span></div>
        </div>
        <div class="block--price-sale">
            <p>Скидка:</p>
            <div class="price"><span><?php echo esc_html(azs_format_price($b['discount'])); ?></span><span>₽</span></div>
        </div>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

// ------------------------------------------------------------------
// Cart data for AJAX responses
// ------------------------------------------------------------------
function azs_get_cart_response_data() {
    $cart = WC()->cart;
    $html = '';

    foreach ($cart->get_cart() as $key => $item) {
        $html .= azs_render_cart_item($key, $item);
    }

    $breakdown = azs_cart_price_breakdown();

    return [
        'items_html'  => $html,
        'totals_html' => azs_render_cart_totals(),
        'side_total'  => azs_format_price($breakdown['total']),
        'cart_count'  => $cart->get_cart_contents_count(),
        'is_empty'    => $cart->is_empty(),
        'related_html'=> azs_render_cart_related_products(),
    ];
}

// ------------------------------------------------------------------
// Related products for cart page
// ------------------------------------------------------------------
function azs_get_cart_related_product_ids($limit = 4) {
    $cart  = WC()->cart;
    $limit = max(1, absint($limit));

    if (!$cart || $cart->is_empty()) {
        return array();
    }

    $cart_ids = array();
    foreach ($cart->get_cart() as $item) {
        $cart_ids[] = absint($item['product_id']);
        if (!empty($item['variation_id'])) {
            $cart_ids[] = absint($item['variation_id']);
        }
    }
    $cart_ids = array_values(array_unique(array_filter($cart_ids)));

    $groups = array();
    foreach ($cart->get_cart() as $item) {
        $product_id = absint($item['product_id']);
        if (!$product_id || isset($groups[$product_id])) {
            continue;
        }

        $ids = function_exists('azs_get_product_recommended_ids')
            ? azs_get_product_recommended_ids($product_id, 0, $cart_ids)
            : array();

        if (!empty($ids)) {
            shuffle($ids);
            $groups[$product_id] = $ids;
        }
    }

    if (empty($groups)) {
        return array();
    }

    if (count($groups) === 1) {
        $only_group = reset($groups);
        return array_slice($only_group, 0, $limit);
    }

    $selected = array();
    $taken_by_product = array_fill_keys(array_keys($groups), 0);

    $group_keys = array_keys($groups);
    shuffle($group_keys);

    foreach ($group_keys as $product_id) {
        if (count($selected) >= $limit) {
            break;
        }

        $candidate = array_shift($groups[$product_id]);
        if ($candidate && !in_array($candidate, $selected, true)) {
            $selected[] = $candidate;
            $taken_by_product[$product_id]++;
        }
    }

    shuffle($group_keys);
    foreach ($group_keys as $product_id) {
        if (count($selected) >= $limit) {
            break;
        }

        if ($taken_by_product[$product_id] >= 2 || empty($groups[$product_id])) {
            continue;
        }

        foreach ($groups[$product_id] as $candidate) {
            if (!in_array($candidate, $selected, true)) {
                $selected[] = $candidate;
                $taken_by_product[$product_id]++;
                break;
            }
        }
    }

    return array_slice($selected, 0, $limit);
}

function azs_render_cart_related_products($limit = 4) {
    $related_ids = azs_get_cart_related_product_ids($limit);
    if (empty($related_ids) || !function_exists('azs_render_recommended_products_block')) {
        return '';
    }

    return azs_render_recommended_products_block($related_ids);
}

// ------------------------------------------------------------------
// AJAX: update cart item quantity
// ------------------------------------------------------------------
add_action('wp_ajax_azs_update_cart_quantity', 'azs_update_cart_quantity');
add_action('wp_ajax_nopriv_azs_update_cart_quantity', 'azs_update_cart_quantity');
function azs_update_cart_quantity() {
    check_ajax_referer('azs_nonce', 'nonce');

    $key = sanitize_text_field(wp_unslash($_POST['cart_item_key'] ?? ''));
    $qty = max(0, intval($_POST['quantity'] ?? 1));

    if (!$key || !function_exists('WC')) {
        wp_send_json_error(['message' => 'Invalid request']);
        return;
    }

    if ($qty === 0) {
        WC()->cart->remove_cart_item($key);
    } else {
        WC()->cart->set_quantity($key, $qty);
    }

    wp_send_json_success(azs_get_cart_response_data());
}

// ------------------------------------------------------------------
// AJAX: remove cart item
// ------------------------------------------------------------------
add_action('wp_ajax_azs_remove_cart_item', 'azs_remove_cart_item');
add_action('wp_ajax_nopriv_azs_remove_cart_item', 'azs_remove_cart_item');
function azs_remove_cart_item() {
    check_ajax_referer('azs_nonce', 'nonce');

    $key = sanitize_text_field(wp_unslash($_POST['cart_item_key'] ?? ''));

    if (!$key || !function_exists('WC')) {
        wp_send_json_error(['message' => 'Invalid request']);
        return;
    }

    WC()->cart->remove_cart_item($key);

    wp_send_json_success(azs_get_cart_response_data());
}

// ------------------------------------------------------------------
// AJAX: place order directly from cart (no WC checkout)
// ------------------------------------------------------------------
add_action('wp_ajax_azs_place_order', 'azs_place_order');
add_action('wp_ajax_nopriv_azs_place_order', 'azs_place_order');
function azs_place_order() {
    check_ajax_referer('azs_nonce', 'nonce');

    $name  = sanitize_text_field(wp_unslash($_POST['name']  ?? ''));
    $phone = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));

    if (!$name || !$phone) {
        wp_send_json_error(['message' => 'Заполните все обязательные поля']);
        return;
    }

    $cart = WC()->cart;
    if ($cart->is_empty()) {
        wp_send_json_error(['message' => 'Корзина пуста']);
        return;
    }

    try {
        $order = wc_create_order();

        foreach ($cart->get_cart() as $cart_item) {
            $order->add_product($cart_item['data'], $cart_item['quantity']);
        }

        $parts      = preg_split('/\s+/', trim($name), 3);
        $last_name  = $parts[0] ?? '';
        $first_name = $parts[1] ?? '';

        $order->set_billing_first_name($first_name ?: $last_name);
        $order->set_billing_last_name($first_name ? $last_name : '');
        $order->set_billing_phone($phone);

        $order->calculate_totals();
        $order->update_status('on-hold', 'Быстрый заказ с сайта');
        $order->save();

        azs_send_order_email($order, $name, $phone);

        $cart->empty_cart();

        wp_send_json_success([
            'order_id'     => $order->get_id(),
            'order_number' => $order->get_order_number(),
        ]);
    } catch (Exception $e) {
        wp_send_json_error(['message' => 'Ошибка при создании заказа']);
    }
}

// ------------------------------------------------------------------
// Order notification email to admin
// ------------------------------------------------------------------
function azs_send_order_email($order, $name, $phone) {
    $admin_email  = get_option('admin_email');
    $site_name    = get_bloginfo('name');
    $order_number = $order->get_order_number();

    $subject = "Новый заказ №{$order_number} — {$site_name}";

    $msg  = "Новый заказ с сайта\n";
    $msg .= "========================\n\n";
    $msg .= "Заказ: №{$order_number}\n";
    $msg .= "Дата: " . date_i18n('d.m.Y H:i') . "\n\n";
    $msg .= "Контакты заказчика:\n";
    $msg .= "Имя: {$name}\n";
    $msg .= "Телефон: {$phone}\n\n";
    $msg .= "Состав заказа:\n";
    $msg .= "------------------------\n";

    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        $qty     = $item->get_quantity();
        $total   = $item->get_total();
        $sku     = $product ? $product->get_sku() : '';

        $msg .= "• {$item->get_name()}";
        if ($sku) $msg .= " (арт. {$sku})";
        $msg .= "\n  Кол-во: {$qty}, Сумма: " . azs_format_price($total) . " ₽\n";
    }

    $msg .= "------------------------\n";
    $msg .= "Итого: " . azs_format_price($order->get_total()) . " ₽\n\n";
    $msg .= "Сайт: " . home_url('/') . "\n";
    $msg .= "Управление заказом: " . admin_url('post.php?post=' . $order->get_id() . '&action=edit') . "\n";

    wp_mail($admin_email, $subject, $msg, ['Content-Type: text/plain; charset=UTF-8']);
}

// ------------------------------------------------------------------
// Inline JS for cart page
// ------------------------------------------------------------------
add_action('wp_footer', 'azs_cart_inline_js', 50);
function azs_cart_inline_js() {
    if (!function_exists('is_cart') || !is_cart()) return;
    ?>
    <script>
    (function() {
        'use strict';

        var ajaxUrl = (typeof azs_ajax !== 'undefined') ? azs_ajax.ajax_url : '';
        var nonce   = (typeof azs_ajax !== 'undefined') ? azs_ajax.nonce : '';
        if (!ajaxUrl) return;

        var cartBlock  = document.getElementById('azs-cart');
        var cartItems  = document.getElementById('azs-cart-items');
        var cartTotals = document.getElementById('azs-cart-totals');
        var cartRelated = document.getElementById('azs-cart-related');
        var sideTotal  = document.getElementById('azs-side-total');
        var orderForm  = document.getElementById('azs-order-form');

        /* ---- Quantity +/- ---- */
        if (cartBlock) {
            cartBlock.addEventListener('click', function(e) {
                var minus = e.target.closest('.azs-qty-minus');
                var plus  = e.target.closest('.azs-qty-plus');
                if (!minus && !plus) return;
                e.preventDefault();

                var card = (minus || plus).closest('.card[data-cart-item-key]');
                if (!card) return;

                var key        = card.dataset.cartItemKey;
                var display    = card.querySelector('.azs-qty-display span');
                var currentQty = parseInt(display.textContent, 10) || 1;
                var newQty     = minus ? currentQty - 1 : currentQty + 1;
                if (newQty < 0) newQty = 0;

                updateCartItem(key, newQty);
            });
        }

        function updateCartItem(key, qty) {
            var fd = new FormData();
            fd.append('action', qty === 0 ? 'azs_remove_cart_item' : 'azs_update_cart_quantity');
            fd.append('nonce', nonce);
            fd.append('cart_item_key', key);
            if (qty > 0) fd.append('quantity', qty);

            if (cartItems) cartItems.style.opacity = '0.5';

            fetch(ajaxUrl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (!res.success) { if (cartItems) cartItems.style.opacity = ''; return; }
                    if (res.data.is_empty) { location.reload(); return; }

                    if (cartItems) {
                        cartItems.innerHTML = res.data.items_html;
                        cartItems.style.opacity = '';
                    }
                    if (cartTotals) cartTotals.innerHTML = res.data.totals_html;
                    if (cartRelated) cartRelated.innerHTML = res.data.related_html || '';
                    if (sideTotal)  sideTotal.innerHTML  = '<span>' + res.data.side_total + '</span><span>₽</span>';

                    updateHeaderCart(res.data.cart_count);
                })
                .catch(function() { if (cartItems) cartItems.style.opacity = ''; });
        }

        /* ---- Order form ---- */
        if (orderForm) {
            orderForm.addEventListener('submit', function(e) {
                e.preventDefault();

                var nameVal  = orderForm.querySelector('input[name="order_name"]');
                var phoneVal = orderForm.querySelector('input[name="order_phone"]');
                var btn      = orderForm.querySelector('[type="submit"]');

                if (!nameVal || !phoneVal || !nameVal.value.trim() || !phoneVal.value.trim()) return;
                if (btn) btn.disabled = true;

                var fd = new FormData();
                fd.append('action', 'azs_place_order');
                fd.append('nonce', nonce);
                fd.append('name', nameVal.value.trim());
                fd.append('phone', phoneVal.value.trim());

                fetch(ajaxUrl, { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        if (res.success) {
                            orderForm.reset();
                            updateHeaderCart(0);
                            if (typeof PopupManager !== 'undefined') PopupManager.open('popup_success');
                            setTimeout(function() { location.reload(); }, 3000);
                        } else {
                            if (typeof PopupManager !== 'undefined') PopupManager.open('popup_error');
                        }
                    })
                    .catch(function() {
                        if (typeof PopupManager !== 'undefined') PopupManager.open('popup_error');
                    })
                    .finally(function() { if (btn) btn.disabled = false; });
            });
        }

        /* ---- Add to cart (related products section) ---- */
        document.addEventListener('click', function(e) {
            var cartBtn = e.target.closest('.azs-add-to-cart');
            if (!cartBtn) return;
            e.preventDefault();
            e.stopPropagation();

            var pid = cartBtn.dataset.productId;
            cartBtn.disabled = true;

            var fd = new FormData();
            fd.append('action', 'azs_add_to_cart');
            fd.append('product_id', pid);

            fetch(ajaxUrl, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (!res.success) { cartBtn.disabled = false; return; }

                    cartBtn.outerHTML =
                        '<button class="button button-filled button-secondary" data-cart data-product-id="' + pid + '">' +
                        '<span>В корзине</span><span class="icon-check"></span></button>';

                    var popup = document.querySelector('[data-popup="popup_appended"]');
                    if (popup) {
                        var img = popup.querySelector('.card--image');
                        var pEl = popup.querySelector('.card--title p');
                        var sEl = popup.querySelector('.card--title span');
                        if (img && res.data.product_image) img.innerHTML = '<img src="' + res.data.product_image + '">';
                        if (pEl) pEl.textContent = res.data.product_name;
                        if (sEl) sEl.textContent = 'Артикул: ' + (res.data.product_sku || '');
                    }

                    updateHeaderCart(res.data.cart_count);
                    if (res.data.cart) {
                        if (cartItems) cartItems.innerHTML = res.data.cart.items_html;
                        if (cartTotals) cartTotals.innerHTML = res.data.cart.totals_html;
                        if (cartRelated) cartRelated.innerHTML = res.data.cart.related_html || '';
                        if (sideTotal) sideTotal.innerHTML = '<span>' + res.data.cart.side_total + '</span><span>₽</span>';
                    }
                    if (typeof PopupManager !== 'undefined') PopupManager.open('popup_appended');
                })
                .catch(function() { cartBtn.disabled = false; });
        });

        function updateHeaderCart(count) {
            var el = document.querySelector('.header__actions .button-accent[data-product]');
            if (el) el.setAttribute('data-product', count);
        }
    })();
    </script>
    <?php
}
