<?php

if (!defined('ABSPATH')) exit;

add_action('acf/init', 'azs_register_options_page');

function azs_register_options_page() {
    if (!function_exists('acf_add_options_page')) return;

    acf_add_options_page(array(
        'page_title' => 'Настройки сайта',
        'menu_title' => 'Настройки сайта',
        'menu_slug'  => 'azs-site-settings',
        'capability' => 'edit_posts',
        'redirect'   => false,
        'icon_url'   => 'dashicons-admin-settings',
        'position'   => 2,
    ));
}
