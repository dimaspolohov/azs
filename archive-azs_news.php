<?php get_header(); ?>

    <div class="block--wrapper">
        <div class="breadcrumbs" itemscope itemtype="https://schema.org/BreadcrumbList">
            <a href="<?php echo esc_url(home_url('/')); ?>" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <meta itemprop="position" content="1"/>
                <span itemprop="name">Главная</span><span class="separator">></span>
            </a>
            <a href="<?php echo esc_url(get_post_type_archive_link('azs_news')); ?>" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <meta itemprop="position" content="2"/>
                <span itemprop="name">Новости</span>
            </a>
        </div>
    </div>

    <div class="block news">
        <div class="block--wrapper">
            <div class="block--title showInUp">
                <h2 class="block--title_title">Новости торгового дома «Все для АЗС»</h2>
            </div>

            <?php if (have_posts()) : ?>
                <div class="block--list" id="azs-news-list">
                    <?php while (have_posts()) : the_post(); ?>
                        <a class="card showInUp" href="<?php the_permalink(); ?>">
                            <div class="card--image">
                                <div class="image">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <img src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'medium_large')); ?>" alt="<?php the_title_attribute(); ?>">
                                    <?php else : ?>
                                        <img src="<?php echo azs_img('1.jpg'); ?>" alt="<?php the_title_attribute(); ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="card--date"><?php echo get_the_date('d.m.Y'); ?></div>
                            </div>
                            <div class="card--content">
                                <div class="card--title"><?php the_title(); ?></div>
                                <div class="card--text"><?php echo wp_trim_words(get_the_excerpt(), 30); ?></div>
                                <div class="card--actions">
                                    <div class="button button-outlined"><span>Подробнее</span><span class="icon-arrow-right"></span></div>
                                </div>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>

                <div class="block--pagination">
                    <div class="button button-filled button-secondary" data-openpopup="popup_news">
                        <span>Подписаться на новости</span><span class="icon-arrow-right"></span>
                    </div>
                    <?php
                    $total_pages = $wp_query->max_num_pages;
                    $current_page = max(1, get_query_var('paged'));
                    if ($current_page < $total_pages) : ?>
                        <a class="button button-outlined" href="#" id="azs-load-more-news"
                           data-page="<?php echo esc_attr($current_page); ?>"
                           data-max="<?php echo esc_attr($total_pages); ?>">
                            <span>Показать еще</span><span class="icon-arrow-down"></span>
                        </a>
                    <?php endif; ?>
                </div>

            <?php else : ?>
                <p>Новостей пока нет.</p>
            <?php endif; ?>
        </div>
    </div>

<?php get_footer(); ?>
