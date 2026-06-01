<?php

if (!defined('ABSPATH')) exit;

require_once get_stylesheet_directory() . '/inc/custom-post-types.php';
require_once get_stylesheet_directory() . '/inc/acf-options.php';
require_once get_stylesheet_directory() . '/inc/acf-fields.php';
require_once get_stylesheet_directory() . '/inc/woocommerce-catalog.php';
require_once get_stylesheet_directory() . '/inc/woocommerce-cart.php';
require_once get_stylesheet_directory() . '/inc/import.php';

// ===========================================
// Theme Setup
// ===========================================
add_action('after_setup_theme', 'azs_theme_setup');
function azs_theme_setup() {
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('title-tag');
    add_theme_support('woocommerce');
    add_theme_support('html5', array('search-form', 'comment-form', 'gallery', 'caption'));

    register_nav_menus(array(
        'primary'      => 'Главное меню (шапка)',
        'footer_col_1' => 'Футер — колонка 1',
        'footer_col_2' => 'Футер — колонка 2',
    ));
}

// ===========================================
// Override Storefront: remove Google Fonts, fix font + color
// ===========================================
add_action('wp_enqueue_scripts', 'azs_dequeue_storefront_fonts', 999);
function azs_dequeue_storefront_fonts() {
    wp_dequeue_style('storefront-fonts');
    wp_deregister_style('storefront-fonts');
}

add_filter('storefront_google_font_families', '__return_empty_array');

add_filter('storefront_customizer_css', 'azs_override_storefront_customizer_css');
function azs_override_storefront_customizer_css($css) {
    $css = preg_replace(
        '/font-family\s*:\s*[^;]*"Source Sans Pro"[^;]*;/i',
        'font-family: "Mostserrat", sans-serif;',
        $css
    );
    $css = preg_replace(
        '/((?:^|[{;}\s])color\s*:\s*)#[0-9a-fA-F]{3,8}\s*;/m',
        '$1#0A0A0A;',
        $css
    );
    return $css;
}

// ===========================================
// Enqueue Styles & Scripts
// ===========================================
add_action('wp_enqueue_scripts', 'azs_enqueue_assets');
function azs_enqueue_assets() {
    $uri  = get_stylesheet_directory_uri();
    $path = get_stylesheet_directory();
    $ver  = wp_get_theme()->get('Version');

    // Cache-busting based on file modification time, so updated assets
    // bypass browser/CDN cache immediately instead of waiting for a
    // manual theme version bump.
    $asset_ver = static function ($relative) use ($path, $ver) {
        $full = $path . $relative;
        return file_exists($full) ? (string) filemtime($full) : $ver;
    };

    wp_enqueue_style('storefront-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('azs-child-style', $uri . '/style.css', array('storefront-style'), $asset_ver('/style.css'));
    wp_enqueue_style('azs-child-style-new', $uri . '/assets/css/style-new.css', array('azs-child-style'), $asset_ver('/assets/css/style-new.css'));

    wp_enqueue_style('azs-normalize', 'https://unitbox.one/dist/css/normalize.css', array(), null);
    wp_enqueue_style('azs-libs', $uri . '/assets/css/libs.min.css', array(), $asset_ver('/assets/css/libs.min.css'));
    wp_enqueue_style('azs-main', $uri . '/assets/css/main.css', array('azs-libs'), $asset_ver('/assets/css/main.css'));
    wp_enqueue_style('azs-wp-overrides', $uri . '/assets/css/wp-overrides.css', array('azs-main'), $asset_ver('/assets/css/wp-overrides.css'));

    wp_enqueue_script('azs-libs-js', $uri . '/assets/js/libs.min.js', array(), $asset_ver('/assets/js/libs.min.js'), true);
    wp_enqueue_script('azs-main-js', $uri . '/assets/js/main.js', array('azs-libs-js'), $asset_ver('/assets/js/main.js'), true);

    $shop_url = function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : '/';
    wp_localize_script('azs-main-js', 'azs_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('azs_nonce'),
        'shop_url' => $shop_url,
    ));

}

// ===========================================
// Custom WooCommerce placeholder image
// ===========================================
add_filter('woocommerce_placeholder_img_src', 'azs_custom_placeholder_img');
function azs_custom_placeholder_img($src) {
    $custom = get_stylesheet_directory() . '/assets/img/placeholder.jpg';
    if (file_exists($custom)) {
        return get_stylesheet_directory_uri() . '/assets/img/placeholder.jpg';
    }
    return $src;
}

// ===========================================
// Body Classes
// ===========================================
add_filter('body_class', 'azs_body_classes');
function azs_body_classes($classes) {
    if (is_front_page()) {
        $classes[] = 'page-main';
    }
    if (is_post_type_archive('azs_news') || is_singular('azs_news')) {
        $classes[] = 'page-news';
    }
    if (is_404()) {
        $classes[] = 'page-error';
    }
    if (is_page_template('page-article.php')) {
        $classes[] = 'page-article';
    }
    return $classes;
}

// ===========================================
// Override Storefront 404 — force child theme template
// ===========================================
add_action('after_setup_theme', 'azs_remove_storefront_404', 20);
function azs_remove_storefront_404() {
    remove_action('storefront_404', 'storefront_404_content', 10);
}

add_filter('template_include', 'azs_force_404_template', 99);
function azs_force_404_template($template) {
    if (is_404()) {
        $child_404 = get_stylesheet_directory() . '/404.php';
        if (file_exists($child_404)) {
            return $child_404;
        }
    }
    return $template;
}

// ===========================================
// Maintenance mode: serve 503.php for non-admins
// ===========================================
add_action('wp_loaded', 'azs_maintenance_mode');
function azs_maintenance_mode() {
    if (!file_exists(ABSPATH . '.maintenance') && !get_option('azs_maintenance_mode')) {
        return;
    }
    if (current_user_can('manage_options')) {
        return;
    }
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }
    if (strpos($_SERVER['REQUEST_URI'], '/wp-admin') !== false || strpos($_SERVER['REQUEST_URI'], '/wp-login') !== false) {
        return;
    }

    include get_stylesheet_directory() . '/503.php';
    exit;
}

// ===========================================
// Helper: theme image URL shorthand
// ===========================================
function azs_img($path) {
    return get_stylesheet_directory_uri() . '/assets/img/' . $path;
}

// ===========================================
// Nav Walker — Header
// ===========================================
class AZS_Header_Walker extends Walker_Nav_Menu {
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $active  = in_array('current-menu-item', $classes) ? ' active' : '';
        $output .= '<a class="nav__item link' . $active . '" href="' . esc_url($item->url) . '">';
        $output .= esc_html($item->title);
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= '</a>';
    }

    public function start_lvl(&$output, $depth = 0, $args = null) {}
    public function end_lvl(&$output, $depth = 0, $args = null) {}
}

// ===========================================
// Nav Walker — Footer
// ===========================================
class AZS_Footer_Walker extends Walker_Nav_Menu {
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $output .= '<a class="footer__nav_item link" href="' . esc_url($item->url) . '">';
        $output .= esc_html($item->title);
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= '</a>';
    }

    public function start_lvl(&$output, $depth = 0, $args = null) {}
    public function end_lvl(&$output, $depth = 0, $args = null) {}
}

// ===========================================
// Nav Walker — Mobile Menu
// ===========================================
class AZS_Mobile_Menu_Walker extends Walker_Nav_Menu {
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $output .= '<a class="menu__item" href="' . esc_url($item->url) . '"><span>';
        $output .= esc_html($item->title);
        $output .= '</span></a>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {}
    public function start_lvl(&$output, $depth = 0, $args = null) {}
    public function end_lvl(&$output, $depth = 0, $args = null) {}
}

// ===========================================
// Form Submission — sends email to admin
// ===========================================
add_action('wp_ajax_azs_send_form', 'azs_handle_form_submission');
add_action('wp_ajax_nopriv_azs_send_form', 'azs_handle_form_submission');
function azs_handle_form_submission() {
    check_ajax_referer('azs_nonce', 'nonce');

    $form_type = sanitize_text_field(wp_unslash($_POST['form_type'] ?? ''));
    $name      = sanitize_text_field(wp_unslash($_POST['name']      ?? ''));
    $phone     = sanitize_text_field(wp_unslash($_POST['phone']     ?? ''));
    $email     = sanitize_email(wp_unslash($_POST['email']          ?? ''));
    $comment   = sanitize_textarea_field(wp_unslash($_POST['comment'] ?? ''));

    $subjects = array(
        'callback'     => 'Заявка на обратный звонок',
        'consultation' => 'Заявка на консультацию',
        'news'         => 'Подписка на новости',
        'product'      => 'Уведомление о поступлении товара',
    );

    $subject_label = isset($subjects[$form_type]) ? $subjects[$form_type] : 'Новая заявка с сайта';
    $site_name     = get_bloginfo('name');
    $subject       = $subject_label . ' — ' . $site_name;

    $message = 'Тип заявки: ' . $subject_label . "\n\n";
    if ($name)    $message .= 'Имя: ' . $name . "\n";
    if ($phone)   $message .= 'Телефон: ' . $phone . "\n";
    if ($email)   $message .= 'Email: ' . $email . "\n";
    if ($comment) $message .= "Комментарий:\n" . $comment . "\n";
    $message .= "\n" . 'Сайт: ' . home_url('/');

    $admin_email = get_option('admin_email');
    $headers     = array('Content-Type: text/plain; charset=UTF-8');

    $sent = wp_mail($admin_email, $subject, $message, $headers);

    if ($sent) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}

// ===========================================
// News archive: posts per page
// ===========================================
add_action('pre_get_posts', 'azs_news_archive_query');
function azs_news_archive_query($query) {
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('azs_news')) {
        $query->set('posts_per_page', 6);
    }
}

// ===========================================
// News AJAX Load More
// ===========================================
add_action('wp_ajax_azs_load_more_news', 'azs_load_more_news');
add_action('wp_ajax_nopriv_azs_load_more_news', 'azs_load_more_news');
function azs_load_more_news() {
    $paged = intval($_POST['page'] ?? 1) + 1;

    $query = new WP_Query(array(
        'post_type'      => 'azs_news',
        'posts_per_page' => 6,
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));

    if (!$query->have_posts()) {
        wp_send_json_error();
    }

    ob_start();
    while ($query->have_posts()) : $query->the_post(); ?>
        <a class="card showInUp" href="<?php the_permalink(); ?>">
            <div class="card--image">
                <div class="image">
                    <?php if (has_post_thumbnail()) : ?>
                        <img src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'medium_large')); ?>" alt="<?php the_title_attribute(); ?>">
                    <?php else : ?>
                        <img src="<?php echo azs_img('1.jpg'); ?>" alt="<?php the_title_attribute(); ?>">
                    <?php endif; ?>
                </div>
                <div class="card--date"><?php echo get_the_date('d.m.Y'); ?></div>
            </div>
            <div class="card--content">
                <div class="card--title"><?php the_title(); ?></div>
                <div class="card--text"><?php echo wp_trim_words(get_the_excerpt(), 30); ?></div>
                <div class="card--actions">
                    <div class="button button-outlined"><span>Подробнее</span><span class="icon-arrow-right"></span></div>
                </div>
            </div>
        </a>
    <?php endwhile;
    wp_reset_postdata();

    wp_send_json_success(array(
        'html'     => ob_get_clean(),
        'has_more' => $paged < $query->max_num_pages,
    ));
}

// ===========================================
// Inline script for news load-more button
// ===========================================
add_action('wp_footer', 'azs_news_load_more_script');
function azs_news_load_more_script() {
    if (!is_post_type_archive('azs_news')) return;
    ?>
    <script>
    (function() {
        var btn = document.getElementById('azs-load-more-news');
        if (!btn) return;
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var page = parseInt(btn.dataset.page, 10);
            var max  = parseInt(btn.dataset.max, 10);
            btn.classList.add('loading');
            var fd = new FormData();
            fd.append('action', 'azs_load_more_news');
            fd.append('page', page);
            fetch(azs_ajax.ajax_url, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    btn.classList.remove('loading');
                    if (res.success) {
                        var list = document.getElementById('azs-news-list');
                        list.insertAdjacentHTML('beforeend', res.data.html);
                        btn.dataset.page = page + 1;
                        if (!res.data.has_more) {
                            btn.classList.add('is-hidden');
                        }
                    }
                })
                .catch(function() {
                    btn.classList.remove('loading');
                });
        });
    })();
    </script>
    <?php
}

// ===========================================
// Cyrillic-safe slug (no transliteration)
// ===========================================
function azs_slug($text) {
    $slug = mb_strtolower($text, 'UTF-8');
    $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

// ===========================================
// Article page: tab switching script
// ===========================================
add_action('wp_footer', 'azs_article_tabs_script');
function azs_article_tabs_script() {
    if (!is_page_template('page-article.php')) return;
    ?>
    <script>
    (function() {
        var buttons  = document.querySelectorAll('[data-article-tab]');
        var contents = document.querySelectorAll('[data-article-content]');
        if (!buttons.length || !contents.length) return;

        function activateTab(slug) {
            var btn = document.querySelector('[data-article-tab="' + slug + '"]');
            if (!btn) return false;
            buttons.forEach(function(b) { b.classList.remove('is_active'); });
            contents.forEach(function(c) { c.classList.add('is_hidden'); });
            btn.classList.add('is_active');
            var target = document.querySelector('[data-article-content="' + slug + '"]');
            if (target) target.classList.remove('is_hidden');
            return true;
        }

        function onHashChange() {
            var hash = location.hash.replace('#', '');
            if (hash) activateTab(decodeURIComponent(hash));
        }

        buttons.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var slug = btn.getAttribute('data-article-tab');
                activateTab(slug);
                history.replaceState(null, '', '#' + slug);
            });
        });

        if (location.hash) onHashChange();
        window.addEventListener('hashchange', onHashChange);
    })();
    </script>
    <?php
}

// ===========================================
// WooCommerce AJAX Product Search
// ===========================================
add_action('wp_ajax_azs_product_search', 'azs_product_search');
add_action('wp_ajax_nopriv_azs_product_search', 'azs_product_search');
function azs_product_search() {
    $q      = sanitize_text_field($_GET['q'] ?? '');
    $cat_id = intval($_GET['category'] ?? 0);

    if (function_exists('azs_get_catalog_query_args')) {
        $args = azs_get_catalog_query_args(array(
            'categories' => $cat_id > 0 ? array($cat_id) : array(),
            'search'     => $q,
            'per_page'   => 10,
        ));
    } else {
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => 10,
            's'              => $q,
            'post_status'    => 'publish',
        );

        if ($cat_id > 0) {
            $args['tax_query'] = array(array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $cat_id,
            ));
        }
    }

    $query   = new WP_Query($args);
    $results = array();

    while ($query->have_posts()) {
        $query->the_post();
        $product = function_exists('wc_get_product') ? wc_get_product(get_the_ID()) : null;
        $results[] = array(
            'id'    => get_the_ID(),
            'title' => get_the_title(),
            'url'   => get_permalink(),
            'price' => $product ? $product->get_price_html() : '',
            'image' => get_the_post_thumbnail_url(get_the_ID(), 'thumbnail'),
        );
    }
    wp_reset_postdata();
    wp_send_json_success($results);
}

// ===========================================
// WooCommerce cart fragments (for AJAX cart count)
// ===========================================
add_filter('woocommerce_add_to_cart_fragments', 'azs_cart_count_fragment');
function azs_cart_count_fragment($fragments) {
    if (!function_exists('WC')) return $fragments;

    $count = WC()->cart->get_cart_contents_count();
    $fragments['.button-accent[data-product]'] = '<a class="button button-filled button-accent" href="' . esc_url(wc_get_cart_url()) . '" data-product="' . esc_attr($count) . '"><span class="icon-cart"></span></a>';

    return $fragments;
}
