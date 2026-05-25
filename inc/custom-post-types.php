<?php

if (!defined('ABSPATH')) exit;

add_action('init', 'azs_register_post_types');

function azs_register_post_types() {
    register_post_type('azs_review', array(
        'labels' => array(
            'name'               => 'Отзывы',
            'singular_name'      => 'Отзыв',
            'add_new'            => 'Добавить отзыв',
            'add_new_item'       => 'Добавить новый отзыв',
            'edit_item'          => 'Редактировать отзыв',
            'new_item'           => 'Новый отзыв',
            'view_item'          => 'Просмотреть отзыв',
            'search_items'       => 'Искать отзывы',
            'not_found'          => 'Отзывы не найдены',
            'not_found_in_trash' => 'В корзине отзывов не найдено',
            'menu_name'          => 'Отзывы',
        ),
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => array('slug' => 'reviews'),
        'supports'     => array('title'),
        'menu_icon'    => 'dashicons-star-filled',
        'show_in_rest' => true,
    ));

    register_post_type('azs_news', array(
        'labels' => array(
            'name'               => 'Новости',
            'singular_name'      => 'Новость',
            'add_new'            => 'Добавить новость',
            'add_new_item'       => 'Добавить новую новость',
            'edit_item'          => 'Редактировать новость',
            'new_item'           => 'Новая новость',
            'view_item'          => 'Просмотреть новость',
            'search_items'       => 'Искать новости',
            'not_found'          => 'Новости не найдены',
            'not_found_in_trash' => 'В корзине новостей не найдено',
            'menu_name'          => 'Новости',
        ),
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => array('slug' => 'news'),
        'supports'     => array('title', 'editor', 'thumbnail', 'excerpt'),
        'menu_icon'    => 'dashicons-megaphone',
        'show_in_rest' => true,
    ));
}
