<?php
$reviews_link = function_exists('get_field') ? get_field('reviews_external_link') : '';
$reviews_orders = function_exists('get_field') ? get_field('reviews_external_orders') : '';
$reviews_rating = function_exists('get_field') ? get_field('reviews_external_rating') : '';
$reviews_feedback = function_exists('get_field') ? get_field('reviews_external_feedback') : '';

$reviews = new WP_Query(array(
    'post_type'      => 'azs_review',    
    'orderby'        => 'date',
    'order'          => 'DESC',
));
?>
<div class="block reviews">
    <div class="block--wrapper">
        <div class="block--title showInUp">
            <div class="block--title_subtitle"><span>Отзывы</span></div>
            <h2 class="block--title_title">Отзывы покупателей</h2>
        </div>
        <div class="block--list">
            <div class="card main showInUp">
                <div class="card--top">
                    <div class="card__subtitle">
                        <p><?php echo $reviews_orders; ?></p>
                    </div>
                    <div class="card__logo"><img src="<?php echo azs_img('ozon.svg'); ?>" alt="Ozon"></div>
                </div>
                <div class="card--content">
                    <div class="card--title">
                        <p>Все для АЗС</p><span><?php echo $reviews_rating; ?></span>
                    </div>
                </div>
                <div class="card__stars"><img src="<?php echo azs_img('stars.svg'); ?>" alt=""><span><?php echo $reviews_feedback; ?></span></div>
                <div class="card--actions">
                    <a class="button button-filled button-secondary" href="<?php echo esc_url($reviews_link ?: '#'); ?>">
                        <span>Смотреть больше отзывов</span><span class="icon-arrow-right"></span>
                    </a>
                </div>
            </div>

            <?php if ($reviews->have_posts()) : ?>
            <div class="block--elements">
                <?php while ($reviews->have_posts()) : $reviews->the_post();
                    $author_name   = get_field('review_author_name');
                    $author_avatar = get_field('review_author_avatar');
                    $review_text   = get_field('review_text');
                    $gallery       = get_field('review_gallery');
                    $review_date   = get_the_date('d F Y');
                    $review_id     = get_the_ID();
                ?>
                <div class="card review showInUp">
                    <div class="review__author">
                        <div class="review__author_image">
                            <?php if ($author_avatar) : ?>
                                <img src="<?php echo esc_url($author_avatar['sizes']['thumbnail'] ?? $author_avatar['url']); ?>"
                                     alt="<?php echo esc_attr($author_name); ?>"
                                     title="<?php echo esc_attr($author_name); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="review__author_description">
                            <div class="review__author_description_name"><?php echo esc_html($author_name); ?></div>
                            <div class="review__author_description_text">
                                <div class="card__stars"><img src="<?php echo azs_img('stars.svg'); ?>" alt=""></div>
                                <span><?php echo esc_html($review_date); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="review__content">
                        <div class="review_description">
                            <p><?php echo esc_html($review_text); ?></p>
                        </div>
                        <?php if ($gallery) : ?>
                        <div class="review__image" data-all="Все">
                            <?php foreach ($gallery as $image) : ?>
                                <img data-fancybox="review_<?php echo $review_id; ?>"
                                     src="<?php echo esc_url($image['url']); ?>"
                                     loading="lazy" alt="">
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php endif; ?>
            <?php wp_reset_postdata(); ?>
        </div>
    </div>
</div>
