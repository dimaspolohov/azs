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

    $terms        = wc_get_product_terms($product_id, 'product_cat', array('orderby' => 'parent', 'order' => 'DESC'));
    $primary_term = !empty($terms) ? $terms[0] : null;
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
                <a class="link" href="<?php echo esc_url(azs_get_payment_delivery_url()); ?>"><span>Оплата и доставка</span></a>
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


<?php get_footer(); ?>
