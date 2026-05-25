<?php get_header(); ?>

<?php while (have_posts()) : the_post(); ?>

    <div class="block--wrapper">
        <div class="breadcrumbs" itemscope itemtype="https://schema.org/BreadcrumbList">
            <a href="<?php echo esc_url(home_url('/')); ?>" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <meta itemprop="position" content="1"/>
                <span itemprop="name">Главная</span><span class="separator">></span>
            </a>
            <a href="<?php echo esc_url(get_post_type_archive_link('azs_news')); ?>" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <meta itemprop="position" content="2"/>
                <span itemprop="name">Новости</span><span class="separator">></span>
            </a>
            <a href="<?php the_permalink(); ?>" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <meta itemprop="position" content="3"/>
                <span itemprop="name"><?php the_title(); ?></span>
            </a>
        </div>
    </div>

    <div class="block news-detail">
        <div class="block--wrapper">
            <div class="card">
                <div class="card--image">
                    <div class="image">
                        <?php if (has_post_thumbnail()) : ?>
                            <img src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'large')); ?>" alt="<?php the_title_attribute(); ?>">
                        <?php else : ?>
                            <img src="<?php echo azs_img('7.jpg'); ?>" alt="<?php the_title_attribute(); ?>">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card--content">
                    <div class="card--date"><?php echo get_the_date('d.m.Y'); ?></div>
                    <h1 class="card--title"><?php the_title(); ?></h1>
                    <div class="card--text">
                        <?php the_content(); ?>
                    </div>
                    <div class="card--actions">
                        <?php if (function_exists('wc_get_page_id')) : ?>
                            <a class="button button-outlined" href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>">
                                <span>Смотреть в каталоге</span><span class="icon-arrow-right"></span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endwhile; ?>

<?php get_footer(); ?>
