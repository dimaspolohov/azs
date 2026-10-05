<?php
/**
 * Template Name:  О нас
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

            <?php
            $sections = array(
                '/about/'                  => 'О нас',
                '/oplata-i-dostavka/'      => 'Оплата и доставка',
                '/garantiya-i-servis/'     => 'Гарантия и сервис',
                '/poleznaya-informaczija/' => 'Полезная информация',
                '/kontakty/'               => 'Контакты',
            );
            $current_path = trailingslashit(wp_parse_url(get_permalink(), PHP_URL_PATH));
            ?>
            <div class="side">
                <div class="side--wrapper">
                    <?php foreach ($sections as $section_path => $section_title) : ?>
                        <a class="item<?php echo $section_path === $current_path ? ' is_active' : ''; ?>" href="<?php echo esc_url($section_path); ?>"><span><?php echo esc_html($section_title); ?></span></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($current_path === '/about/') :
                // The sections used to be tabs of this page: /about/#контакты
                $old_anchors = array();
                foreach ($sections as $section_path => $section_title) {
                    $old_anchors[azs_slug($section_title)] = $section_path;
                }
            ?>
                <script>
                    (function() {
                        var sections = <?php echo wp_json_encode($old_anchors, JSON_UNESCAPED_UNICODE); ?>;
                        var path = sections[decodeURIComponent(location.hash.replace('#', ''))];
                        if (path && path !== location.pathname) location.replace(path);
                    })();
                </script>
            <?php endif; ?>



            <div class="block--content is_cascade">
                <h2><?php the_title(); ?></h2>
                <?php the_content(); ?>
            </div>

    </div>
</div>

<?php get_footer(); ?>
