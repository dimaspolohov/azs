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

            <div class="side">
                <div class="side--wrapper">
                    
                        <a class="item" href="/about/"><span>О нас</span></a>
                        <a class="item" href="/oplata-i-dostavka/"><span>Оплата и доставка</span></a>
                        <a class="item" href="/garantiya-i-servis/"><span>Гарантия и сервис</span></a>
                        <a class="item" href="/poleznaya-informaczija/"><span>Полезная информация</span></a>
                        <a class="item" href="/kontakty/"><span>Контакты</span></a>
                    
                </div>
            </div>



            <div class="block--content is_cascade">
                <h2><?php the_title(); ?></h2>
                <?php the_content(); ?>
            </div>

    </div>
</div>

<?php get_footer(); ?>
