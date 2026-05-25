<?php
/**
 * 503 Service Unavailable (maintenance mode) template
 *
 * Used by the azs_maintenance_mode() function in functions.php
 * when WordPress enters maintenance mode.
 */

if (!defined('ABSPATH')) exit;

$protocol = wp_get_server_protocol();
header("$protocol 503 Service Unavailable", true, 503);
header('Retry-After: 600');

get_header();

$shop_url = function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : home_url('/');
?>

    <div class="block error">
        <div class="block--wrapper">
            <div class="error--title">
                <h1>503</h1>
                <p>Ошибка сервера</p>
                <span>Похоже, ссылка устарела или введена с ошибкой</span>
            </div>
            <div class="block--actions">
                <a class="button button-filled button-accent" href="<?php echo esc_url($shop_url); ?>">
                    <span>Перейти в каталог</span><span class="icon-arrow-right"></span>
                </a>
                <a class="button button-outlined" href="<?php echo esc_url(home_url('/')); ?>">
                    <span>Перейти на главную</span><span class="icon-arrow-right"></span>
                </a>
            </div>
        </div>
    </div>

<?php get_footer();
