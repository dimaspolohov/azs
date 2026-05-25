<?php
/**
 * Custom cart page template.
 * Replaces the default WooCommerce cart layout with the design from frontend/cart.html.
 * Orders are placed directly from this page (no WC checkout redirect).
 */

defined('ABSPATH') || exit;

get_header();

$cart       = WC()->cart;
$cart_items = $cart->get_cart();
$is_empty   = empty($cart_items);
$shop_url   = function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : home_url('/');
$privacy_url = get_privacy_policy_url();
?>

<div class="block--wrapper">
    <div class="breadcrumbs" itemscope="" itemtype="https://schema.org/BreadcrumbList">
        <a href="<?php echo esc_url(home_url('/')); ?>" itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">
            <meta itemprop="position" content="1"/><span itemprop="name">Главная</span><span class="separator">></span>
        </a>
        <a href="<?php echo esc_url(wc_get_cart_url()); ?>" itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">
            <meta itemprop="position" content="2"/><span itemprop="name">Корзина</span>
        </a>
    </div>
</div>

<?php if (!$is_empty) :
    $breakdown = azs_cart_price_breakdown();
?>
<div class="block cart" id="azs-cart">
    <div class="block--wrapper">
        <div class="block--content">
            <div class="block--title showInUp">
                <h2 class="block--title_title">Корзина</h2>
            </div>
            <div class="block--description -desk">
                <span>Товар</span><span>Описание</span><span>Цена</span><span>Количество</span><span>Итого</span>
            </div>
            <div class="block--list" id="azs-cart-items">
                <?php
                foreach ($cart_items as $key => $item) {
                    echo azs_render_cart_item($key, $item);
                }
                ?>
            </div>
            <div class="block--price" id="azs-cart-totals">
                <?php echo azs_render_cart_totals(); ?>
            </div>
        </div>

        <div class="block--side">
            <div class="side">
                <div class="side--title">
                    <p>Оформление заказа</p>
                </div>
                <div class="side--total">
                    <p>Итого к оплате:</p>
                    <div id="azs-side-total">
                        <span><?php echo esc_html(azs_format_price($breakdown['total'])); ?></span><span>₽</span>
                    </div>
                </div>
                <form class="form" id="azs-order-form">
                    <label class="form--input">
                        <input type="text" placeholder="Иванов Иван Иванович" name="order_name" required autocomplete="name">
                    </label>
                    <label class="form--input">
                        <input type="tel" placeholder="+7 999 999-99-99" name="order_phone" inputmode="numeric" autocomplete="tel" required>
                    </label>
                    <button class="button button-filled button-accent" type="submit">
                        <span>Оформить заказ</span><span class="icon-arrow-right"></span>
                    </button>
                    <div class="form--policy">
                        <label class="form--input input-checkbox">
                            <input type="checkbox" required><span></span>
                        </label>
                        <span>Отправляя форму, вы соглашаетесь на обработку персональных данных в соответствии с
                            <a href="<?php echo esc_url($privacy_url ?: '#'); ?>">политикой обработки персональных данных</a>
                        </span>
                    </div>
                </form>
                <div class="note">
                    <p>После оформления заказа с Вами свяжется наш менеджер.</p>
                    <p>Он проконсультирует Вас по всем вопросам. И уточнит необходимую информацию для доставки.</p>
                </div>
            </div>
        </div>

        <div class="block--pagination">
            <a class="button button-outlined" href="<?php echo esc_url($shop_url); ?>">
                <span>Вернуться в каталог</span><span class="icon-arrow-right"></span>
            </a>
        </div>
    </div>
</div>

<?php
    $cart_related_html = azs_render_cart_related_products(4);
?>
<div id="azs-cart-related"><?php echo $cart_related_html; ?></div>

<?php else : ?>

<div class="block cart" id="azs-cart">
    <div class="block--wrapper">
        <div class="block--content">
            <div class="block--title showInUp">
                <h2 class="block--title_title">Корзина</h2>
            </div>
            <div class="block--list">
                <div class="catalog-empty" style="padding: var(--s-40) 0; text-align: center;">
                    <p style="font-size: var(--t-20); color: var(--c-secondary);">Ваша корзина пуста</p>
                </div>
            </div>
        </div>
        <div class="block--pagination">
            <a class="button button-outlined" href="<?php echo esc_url($shop_url); ?>">
                <span>Перейти в каталог</span><span class="icon-arrow-right"></span>
            </a>
        </div>
    </div>
</div>

<?php endif; ?>

<?php get_footer(); ?>
