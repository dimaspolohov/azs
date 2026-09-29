<?php
$categories = function_exists('get_field') ? get_field('catalog_categories') : null;
$shop_url   = function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : '/';

if (!$categories) return;
?>
<div class="catalog-hero block">
    <div class="block--wrapper">
        <div class="block--title showInUp">
            <div class="block--title_subtitle"><span>Каталог</span></div>
            <h2 class="block--title_title">Широкий ассортимент мировых и российских брендов</h2>
        </div>
        <div class="block--elements">
            <?php foreach ($categories as $row) :
                $term_id = $row['category'];
                $term    = get_term($term_id, 'product_cat');
                if (!$term || is_wp_error($term)) continue;

                $thumb_id  = get_term_meta($term_id, 'thumbnail_id', true);
                $thumb_url = $thumb_id ? wp_get_attachment_url($thumb_id) : '';
                $term_link = get_term_link($term);

                $category_acf_id = 'product_cat_'.$term_id;
                $category_short_name = get_field('category_short_name', $category_acf_id);

                $children = get_terms(array(
                    'taxonomy'   => 'product_cat',
                    'parent'     => $term_id,
                    'hide_empty' => false,
                    'number'     => 4,
                ));
            ?>
            <div class="card showInUp">
                <a class="card--link" href="<?php echo esc_url($term_link); ?>"></a>
                <?php if ($thumb_url) : ?>
                <div class="card--image"><img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr($term->name); ?>"></div>
                <?php endif; ?>

                <div class="card--content">
                    <div class="card--title">
                    <?if(!empty($category_short_name)):?>
                        <?php echo $category_short_name?>
                    <?else:?>
                        <?php echo esc_html($term->name); ?>
                    <?endif?>
                </div>

                    <?php if (!empty($children) && !is_wp_error($children)) : ?>
                    <div class="card--list">
                        <?php foreach ($children as $child) : ?>
                            <a class="link" href="<?php echo esc_url(get_term_link($child)); ?>">

                                <?//
                                $child_id = $child->term_id;
                                $category_acf_id = 'product_cat_'.$child_id;
                                $category_short_name = get_field('category_short_name', $category_acf_id);
                                ?>

                                <?if(!empty($category_short_name)):?>
                                    <?php echo esc_html($category_short_name)?>
                                <?else:?>
                                    <?php echo esc_html($child->name); ?>
                                <?endif?>

                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <a class="card card-last showInUp" href="<?php echo esc_url($shop_url); ?>">
                <div class="card--content">
                    <div class="card--title"></div>
                    <div class="card--action">
                        <div class="button button-link"><span>Смотреть в каталоге</span><span class="icon-arrow-right"></span></div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
