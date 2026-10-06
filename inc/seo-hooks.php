<?php

if (!defined('ABSPATH')) {
    exit;
}

function azs_exclude_service_pages_from_sitemap($excluded_ids)
{
    if (!function_exists('wc_get_page_id')) {
        return $excluded_ids;
    }

    foreach (['cart', 'myaccount'] as $page) {
        $page_id = (int) wc_get_page_id($page);
        if ($page_id > 0) {
            $excluded_ids[] = $page_id;
        }
    }

    return $excluded_ids;
}

add_filter('wpseo_exclude_from_sitemap_by_post_ids', 'azs_exclude_service_pages_from_sitemap');

function azs_invalidate_service_page_sitemap()
{
    if (get_option('azs_sitemap_cart_account_excluded') === '1') {
        return;
    }
    if (!class_exists('WPSEO_Sitemaps_Cache_Validator') || !method_exists('WPSEO_Sitemaps_Cache_Validator', 'invalidate_storage')) {
        return;
    }

    WPSEO_Sitemaps_Cache_Validator::invalidate_storage('page');
    update_option('azs_sitemap_cart_account_excluded', '1');
}

add_action('init', 'azs_invalidate_service_page_sitemap');
