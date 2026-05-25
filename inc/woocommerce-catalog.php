<?php
/**
 * WooCommerce Catalog: helpers, AJAX handlers, inline JS.
 */

if (!defined('ABSPATH')) exit;

// ------------------------------------------------------------------
// "Новинка" checkbox in the General product data tab (below prices)
// ------------------------------------------------------------------
add_action('woocommerce_product_options_general_product_data', 'azs_product_is_new_field');
function azs_product_is_new_field() {
    woocommerce_wp_checkbox(array(
        'id'          => 'is_new',
        'label'       => 'Новинка',
        'description' => 'Отметьте, чтобы показывать метку «НОВИНКА» в каталоге',
    ));
}

add_action('woocommerce_process_product_meta', 'azs_save_product_is_new_field');
function azs_save_product_is_new_field($post_id) {
    $value = isset($_POST['is_new']) ? 'yes' : 'no';
    update_post_meta($post_id, 'is_new', $value);
}

// ------------------------------------------------------------------
// Body class
// ------------------------------------------------------------------
add_filter('body_class', 'azs_catalog_body_class');
function azs_catalog_body_class($classes) {
    if (function_exists('is_shop') && (is_shop() || is_product_taxonomy())) {
        $classes[] = 'page-catalog';
    }
    if (function_exists('is_product') && is_product()) {
        $classes[] = 'page-product';
    }
    return $classes;
}

// ------------------------------------------------------------------
// Remove default Storefront / WC wrappers on shop pages
// ------------------------------------------------------------------
add_action('init', 'azs_remove_wc_wrappers');
function azs_remove_wc_wrappers() {
    remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
    remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
    remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
}

// ------------------------------------------------------------------
// Register "Вид жидкости" taxonomy (sidebar checkboxes, like Бренды)
// ------------------------------------------------------------------
add_action('init', 'azs_register_liquid_type_taxonomy', 5);
function azs_register_liquid_type_taxonomy() {
    if (taxonomy_exists('product_liquid_type')) return;

    register_taxonomy('product_liquid_type', array('product'), array(
        'labels' => array(
            'name'          => 'Виды жидкостей',
            'singular_name' => 'Вид жидкости',
            'search_items'  => 'Найти вид жидкости',
            'all_items'     => 'Все виды жидкостей',
            'edit_item'     => 'Редактировать вид жидкости',
            'update_item'   => 'Обновить вид жидкости',
            'add_new_item'  => 'Добавить вид жидкости',
            'new_item_name' => 'Новый вид жидкости',
            'menu_name'     => 'Виды жидкостей',
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_menu'      => true,
        'show_tagcloud'     => false,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'liquid-type'),
    ));
}

// ------------------------------------------------------------------
// Auto-detect brand taxonomy registered by a plugin or WC attribute
// ------------------------------------------------------------------
function azs_get_brand_taxonomy() {
    static $found = null;
    if ($found !== null) return $found;

    $candidates = array('product_brand', 'pwb-brand', 'yith_product_brand', 'pa_brand');
    foreach ($candidates as $tax) {
        if (taxonomy_exists($tax)) {
            $found = $tax;
            return $found;
        }
    }

    $taxonomies = get_object_taxonomies('product', 'objects');
    foreach ($taxonomies as $tax) {
        $label = mb_strtolower($tax->label);
        if (strpos($label, 'бренд') !== false || strpos($label, 'brand') !== false) {
            $found = $tax->name;
            return $found;
        }
    }

    $found = '';
    return $found;
}

// ------------------------------------------------------------------
// Search helpers
// ------------------------------------------------------------------

/**
 * Generate search term variants to handle:
 * - Extra/multiple whitespace (e.g. "АСТР 80" → "АСТР80")
 * - Visually identical Russian/Latin characters typed interchangeably
 *   (e.g. user types "АСТР80" with Cyrillic letters, but title stored as "ACTP80")
 */
function azs_get_search_variants($term) {
    $term = trim($term);
    if ($term === '') {
        return array();
    }

    // Visually identical uppercase pairs: Cyrillic → Latin
    $ru_to_lat = array(
        'А' => 'A', 'В' => 'B', 'С' => 'C', 'Е' => 'E',
        'Н' => 'H', 'К' => 'K', 'М' => 'M', 'О' => 'O',
        'Р' => 'P', 'Т' => 'T', 'Х' => 'X',
        'а' => 'a', 'в' => 'b', 'с' => 'c', 'е' => 'e',
        'н' => 'h', 'к' => 'k', 'м' => 'm', 'о' => 'o',
        'р' => 'p', 'т' => 't', 'х' => 'x',
    );

    // Latin → Cyrillic (reverse)
    $lat_to_ru = array(
        'A' => 'А', 'B' => 'В', 'C' => 'С', 'E' => 'Е',
        'H' => 'Н', 'K' => 'К', 'M' => 'М', 'O' => 'О',
        'P' => 'Р', 'T' => 'Т', 'X' => 'Х',
        'a' => 'а', 'b' => 'в', 'c' => 'с', 'e' => 'е',
        'h' => 'н', 'k' => 'к', 'm' => 'м', 'o' => 'о',
        'p' => 'р', 't' => 'т', 'x' => 'х',
    );

    // Base variants: original and without whitespace
    $collapsed = preg_replace('/\s+/', ' ', $term);
    $no_spaces  = preg_replace('/\s+/', '', $term);
    $bases      = array_unique(array($collapsed, $no_spaces));

    $variants = $bases;
    foreach ($bases as $base) {
        $variants[] = strtr($base, $ru_to_lat);
        $variants[] = strtr($base, $lat_to_ru);
    }

    return array_unique($variants);
}

function azs_get_search_tokens($term) {
    $term = trim($term);
    if ($term === '') {
        return array();
    }

    $tokens = preg_split('/[^\p{L}\p{N}]+/u', $term, -1, PREG_SPLIT_NO_EMPTY);
    if (empty($tokens)) {
        return array();
    }

    return array_values(array_unique($tokens));
}

// ------------------------------------------------------------------
// Build WP_Query args from filter parameters
// ------------------------------------------------------------------
function azs_get_catalog_query_args($params = array()) {
    global $wpdb;

    $defaults = array(
        'categories'    => array(),
        'liquid_types'  => array(),
        'manufacturers' => array(),
        'search'        => '',
        'sort'          => 'default',
        'page'          => 1,
        'per_page'      => 12,
        'is_new'        => false,
        'is_sale'       => false,
        'base_cat_id'   => 0,
    );
    $p = wp_parse_args($params, $defaults);

    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => intval($p['per_page']),
        'paged'          => max(1, intval($p['page'])),
    );

    switch ($p['sort']) {
        case 'new':
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
            break;
        case 'price-asc':
            $args['meta_key'] = '_price';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'ASC';
            break;
        case 'price-desc':
            $args['meta_key'] = '_price';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
            break;
        default:
            $args['orderby'] = 'menu_order title';
            $args['order']   = 'ASC';
            break;
    }

    $tax_query = array('relation' => 'AND');
    $has_tax   = false;

    $cat_ids = array_filter(array_map('intval', (array) $p['categories']));
    if (!empty($cat_ids)) {
        $tax_query[] = array(
            'taxonomy'         => 'product_cat',
            'field'            => 'term_id',
            'terms'            => $cat_ids,
            'include_children' => true,
        );
        $has_tax = true;
    } elseif (intval($p['base_cat_id']) > 0) {
        $tax_query[] = array(
            'taxonomy'         => 'product_cat',
            'field'            => 'term_id',
            'terms'            => intval($p['base_cat_id']),
            'include_children' => true,
        );
        $has_tax = true;
    }

    $liquid_ids = array_filter(array_map('intval', (array) $p['liquid_types']));
    if (!empty($liquid_ids) && taxonomy_exists('product_liquid_type')) {
        $tax_query[] = array(
            'taxonomy' => 'product_liquid_type',
            'field'    => 'term_id',
            'terms'    => $liquid_ids,
        );
        $has_tax = true;
    }

    $brand_tax = azs_get_brand_taxonomy();
    $mfr_ids   = array_filter(array_map('intval', (array) $p['manufacturers']));
    if (!empty($mfr_ids) && $brand_tax) {
        $tax_query[] = array(
            'taxonomy' => $brand_tax,
            'field'    => 'term_id',
            'terms'    => $mfr_ids,
        );
        $has_tax = true;
    }

    if ($has_tax) {
        $args['tax_query'] = $tax_query;
    }

    $search = trim($p['search']);
    if ($search !== '') {
        $variants    = azs_get_search_variants($search);
        $conditions  = array();
        $params      = array();
        foreach ($variants as $variant) {
            $conditions[] = 'p.post_title LIKE %s';
            $params[]     = '%' . $wpdb->esc_like($variant) . '%';
        }

        $token_conditions = array();
        foreach (azs_get_search_tokens($search) as $token) {
            $token_variants = azs_get_search_variants($token);
            if (empty($token_variants)) {
                continue;
            }

            $token_like = array();
            foreach ($token_variants as $variant) {
                $token_like[] = 'p.post_title LIKE %s';
                $params[]     = '%' . $wpdb->esc_like($variant) . '%';
            }

            $token_conditions[] = '(' . implode(' OR ', $token_like) . ')';
        }

        if (!empty($token_conditions)) {
            $conditions[] = '(' . implode(' AND ', $token_conditions) . ')';
        }

        $where       = implode(' OR ', $conditions);
        $product_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT p.ID FROM {$wpdb->posts} p
                 WHERE p.post_type = 'product' AND p.post_status = 'publish'
                 AND ({$where})",
                ...$params
            )
        );
        $args['post__in'] = !empty($product_ids) ? $product_ids : array(0);
    }

    if ($p['is_new']) {
        $existing_meta = isset($args['meta_query']) ? $args['meta_query'] : array('relation' => 'AND');
        $existing_meta[] = array(
            'key'     => 'is_new',
            'value'   => 'yes',
            'compare' => '=',
        );
        $args['meta_query'] = $existing_meta;
    }

    if ($p['is_sale'] && function_exists('wc_get_product_ids_on_sale')) {
        $sale_ids = wc_get_product_ids_on_sale();
        if (!empty($sale_ids)) {
            $existing = isset($args['post__in']) ? $args['post__in'] : array();
            if (!empty($existing) && $existing !== array(0)) {
                $args['post__in'] = array_values(array_intersect($existing, $sale_ids));
                if (empty($args['post__in'])) $args['post__in'] = array(0);
            } else {
                $args['post__in'] = $sale_ids;
            }
        } else {
            $args['post__in'] = array(0);
        }
    }

    return $args;
}

// ------------------------------------------------------------------
// Render a single product card (matches catalog.html .card markup)
// ------------------------------------------------------------------
function azs_render_product_card($product_id) {
    $product = wc_get_product($product_id);
    if (!$product) return '';

    $is_in_stock   = $product->is_in_stock();
    $is_on_sale    = $product->is_on_sale();
    $is_new        = get_post_meta($product_id, 'is_new', true) === 'yes';
    $sku           = $product->get_sku();
    $stock_qty     = $product->get_stock_quantity();
    $permalink     = get_permalink($product_id);
    $image_url     = get_the_post_thumbnail_url($product_id, 'medium_large');
    $title         = get_the_title($product_id);
    $regular_price = $product->get_regular_price();
    $sale_price    = $product->get_sale_price();
    $price         = $product->get_price();

    $in_cart = false;
    if (function_exists('WC') && WC()->cart) {
        foreach (WC()->cart->get_cart() as $cart_item) {
            if ((int) $cart_item['product_id'] === (int) $product_id) {
                $in_cart = true;
                break;
            }
        }
    }

    ob_start();
    ?>
    <div class="card showInUp" data-product-id="<?php echo esc_attr($product_id); ?>">
        <a class="card--link" href="<?php echo esc_url($permalink); ?>"></a>
        <div class="card--image">
            <?php
                $img_src = $image_url ? $image_url : get_stylesheet_directory_uri() . '/assets/img/empty.jpg';
            ?>
                <img src="<?php echo esc_url($img_src); ?>" alt="<?php echo esc_attr($title); ?>"/>
            <div class="card--labels">
                <?php if ($is_new) : ?>
                    <span class="card--label card--label-new">НОВИНКА</span>
                <?php endif; ?>
                <?php if ($is_on_sale && $is_in_stock) : ?>
                    <span class="card--label card--label-sale">% Скидка</span>
                <?php endif; ?>
                <?php if (!$is_in_stock) : ?>
                    <span class="card--label card--label-noStock">нет в наличии</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card--content">
            <div class="card--title">
                <?php if ($sku) : ?>
                    <span>Артикул: <?php echo esc_html($sku); ?></span>
                <?php endif; ?>
                <p><?php echo esc_html($title); ?></p>
            </div>
            <div class="card--info">
                <div class="card--price">
                    <?php if ($price !== '' && $price !== null) : ?>
                        <div class="card--price-current">
                            <span><?php echo esc_html(number_format((float) $price, 0, '', ' ')); ?></span><span>₽</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($is_on_sale && $regular_price && $sale_price && (float) $regular_price > (float) $sale_price) : ?>
                        <span class="card--price-old">
                            <span><?php echo esc_html(number_format((float) $regular_price, 0, '', ' ')); ?> </span><span>₽</span>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="card--stock<?php echo !$is_in_stock ? ' card--stock--out' : ''; ?>">
                    <?php if ($is_in_stock) : ?>
                        <span class="card--stock-label">В наличии: </span>
                        <span class="card--stock-value"><?php echo $stock_qty !== null ? esc_html($stock_qty . ' шт') : 'да'; ?></span>
                    <?php else : ?>
                        <span class="card--stock-out">Нет в наличии</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card--actions">
                <?php if ($is_in_stock) : ?>
                    <a class="button button-filled button-accent azs-buy-now"
                       href="<?php echo esc_url(add_query_arg('add-to-cart', $product_id, wc_get_cart_url())); ?>">
                        <span>Купить в один клик</span><span class="icon-arrow-right"></span>
                    </a>
                    <?php if ($in_cart) : ?>
                        <button class="button button-filled button-secondary" data-cart data-product-id="<?php echo esc_attr($product_id); ?>">
                            <span>В корзине</span><span class="icon-check"></span>
                        </button>
                    <?php else : ?>
                        <button class="button button-outlined azs-add-to-cart"
                                type="button"
                                data-product-id="<?php echo esc_attr($product_id); ?>"
                                data-product-name="<?php echo esc_attr($title); ?>"
                                data-product-image="<?php echo esc_url($img_src); ?>"
                                data-product-sku="<?php echo esc_attr($sku); ?>">
                            <span>Добавить в корзину</span><span class="icon-arrow-right"></span>
                        </button>
                    <?php endif; ?>
                <?php else : ?>
                    <div class="button button-filled button-accent -desk" data-openpopup="popup_form">
                        <span>Сообщить о поступлении</span><span class="icon-arrow-right"></span>
                    </div>
                    <div class="button button-filled button-accent" data-openpopup="popup_form">
                        <span>Сообщить о поступлении</span><span class="icon-arrow-right"></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// ------------------------------------------------------------------
// Recommended products helpers
// ------------------------------------------------------------------
function azs_get_product_recommended_ids($product_id, $limit = 4, $exclude_ids = array()) {
    $product_id = absint($product_id);
    $product    = $product_id ? wc_get_product($product_id) : false;
    if (!$product) {
        return array();
    }

    $exclude_ids   = array_map('absint', (array) $exclude_ids);
    $exclude_ids[] = $product_id;
    $exclude_ids   = array_unique(array_filter($exclude_ids));

    $ids = array_map('absint', (array) $product->get_cross_sell_ids());
    $ids = array_values(array_unique(array_filter($ids, function($id) use ($exclude_ids) {
        if (!$id || in_array($id, $exclude_ids, true)) {
            return false;
        }

        return get_post_status($id) === 'publish' && wc_get_product($id);
    })));

    if ((int) $limit > 0) {
        $ids = array_slice($ids, 0, (int) $limit);
    }

    return $ids;
}

function azs_render_recommended_products_block($product_ids, $title = 'Вам может понравиться') {
    $product_ids = array_values(array_filter(array_map('absint', (array) $product_ids)));
    if (empty($product_ids)) {
        return '';
    }

    ob_start();
    ?>
    <div class="block catalog">
        <div class="block--wrapper">
            <div class="block--title showInUp">
                <h2 class="block--title_title"><?php echo esc_html($title); ?></h2>
            </div>
            <div class="block--elements">
                <?php
                foreach ($product_ids as $pid) {
                    echo azs_render_product_card($pid);
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// ------------------------------------------------------------------
// Render pagination matching catalog.html markup
// ------------------------------------------------------------------
function azs_render_catalog_pagination($query, $current_page = 1) {
    $total = (int) $query->max_num_pages;
    if ($total <= 1) return '';

    $current = (int) $current_page;
    $start   = max(1, $current - 2);
    $end     = min($total, $current + 2);
    if ($end - $start < 4) {
        if ($start === 1)      $end   = min($total, 5);
        elseif ($end === $total) $start = max(1, $total - 4);
    }

    ob_start();
    ?>
    <div class="block--pagination" id="azs-pagination">
        <?php if ($current > 1) : ?>
            <div class="button button-outlined button-prev" data-page="<?php echo $current - 1; ?>"><span class="icon-arrow-left"></span></div>
        <?php else : ?>
            <div class="button button-outlined button-prev" style="visibility:hidden"><span class="icon-arrow-left"></span></div>
        <?php endif; ?>
        <div>
            <?php for ($i = $start; $i <= $end; $i++) : ?>
                <div class="button button-outlined<?php echo $i === $current ? ' is-active' : ''; ?>" data-page="<?php echo $i; ?>"><span><?php echo $i; ?></span></div>
            <?php endfor; ?>
        </div>
        <?php if ($current < $total) : ?>
            <div class="button button-outlined button-next" data-page="<?php echo $current + 1; ?>"><span class="icon-arrow-right"></span></div>
        <?php else : ?>
            <div class="button button-outlined button-next" style="visibility:hidden"><span class="icon-arrow-right"></span></div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// ------------------------------------------------------------------
// AJAX: filter / paginate catalog
// ------------------------------------------------------------------
add_action('wp_ajax_azs_catalog_filter', 'azs_catalog_filter_ajax');
add_action('wp_ajax_nopriv_azs_catalog_filter', 'azs_catalog_filter_ajax');
function azs_catalog_filter_ajax() {
    $params = array(
        'categories'    => array_filter(array_map('sanitize_text_field', (array) ($_POST['categories'] ?? array()))),
        'liquid_types'  => array_filter(array_map('sanitize_text_field', (array) ($_POST['liquid_types'] ?? array()))),
        'manufacturers' => array_filter(array_map('sanitize_text_field', (array) ($_POST['manufacturers'] ?? array()))),
        'search'        => sanitize_text_field($_POST['search'] ?? ''),
        'sort'          => sanitize_text_field($_POST['sort'] ?? 'default'),
        'page'          => intval($_POST['page'] ?? 1),
        'per_page'      => 12,
        'is_new'        => !empty($_POST['is_new']),
        'is_sale'       => !empty($_POST['is_sale']),
        'base_cat_id'   => intval($_POST['base_cat_id'] ?? 0),
    );

    $args  = azs_get_catalog_query_args($params);
    $query = new WP_Query($args);

    $html = '';
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $html .= azs_render_product_card(get_the_ID());
        }
        wp_reset_postdata();
    } else {
        $html = '<div class="catalog-empty"><p>Товары не найдены</p></div>';
    }

    wp_send_json_success(array(
        'html'       => $html,
        'pagination' => azs_render_catalog_pagination($query, $params['page']),
        'total'      => $query->found_posts,
        'max_pages'  => $query->max_num_pages,
    ));
}

// ------------------------------------------------------------------
// AJAX: add to cart (returns product data for popup)
// ------------------------------------------------------------------
add_action('wp_ajax_azs_add_to_cart', 'azs_add_to_cart_ajax');
add_action('wp_ajax_nopriv_azs_add_to_cart', 'azs_add_to_cart_ajax');
function azs_add_to_cart_ajax() {
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity   = max(1, intval($_POST['quantity'] ?? 1));

    if (!$product_id || !function_exists('WC')) {
        wp_send_json_error(array('message' => 'Invalid request'));
        return;
    }

    $added = WC()->cart->add_to_cart($product_id, $quantity);
    if (!$added) {
        wp_send_json_error(array('message' => 'Could not add to cart'));
        return;
    }

    $product    = wc_get_product($product_id);
    $image_url  = get_the_post_thumbnail_url($product_id, 'thumbnail');
    $cart_count = WC()->cart->get_cart_contents_count();

    $response = array(
        'product_name'  => $product ? $product->get_name() : '',
        'product_sku'   => $product ? $product->get_sku() : '',
        'product_image' => $image_url ?: '',
        'cart_count'    => $cart_count,
        'cart_url'      => wc_get_cart_url(),
    );

    if (function_exists('azs_get_cart_response_data')) {
        $response['cart'] = azs_get_cart_response_data();
    }

    wp_send_json_success($response);
}

// ------------------------------------------------------------------
// Catalog inline JS (only on shop / product-taxonomy pages)
// Priority 50 so it runs AFTER wp_print_footer_scripts (priority 20)
// which outputs azs_ajax localized data and enqueued JS files.
// ------------------------------------------------------------------
add_action('wp_footer', 'azs_catalog_inline_js', 50);
function azs_catalog_inline_js() {
    if (!function_exists('is_shop')) return;
    if (!is_shop() && !is_product_taxonomy()) return;
    ?>
    <script>
    (function() {
        'use strict';

        var catalog = document.getElementById('azs-catalog');
        if (!catalog) return;

        var baseCatId = catalog.dataset.baseCat || '0';
        var ajaxUrl   = (typeof azs_ajax !== 'undefined') ? azs_ajax.ajax_url : '';
        if (!ajaxUrl) return;

        var productsEl  = document.getElementById('azs-products');
        var tagsEl      = document.getElementById('azs-tags');
        var searchInput = catalog.querySelector('.block--search input');
        var searchBtn   = catalog.querySelector('.block--search .button');
        var applyBtn    = catalog.querySelector('.filter__action .button');

        var state = {
            categories: [], liquidTypes: [], manufacturers: [],
            search: '', sort: 'default', page: 1,
            isNew: false, isSale: false
        };

        var params = new URLSearchParams(window.location.search);
        if (params.get('azs_cat'))    state.categories   = params.get('azs_cat').split(',').filter(Boolean);
        if (params.get('azs_liquid')) state.liquidTypes   = params.get('azs_liquid').split(',').filter(Boolean);
        if (params.get('azs_mfr'))    state.manufacturers = params.get('azs_mfr').split(',').filter(Boolean);
        if (params.get('azs_sort'))   state.sort   = params.get('azs_sort');
        if (params.get('azs_search')) state.search = params.get('azs_search');
        if (params.get('azs_new'))    state.isNew  = true;
        if (params.get('azs_sale'))   state.isSale = true;
        if (params.get('paged'))      state.page   = parseInt(params.get('paged'), 10) || 1;

        restoreUI();
        renderTags();

        // --- Fix: sidebar dropdown links blocked by e.preventDefault in initCategoryDropdowns ---
        catalog.querySelectorAll('.category .dropdown__item').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });

        // --- Filter option toggle ---
        catalog.querySelectorAll('.filter__content_item--option[data-slug]').forEach(function(opt) {
            opt.addEventListener('click', function(e) {
                e.stopPropagation();
                opt.classList.toggle('is-selected');
            });
        });

        // --- Apply filters ---
        if (applyBtn) {
            applyBtn.addEventListener('click', function() {
                collectFromUI();
                state.page = 1;
                closeFilterPanels();
                loadProducts();
            });
        }

        // --- Sort ---
        catalog.querySelectorAll('.sort .item').forEach(function(item) {
            item.addEventListener('click', function() {
                var radio = item.querySelector('input[type="radio"]');
                if (radio && !radio.checked) {
                    radio.checked = true;
                    state.sort = radio.value;
                    state.page = 1;
                    closeSortPanel();
                    loadProducts();
                }
            });
        });
        catalog.querySelectorAll('input[name="sort"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                state.sort = this.value;
                state.page = 1;
                closeSortPanel();
                loadProducts();
            });
        });

        // --- Search ---
        if (searchBtn) {
            searchBtn.addEventListener('click', function() {
                state.search = searchInput ? searchInput.value.trim() : '';
                state.page = 1;
                loadProducts();
            });
        }
        if (searchInput) {
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    state.search = this.value.trim();
                    state.page = 1;
                    loadProducts();
                }
            });
        }

        // --- Pagination ---
        catalog.addEventListener('click', function(e) {
            var btn = e.target.closest('.block--pagination [data-page]');
            if (!btn) return;
            e.preventDefault();
            state.page = parseInt(btn.dataset.page, 10) || 1;
            loadProducts();
            catalog.scrollIntoView({behavior: 'smooth', block: 'start'});
        });

        // --- Tags ---
        catalog.addEventListener('click', function(e) {
            var tagBtn = e.target.closest('#azs-tags .button[data-tag-type]');
            if (tagBtn) {
                removeTag(tagBtn.dataset.tagType, tagBtn.dataset.tagValue);
                state.page = 1;
                loadProducts();
                return;
            }
            if (e.target.closest('#azs-tags .button-reset')) {
                resetAll();
                state.page = 1;
                loadProducts();
            }
        });

        // --- Sidebar special links ---
        catalog.querySelectorAll('[data-sidebar-new]').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                resetAll();
                state.isNew = true;
                state.page  = 1;
                loadProducts();
            });
        });
        catalog.querySelectorAll('[data-sidebar-sale]').forEach(function(el) {
            el.addEventListener('click', function(e) {
                e.preventDefault();
                resetAll();
                state.isSale = true;
                state.page   = 1;
                loadProducts();
            });
        });

        // --- Add to cart ---
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

            fetch(ajaxUrl, {method:'POST', body:fd})
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

                    var hCart = document.querySelector('.header__actions .button-accent[data-product]');
                    if (hCart) hCart.setAttribute('data-product', res.data.cart_count);

                    if (typeof PopupManager !== 'undefined') PopupManager.open('popup_appended');
                })
                .catch(function() { cartBtn.disabled = false; });
        });

        // --- Helpers ---
        function collectFromUI() {
            state.categories   = [];
            state.liquidTypes  = [];
            state.manufacturers = [];
            catalog.querySelectorAll('.filter__content_item--option.is-selected[data-slug]').forEach(function(opt) {
                var t = opt.dataset.filterType, s = opt.dataset.slug;
                if (t === 'category'     && state.categories.indexOf(s) === -1)   state.categories.push(s);
                if (t === 'liquid_type'  && state.liquidTypes.indexOf(s) === -1)   state.liquidTypes.push(s);
                if (t === 'manufacturer' && state.manufacturers.indexOf(s) === -1) state.manufacturers.push(s);
            });
        }

        function restoreUI() {
            catalog.querySelectorAll('.filter__content_item--option[data-slug]').forEach(function(opt) {
                var t = opt.dataset.filterType, s = opt.dataset.slug;
                var arr = t === 'category' ? state.categories : (t === 'liquid_type' ? state.liquidTypes : state.manufacturers);
                if (arr.indexOf(s) !== -1) opt.classList.add('is-selected');
            });
            var radio = catalog.querySelector('input[name="sort"][value="' + state.sort + '"]');
            if (radio) radio.checked = true;
            if (searchInput && state.search) searchInput.value = state.search;
        }

        function removeTag(type, value) {
            var map = {category: 'categories', liquid_type: 'liquidTypes', manufacturer: 'manufacturers'};
            if (map[type]) {
                state[map[type]] = state[map[type]].filter(function(v){ return v !== value; });
                catalog.querySelectorAll('.filter__content_item--option.is-selected[data-slug="' + value + '"][data-filter-type="' + type + '"]')
                    .forEach(function(el){ el.classList.remove('is-selected'); });
            }
            if (type === 'new')    state.isNew  = false;
            if (type === 'sale')   state.isSale = false;
            if (type === 'search') { state.search = ''; if (searchInput) searchInput.value = ''; }
        }

        function resetAll() {
            state.categories = []; state.liquidTypes = []; state.manufacturers = [];
            state.search = ''; state.isNew = false; state.isSale = false;
            if (searchInput) searchInput.value = '';
            catalog.querySelectorAll('.filter__content_item--option.is-selected').forEach(function(el){ el.classList.remove('is-selected'); });
        }

        function closeFilterPanels() {
            catalog.querySelectorAll('.filter__item.is-open').forEach(function(el){ el.classList.remove('is-open'); });
            var fp = catalog.querySelector('.filter.is-open');
            if (fp) fp.classList.remove('is-open');
        }

        function closeSortPanel() {
            var sp = catalog.querySelector('.sort.is-open');
            if (sp) sp.classList.remove('is-open');
        }

        function revealCards() {
            if (!productsEl) return;
            requestAnimationFrame(function() {
                productsEl.querySelectorAll('.showInUp').forEach(function(el) {
                    el.classList.add('showInUp--visible');
                });
            });
        }

        function updateURL() {
            var p = new URLSearchParams();
            if (state.categories.length)   p.set('azs_cat', state.categories.join(','));
            if (state.liquidTypes.length)  p.set('azs_liquid', state.liquidTypes.join(','));
            if (state.manufacturers.length) p.set('azs_mfr', state.manufacturers.join(','));
            if (state.sort !== 'default')  p.set('azs_sort', state.sort);
            if (state.search)              p.set('azs_search', state.search);
            if (state.isNew)               p.set('azs_new', '1');
            if (state.isSale)              p.set('azs_sale', '1');
            if (state.page > 1)            p.set('paged', state.page);
            var qs = p.toString();
            history.replaceState(null, '', window.location.pathname + (qs ? '?' + qs : ''));
        }

        function renderTags() {
            if (!tagsEl) return;
            var html = '';

            function tagHTML(type, value, label) {
                return '<div class="button" data-tag-type="' + type + '" data-tag-value="' + value + '"><span>' + label + '</span><span class="icon-close"></span></div>';
            }
            function labelFor(type, slug) {
                var el = catalog.querySelector('.filter__content_item--option[data-slug="' + slug + '"][data-filter-type="' + type + '"]');
                return el ? el.textContent.trim() : slug;
            }

            state.categories.forEach(function(s)   { html += tagHTML('category', s, labelFor('category', s)); });
            state.liquidTypes.forEach(function(s)  { html += tagHTML('liquid_type', s, labelFor('liquid_type', s)); });
            state.manufacturers.forEach(function(s){ html += tagHTML('manufacturer', s, labelFor('manufacturer', s)); });
            if (state.isNew)  html += tagHTML('new', '1', 'Новинки');
            if (state.isSale) html += tagHTML('sale', '1', 'Акции');
            if (state.search) html += tagHTML('search', state.search, 'Поиск: ' + state.search);

            if (html) html += '<div class="button button-reset"><span>Сбросить фильтры</span></div>';
            tagsEl.innerHTML = html;
        }

        function loadProducts() {
            var fd = new FormData();
            fd.append('action', 'azs_catalog_filter');
            fd.append('sort', state.sort);
            fd.append('page', state.page);
            fd.append('search', state.search);
            fd.append('base_cat_id', baseCatId);
            if (state.isNew)  fd.append('is_new', '1');
            if (state.isSale) fd.append('is_sale', '1');
            state.categories.forEach(function(v)   { fd.append('categories[]', v); });
            state.liquidTypes.forEach(function(v)  { fd.append('liquid_types[]', v); });
            state.manufacturers.forEach(function(v){ fd.append('manufacturers[]', v); });

            if (productsEl) productsEl.style.opacity = '0.5';

            fetch(ajaxUrl, {method:'POST', body:fd})
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (!res.success) { if (productsEl) productsEl.style.opacity = ''; return; }
                    if (productsEl) {
                        productsEl.innerHTML = res.data.html;
                        productsEl.style.opacity = '';
                        revealCards();
                    }
                    var pag = catalog.querySelector('.block--pagination');
                    if (pag && res.data.pagination) { pag.outerHTML = res.data.pagination; }
                    else if (pag && !res.data.pagination) { pag.remove(); }
                    else if (!pag && res.data.pagination) {
                        var wrapper = catalog.querySelector('.block--wrapper');
                        if (wrapper) wrapper.insertAdjacentHTML('beforeend', res.data.pagination);
                    }
                })
                .catch(function() { if (productsEl) productsEl.style.opacity = ''; });

            updateURL();
            renderTags();
        }
    })();
    </script>
    <?php
}

// ------------------------------------------------------------------
// Helper: get "Оплата и доставка" page URL
// ------------------------------------------------------------------
function azs_get_payment_delivery_url() {
    static $url = null;
    if ($url !== null) return $url;

    $pages = get_posts(array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        's'              => 'Оплата и доставка',
    ));

    $url = !empty($pages) ? get_permalink($pages[0]) : home_url('/');
    return $url;
}

// ------------------------------------------------------------------
// Remove default WC / Storefront single product hooks
// ------------------------------------------------------------------
add_action('template_redirect', 'azs_remove_single_product_hooks');
function azs_remove_single_product_hooks() {
    if (!function_exists('is_product') || !is_product()) return;

    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50);

    remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
    remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);

    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
}

// ------------------------------------------------------------------
// Single Product: inline JS for add-to-cart & buy-now
// ------------------------------------------------------------------
add_action('wp_footer', 'azs_single_product_inline_js', 50);
function azs_single_product_inline_js() {
    if (!function_exists('is_product') || !is_product()) return;
    ?>
    <script>
    (function() {
        'use strict';

        var ajaxUrl = (typeof azs_ajax !== 'undefined') ? azs_ajax.ajax_url : '';
        if (!ajaxUrl) return;

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

            fetch(ajaxUrl, {method: 'POST', body: fd})
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (!res.success) { cartBtn.disabled = false; return; }

                    cartBtn.outerHTML =
                        '<button class="button button-filled button-secondary" data-cart data-product-id="' + pid + '">' +
                        '<span>В корзине</span><span class="icon-check"></span></button>';

                    var popup = document.querySelector('[data-popup="popup_appended"]');
                    if (popup) {
                        var img  = popup.querySelector('.card--image');
                        var pEl  = popup.querySelector('.card--title p');
                        var sEl  = popup.querySelector('.card--title span');
                        if (img && res.data.product_image) img.innerHTML = '<img src="' + res.data.product_image + '">';
                        if (pEl) pEl.textContent = res.data.product_name;
                        if (sEl) sEl.textContent = 'Артикул: ' + (res.data.product_sku || '');
                    }

                    var hCart = document.querySelector('.header__actions .button-accent[data-product]');
                    if (hCart) hCart.setAttribute('data-product', res.data.cart_count);

                    if (typeof PopupManager !== 'undefined') PopupManager.open('popup_appended');
                })
                .catch(function() { cartBtn.disabled = false; });
        });
    })();
    </script>
    <?php
}
