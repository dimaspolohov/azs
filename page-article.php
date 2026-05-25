<?php
/**
 * Template Name: Статья (О нас)
 */

if (!defined('ABSPATH')) exit;

get_header();

$tabs = function_exists('get_field') ? get_field('article_tabs') : null;
?>

<div class="block--wrapper">
    <div class="breadcrumbs" itemscope itemtype="https://schema.org/BreadcrumbList">
        <a href="<?php echo esc_url(home_url('/')); ?>" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <meta itemprop="position" content="1"/>
            <span itemprop="name">Главная</span>
            <span class="separator">></span>
        </a>
        <a href="<?php the_permalink(); ?>" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <meta itemprop="position" content="2"/>
            <span itemprop="name"><?php the_title(); ?></span>
        </a>
    </div>
</div>

<div class="block article">
    <div class="block--wrapper">
        <?php if ($tabs && is_array($tabs) && count($tabs) > 0) : ?>
            <div class="side">
                <div class="side--wrapper">
                    <?php foreach ($tabs as $i => $tab) :
                        $slug = azs_slug($tab['tab_title']);
                    ?>
                        <a class="item<?php echo $i === 0 ? ' is_active' : ''; ?>" href="#<?php echo esc_attr($slug); ?>" data-article-tab="<?php echo esc_attr($slug); ?>">
                            <span><?php echo esc_html($tab['tab_title']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php foreach ($tabs as $i => $tab) :
                $slug = azs_slug($tab['tab_title']);
            ?>
                <div class="block--content is_cascade<?php echo $i !== 0 ? ' is_hidden' : ''; ?>" data-article-content="<?php echo esc_attr($slug); ?>" id="<?php echo esc_attr($slug); ?>">
                    <?php if (!empty($tab['tab_image'])) : ?>
                        <img src="<?php echo esc_url($tab['tab_image']['url']); ?>"
                             alt="<?php echo esc_attr($tab['tab_image']['alt'] ?? $tab['tab_title']); ?>">
                    <?php endif; ?>
                    <?php echo $tab['tab_content']; ?>
                </div>
            <?php endforeach; ?>

        <?php else : ?>
            <div class="block--content is_cascade">
                <?php the_content(); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
