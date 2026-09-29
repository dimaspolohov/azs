<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="ltr">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="google-site-verification" content="JyMjjdce7dnrdJa_WfcJxNvLQxC0zGSGTl_OF_V-pmw" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
<meta property="og:image" content="https://piusi-raasm.ru/wp-content/themes/azs/assets/img/logo-green.jpg"/>
    <?php wp_head(); ?>



    <!-- Yandex.Metrika counter -->
    <script type="text/javascript">
        (function(m, e, t, r, i, k, a) {
            m[i] = m[i] || function() {
                (m[i].a = m[i].a || []).push(arguments)
            };
            m[i].l = 1 * new Date();
            for (var j = 0; j < document.scripts.length; j++) {
                if (document.scripts[j].src === r) {
                    return;
                }
            }
            k = e.createElement(t), a = e.getElementsByTagName(t)[0], k.async = 1, k.src = r, a.parentNode.insertBefore(k, a)
        })(window, document, 'script', 'https://mc.yandex.ru/metrika/tag.js', 'ym');

        ym(26808882, 'init', {
            webvisor: true,
            clickmap: true,
            referrer: document.referrer,
            url: location.href,
            accurateTrackBounce: true,
            trackLinks: true
        });
    </script>
    <noscript>
        <div><img src="https://mc.yandex.ru/watch/26808882" style="position:absolute; left:-9999px;" alt="" /></div>
    </noscript>
    <!-- /Yandex.Metrika counter -->


</head>

<body <?php body_class(); ?>>



    <?php wp_body_open(); ?>
    <main>
        <?php
        $phone    = function_exists('get_field') ? get_field('site_phone', 'option') : '';
        $email    = function_exists('get_field') ? get_field('site_email', 'option') : '';
        $schedule = function_exists('get_field') ? get_field('site_schedule', 'option') : '';
        $phone    = $phone ?: '+7 (495) 789-34-64';
        $email    = $email ?: 'info@pse.ru';
        $schedule = $schedule ?: 'ежедневно: 9:00-18:00';
        $phone_clean = preg_replace('/[^+\d]/', '', $phone);
        ?>
        <div class="top__bar">
            <div class="shedule -desk"><span>График работы:&nbsp;</span><span><?php echo esc_html($schedule); ?></span></div>
            <div class="top__bar__actions">
                <a href="tel:<?php echo esc_attr($phone_clean); ?>"><?php echo esc_html($phone); ?></a>
                <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
            </div>
        </div>

        <header class="header">
            <div class="header__wrapper" itemscope="" itemtype="https://schema.org/SiteNavigationElement">
                <a class="header__logo" href="<?php echo esc_url(home_url('/')); ?>">
                    <img src="<?php echo azs_img('logo-green.svg'); ?>" alt="<?php bloginfo('name'); ?>">
                </a>
                <nav class="header__nav" itemprop="about" itemscope="" itemtype="https://schema.org/ItemList">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'walker'         => new AZS_Header_Walker(),
                        'container'      => false,
                        'items_wrap'     => '%3$s',
                        'fallback_cb'    => false,
                    ));
                    ?>
                </nav>
                <div class="header__actions">
                    <a class="button button-outlined -desk" data-openpopup="popup_callback" data-ym-goal="zakazat-zvonok-v-hedere">
                        <span>Заказать звонок</span><span class="icon-arrow-right"></span>
                    </a>
                    <?php if (function_exists('WC')) : ?>
                        <a class="button button-filled button-accent" href="<?php echo esc_url(wc_get_cart_url()); ?>" data-product="<?php echo esc_attr(WC()->cart->get_cart_contents_count()); ?>">
                            <span class="icon-cart"></span>
                        </a>
                    <?php endif; ?>
                </div>
                <a class="button button-filled button-accent header__menu-btn" data-openpopup="popup_menu">
                    <span class="icon-menu"></span>
                </a>
            </div>
        </header>