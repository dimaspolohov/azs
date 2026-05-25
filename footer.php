    <?php
    $phone    = function_exists('get_field') ? get_field('site_phone', 'option') : '';
    $email    = function_exists('get_field') ? get_field('site_email', 'option') : '';
    $address  = function_exists('get_field') ? get_field('site_address', 'option') : '';
    $map_img  = function_exists('get_field') ? get_field('map_image', 'option') : null;
    $map_link = function_exists('get_field') ? get_field('map_link', 'option') : '';

    $phone   = $phone ?: '+7 (495) 789-34-64';
    $email   = $email ?: 'info@pse.ru';
    $address = $address ?: "125130, г. Москва,\nСтаропетровский пр., д. 7А, стр. 6";
    $phone_clean = preg_replace('/[^+\d]/', '', $phone);

    $tg   = function_exists('get_field') ? get_field('social_telegram', 'option') : '';
    $ym   = function_exists('get_field') ? get_field('social_yandex_market', 'option') : '';
    $ozon = function_exists('get_field') ? get_field('social_ozon', 'option') : '';
    $dzen = function_exists('get_field') ? get_field('social_dzen', 'option') : '';
    ?>

    <footer class="footer">
        <div class="wrapper">
            <div class="footer__actions">
                <div class="footer__logo">
                    <img src="<?php echo azs_img('logo-dark.svg'); ?>" alt="<?php bloginfo('name'); ?>">
                </div>
                <div class="button button-outlined" data-openpopup="popup_callback">
                    <span>Заказать звонок</span><span class="icon-arrow-right"></span>
                </div>
                <div class="footer__actions_contacts">
                    <a class="link" href="tel:<?php echo esc_attr($phone_clean); ?>"><?php echo esc_html($phone); ?></a>
                    <a class="link" href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                </div>
                <div class="soc">
                    <?php if ($tg) : ?>
                        <a class="button button-outlined" href="<?php echo esc_url($tg); ?>" target="_blank" rel="noopener"><span class="icon-tg"></span></a>
                    <?php endif; ?>
                    <?php if ($ym) : ?>
                        <a class="button button-outlined" href="<?php echo esc_url($ym); ?>" target="_blank" rel="noopener"><span class="icon-yandexmarket"></span></a>
                    <?php endif; ?>
                    <?php if ($ozon) : ?>
                        <a class="button button-outlined" href="<?php echo esc_url($ozon); ?>" target="_blank" rel="noopener"><span class="icon-ozon"></span></a>
                    <?php endif; ?>
                    <?php if ($dzen) : ?>
                        <a class="button button-outlined" href="<?php echo esc_url($dzen); ?>" target="_blank" rel="noopener"><span class="icon-dzen"></span></a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="footer__contacts">
                <div class="adress">
                    <span>Адрес:</span>
                    <p><?php echo nl2br(esc_html($address)); ?></p>
                </div>
                <div class="map">
                    <?php if ($map_link) : ?><a href="<?php echo esc_url($map_link); ?>" target="_blank" rel="noopener"><?php endif; ?>
                        <?php if ($map_img) : ?>
                            <img src="<?php echo esc_url($map_img['url']); ?>" alt="<?php echo esc_attr($map_img['alt'] ?? 'Карта'); ?>">
                        <?php else : ?>
                            <img src="<?php echo azs_img('4.jpg'); ?>" alt="Карта">
                        <?php endif; ?>
                    <?php if ($map_link) : ?></a><?php endif; ?>
                </div>
                <div class="footer__contacts_actions">
                    <?php if (function_exists('wc_get_page_id')) : ?>
                        <a class="button button-outlined footer__catalog-btn" href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>">
                            <span>Каталог</span><span class="icon-arrow-right"></span>
                        </a>
                    <?php endif; ?>
                    <div class="button button-outlined -mob" data-openpopup="popup_menu">
                        <span>Меню</span><span class="icon-arrow-right"></span>
                    </div>
                </div>
            </div>

            <div class="footer__nav -desk">
                <div class="nav">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer_col_1',
                        'walker'         => new AZS_Footer_Walker(),
                        'container'      => false,
                        'items_wrap'     => '%3$s',
                        'fallback_cb'    => false,
                    ));
                    ?>
                </div>
            </div>

            <div class="footer__nav">
                <div class="nav -desk">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer_col_2',
                        'walker'         => new AZS_Footer_Walker(),
                        'container'      => false,
                        'items_wrap'     => '%3$s',
                        'fallback_cb'    => false,
                    ));
                    ?>
                </div>
                <div class="footer__btn">
                    <div class="button button-outlined btn_up"><span>Вверх</span><span class="icon-arrow-up"></span></div>
                </div>
            </div>

            <div class="footer__copyright">
                <a class="footer__bottom_item link" href="<?php echo esc_url(home_url('/')); ?>">©1997-<?php echo date('Y'); ?> ООО «Торговый Дом «Всё для АЗС»</a>
                <a class="footer__bottom_item link" href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">Политика конфиденциальности</a>
                <a class="footer__bottom_item ritm link" href="https://ritmo.studio/">Сделано в Ритме</a>
            </div>
        </div>
    </footer>

    <?php get_template_part('template-parts/popups'); ?>

</main>
<?php wp_footer(); ?>
</body>
</html>
