<?php

if (!defined('ABSPATH')) exit;

define('AZS_CANONICAL_HOST', 'piusi-raasm.ru');
define('AZS_CANONICAL_SCHEME', 'https');

add_action('template_redirect', 'azs_canonical_redirect', 0);

function azs_is_https_request()
{
    if (is_ssl()) {
        return true;
    }
    $proto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    if ($proto === 'https') {
        return true;
    }
    return (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
}

function azs_canonical_redirect()
{
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
        return;
    }
    if (defined('WP_CLI') && WP_CLI) {
        return;
    }

    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($method !== 'GET' && $method !== 'HEAD') {
        return;
    }

    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $host = preg_replace('/:\d+$/', '', $host);
    $raw_uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');

    $path = parse_url($raw_uri, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        $path = '/';
    }

    $query = (string) ($_SERVER['QUERY_STRING'] ?? '');
    $has_empty_question_mark = ($query === '' && strpos($raw_uri, '?') !== false);

    $is_homepage_index = (bool) preg_match('#^/index\.(html?|php)$#i', $path);
    if ($is_homepage_index) {
        $path = '/';
    }

    $scheme = azs_is_https_request() ? 'https' : 'http';

    $need_redirect = false;

    if ($host !== AZS_CANONICAL_HOST) {
        $need_redirect = true;
    }
    if ($scheme !== AZS_CANONICAL_SCHEME) {
        $need_redirect = true;
    }
    if ($is_homepage_index) {
        $need_redirect = true;
    }
    if ($has_empty_question_mark) {
        $need_redirect = true;
    }

    if (!$need_redirect) {
        return;
    }

    $target = AZS_CANONICAL_SCHEME . '://' . AZS_CANONICAL_HOST . $path;
    if ($query !== '') {
        $target .= '?' . $query;
    }

    wp_redirect($target, 301);
    exit;
}
