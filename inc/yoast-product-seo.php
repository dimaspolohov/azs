<?php

/**
 * Yoast SEO: шаблоны title/description для карточек товаров.
 */

if (!defined('ABSPATH')) {
    exit;
}

function azs_is_product_seo_context()
{
    return function_exists('is_product') && is_product();
}

function azs_get_product_seo_h1()
{
    $title = get_the_title();
    return is_string($title) ? trim(wp_strip_all_tags($title)) : '';
}

function azs_yoast_product_has_custom($field)
{
    if (!class_exists('WPSEO_Meta')) {
        return false;
    }

    $post_id = get_queried_object_id();
    if (!$post_id) {
        return false;
    }

    $value = WPSEO_Meta::get_value($field, $post_id);
    return is_string($value) && trim($value) !== '';
}

function azs_get_product_seo_title()
{
    $h1 = azs_get_product_seo_h1();
    if ($h1 === '') {
        return '';
    }

    return $h1 . ' — купить в компании «Всё для АЗС»';
}

function azs_get_product_seo_description()
{
    $h1 = azs_get_product_seo_h1();
    if ($h1 === '') {
        return '';
    }

    return $h1 . ' купить в интернет-магазине по выгодным ценам. Доставка по всей России, гарантия, оплата по счёту.';
}

add_filter('wpseo_title', 'azs_yoast_product_title', 20);
function azs_yoast_product_title($title)
{
    if (!azs_is_product_seo_context() || azs_yoast_product_has_custom('title')) {
        return $title;
    }

    $generated = azs_get_product_seo_title();
    return $generated !== '' ? $generated : $title;
}

add_filter('wpseo_metadesc', 'azs_yoast_product_description', 20);
function azs_yoast_product_description($description)
{
    if (!azs_is_product_seo_context() || azs_yoast_product_has_custom('metadesc')) {
        return $description;
    }

    $generated = azs_get_product_seo_description();
    return $generated !== '' ? $generated : $description;
}

add_filter('wpseo_opengraph_title', 'azs_yoast_product_title', 20);
add_filter('wpseo_opengraph_desc', 'azs_yoast_product_description', 20);
add_filter('wpseo_twitter_title', 'azs_yoast_product_title', 20);
add_filter('wpseo_twitter_description', 'azs_yoast_product_description', 20);
