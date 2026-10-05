<?php
/**
 * WooCommerce Single Product — fully replaces default template.
 * Matches frontend/catalog-detail.html layout.
 */

if (!defined('ABSPATH')) exit;

get_header();


$brand_schema = '';
$img_url_schema = '';
$stock_schema = '';
$description_schema = '';
$category_schema = '';

while (have_posts()) : the_post();
    global $product;
    $product = wc_get_product(get_the_ID());
    if (!$product) continue;

    $product_id    = $product->get_id();
    $title         = get_the_title();
    $sku           = $product->get_sku();
    $short_desc    = $product->get_short_description();
    $description   = apply_filters('the_content', $product->get_description() ?: get_the_content());
    $is_in_stock   = $product->is_in_stock();
    $is_on_sale    = $product->is_on_sale();
    $is_new        = get_post_meta($product_id, 'is_new', true) === 'yes';
    $regular_price = $product->get_regular_price();
    $sale_price    = $product->get_sale_price();
    $price         = $product->get_price();
    $stock_qty     = $product->get_stock_quantity();

    $featured_id   = $product->get_image_id();
    $gallery_ids   = $product->get_gallery_image_ids();
    $all_image_ids = $featured_id ? array_merge(array($featured_id), $gallery_ids) : $gallery_ids;

    $featured_url = $featured_id
        ? wp_get_attachment_image_url($featured_id, 'medium_large')
        : get_stylesheet_directory_uri() . '/assets/img/empty.jpg';

    $in_cart = false;
    if (function_exists('WC') && WC()->cart) {
        foreach (WC()->cart->get_cart() as $cart_item) {
            if ((int) $cart_item['product_id'] === $product_id) {
                $in_cart = true;
                break;
            }
        }
    }

    $primary_term = azs_get_product_primary_cat($product_id);
    $shop_url     = get_permalink(wc_get_page_id('shop'));
    $attributes   = $product->get_attributes();

    $passport_file = function_exists('get_field') ? get_field('product_passport', $product_id) : null;
    $catalog_file  = function_exists('get_field') ? get_field('product_manufacturer_catalog', $product_id) : null;

    if (empty($passport_file)) {
        $passport_url = get_post_meta($product_id, '_product_passport_url', true);
        if ($passport_url) $passport_file = array('url' => $passport_url);
    }
    if (empty($catalog_file)) {
        $catalog_url = get_post_meta($product_id, '_product_mfr_catalog_url', true);
        if ($catalog_url) $catalog_file = array('url' => $catalog_url);
    }

    $buy_now_url = add_query_arg('add-to-cart', $product_id, wc_get_cart_url());
?>

      <div class="block--wrapper">
        <div class="breadcrumbs" itemscope="" itemtype="https://schema.org/BreadcrumbList"><a href="<?php echo esc_url(home_url('/')); ?>" itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">
            <meta itemprop="position" content="1"/><span itemprop="name">Главная</span><span class="separator">></span></a><a href="<?php echo esc_url($shop_url); ?>" itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">
            <meta itemprop="position" content="2"/><span itemprop="name">Каталог</span><span class="separator">></span></a><?php
        if ($primary_term) :
            $ancestors = array_reverse(get_ancestors($primary_term->term_id, 'product_cat'));
            $pos = 3;

			$category_schema = $primary_term->name;

            foreach ($ancestors as $key => $anc_id) :
                $anc = get_term($anc_id, 'product_cat');
                if (!$anc || is_wp_error($anc)) continue;
            ?><a href="<?php echo esc_url(get_term_link($anc)); ?>" itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">
            <meta itemprop="position" content="<?php echo $pos++; ?>"/><span itemprop="name"><?php echo esc_html($anc->name); ?></span><span class="separator">></span></a><?php
            endforeach;
            ?><a href="<?php echo esc_url(get_term_link($primary_term)); ?>" itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">
            <meta itemprop="position" content="<?php echo $pos++; ?>"/><span itemprop="name"><?php echo esc_html($primary_term->name); ?></span><span class="separator">></span></a><a href="" itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">
            <meta itemprop="position" content="<?php echo $pos; ?>"/><span itemprop="name"><?php echo esc_html($title); ?></span></a>
        <?php else : ?><a itemprop="itemListElement" itemscope="" itemtype="https://schema.org/ListItem">
            <meta itemprop="position" content="3"/><span itemprop="name"><?php echo esc_html($title); ?></span></a>
        <?php endif; ?>
        </div>
      </div>

      <div class="block catalog catalog-detail">
        <div class="block--wrapper">
          <div class="card" data-product-id="<?php echo esc_attr($product_id); ?>">
            <div class="card--image">

              <?php if (!empty($all_image_ids)) :

                      $img_url_schema = wp_get_attachment_image_url($all_image_ids[0], 'large');

                  foreach ($all_image_ids as $key => $img_id) :
                      $img_url = wp_get_attachment_image_url($img_id, 'large');
                      if (!$img_url) continue;
              ?>
              <div class="img-slide"><img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($title); ?>"></div>
              <?php endforeach;
              else : ?>
              <div class="img-slide"><img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/empty.jpg'); ?>" alt="<?php echo esc_attr($title); ?>"></div>
              <?php endif; ?>
              <div class="card--labels"><?php if ($is_new) : ?><span class="card--label card--label-new">НОВИНКА</span><?php endif; ?><?php if ($is_on_sale && $is_in_stock) : ?><span class="card--label card--label-sale">% Скидка</span><?php endif; ?><?php if (!$is_in_stock) : ?><span class="card--label card--label-noStock">нет в наличии</span><?php endif; ?></div>
            </div>
            <div class="card--content">
              <div class="card--title">
                <?php if ($sku) : ?><span>Артикул: <?php echo esc_html($sku); ?></span><?php endif; ?><h1><?php echo esc_html($title); ?></h1>
              </div>
              <?php if ($short_desc) : ?>
              <div class="card--description">
                <?php echo wp_kses_post(wpautop($short_desc)); ?><a class="to-description" href="#description"><span>Подробнее</span></a>
              </div>
              <?php endif; ?>
              <div class="card--info">
                <div class="card--price">
                  <?php if ($price !== '' && $price !== null) : ?><div class="card--price-current"><span><?php echo esc_html(number_format((float) $price, 0, '', ' ')); ?></span><span>₽</span></div><?php endif; ?><?php if ($is_in_stock && $is_on_sale && $regular_price && $sale_price && (float) $regular_price > (float) $sale_price) : ?><span class="card--price-old"><span><?php echo esc_html(number_format((float) $regular_price, 0, '', ' ')); ?></span><span>₽</span></span><?php endif; ?>
                </div>

<?php if ($is_in_stock) : ?>
<?$stock_schema = 'instock';?>
<?endif?>


                <div class="card--stock"><?php if ($is_in_stock) : ?><span class="card--stock-label">В наличии:</span><span class="card--stock-value"><?php echo $stock_qty !== null ? esc_html($stock_qty . ' шт') : 'на складе'; ?></span><?php else : ?><span class="card--stock-label">Нет в наличии</span><?php endif; ?></div>
              </div>
              <div class="card--actions">
                <?php if ($is_in_stock) : ?>
                <a class="button button-filled button-accent azs-buy-now" href="<?php echo esc_url($buy_now_url); ?>"><span>Купить в один клик</span><span class="icon-arrow-right"></span></a>
                <?php if ($in_cart) : ?>
                <button class="button button-filled button-secondary" type="button" data-cart data-product-id="<?php echo esc_attr($product_id); ?>"><span>В корзине</span><span class="icon-check"></span></button>
                <?php else : ?>
                <button class="button button-outlined azs-add-to-cart" type="button" data-product-id="<?php echo esc_attr($product_id); ?>" data-product-name="<?php echo esc_attr($title); ?>" data-product-image="<?php echo esc_url($featured_url); ?>" data-product-sku="<?php echo esc_attr($sku); ?>"><span>Добавить в корзину</span><span class="icon-arrow-right"></span></button>
                <?php endif; ?>


                <!--a class="link" href="<?php echo esc_url(azs_get_payment_delivery_url()); ?>"><span>Оплата и доставка</span></a-->






<a class="link js-popup-open" href="#azs-popup-delivery" data-popup="azs-popup-delivery"
   aria-haspopup="dialog"><span>Оплата и доставка</span></a>


<div class="accordion" >
                    <div class="accordion--button">
                        <span class="icon-plus"></span>
                        <p class="accordion--button_text" >Способы доставки</p>
                    </div>
                    <div class="accordion--content" >
<ul>
<li>Самовывоз (МСК)</li>
<li>Доставка по Москве службой доставки ООО «ТД «Все для АЗС» (в течение 5 рабочих дней с момента 100% оплаты счёта)</li>
<li>Транспортные компании (Деловые Линии, ПЭК, СДЭК и др.), стоимость рассчитывается индивидуально</li>
</ul>

            
                    </div>
                </div>



<div class="accordion" >
                    <div class="accordion--button">
                        <span class="icon-plus"></span>
                        <p class="accordion--button_text" >Способы оплаты</p>
                    </div>
                    <div class="accordion--content" >
<ul>
<li>Безналичный расчёт по счёту (для юр. лиц и ИП)</li>
<li>Онлайн-оплата через Robokassa (для физ. лиц)</li>
<li>Покупка через Ozon (для физ. лиц)</li>

</ul>

            
                    </div>
                </div>








                <?php else : ?>
                <div class="button button-filled button-accent" data-openpopup="popup_form"><span>Сообщить о поступлении</span><span class="icon-arrow-right"></span></div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php
          $has_description = trim(strip_tags($product->get_description() ?: get_the_content()));
          $has_files       = $passport_file || $catalog_file;
          if ($has_description || $has_files) :
          ?>
          <div class="description info" id="description">
            <h3>Описание</h3>
            <?php if ($has_description) : ?>
            <div class="text">
              <?php echo $description; ?>
              <?$description_schema = strip_tags(str_replace(["\r\n", "\r", "\n", "\t"], '', $description));?>
            </div>
            <?php endif; ?>
            <?php if ($has_files) : ?>
            <div class="block--actions"><?php if ($passport_file) : ?><a class="button button-outlined" href="<?php echo esc_url($passport_file['url']); ?>" target="_blank" rel="noopener"><span>Скачать паспорт продукта</span><span class="icon-arrow-right"></span></a><?php endif; ?><?php if ($catalog_file) : ?><a class="button button-outlined" href="<?php echo esc_url($catalog_file['url']); ?>" target="_blank" rel="noopener"><span>Скачать каталог производителя</span><span class="icon-arrow-right"></span></a><?php endif; ?></div>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          <?php
          $char_rows = array();

          $brand_tax = azs_get_brand_taxonomy();
          if ($brand_tax) {
              $brand_terms = wp_get_post_terms($product_id, $brand_tax, array('fields' => 'names'));
              if (!is_wp_error($brand_terms) && !empty($brand_terms)) {
                  $char_rows[] = array('Производитель', implode(', ', $brand_terms));
              }
          }

          if (taxonomy_exists('product_liquid_type')) {
              $liquid_terms = wp_get_post_terms($product_id, 'product_liquid_type', array('fields' => 'names'));
              if (!is_wp_error($liquid_terms) && !empty($liquid_terms)) {
                  $char_rows[] = array('Виды жидкости', implode(', ', $liquid_terms));
              }
          }

          foreach ($attributes as $attribute) {
              if (!$attribute->get_visible()) continue;
              $attr_name = $attribute->get_name();
              if ($attr_name === $brand_tax || $attr_name === 'product_liquid_type') continue;
              $label = wc_attribute_label($attr_name);
              if ($attribute->is_taxonomy()) {
                  $attr_terms = wp_get_post_terms($product_id, $attr_name, array('fields' => 'names'));
                  $val = is_wp_error($attr_terms) ? '' : implode(', ', $attr_terms);
              } else {
                  $val = implode(', ', $attribute->get_options());
              }
              if ($val) {
                  $char_rows[] = array($label, $val);
              }
          }

          if (!empty($char_rows)) :
          ?>
          <div class="characteristics info">
            <h3>Характеристики</h3>
            <table>
              <?php foreach ($char_rows as $row) : ?>
              <tr>
                <td><?php echo esc_html($row[0]); ?></td>
                <td><?php echo esc_html($row[1]); ?></td>
              </tr>
		<?if(esc_html($row[0]) == 'Производитель') {
			$brand_schema = esc_html($row[1]);
		}?>
              <?php endforeach; ?>
            </table>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <?php
      if (function_exists('azs_get_product_recommended_ids') && function_exists('azs_render_recommended_products_block')) {
          echo azs_render_recommended_products_block(azs_get_product_recommended_ids($product_id, 4));
      }
      ?>

<?php endwhile; ?>






<div class="catalog">
<!-- тут вывести карточки товаров -->
<?php
$rec_limit           = 4;                                        // сколько карточек вывести
$rec_title           = 'Рекомендуемые товары';                   // заголовок блока ('' = без заголовка)
$rec_orderby         = array('menu_order' => 'ASC', 'title' => 'ASC'); // порядок как «По умолчанию» в каталоге
$rec_in_stock_only   = false;                                    // true = только товары в наличии
$rec_hide_current    = true;                                     // исключить сам текущий товар
$rec_fallback_parent = false;                                    // true = если в категории меньше 3 товаров, добрать из родительской
$rec_use_theme_card  = true;                                     // true = карточка функцией темы azs_render_product_card()
                                                                 // false = простая статичная карточка (WC API)

$rec_product_id = isset($product_id) ? (int) $product_id : (int) get_queried_object_id();

/* Категория текущего товара — та же, что и в хлебных крошках */
$rec_term = (isset($primary_term) && $primary_term instanceof WP_Term) ? $primary_term : null;

if (!$rec_term && $rec_product_id) {
    $rec_terms = wp_get_post_terms($rec_product_id, 'product_cat');
    if (!is_wp_error($rec_terms) && !empty($rec_terms)) {
        $rec_term = $rec_terms[0];
    }
}

if ($rec_product_id && $rec_term instanceof WP_Term) :

    $rec_args = array(
        'status'     => 'publish',
        'limit'      => (int) $rec_limit,
        'visibility' => 'catalog',  
        'orderby'    => $rec_orderby,
        'return'     => 'ids',
    );

    if ($rec_in_stock_only) {
        $rec_args['stock_status'] = 'instock';
    }
    if ($rec_hide_current) {
        $rec_args['exclude'] = array($rec_product_id);
    }
    $rec_ids = wc_get_products($rec_args + array('category' => array($rec_term->slug)));

    /* добираем из родительской категории, если товаров не хватило */
    if ($rec_fallback_parent && count($rec_ids) < $rec_limit && $rec_term->parent) {
        $rec_parent = get_term((int) $rec_term->parent, 'product_cat');

        if ($rec_parent instanceof WP_Term) {
            $rec_exclude = isset($rec_args['exclude']) ? $rec_args['exclude'] : array();

            $rec_ids = array_merge($rec_ids, wc_get_products(array_merge($rec_args, array(
                'category' => array($rec_parent->slug),
                'exclude'  => array_merge($rec_exclude, $rec_ids),
                'limit'    => $rec_limit - count($rec_ids),
            ))));
        }
    }

    if (!empty($rec_ids)) :
        ?>
        <!-- ===== Рекомендуемые товары ===== -->
        <div class="block block--recommended">
            <div class="block--wrapper">

                <?php if ($rec_title) : ?>
                    <div class="block--title showInUp">
                        <h2 class="block--title_title"><?php echo esc_html($rec_title); ?></h2>
                    </div>
                <?php endif; ?>

                <div class="block--elements">
                    <?php foreach ($rec_ids as $rec_id) : ?>

                        <?php if ($rec_use_theme_card && function_exists('azs_render_product_card')) : ?>

                            <?php
                            /* Контекст как в цикле каталога: хелперы карточки могут
                               смотреть на global $post / global $product. */
                            $GLOBALS['post'] = get_post($rec_id);
                            if (function_exists('wc_setup_product_data')) {
                                wc_setup_product_data($rec_id); // принимает ID
                            }
                            echo azs_render_product_card($rec_id);
                            ?>

                        <?php else : ?>

                            <?php
                            /* Простая статичная карточка — на случай, если функции темы нет */
                            $rec_product = wc_get_product($rec_id);

                            if (!$rec_product) {
                                continue;
                            }

                            $rec_price = $rec_product->get_price();
                            ?>
                            <a class="card" href="<?php echo esc_url($rec_product->get_permalink()); ?>">
                                <div class="card--image">
                                    <?php echo $rec_product->get_image('woocommerce_thumbnail', array('loading' => 'lazy')); ?>
                                    <div class="card--labels">
                                        <?php if (!$rec_product->is_in_stock()) : ?>
                                            <span class="card--label card--label-noStock">нет в наличии</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card--content">
                                    <div class="card--title">
                                        <h3><?php echo esc_html($rec_product->get_name()); ?></h3>
                                    </div>
                                    <?php if ($rec_price !== '' && $rec_price !== null) : ?>
                                        <div class="card--price">
                                            <div class="card--price-current">
                                                <span><?php echo esc_html(number_format((float) $rec_price, 0, '', ' ')); ?></span><span>₽</span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>

                        <?php endif; ?>

                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php
        /* Возвращаем контекст текущего товара (ниже идут JSON-LD и футер) */
        $GLOBALS['post'] = get_post($rec_product_id);
        if (function_exists('wc_setup_product_data')) {
            wc_setup_product_data($rec_product_id);
        }
        ?>

    <?php endif; ?>

<?php endif; ?>
<div>




<?// 
$current_url = get_self_link();
?>

<script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "Product",
      "@id": "<?=$current_url?>#product",
      "name": "<?php echo esc_html($title); ?>",
<?if(!empty($category_schema)):?>
      "category": "<?=$category_schema?>",
<?endif?>
<?if(!empty($img_url_schema)):?>
      "image": [
        "<?= $img_url_schema?>"
       ],
<?endif?>
<?if(!empty($description_schema)):?>
      "description": "<?=$description_schema?>",
<?endif?>
<?if(!empty($sku)):?>
      "sku": "<?php echo esc_html($sku); ?>",
<?endif?>
<?if(!empty($brand_schema)):?>
      "brand": {
        "@type": "Organization",
        "name": "<?=$brand_schema?>"
      },
<?endif?>
      "offers": {
        "@type": "Offer",
        "url": "<?=$current_url?>",
        "priceCurrency": "RUB",
<?if(!empty($price)):?>
        "price": <?=round((int)$price)?>,
<?endif?>
        "itemCondition": "https://schema.org/NewCondition",
<?if($stock_schema == 'instock'):?>
        "availability": "https://schema.org/InStock",
<?else:?>
        "availability": "https://schema.org/OutOfStock",
<?endif?>
        "seller": {
          "@type": "Organization",
          "name": "ООО «Торговый Дом «Все для АЗС»"
        }
      }
    }
</script>


<script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "Offer",
      "itemOffered": "<?=$current_url?>#product",
      "offeredBy": {
         "@id": "https://piusi-raasm.ru/#organization"
       },
      "name": "<?php echo esc_html($title); ?>",
<?if($stock_schema == 'instock'):?>
      "availability": "https://schema.org/InStock",
<?else:?>
      "availability": "https://schema.org/OutOfStock",
<?endif?>
      "priceCurrency": "RUB",
<?if(!empty($price)):?>
      "price": <?=round((int)$price)?>
<?endif?>
    }
</script>




<style>
@media (min-width: 641px) {.accordion {padding: 0;}}
@media (min-width: 641px) {.accordion--button .accordion--button_text {font-size: 16px;}}
@media (min-width: 641px) {.accordion--content {font-size: 14px;}}
</style>



<div class="azs-popup" id="azs-popup-delivery" aria-hidden="true">

    <!-- затемнение; href="#" закрывает окно даже без JS -->
    <a class="azs-popup__overlay" href="#" data-popup-close aria-label="Закрыть окно" tabindex="-1"></a>

    <div class="azs-popup__dialog" role="dialog" aria-modal="true"
         aria-label="Оплата и доставка" tabindex="-1">

        <!-- закреплённая шапка: не уезжает при прокрутке -->
        <div class="azs-popup__head">
            <div class="azs-popup__title">Оплата и доставка</div>
            <a class="azs-popup__close" href="#" data-popup-close aria-label="Закрыть окно">&times;</a>
        </div>

        <!-- прокручиваемое тело окна -->
        <div class="azs-popup__content">

            <p><strong>Юридические лица</strong></p>

            <p>«Торговый дом «Все для АЗС» работает с юридическими лицами и ИП. Мы оперативно формируем и отправляем вам счёт на указанный email. Для выставления счёта потребуются реквизиты вашей организации. Покупки необходимо оплатить на основании выставленного счета. Счет, выставляемый менеджером, действителен в течение 3 (трех) рабочих дней. Доставка товара планируется с того момента, как денежные средства поступят на счет компании.</p>

            <p><strong>Физические лица</strong></p>

            <p>Физические лица могут приобрести ассортимент нашей продукции на <a href="https://www.ozon.ru/seller/vse-dlya-azs-3010944/" target="_blank" rel="noopener">Ozon</a>, а также заказать товар непосредственно на сайте, оплачивая товары через сервис онлайн-платежей Robokassa (<a href="https://piusi-raasm.ru/publichnaya-oferta/" target="_blank" rel="noopener">публичная оферта</a>).</p>

            <p><strong>Для оформления заказа на сайте:</strong></p>
            <ol>
                <li>Добавьте интересующие Вас Товары в Корзину;</li>
                <li>Перейдите на страницу Корзины и оформите Заказ, указав ФИО, контактные данные и комментарии к заказу;</li>
                <li>Наш менеджер свяжется с Вами для уточнения данных и направления ссылки на оплату Заказа;</li>
                <li>Оплатите Заказ удобным для Вас способом;</li>
                <li>Ожидайте доставку Товаров или приезжайте за самовывозом.</li>
            </ol>

            <div class="logo-wrapper">
                <img src="https://piusi-raasm.ru/wp-content/uploads/2026/08/mir-1-1.svg" alt="Мир" width="88" height="88" />
                <img src="https://piusi-raasm.ru/wp-content/uploads/2026/08/yandexsplit-1-1.svg" alt="Yandex Split" width="88" height="88" />
                <img src="https://piusi-raasm.ru/wp-content/uploads/2026/08/mastercard-1.svg" alt="Mastercard" width="88" height="88" />
                <img src="https://piusi-raasm.ru/wp-content/uploads/2026/08/sbp-1.svg" alt="СБП" width="88" height="88" />
                <img src="https://piusi-raasm.ru/wp-content/uploads/2026/08/visa-1.svg" alt="Visa" width="88" height="88" />
                <img src="https://piusi-raasm.ru/wp-content/uploads/2026/08/tpay-1.svg" alt="T-Pay" width="88" height="88" />
                <img src="https://piusi-raasm.ru/wp-content/uploads/2026/08/yandexpay-1.svg" alt="Yandex Pay" width="88" height="88" />
                <img src="https://piusi-raasm.ru/wp-content/uploads/2026/08/logo_robokassa-1.svg" alt="Robokassa" width="174" height="88" />
            </div>

            <p><strong>Доставка</strong></p>

            <p>Осуществляем доставку по всей России транспортными компаниями (Деловые Линии, ПЭК, СДЭК и др.). Отгрузка заказанных вами товаров со склада компании будет осуществлена в кратчайшие сроки — от 2 рабочих дней в зависимости от наличия товара на складе и региона доставки. Возможен самовывоз со склада в Москве. Стоимость доставки рассчитывается индивидуально. Оформляйте доставку удобным для вас способом.</p>

            <p>Срок доставки рассчитывается индивидуально. Отсчет времени доставки начинается с момента оформления заказа.</p>

            <p>За более подробной информацией обращайтесь к менеджерам компании по телефону <a href="tel:+74957893464">+7 (495) 789-34-64</a> или на почту <a href="mailto:info@pse.ru">info@pse.ru</a></p>

            <p><strong>Доставка по Москве</strong></p>

            <p>В г. Москва работает служба доставки ООО «Торговый Дом «Все для АЗС». Доставка осуществляется в течение 5 рабочих дней с момента 100% оплаты счета.</p>

            <p>За дополнительной информацией обращайтесь по телефону <a href="tel:+74957893464">+7 (495) 789-34-64</a> или на почту <a href="mailto:andrey.borisov@pse.ru">andrey.borisov@pse.ru</a></p>

        </div>
    </div>
</div>



<script>
(function () {
    'use strict';

    function onClick(e) {
        var target = e.target;
        if (!target || !target.closest) return;

        // ----- открытие -----
        var trigger = target.closest('[data-popup]');
        if (trigger) {
            var id = trigger.getAttribute('data-popup');
            var popup = document.getElementById(id);
            if (!popup) return;

            e.preventDefault();
            e.stopPropagation();

            open(popup);
            return;
        }

        // ----- закрытие (крестик или затемнение) -----
        if (target.closest('[data-popup-close]')) {
            e.preventDefault();
            e.stopPropagation();
            close();
        }
    }

    function open(popup) {
        close();

        popup.classList.add('is-open');
        popup.setAttribute('aria-hidden', 'false');
        document.body.classList.add('azs-popup-opened');

        // прокрутку окна — в начало
        var content = popup.querySelector('.azs-popup__content');
        if (content) content.scrollTop = 0;

        var dialog = popup.querySelector('.azs-popup__dialog');
        if (dialog) dialog.focus();
    }

    function close() {
        var opened = document.querySelectorAll('.azs-popup.is-open');

        for (var i = 0; i < opened.length; i++) {
            opened[i].classList.remove('is-open');
            opened[i].setAttribute('aria-hidden', 'true');
        }

        document.body.classList.remove('azs-popup-opened');

        // если окно открылось по хэшу (#azs-popup-…), убираем хэш,
        // иначе CSS-правило :target держало бы окно открытым
        if (/^#azs-popup-/.test(location.hash)) {
            if (window.history && history.replaceState) {
                history.replaceState(null, '', location.pathname + location.search);
            }
        }
    }

    document.addEventListener('click', onClick, true);

    document.addEventListener('keydown', function (e) {
        // Esc — закрыть; пробел/стрелки не трогаем, чтобы окно скроллилось клавиатурой
        if (e.key === 'Escape' || e.keyCode === 27) close();
    });

    // API для проверки из консоли браузера:
    //   azsPopupOpen('azs-popup-delivery')  — открыть
    //   azsPopupClose()                     — закрыть
    window.azsPopupOpen = function (id) {
        var popup = document.getElementById(id);
        if (popup) open(popup);
        return !!popup;
    };
    window.azsPopupClose = close;
})();
</script>


<?php get_footer(); ?>
