<?php
$phone    = function_exists('get_field') ? get_field('site_phone', 'option') : '';
$email    = function_exists('get_field') ? get_field('site_email', 'option') : '';
$schedule = function_exists('get_field') ? get_field('site_schedule', 'option') : '';

$phone    = $phone ?: '+7 (495) 789-34-64';
$email    = $email ?: 'info@pse.ru';
$schedule = $schedule ?: 'ежедневно: 9:00-18:00';
$phone_clean = preg_replace('/[^+\d]/', '', $phone);

?>

<!-- Cookies -->
<div class="popup popup-cookies" data-popup="popup_cookies" data-lenis-prevent>
    <div class="popup--content">
        <p>Пользуясь нашим сайтом, вы соглашаетесь на обработку файлов cookies в соответствии с <a href="">политикой обработки персональных данных</a></p>
        <div class="button button-filled button-secondary popup--close" data-closepopup data-accept><span>ОК</span></div>
    </div>
</div>

<!-- Mobile Menu -->
<div class="popup popup-menu" data-popup="popup_menu" data-lenis-prevent>
    <div class="top__bar">
        <div class="shedule -desk"><span>График работы:</span><span><?php echo esc_html(ucfirst($schedule)); ?></span></div>
        <div class="email"><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></div>
        <div class="tel">
            <a href="tel:<?php echo esc_attr($phone_clean); ?>"><?php echo esc_html($phone); ?></a>
            <span class="-desk">Заказать звонок</span>
        </div>
    </div>
    <div class="header">
        <div class="header__wrapper">
            <a class="header__logo" href="<?php echo esc_url(home_url('/')); ?>">
                <img src="<?php echo azs_img('logo.svg'); ?>" alt="<?php bloginfo('name'); ?>">
            </a>
            <div class="header__actions">
                <div class="button button-outlined -desk" data-closepopup="popup_menu" data-openpopup="popup_callback">
                    <span>Заказать звонок</span><span class="icon-arrow-right"></span>
                </div>
                <?php if (function_exists('WC')) : ?>
                    <a class="button button-filled button-accent" href="<?php echo esc_url(wc_get_cart_url()); ?>">
                        <span class="icon-cart"></span>
                    </a>
                <?php endif; ?>
            </div>
            <a class="button button-filled button-accent header__menu-btn" data-closepopup="popup_menu">
                <span class="icon-close"></span>
            </a>
        </div>
    </div>
    <div class="popup--content">
        <div class="menu">
            <div class="dropdown" data-menu-dropdown>
                <div class="menu__item"><span>Каталог</span><span class="icon-drop-down"></span></div>
                <div class="dropdown__wrapper">
                    <div class="dropdown__content">
                        <?php
                        if (taxonomy_exists('product_cat')) {
                            $uncat = get_term_by('slug', 'uncategorized', 'product_cat');
                            $cats = get_terms(array(
                                'taxonomy'   => 'product_cat',
                                'parent'     => 0,
                                'hide_empty' => false,
                                'exclude'    => $uncat ? array($uncat->term_id) : array(),
                            ));
                            if (!is_wp_error($cats)) {
                                foreach ($cats as $cat) {
                                    echo '<a class="item" href="' . esc_url(get_term_link($cat)) . '">' . esc_html($cat->name) . '</a>';
                                }
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'walker'         => new AZS_Mobile_Menu_Walker(),
                'container'      => false,
                'items_wrap'     => '%3$s',
                'fallback_cb'    => false,
            ));
            ?>
        </div>
        <div class="popup--actions">
            <div class="button button-filled button-accent" data-closepopup="popup_menu" data-openpopup="popup_callback">
                <span>Заказать звонок</span><span class="icon-arrow-right"></span>
            </div>
        </div>
    </div>
</div>

<!-- Callback Form -->
<div class="popup popup-form popup-universal" data-popup="popup_callback" data-lenis-prevent>
    <div class="popup--content">
        <div class="popup--title">
            <h3>Заполните форму и мы вам перезвоним</h3>
            <div class="popup--close" data-closepopup="popup_callback"><span class="icon-close"></span></div>
        </div>
        <form class="form" method="post" data-form-type="callback">
            <label class="form--input">
                <input type="text" placeholder="Иванов Иван Иванович" name="name" required>
            </label>
            <label class="form--input">
                <input type="tel" placeholder="+7 999 999-99-99" name="phone" inputmode="numeric" autocomplete="tel" required>
            </label>
            <button class="button button-filled button-accent" type="submit"><span>Заказать звонок</span><span class="icon-arrow-right"></span></button>
            <div class="form--policy">
                <label class="form--input input-checkbox">
                    <input type="checkbox" required><span></span>
                </label>
                <span>Отправляя форму, вы соглашаетесь на обработку персональных данных в соответствии с <a href="">политикой обработки персональных данных</a></span>
            </div>
        </form>
    </div>
</div>

<!-- Product Notification Form -->
<div class="popup popup-form popup-universal" data-popup="popup_form" data-lenis-prevent>
    <div class="popup--content">
        <div class="popup--title">
            <h3>Сообщить о поступлении товара</h3>
            <div class="popup--close" data-closepopup="popup_form"><span class="icon-close"></span></div>
        </div>
        <form class="form" method="post" data-form-type="product">
            <label class="form--input">
                <input type="text" placeholder="Иванов Иван Иванович" name="name" required autocomplete="name">
            </label>
            <label class="form--input">
                <input type="tel" placeholder="+7 999 999-99-99" name="phone" inputmode="numeric" autocomplete="tel" required>
            </label>
            <label class="form--input">
                <input type="email" placeholder="example@imperial.ru" name="email" autocomplete="email" required>
            </label>
            <label class="form--input">
                <textarea placeholder="Введите комментарий" name="comment" rows="4" maxlength="1000"></textarea>
            </label>
            <button class="button button-filled button-accent" type="submit"><span>Сообщить о поступлении</span><span class="icon-arrow-right"></span></button>
            <div class="form--policy">
                <label class="form--input input-checkbox">
                    <input type="checkbox" required><span></span>
                </label>
                <span>Отправляя форму, вы соглашаетесь на обработку персональных данных в соответствии с <a href="">политикой обработки персональных данных</a></span>
            </div>
        </form>
    </div>
</div>

<!-- News Subscription -->
<div class="popup popup-form popup-universal" data-popup="popup_news" data-lenis-prevent>
    <div class="popup--content">
        <div class="popup--title">
            <h3>Подпишитесь на новости, чтобы не пропустить акции и новые товары</h3>
            <div class="popup--close" data-closepopup="popup_news"><span class="icon-close"></span></div>
        </div>
        <form class="form" method="post" data-form-type="news">
            <label class="form--input">
                <input type="text" placeholder="Иванов Иван Иванович" name="name" required>
            </label>
            <label class="form--input">
                <input type="email" placeholder="example@imperial.ru" name="email" autocomplete="email" required>
            </label>
            <button class="button button-filled button-accent" type="submit"><span>Подписаться на новости</span><span class="icon-arrow-right"></span></button>
            <div class="form--policy">
                <label class="form--input input-checkbox">
                    <input type="checkbox" required><span></span>
                </label>
                <span>Отправляя форму, вы соглашаетесь на обработку персональных данных в соответствии с <a href="">политикой обработки персональных данных</a></span>
            </div>
        </form>
    </div>
</div>

<!-- Product Added to Cart -->
<div class="popup popup-appended popup-universal" data-popup="popup_appended" data-lenis-prevent>
    <div class="popup--content">
        <div class="popup--title">
            <h3>Товар добавлен в корзину</h3>
            <div class="popup--close" data-closepopup="popup_appended"><span class="icon-close"></span></div>
        </div>
        <div class="catalog">
            <div class="card">
                <div class="card--image"></div>
                <div class="card--title">
                    <span></span><p></p>
                </div>
            </div>
        </div>
        <div class="block--actions">
            <?php if (function_exists('wc_get_cart_url')) : ?>
                <a class="button button-filled button-accent" data-closepopup="popup_appended" href="<?php echo esc_url(wc_get_cart_url()); ?>">
                    <span>Оформить заказ</span><span class="icon-arrow-right"></span>
                </a>
            <?php endif; ?>
            <button class="button button-outlined" data-closepopup="popup_appended">
                <span>Продолжить покупки</span><span class="icon-arrow-right"></span>
            </button>
        </div>
    </div>
</div>

<!-- Success -->
<div class="popup popup-universal popup" data-popup="popup_success" data-lenis-prevent>
    <div class="popup--content">
        <div class="popup--title">
            <div class="popup--close" data-closepopup="popup_success"><span class="icon-close"></span></div>
        </div>
        <div class="notification notification-success">
            <div class="notification--title">Спасибо! <br> Ваша заявка принята</div>
            <div class="notification--img"></div>
            <div class="notification--text">Наш менеджер свяжится с вами в ближайшее время</div>
        </div>
    </div>
</div>

<!-- Error -->
<div class="popup popup-universal popup" data-popup="popup_error" data-lenis-prevent>
    <div class="popup--content">
        <div class="popup--title">
            <div class="popup--close" data-closepopup="popup_error"><span class="icon-close"></span></div>
        </div>
        <div class="notification notification-error">
            <div class="notification--title">Ошибка отправки</div>
            <div class="notification--img"></div>
            <div class="notification--text">Попробуйте повторить отправку позже или
                <div class="button" data-openpopup="popup_callback" data-closepopup="popup_error">свяжитесь с нами</div>
            </div>
        </div>
    </div>
</div>
