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
                    <p>Нужна консультация?<br> Оставьте заявку ↓</p>
                    <span>Поможем выбрать, оформить и установить оборудование</span>
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
                            <span>Нажимая на кнопку «Оставить заявку», вы соглашаетесь на обработку персональных данных в соответствии с <a href="/privacy-policy/">политикой обработки персональных данных</a></span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>