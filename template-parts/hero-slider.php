<?php
$slides   = function_exists('get_field') ? get_field('slider_items') : null;
$shop_url = function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : '/';
?>
<div class="block block_banner banner-hero banner-group">
    <div class="banner-hero_search">
        <div class="banner-hero_search__wrapper">
            <a class="button button-filled button-secondary -desk" href="<?php echo esc_url($shop_url); ?>">
                <span>КАТАЛОГ</span><span class="icon-arrow-right"></span>
            </a>
            <div class="search" id="hero-search"
                 data-search-url="<?php echo esc_url($shop_url); ?>">
                <div class="search__wrapper">
                    <input type="text" id="hero-search-input" placeholder="Артикул или название товара" value="<?php echo esc_attr(get_search_query()); ?>"/>
                    <div class="button btn-filter">
                        <div class="icon-filtr"></div>
                    </div>
                    <div class="filter">
                        <div class="filter__wrapper">
                            <div class="filter__title">Подбор по фильтру</div>
                            <div class="filter__list">
                                <?php if (taxonomy_exists('product_cat')) : ?>
                                <div class="filter__item">
                                    <div class="filter__button filter__button_trigger" role="button" aria-expanded="false">
                                        <span>Каталог</span>
                                        <span class="filter__button_icon icon-drop-down"></span>
                                    </div>
                                    <div class="filter__content" aria-hidden="true">
                                        <?php
                                        $uncat = get_term_by('slug', 'uncategorized', 'product_cat');
                                        $parent_cats = get_terms(array(
                                            'taxonomy'   => 'product_cat',
                                            'parent'     => 0,
                                            'hide_empty' => false,
                                            'exclude'    => $uncat ? array($uncat->term_id) : array(),
                                            'orderby'    => 'menu_order',
                                            'order'      => 'ASC',
                                        ));
                                        if (!is_wp_error($parent_cats)) :
                                            foreach ($parent_cats as $pcat) :
                                                $children = get_terms(array(
                                                    'taxonomy'   => 'product_cat',
                                                    'parent'     => $pcat->term_id,
                                                    'hide_empty' => false,
                                                    'orderby'    => 'menu_order',
                                                    'order'      => 'ASC',
                                                ));
                                                if (!empty($children) && !is_wp_error($children)) : ?>
                                                    <div class="filter__group">
                                                        <div class="filter__group_title"><?php echo esc_html($pcat->name); ?></div>
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
                                                         data-slug="<?php echo esc_attr($pcat->term_id); ?>"
                                                         data-filter-type="category">
                                                        <?php echo esc_html($pcat->name); ?>
                                                    </div>
                                                <?php endif;
                                            endforeach;
                                        endif;
                                        ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php
                                if (taxonomy_exists('product_liquid_type')) :
                                    $liquid_terms = get_terms(array('taxonomy' => 'product_liquid_type', 'hide_empty' => false));
                                    if (!is_wp_error($liquid_terms) && !empty($liquid_terms)) : ?>
                                    <div class="filter__item">
                                        <div class="filter__button filter__button_trigger" role="button" aria-expanded="false">
                                            <span>Вид жидкости</span>
                                            <span class="filter__button_icon icon-drop-down"></span>
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
                                <?php endif;
                                endif;

                                $brand_tax = function_exists('azs_get_brand_taxonomy') ? azs_get_brand_taxonomy() : '';
                                if ($brand_tax && taxonomy_exists($brand_tax)) :
                                    $mfr_terms = get_terms(array('taxonomy' => $brand_tax, 'hide_empty' => false));
                                    if (!is_wp_error($mfr_terms) && !empty($mfr_terms)) : ?>
                                    <div class="filter__item">
                                        <div class="filter__button filter__button_trigger" role="button" aria-expanded="false">
                                            <span>Производитель</span>
                                            <span class="filter__button_icon icon-drop-down"></span>
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
                                <?php endif;
                                endif; ?>
                            </div>
                            <div class="filter__action">
                                <div class="button button-filled button-secondary" data-hero-search-submit>
                                    <span>Показать товар</span><span class="icon-arrow-right"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="button button-filled button-secondary" data-hero-search-submit>
                    <span class="icon-search"></span>
                </div>
            </div>
        </div>
        <div class="banner-hero_search__note -desk">Поставляем продукцию с 1997 года</div>
    </div>

    <?php if ($slides) : ?>
    <div class="block--wrapper">
        <?php foreach ($slides as $slide) :
            $img     = $slide['slide_image'];
            $img_mob = !empty($slide['slide_image_mobile']) ? $slide['slide_image_mobile'] : null;
        ?>
        <div class="banner">
            <div class="block--image">
                <picture>
                    <?php if ($img_mob) : ?>
                        <source media="(max-width: 640px)" srcset="<?php echo esc_url($img_mob['url']); ?>">
                    <?php endif; ?>
                    <img src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($slide['slide_title']); ?>">
                </picture>
            </div>
            <div class="block--content">
                <div class="banner--title"><?php echo esc_html($slide['slide_title']); ?></div>
                <?php if (!empty($slide['slide_text'])) : ?>
                    <div class="banner--text"><?php echo esc_html($slide['slide_text']); ?></div>
                <?php endif; ?>
                <?php if (!empty($slide['slide_button_link'])) : ?>
                    <div class="banner--actions">
                        <a class="button button-filled button-secondary" href="<?php echo esc_url($slide['slide_button_link']); ?>">
                            <span><?php echo esc_html($slide['slide_button_text'] ?: 'Смотреть в каталоге'); ?></span>
                            <span class="icon-arrow-right"></span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
