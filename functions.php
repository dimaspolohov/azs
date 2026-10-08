<?php

if (!defined('ABSPATH')) exit;

require_once get_stylesheet_directory() . '/inc/consent-log.php';
require_once get_stylesheet_directory() . '/inc/custom-post-types.php';
require_once get_stylesheet_directory() . '/inc/acf-options.php';
require_once get_stylesheet_directory() . '/inc/acf-fields.php';
require_once get_stylesheet_directory() . '/inc/woocommerce-catalog.php';
require_once get_stylesheet_directory() . '/inc/woocommerce-cart.php';
require_once get_stylesheet_directory() . '/inc/import.php';
require_once get_stylesheet_directory() . '/inc/canonical-redirect.php';
require_once get_stylesheet_directory() . '/inc/yoast-product-seo.php';
require_once get_stylesheet_directory() . '/inc/seo-hooks.php';

// ===========================================
// Theme Setup
// ===========================================
add_action('after_setup_theme', 'azs_theme_setup');
function azs_theme_setup()
{
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
function azs_dequeue_storefront_fonts()
{
    wp_dequeue_style('storefront-fonts');
    wp_deregister_style('storefront-fonts');
}

add_filter('storefront_google_font_families', '__return_empty_array');

add_filter('storefront_customizer_css', 'azs_override_storefront_customizer_css');
function azs_override_storefront_customizer_css($css)
{
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
function azs_enqueue_assets()
{
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

    wp_enqueue_script('azs-libs-js', $uri . '/assets/js/libs.min.js', array(), $asset_ver('/assets/js/libs.min.js'), true);
    wp_enqueue_script('azs-form-validation', $uri . '/assets/js/form-validation.js', array(), $asset_ver('/assets/js/form-validation.js'), true);
    wp_enqueue_script('azs-main-js', $uri . '/assets/js/main.js', array('azs-libs-js'), $asset_ver('/assets/js/main.js'), true);
    wp_enqueue_script('azs-metrika-js', $uri . '/assets/js/metrika.js', array('azs-libs-js'), $asset_ver('/assets/js/metrika.js'), true);



    $shop_url = function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : '/';
    wp_localize_script('azs-main-js', 'azs_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('azs_nonce'),
        'shop_url' => $shop_url,
    ));

    wp_add_inline_style('azs-main', '
        body,
        body.storefront,
        .storefront-primary-navigation,
        .storefront-secondary-navigation,
        .main-navigation ul li a,
        .site-header,
        .site-footer,
        .widget,
        h1, h2, h3, h4, h5, h6,
        p, a, span, li, td, th, dt, dd,
        button,
        input,
        select,
        textarea,
        label,
        .woocommerce div.product,
        .woocommerce .products,
        .woocommerce-cart,
        .woocommerce-checkout,
        .woocommerce-account,
        .entry-content,
        .page-template-default {
            font-family: "Mostserrat", sans-serif !important;
            color: var(--c-dark, #0A0A0A);
        }

        .button.button-filled:not(.button-filter),
        .button.button-filled:not(.button-filter) span,
        .button.button-filled:not(.button-filter) [class^="icon-"] {
            color: var(--c-white, #FFFFFF) !important;
        }

        .button.button-filter,
        .button.button-filter span,
        .button.button-filter [class^="icon-"] {
            color: var(--dark, #0A0A0A) !important;
        }

        .button.button-filter:hover,
        .button.button-filter:hover span,
        .button.button-filter:hover [class^="icon-"] {
            color: var(--c-white, #FFFFFF) !important;
        }

        .button.button-outlined:not(.button-filter),
        .button.button-outlined:not(.button-filter) span,
        .button.button-outlined:not(.button-filter) [class^="icon-"] {
            color: var(--c-dark, #0A0A0A) !important;
        }

        .button.button-outlined:not(.button-filter):hover,
        .button.button-outlined:not(.button-filter):hover span,
        .button.button-outlined:not(.button-filter):hover [class^="icon-"] {
            color: var(--c-white, #FFFFFF) !important;
        }

        .button.button-reset,
        .button.button-reset span {
            background: var(--dark, #0A0A0A);
            color: var(--c-white, #FFFFFF) !important;
            outline: none;
        }

        .page-catalog .catalog .block--wrapper {
            display: flex !important;
            flex-direction: column;
            gap: var(--s-8);
        }

        @media (min-width: 641px) {
            .page-catalog .catalog .block--wrapper {
                display: grid !important;
                grid-template-columns: 1fr 3fr;
                grid-template-rows: auto auto 1fr auto;
            }

            .catalog .side {
                grid-row: 1 / -1;
                align-self: stretch;
                min-width: 0;
            }

            .catalog .block--filters {
                flex-direction: row;
                grid-column: 2 / span 3;
            }

            .catalog .block--elements {
                grid-column: 2 / span 3;
            }

            .catalog .block--tags {
                grid-column: 2 / span 3;
            }

            .catalog .block--pagination {
                grid-column: 2 / span 3;
            }

            /* Subcategory tiles take the first row: shift the tags and keep the stretchable row
               for the products, otherwise the search grows to the side menu height on short pages */
            .page-catalog .catalog .block--wrapper:has(> .block--subcats) {
                grid-template-rows: auto auto auto 1fr auto;
            }

            .catalog .block--wrapper:has(> .block--subcats) > .block--tags {
                grid-row: 3;
            }

            .category {
                background: var(--c-white, #FFFFFF) !important;
            }

            .category .item {
                display: flex;
                position: relative;
                width: 100%;
            }

            .category .item .button {
                width: 100%;
                padding: var(--s-20) var(--s-12);
                font-weight: 400;
                color: var(--c-dark, #0A0A0A);
                background: transparent;
                border: none;
                outline: none;
                overflow-wrap: break-word;
                word-break: break-word;
            }

            .category .item:hover .button {
                background: var(--accent);
                color: var(--c-white, #FFFFFF) !important;
            }
            .category .item:hover .button span,
            .category .item:hover .button [class^="icon-"] {
                color: var(--c-white, #FFFFFF) !important;
            }

            .category .item.is-active .button,
            .category .item.is_open .button {
                background: var(--accent);
                color: var(--c-white, #FFFFFF) !important;
            }
            .category .item.is-active .button span,
            .category .item.is-active .button [class^="icon-"],
            .category .item.is_open .button span,
            .category .item.is_open .button [class^="icon-"] {
                color: var(--c-white, #FFFFFF) !important;
            }

            .category .item:not(:last-child):after {
                content: "";
                width: calc(100% - var(--s-12) * 2);
                border-bottom: 1px solid var(--bg-secondary);
                margin: 0 auto;
                position: absolute;
                translate: -50% 0;
                top: 100%;
                left: 50%;
            }

            .category .item-dropdown {
                display: flex;
                flex-direction: column;
            }

            .category .item .dropdown {
                max-height: 0;
                overflow: hidden;
            }

            .category .item.is_open .dropdown {
                max-height: 100vh;
            }

            .category .item .dropdown__wrapper {
                padding: var(--s-20) var(--s-16);
                display: flex;
                flex-direction: column;
                gap: var(--s-20);
            }

            .category .item .dropdown__item {
                font-size: var(--s-16);
                line-height: var(--s-24);
                color: var(--c-dark, #0A0A0A);
            }

            .category .item .dropdown .link:before {
                background-color: var(--accent);
            }
        }

        .storefront-handheld-footer-bar,
        .storefront-handheld-footer-bar ul,
        .storefront-handheld-footer-bar ul li,
        .storefront-handheld-footer-bar ul li a {
            font-family: "Mostserrat", sans-serif !important;
        }

        .header,
        .header a,
        .header__nav .nav__item,
        .header__actions,
        .header__actions a,
        .header__actions span {
            color: var(--c-dark, #0A0A0A);
        }

        .top__bar,
        .top__bar a,
        .top__bar__actions a,
        .top__bar span {
            color: var(--c-white, #FFFFFF) !important;
        }

        .page-cart .cart .block--title_title,
        .page-cart .cart .block--description span,
        .page-cart .cart .card--title p,
        .page-cart .cart .card--title span,
        .page-cart .cart .card--price-current span,
        .page-cart .cart .card--total-current span,
        .page-cart .cart .card--count .button span,
        .page-cart .cart .block--price p,
        .page-cart .cart .block--price .price span,
        .page-cart .cart .side p,
        .page-cart .cart .side--title p,
        .page-cart .cart .side--total p,
        .page-cart .cart .side--total div span,
        .page-cart .cart .form input,
        .page-cart .cart .form--policy span,
        .page-cart .cart .note p,
        .page-cart .cart .block--pagination a span {
            color: var(--c-dark, #0A0A0A);
        }

        .breadcrumbs,
        .breadcrumbs a,
        .breadcrumbs span {
            color: var(--c-dark, #0A0A0A);
        }

        .footer,
        .footer a,
        .footer p,
        .footer span,
        .footer__copyright a,
        .footer__nav_item {
            color: currentColor;
        }

        .catalog .block--title_title,
        .catalog .card--title p,
        .catalog .card--title span,
        .catalog .card--price-current span,
        .catalog .card--stock-label,
        .catalog .card--stock-value,
        .catalog .card--description p,
        .catalog-detail .card--title h2,
        .catalog-detail .card--title span,
        .catalog-detail .card--description,
        .catalog-detail .card--price-current span,
        .catalog-detail .card--stock-label,
        .catalog-detail .card--stock-value,
        .catalog-detail .info h3,
        .catalog-detail .info,
        .catalog-detail .info p,
        .catalog-detail .info td {
            color: var(--c-dark, #0A0A0A);
        }

        .page-news .card--title,
        .page-news .card--text,
        .page-news .card--date,
        .news-detail .card--title,
        .news-detail .card--text,
        .news-detail .card--date {
            color: var(--c-dark, #0A0A0A);
        }

        .filter__button span,
        .sort span,
        .search input,
        .side .category .item span,
        .side .category .dropdown__item {
            color: var(--c-dark, #0A0A0A);
        }

        .popup--title h3,
        .popup--content p,
        .popup--content span,
        .notification--title,
        .notification--text {
            color: var(--c-dark, #0A0A0A);
        }

        .filter__content_item--option.is-selected {
            background-color: var(--c-dark);
            color: var(--c-white);
        }

        .page-product .catalog-detail .info {
            color: var(--c-dark);
            font-family: "Mostserrat", sans-serif;
        }
        .page-product .catalog-detail .info table {
            border-collapse: collapse;
            border-spacing: 0;
            width: 100%;
            margin: 0;
            border: none;
        }
        .page-product .catalog-detail .info table tr td:first-child {
            width: 50%;
            font-weight: 500;
        }
        .page-product .catalog-detail .info table td {
            border: 1px solid var(--bg-secondary);
            padding: var(--s-12) var(--s-10);
            vertical-align: top;
            color: var(--c-dark);
            font-size: inherit;
            line-height: inherit;
        }
        .page-product .catalog-detail .info h3 {
            color: var(--c-dark);
        }
        .page-product .catalog-detail .description.info .text {
            color: var(--c-dark);
        }
        .page-product .catalog-detail .description.info .text p {
            color: var(--c-dark);
            margin: 0;
        }

        .page-article .article .block--content.is_hidden {
            display: none;
        }

        .page-article .article .block--content.is_cascade {
            overflow-wrap: break-word;
            word-break: break-word;
        }

        .page-article .article .side .item.is_active span,
        .page-article .article .side .item:hover span {
            color: var(--c-white, #FFFFFF) !important;
        }

        .page-main img {
            border-radius: 0 !important;
        }

        .catalog-hero .card--list a,
        .catalog-hero .card--list .link {
            color: var(--c-white, #FFFFFF) !important;
        }

        .catalog-hero .card--link {
            position: absolute;
            inset: 0;
            z-index: 3;
        }

        .catalog-hero .card--list {
            position: relative;
            z-index: 4;
        }

        .catalog .block--filters .search input {
            background-color: var(--c-white, #FFFFFF);
        }

        .catalog .block--pagination .button.is-active {
            background-color: var(--accent, #068411);
            color: var(--c-white, #FFFFFF) !important;
            outline-color: var(--accent, #068411);
        }
        .catalog .block--pagination .button.is-active span {
            color: var(--c-white, #FFFFFF) !important;
        }

        .footer__catalog-btn {
            font-size: var(--s-14);
        }

        .catalog-hero .card-last .button {
            background: transparent !important;
            border: none !important;
            outline: none !important;
            padding: 0 !important;
            width: 100%;
        }
        .catalog-hero .card-last .button span,
        .catalog-hero .card-last .button [class^="icon-"] {
            color: var(--c-white, #FFFFFF) !important;
        }

        .banner-hero_search a.button {
            text-decoration: none !important;
        }
        .banner-hero_search .search input {
            box-shadow: none !important;
            height: auto !important;
            min-height: auto !important;
            border: none !important;
            border-radius: 0 !important;
        }
        .banner-hero_search .button {
            min-height: auto !important;
            box-shadow: none !important;
        }
    ');
}

// ===========================================
// Custom WooCommerce placeholder image
// ===========================================
add_filter('woocommerce_placeholder_img_src', 'azs_custom_placeholder_img');
function azs_custom_placeholder_img($src)
{
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
function azs_body_classes($classes)
{
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
function azs_remove_storefront_404()
{
    remove_action('storefront_404', 'storefront_404_content', 10);
}

add_filter('template_include', 'azs_force_404_template', 99);
function azs_force_404_template($template)
{
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
function azs_maintenance_mode()
{
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
function azs_img($path)
{
    return get_stylesheet_directory_uri() . '/assets/img/' . $path;
}

// ===========================================
// Nav Walker — Header
// ===========================================
class AZS_Header_Walker extends Walker_Nav_Menu
{
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $active  = in_array('current-menu-item', $classes) ? ' active' : '';
        $output .= '<a class="nav__item link' . $active . '" href="' . esc_url($item->url) . '">';
        $output .= esc_html($item->title);
    }

    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        $output .= '</a> <div style="display: none;" itemprop="itemListElement" itemscope ="" itemtype="https://schema.org/ItemList"><a itemprop="url" href="' . esc_url($item->url) . '"><span itemprop="name">' . esc_html($item->title) . '</span></a></div>';
    }

    public function start_lvl(&$output, $depth = 0, $args = null) {}
    public function end_lvl(&$output, $depth = 0, $args = null) {}
}

// ===========================================
// Nav Walker — Footer
// ===========================================
class AZS_Footer_Walker extends Walker_Nav_Menu
{
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $output .= '<a class="footer__nav_item link" href="' . esc_url($item->url) . '">';
        $output .= esc_html($item->title);
    }

    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        $output .= '</a>';
    }

    public function start_lvl(&$output, $depth = 0, $args = null) {}
    public function end_lvl(&$output, $depth = 0, $args = null) {}
}

// ===========================================
// Nav Walker — Mobile Menu
// ===========================================
class AZS_Mobile_Menu_Walker extends Walker_Nav_Menu
{
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
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
function azs_handle_form_submission()
{
    check_ajax_referer('azs_nonce', 'nonce');

    $form_type = sanitize_text_field(wp_unslash($_POST['form_type'] ?? ''));

    if (!azs_consent_privacy_given()) {
        azs_consent_log($form_type, 400);
        wp_send_json_error(array('message' => 'Необходимо согласие на обработку персональных данных'));
    }

    $name      = sanitize_text_field(wp_unslash($_POST['name']      ?? ''));
    $phone     = sanitize_text_field(wp_unslash($_POST['phone']     ?? ''));
    $email     = sanitize_email(wp_unslash($_POST['email']          ?? ''));
    $comment   = sanitize_textarea_field(wp_unslash($_POST['comment'] ?? ''));
    $privacy   = azs_consent_privacy_given() ? 'да' : 'нет';
    $mailing   = azs_consent_mailing_given() ? 'да' : 'нет';

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
    $message .= 'Согласие на обработку персональных данных: ' . $privacy . "\n";
    $message .= 'Согласие на рекламную и информационную рассылку: ' . $mailing . "\n";
    $message .= "\n" . 'Сайт: ' . home_url('/');

    $admin_email = get_option('admin_email');
    
    $headers     = array('Content-Type: text/plain; charset=UTF-8');

    $sent = wp_mail($admin_email, $subject, $message, $headers);

    azs_consent_log($form_type, $sent ? 200 : 500, array(
        'name'    => $name,
        'phone'   => $phone,
        'email'   => $email,
        'comment' => $comment,
    ));

    if ($sent) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}
// ===========================================
// News card image: the list image if set, otherwise the post image
// ===========================================
function azs_news_card_image()
{
    $list_image_id = function_exists('get_field') ? (int) get_field('news_list_image') : 0;
    $url = $list_image_id ? wp_get_attachment_image_url($list_image_id, 'medium_large') : '';
    if (!$url && has_post_thumbnail()) {
        $url = get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
    }
    if (!$url) {
        $url = azs_img('1.jpg');
    }

    // The image is shown whole on a white background when the proportions differ
    echo '<img src="' . esc_url($url) . '" alt="' . the_title_attribute(array('echo' => false)) . '">';
}

// ===========================================
// News archive: posts per page
// ===========================================
add_action('pre_get_posts', 'azs_news_archive_query');
function azs_news_archive_query($query)
{
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('azs_news')) {
        $query->set('posts_per_page', 6);
    }
}

// ===========================================
// News AJAX Load More
// ===========================================
add_action('wp_ajax_azs_load_more_news', 'azs_load_more_news');
add_action('wp_ajax_nopriv_azs_load_more_news', 'azs_load_more_news');
function azs_load_more_news()
{
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
                <div class="image"><?php azs_news_card_image(); ?></div>
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
function azs_news_load_more_script()
{
    if (!is_post_type_archive('azs_news')) return;
    ?>
    <script>
        (function() {
            var btn = document.getElementById('azs-load-more-news');
            if (!btn) return;
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var page = parseInt(btn.dataset.page, 10);
                var max = parseInt(btn.dataset.max, 10);
                btn.classList.add('loading');
                var fd = new FormData();
                fd.append('action', 'azs_load_more_news');
                fd.append('page', page);
                fetch(azs_ajax.ajax_url, {
                        method: 'POST',
                        body: fd
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(res) {
                        btn.classList.remove('loading');
                        if (res.success) {
                            var list = document.getElementById('azs-news-list');
                            list.insertAdjacentHTML('beforeend', res.data.html);
                            btn.dataset.page = page + 1;
                            if (!res.data.has_more) {
                                btn.style.display = 'none';
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
function azs_slug($text)
{
    $slug = mb_strtolower($text, 'UTF-8');
    $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

// ===========================================
// Article page: tab switching script
// ===========================================
add_action('wp_footer', 'azs_article_tabs_script');
function azs_article_tabs_script()
{
    if (!is_page_template('page-article.php')) return;
?>
    <script>
        (function() {
            var buttons = document.querySelectorAll('[data-article-tab]');
            var contents = document.querySelectorAll('[data-article-content]');
            if (!buttons.length || !contents.length) return;

            function activateTab(slug) {
                var btn = document.querySelector('[data-article-tab="' + slug + '"]');
                if (!btn) return false;
                buttons.forEach(function(b) {
                    b.classList.remove('is_active');
                });
                contents.forEach(function(c) {
                    c.classList.add('is_hidden');
                });
                btn.classList.add('is_active');
                var target = document.querySelector('[data-article-content="' + slug + '"]');
                if (target) target.classList.remove('is_hidden');
                return true;
            }

            function scrollToTab(slug) {
                if (window.azsScrollToElement) {
                    window.azsScrollToElement(document.querySelector('[data-article-content="' + slug + '"]'));
                }
            }

            function hashSlug() {
                return decodeURIComponent(location.hash.replace('#', ''));
            }

            function onHashChange() {
                var slug = hashSlug();
                if (slug && activateTab(slug)) scrollToTab(slug);
            }

            buttons.forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var slug = btn.getAttribute('data-article-tab');
                    activateTab(slug);
                    history.replaceState(null, '', '#' + slug);
                    scrollToTab(slug);
                });
            });

            // Header and mobile menu links to a tab of this same page: switch the tab here
            // instead of letting the browser jump to the anchor (it lands under the sticky header)
            document.addEventListener('click', function(e) {
                var link = e.target.closest('a[href*="#"]');
                if (!link || link.hasAttribute('data-article-tab')) return;
                if (link.pathname !== location.pathname || !link.hash) return;
                var slug = decodeURIComponent(link.hash.replace('#', ''));
                if (!activateTab(slug)) return;
                e.preventDefault();
                history.replaceState(null, '', '#' + slug);
                if (typeof PopupManager !== 'undefined' && link.closest('[data-popup="popup_menu"]')) {
                    PopupManager.close('popup_menu');
                }
                scrollToTab(slug);
            });

            if (location.hash) activateTab(hashSlug());
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
function azs_product_search()
{
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
function azs_cart_count_fragment($fragments)
{
    if (!function_exists('WC')) return $fragments;

    $count = WC()->cart->get_cart_contents_count();
    $fragments['.button-accent[data-product]'] = '<a class="button button-filled button-accent" href="' . esc_url(wc_get_cart_url()) . '" data-product="' . esc_attr($count) . '"><span class="icon-cart"></span></a>';

    return $fragments;
}


add_filter( 'wpseo_frontend_presenter_classes', function ( $presenters ) {
    if ( ( $key = array_search( 'Yoast\WP\SEO\Presenters\Open_Graph\Article_Modified_Time_Presenter', $presenters ) ) !== false ) {
        unset( $presenters[$key] );
    }
    return $presenters;
});



