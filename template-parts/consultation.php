<?php
$title    = !empty($args['title']) ? $args['title'] : 'Нужна консультация?<br> Оставьте заявку ↓';
$subtitle = !empty($args['subtitle']) ? $args['subtitle'] : 'Поможем выбрать, оформить и установить оборудование';

$privacy_url = home_url('/privacy-policy/');
$offer_url   = home_url('/publichnaya-oferta/');
?>
<div class="block block_banner block_banner-form">
    <div class="block--wrapper">
        <div class="banner">
            <div class="block--image">
                <picture>
                    <source media="(max-width: 640px)" srcset="<?php echo azs_img('3.png'); ?>">
                    <img src="<?php echo azs_img('3.png'); ?>">
                </picture>
            </div>
            <div class="block--content">
                <div class="banner--title showInUp">
                    <p><?php echo wp_kses_post($title); ?></p>
                    <span><?php echo esc_html($subtitle); ?></span>
                </div>
                <div class="banner--form">
                    <form class="form" method="post" data-form-type="consultation" novalidate>
                        <div class="form--input">
                            <input type="text" placeholder="Ваше имя" name="name" data-validate="name" required>
                        </div>
                        <div class="form--input">
                            <input type="tel" placeholder="Телефон" name="phone" data-validate="phone" required>
                        </div>
                        <button class="button button-filled button-accent" type="submit">
                            <span>Заказать звонок</span><span class="icon-arrow-right"></span>
                        </button>
                        <div class="form--policy">
                    <label class="form--input input-checkbox">
                        <input type="checkbox" name="privacy" value="1" data-validate="privacy" required><span></span>
                    </label>
                    <span>Согласен на обработку персональных данных в соответствии с
                        <a href="<?php echo esc_url($privacy_url); ?>" target="_blank" rel="noopener">Политикой конфиденциальности</a>
                    </span>
                </div>
                <div class="form--policy">
                    <label class="form--input input-checkbox">
						<input type="checkbox" name="mailing" value="1" data-validate="mailing" required><span></span>
                    </label>
                    <span>Согласен на получение рекламных и информационных сообщений в соответствии с
                        <a href="<?php echo esc_url($offer_url); ?>">Офертой</a>
                    </span>
                </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
