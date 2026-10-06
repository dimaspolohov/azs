<?php

/**
 * WooCommerce Product Archive — fully replaces default template.
 * Matches frontend/catalog.html layout.
 */

if (!defined('ABSPATH')) exit;

get_header();

$is_cat       = is_product_taxonomy();
$current_term = $is_cat ? get_queried_object() : null;
$shop_url     = get_permalink(wc_get_page_id('shop'));
$base_cat_id  = $current_term ? (int) $current_term->term_id : 0;

// URL filter state
$url_cats    = array_filter(explode(',', sanitize_text_field($_GET['azs_cat'] ?? '')));
$url_liquid  = array_filter(explode(',', sanitize_text_field($_GET['azs_liquid'] ?? '')));
$url_mfr     = array_filter(explode(',', sanitize_text_field($_GET['azs_mfr'] ?? '')));
$url_sort    = sanitize_text_field($_GET['azs_sort'] ?? 'default');
$url_search  = sanitize_text_field($_GET['azs_search'] ?? '');
$is_new_f    = !empty($_GET['azs_new']);
$is_sale_f   = !empty($_GET['azs_sale']);
$paged       = max(1, get_query_var('paged', 1));
if ($paged <= 1 && isset($_GET['paged'])) {
    $paged = max(1, intval($_GET['paged']));
}

$products_query = new WP_Query(azs_get_catalog_query_args(array(
    'categories'    => $url_cats,
    'liquid_types'  => $url_liquid,
    'manufacturers' => $url_mfr,
    'search'        => $url_search,
    'sort'          => $url_sort,
    'page'          => $paged,
    'is_new'        => $is_new_f,
    'is_sale'       => $is_sale_f,
    'base_cat_id'   => $base_cat_id,
)));

// Sidebar & filter data
$uncategorized_term = get_term_by('slug', 'uncategorized', 'product_cat');
$exclude_cat_ids = $uncategorized_term ? array($uncategorized_term->term_id) : array();
$top_cats = get_terms(array(
    'taxonomy'   => 'product_cat',
    'parent'     => 0,
    'hide_empty' => false,
    'exclude'    => $exclude_cat_ids,
    'orderby'    => 'menu_order',
    'order'      => 'ASC',
));
if (is_wp_error($top_cats)) $top_cats = array();

$liquid_terms = taxonomy_exists('product_liquid_type') ? get_terms(array('taxonomy' => 'product_liquid_type', 'hide_empty' => false, 'childless' => true)) : array();
$brand_tax    = azs_get_brand_taxonomy();
$mfr_terms    = $brand_tax ? get_terms(array('taxonomy' => $brand_tax, 'hide_empty' => false)) : array();
if (is_wp_error($liquid_terms)) $liquid_terms = array();
if (is_wp_error($mfr_terms))    $mfr_terms    = array();

// SEO text
$seo_content = '';
if ($current_term && !empty($current_term->description)) {
    $seo_content = wpautop($current_term->description);
} elseif (is_shop()) {
    $shop_page = get_post(wc_get_page_id('shop'));
    if ($shop_page && !empty($shop_page->post_content)) {
        $seo_content = apply_filters('the_content', $shop_page->post_content);
    }
}
?>

<!-- Breadcrumbs -->
<div class="block--wrapper">
    <div class="breadcrumbs" itemscope itemtype="https://schema.org/BreadcrumbList">
        <a href="<?php echo esc_url(home_url('/')); ?>" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <meta itemprop="position" content="1" />
            <span itemprop="name">Главная</span><span class="separator">></span>
        </a>
        <?php if ($current_term) :
            $ancestors = array_reverse(get_ancestors($current_term->term_id, 'product_cat'));
            $pos = 2;
        ?>
            <a href="<?php echo esc_url($shop_url); ?>" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <meta itemprop="position" content="<?php echo $pos++; ?>" />
                <span itemprop="name">Каталог</span><span class="separator">></span>
            </a>
            <?php foreach ($ancestors as $anc_id) :
                $anc = get_term($anc_id, 'product_cat');
                if (!$anc || is_wp_error($anc)) continue;
            ?>
                <a href="<?php echo esc_url(get_term_link($anc)); ?>" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <meta itemprop="position" content="<?php echo $pos++; ?>" />
                    <span itemprop="name"><?php echo esc_html($anc->name); ?></span><span class="separator">></span>
                </a>
            <?php endforeach; ?>
            <a itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <meta itemprop="position" content="<?php echo $pos; ?>" />
                <span itemprop="name"><?php echo esc_html($current_term->name); ?></span>
            </a>
        <?php else : ?>
            <a itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <meta itemprop="position" content="2" />
                <span itemprop="name">Каталог</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<?php
$catalog_h1 = '';
if ($current_term && !empty($current_term->name)) {
    $catalog_h1 = $current_term->name;
} elseif (is_shop()) {
    $catalog_h1 = get_the_title(wc_get_page_id('shop'));
}
?>
<?php if ($catalog_h1) : ?>
    <div class="block--wrapper">
        <div class="block--title showInUp">
            <h1 class="block--title_title"><?php echo esc_html($catalog_h1); ?></h1>
        </div>
    </div>
<?php endif; ?>

<!-- Catalog -->
<div class="block catalog" id="azs-catalog" data-base-cat="<?php echo esc_attr($base_cat_id); ?>" data-seo="<?php echo $seo_content !== '' ? '1' : '0'; ?>">
    <div class="block--wrapper">

        <!-- ===== Sidebar ===== -->
        <div class="side">
            <div class="category -desk">
                <div class="category--wrapper">
                    <a class="item"
                        href="<?php echo esc_url($shop_url); ?>">
                        <div class="button"><span>СМОТРЕТЬ ВСЕ</span></div>
                    </a>
                    <a class="item<?php echo $is_new_f ? ' is-active' : ''; ?>"
                        href="<?php echo esc_url($shop_url); ?>" data-sidebar-new>
                        <div class="button"><span>Новинки</span></div>
                    </a>
                    <a class="item<?php echo $is_sale_f ? ' is-active' : ''; ?>"
                        href="<?php echo esc_url($shop_url); ?>" data-sidebar-sale>
                        <div class="button"><span>Акции</span></div>
                    </a>

                    <?php foreach ($top_cats as $cat) :
                        $children = get_terms(array(
                            'taxonomy'   => 'product_cat',
                            'parent'     => $cat->term_id,
                            'hide_empty' => false,
                            'orderby'    => 'menu_order',
                            'order'      => 'ASC',
                        ));
                        if (is_wp_error($children)) $children = array();

                        $cat_active = $current_term && ($current_term->term_id === $cat->term_id || term_is_ancestor_of($cat->term_id, $current_term->term_id, 'product_cat'));

                        $category_acf_id = 'product_cat_'.$cat->term_id;
                        $category_short_name = get_field('category_short_name', $category_acf_id);



                        if (empty($children)) : ?>
                            <a class="item<?php echo $cat_active ? ' is-active' : ''; ?>"
                                href="<?php echo esc_url(get_term_link($cat)); ?>">

                                <div class="button">
                                    <span>
                                    <?if(!empty($category_short_name)):?>
                                        <?php echo esc_html($category_short_name); ?>
                                    <?else:?>
                                        <?php echo esc_html($cat->name); ?>
                                    <?endif?>
                                    </span>
                                </div>
                            </a>
                        <?php else : ?>
                            <div class="item item-dropdown<?php echo $cat_active ? ' is_open' : ''; ?>">
                                <div class="button">
                                    <span>
                                    <?if(!empty($category_short_name)):?>
                                        <?php echo esc_html($category_short_name); ?>
                                    <?else:?>
                                        <?php echo esc_html($cat->name); ?>
                                    <?endif?>
                                    </span>
                                    <span class="icon-drop-down"></span>
                                </div>
                                <div class="dropdown">
                                    <div class="dropdown__wrapper">
                                        <a class="dropdown__item link<?php echo ($current_term && (int) $current_term->term_id === (int) $cat->term_id) ? ' is-active' : ''; ?>"
                                            href="<?php echo esc_url(get_term_link($cat)); ?>">
                                            Все 
                                            <?if(!empty($category_short_name)):?>
                                                <?php echo esc_html(mb_strtolower($category_short_name)); ?>
                                            <?else:?>
                                                <?php echo esc_html(mb_strtolower($cat->name)); ?>
                                            <?endif?>
                                        </a>
                                        <?php foreach ($children as $child) :
                                            $child_active = $current_term && (int) $current_term->term_id === (int) $child->term_id;
                                        ?>

                                            <?//
                                            $category_acf_id = 'product_cat_'.$child->term_id;
                                            $category_short_name = get_field('category_short_name', $category_acf_id);
                                            ?>

                                            <a class="dropdown__item link<?php echo $child_active ? ' is-active' : ''; ?>"
                                                href="<?php echo esc_url(get_term_link($child)); ?>">

                                                <?if(!empty($category_short_name)):?>
                                                    <?php echo esc_html($category_short_name); ?>
                                                <?else:?>
                                                    <?php echo esc_html($child->name); ?>
                                                <?endif?>

                                                
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                    <?php endif;
                    endforeach; ?>
                </div>
            </div>
        </div>





<?php
/* ===== ПЛИТКА ВЛОЖЕННЫХ КАТЕГОРИЙ ===== */
$tile_show_count  = true;                     // показывать количество товаров
$tile_hide_empty  = true;                     // скрывать категории без товаров
$tile_count_deep  = true;                     // считать товары и во вложенных подкатегориях
$tile_placeholder = true;                     // заглушка WC, если у категории нет картинки
$tile_img_size    = 'woocommerce_thumbnail';  // размер картинки
$tile_columns     = 4;                        // колонок на десктопе

$tiles = array();

if ($current_term) {   // на главной каталога $current_term пустой → блок ничего не выведет
    $tile_terms = get_terms(array(
        'taxonomy'   => 'product_cat',
        'parent'     => (int) $current_term->term_id, // прямые дочерние; все уровни — 'child_of'
        'hide_empty' => false,
        'exclude'    => $exclude_cat_ids,
        'orderby'    => 'menu_order',                 // тот же порядок, что в сайдбаре
        'order'      => 'ASC',
    ));

    if (!is_wp_error($tile_terms)) {
        foreach ($tile_terms as $tile_term) {
            $tile_count = (int) $tile_term->count;    // свои товары

            if ($tile_count_deep) {                   // + товары во вложенных
                $tile_deep = get_terms(array(
                    'taxonomy' => 'product_cat', 'child_of' => (int) $tile_term->term_id,
                    'hide_empty' => true, 'fields' => 'all',
                ));
                if (!is_wp_error($tile_deep)) {
                    foreach ($tile_deep as $t) { $tile_count += (int) $t->count; }
                }
            }

            if ($tile_hide_empty && $tile_count < 1) continue;

            $tile_link = get_term_link($tile_term);
            if (is_wp_error($tile_link)) continue;

            $tile_short_name = function_exists('get_field')
                ? get_field('category_short_name', 'product_cat_' . $tile_term->term_id) : '';

            $tile_thumb_id = (int) get_term_meta($tile_term->term_id, 'thumbnail_id', true);
            $tile_img = $tile_thumb_id
                ? wp_get_attachment_image_url($tile_thumb_id, $tile_img_size)
                : ($tile_placeholder ? wc_placeholder_img_src($tile_img_size) : '');

            $tiles[] = array(
                'url'   => $tile_link,
                'title' => $tile_short_name ? $tile_short_name : $tile_term->name,
                'img'   => $tile_img ? $tile_img : '',
                'count' => $tile_count,
            );
        }
    }
}
?>

<?php if (!empty($tiles)) : ?>
    <!-- ===== Плитка вложенных категорий ===== -->
    <div class="block--subcats">
        <div class="subcats<?php echo $tile_show_count ? ' is-with-count' : ''; ?>"
             style="--subcats-columns:<?php echo (int) $tile_columns; ?>">
            <?php foreach ($tiles as $tile) : ?>
                <a class="subcat" href="<?php echo esc_url($tile['url']); ?>">
                    <span class="subcat__body">
                        <span class="subcat__title"><?php echo esc_html($tile['title']); ?></span>
                        <?php if ($tile_show_count) : ?><span class="subcat__count"><?php echo esc_html($tile['count']); ?></span><?php endif; ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>









        <!-- ===== Filters ===== -->
        <div class="block--filters">

            <!-- Search -->
            <div class="block--search">
                <div class="search">
                    <div class="search__wrapper">
                        <input type="text" placeholder="Артикул или название товара"
                            value="<?php echo esc_attr($url_search); ?>" />
                    </div>
                    <div class="button button-filled button-secondary"><span class="icon-search"></span></div>
                </div>
            </div>


            <!-- Filter bar + Sort -->
            <div class="block--actions">
                <div class="filter">
                    <div class="filter__wrapper">
                        <div class="filter__list">

                            <!-- Filter: Каталог (categories) -->
                            <div class="filter__item">
                                <div class="filter__button filter__button_trigger" role="button" aria-expanded="false">
                                    <span>Каталог</span><span class="filter__button_icon icon-drop-down"></span>
                                </div>
                                <div class="filter__content" aria-hidden="true">
                                    <?php foreach ($top_cats as $cat) :
                                        $children = get_terms(array(
                                            'taxonomy'   => 'product_cat',
                                            'parent'     => $cat->term_id,
                                            'hide_empty' => false,
                                            'orderby'    => 'menu_order',
                                            'order'      => 'ASC',
                                        ));
                                        if (is_wp_error($children)) $children = array();

                                        if (!empty($children)) : ?>
                                            <div class="filter__group">
                                                <div class="filter__group_title"><?php echo esc_html($cat->name); ?></div>
                                                <div class="filter__group_list">
                                                    <?php foreach ($children as $child) : ?>
                                                        <div class="filter__content_item filter__content_item--option"
                                                            data-slug="<?php echo esc_attr($child->term_id); ?>"
                                                            data-filter-type="category">
                                                            <?php echo esc_html($child->name); ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php else : ?>
                                            <div class="filter__content_item filter__content_item--option"
                                                data-slug="<?php echo esc_attr($cat->term_id); ?>"
                                                data-filter-type="category">
                                                <?php echo esc_html($cat->name); ?>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                            </div>

                            <?php if (!empty($liquid_terms) && !is_wp_error($liquid_terms)) : ?>
                                <!-- Filter: Вид жидкости -->
                                <div class="filter__item">
                                    <div class="filter__button filter__button_trigger" role="button" aria-expanded="false">
                                        <span>Вид жидкости</span><span class="filter__button_icon icon-drop-down"></span>
                                    </div>
                                    <div class="filter__content" aria-hidden="true">
                                        <?php foreach ($liquid_terms as $term) : ?>
                                            <div class="filter__content_item filter__content_item--option"
                                                data-slug="<?php echo esc_attr($term->term_id); ?>"
                                                data-filter-type="liquid_type">
                                                <?php echo esc_html($term->name); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($mfr_terms) && !is_wp_error($mfr_terms)) : ?>
                                <!-- Filter: Производитель -->
                                <div class="filter__item">
                                    <div class="filter__button filter__button_trigger" role="button" aria-expanded="false">
                                        <span>Производитель</span><span class="filter__button_icon icon-drop-down"></span>
                                    </div>
                                    <div class="filter__content" aria-hidden="true">
                                        <?php foreach ($mfr_terms as $term) : ?>
                                            <div class="filter__content_item filter__content_item--option"
                                                data-slug="<?php echo esc_attr($term->term_id); ?>"
                                                data-filter-type="manufacturer">
                                                <?php echo esc_html($term->name); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                        <div class="filter__action">
                            <div class="button button-filled button-secondary">
                                <span>Применить фильтр</span><span class="icon-arrow-right"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sort -->
                <form class="sort" onsubmit="return false">
                    <div class="sort--wrapper">
                        <div class="item">
                            <label>
                                <input type="radio" name="sort" value="default" <?php checked($url_sort, 'default'); ?>>
                                <div class="radio"></div>
                            </label><span>По умолчанию</span>
                        </div>
                        <div class="item">
                            <label>
                                <input type="radio" name="sort" value="new" <?php checked($url_sort, 'new'); ?>>
                                <div class="radio"></div>
                            </label><span>По новизне</span>
                        </div>
                        <div class="item">
                            <label>
                                <input type="radio" name="sort" value="price-asc" <?php checked($url_sort, 'price-asc'); ?>>
                                <div class="radio"></div>
                            </label><span>По возрастанию цены</span>
                        </div>
                        <div class="item">
                            <label>
                                <input type="radio" name="sort" value="price-desc" <?php checked($url_sort, 'price-desc'); ?>>
                                <div class="radio"></div>
                            </label><span>По убыванию цены</span>
                        </div>
                    </div>
                </form>

                <!-- Mobile buttons -->
                <div class="button button-filled button-filter btn-filter"><span>Фильтры</span><span class="icon-filtr"></span></div>
                <div class="button button-filled button-filter btn-sort"><span>Сортировать</span><span class="icon-drop-down"></span></div>
            </div>
        </div>




        <!-- ===== Active filter tags ===== -->
        <div class="block--tags" id="azs-tags"></div>





        <!-- ===== Product grid ===== -->
        <div class="block--elements" id="azs-products">
            <?php if ($products_query->have_posts()) :
                while ($products_query->have_posts()) : $products_query->the_post();
                    echo azs_render_product_card(get_the_ID());
                endwhile;
                wp_reset_postdata();
            else : ?>
                <div class="catalog-empty">
                    <p>Товары не найдены</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- ===== Pagination ===== -->
        <?php echo azs_render_catalog_pagination($products_query, $paged); ?>

    </div>
</div>

<?php if ($seo_content && $paged <= 1) : ?>
    <div class="block html-code">
        <div class="block--wrapper">
            <div class="block--content is_cascade">
                <?php echo wp_kses_post($seo_content); ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
if ($current_term && function_exists('get_field')) {
    $cat_acf_id = 'product_cat_' . (int) $current_term->term_id;
    if (get_field('category_show_form', $cat_acf_id)) {
        $form_title    = get_field('category_form_title', $cat_acf_id);
        $form_subtitle = get_field('category_form_subtitle', $cat_acf_id);
        get_template_part('template-parts/consultation', null, array(
            'title'    => $form_title ?: null,
            'subtitle' => $form_subtitle ?: null,
        ));
    }
}
?>

<?// 
$current_url = get_self_link();
$page_title = wp_get_document_title();

$term_id = $base_cat_id;
$yoast_tax_meta = get_option( 'wpseo_taxonomy_meta' );
$meta_desc = '';

if (!empty($yoast_tax_meta['product_cat'][$term_id]['wpseo_desc'])) {
	$meta_desc = $yoast_tax_meta['product_cat'][$term_id]['wpseo_desc'];
}
?>


<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebPage",
  "@id": "<?=$current_url?>#webpage",
  "name": "<?=$page_title?>",
  "description": "<?=$meta_desc?>",
  "url": "<?=$current_url?>",
  "about": {
    "@id": "<?=$current_url?>"
  },
  "breadcrumb": {
    "@id": "<?=$current_url?>#breadcrumb"
  },
  "inLanguage": "ru-RU",
  "isPartOf": {
    "@id": "https://piusi-raasm.ru/#website"
  }
}
</script>

<?php get_footer(); ?>