<?php
$news_query = new WP_Query(array(
    'post_type'      => 'azs_news',
    'posts_per_page' => 3,
    'orderby'        => 'date',
    'order'          => 'DESC',
));

if (!$news_query->have_posts()) return;
?>
<div class="block news">
    <div class="block--wrapper">
        <div class="block--title showInUp">
            <div class="block--title_subtitle"><span>НОВОСТИ</span></div>
            <h2 class="block--title_title">Новости торгового дома «Все для АЗС»</h2>
        </div>
        <div class="block--list">
            <?php while ($news_query->have_posts()) : $news_query->the_post(); ?>
                <a class="card showInUp" href="<?php the_permalink(); ?>">
                    <div class="card--image">
                        <div class="image"><?php azs_news_card_image(); ?></div>
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
        <?php wp_reset_postdata(); ?>

        <div class="block--pagination">
            <a class="button button-outlined" href="" data-openpopup="popup_news" data-ym-goal="podpisatsya-na-novosti"> 
                <span>Подписаться на новости</span><span class="icon-arrow-right"></span>
            </a>
            <a class="button button-filled button-secondary" href="<?php echo get_post_type_archive_link('azs_news'); ?>">
                <span>Все новости</span><span class="icon-arrow-right"></span>
            </a>
        </div>
    </div>
</div>