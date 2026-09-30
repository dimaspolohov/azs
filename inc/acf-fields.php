<?php

if (!defined('ABSPATH')) exit;

add_action('acf/init', 'azs_register_acf_fields');

function azs_register_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) return;

    // =============================================
    // FRONT PAGE FIELDS
    // =============================================
    acf_add_local_field_group(array(
        'key'    => 'group_front_page',
        'title'  => 'Настройки главной страницы',
        'fields' => array(
            // --- Tab: Slider ---
            array(
                'key'   => 'field_fp_slider_tab',
                'label' => 'Слайдер',
                'type'  => 'tab',
            ),
            array(
                'key'          => 'field_fp_slider_items',
                'label'        => 'Слайды',
                'name'         => 'slider_items',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => 'Добавить слайд',
                'sub_fields'   => array(
                    array(
                        'key'           => 'field_fp_slide_image',
                        'label'         => 'Изображение',
                        'name'          => 'slide_image',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'required'      => 1,
                    ),
                    array(
                        'key'           => 'field_fp_slide_image_mob',
                        'label'         => 'Изображение (мобильное)',
                        'name'          => 'slide_image_mobile',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                    ),
                    array(
                        'key'      => 'field_fp_slide_title',
                        'label'    => 'Заголовок',
                        'name'     => 'slide_title',
                        'type'     => 'text',
                        'required' => 1,
                    ),
                    array(
                        'key'  => 'field_fp_slide_text',
                        'label' => 'Описание',
                        'name' => 'slide_text',
                        'type' => 'textarea',
                        'rows' => 3,
                    ),
                    array(
                        'key'           => 'field_fp_slide_btn_text',
                        'label'         => 'Текст кнопки',
                        'name'          => 'slide_button_text',
                        'type'          => 'text',
                        'default_value' => 'Смотреть в каталоге',
                    ),
                    array(
                        'key'   => 'field_fp_slide_btn_link',
                        'label' => 'Ссылка кнопки',
                        'name'  => 'slide_button_link',
                        'type'  => 'url',
                    ),
                ),
            ),

            // --- Tab: Catalog ---
            array(
                'key'   => 'field_fp_catalog_tab',
                'label' => 'Каталог',
                'type'  => 'tab',
            ),
            array(
                'key'          => 'field_fp_catalog_cats',
                'label'        => 'Категории товаров для вывода',
                'name'         => 'catalog_categories',
                'type'         => 'repeater',
                'layout'       => 'table',
                'button_label' => 'Добавить категорию',
                'sub_fields'   => array(
                    array(
                        'key'           => 'field_fp_catalog_cat',
                        'label'         => 'Категория',
                        'name'          => 'category',
                        'type'          => 'taxonomy',
                        'taxonomy'      => 'product_cat',
                        'field_type'    => 'select',
                        'return_format' => 'id',
                        'allow_null'    => 0,
                    ),
                ),
            ),

            // --- Tab: FAQ ---
            array(
                'key'   => 'field_fp_faq_tab',
                'label' => 'FAQ',
                'type'  => 'tab',
            ),
            array(
                'key'          => 'field_fp_faq_items',
                'label'        => 'Вопросы и ответы',
                'name'         => 'faq_items',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => 'Добавить вопрос',
                'sub_fields'   => array(
                    array(
                        'key'      => 'field_fp_faq_question',
                        'label'    => 'Вопрос',
                        'name'     => 'question',
                        'type'     => 'text',
                        'required' => 1,
                    ),
                    array(
                        'key'          => 'field_fp_faq_answer',
                        'label'        => 'Ответ',
                        'name'         => 'answer',
                        'type'         => 'wysiwyg',
                        'toolbar'      => 'basic',
                        'media_upload' => 0,
                    ),
                ),
            ),

            // --- Tab: Suppliers ---
            array(
                'key'   => 'field_fp_suppliers_tab',
                'label' => 'Поставщики',
                'type'  => 'tab',
            ),
            array(
                'key'          => 'field_fp_supplier_items',
                'label'        => 'Логотипы поставщиков',
                'name'         => 'supplier_items',
                'type'         => 'repeater',
                'layout'       => 'table',
                'button_label' => 'Добавить поставщика',
                'sub_fields'   => array(
                    array(
                        'key'           => 'field_fp_supplier_logo',
                        'label'         => 'Логотип',
                        'name'          => 'supplier_logo',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'thumbnail',
                        'required'      => 1,
                    ),
                ),
            ),

            // --- Tab: Reviews ---
            array(
                'key'   => 'field_fp_reviews_tab',
                'label' => 'Отзывы',
                'type'  => 'tab',
            ),
            array(
                'key'          => 'field_fp_reviews_link',
                'label'        => 'Ссылка «Смотреть больше отзывов»',
                'name'         => 'reviews_external_link',
                'type'         => 'url',
                'instructions' => 'External URL (e.g. Ozon reviews page)',
            ),
			array(
				'key'           => 'field_fp_reviews_orders',
				'label'         => 'Количество звказов',
				'name'          => 'reviews_external_orders',
				'type'          => 'text',				
			),
			array(
				'key'           => 'field_fp_reviews_rating',
				'label'         => 'Рейтинг',
				'name'          => 'reviews_external_rating',
				'type'          => 'text',				
			),
			array(
				'key'           => 'field_fp_reviews_feedback',
				'label'         => 'Количество отзывов',
				'name'          => 'reviews_external_feedback',
				'type'          => 'text',				
			),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'page_type',
                    'operator' => '==',
                    'value'    => 'front_page',
                ),
            ),
        ),
        'menu_order'      => 0,
        'position'        => 'normal',
        'style'           => 'default',
        'label_placement' => 'top',
    ));

    // =============================================
    // SITE SETTINGS (OPTIONS PAGE)
    // =============================================
    acf_add_local_field_group(array(
        'key'    => 'group_site_settings',
        'title'  => 'Контактная информация и настройки',
        'fields' => array(
            // --- Tab: Contacts ---
            array(
                'key'   => 'field_opt_contacts_tab',
                'label' => 'Контакты',
                'type'  => 'tab',
            ),
            array(
                'key'           => 'field_opt_phone',
                'label'         => 'Телефон',
                'name'          => 'site_phone',
                'type'          => 'text',
                'default_value' => '+7 (495) 789-34-64',
            ),
            array(
                'key'           => 'field_opt_email',
                'label'         => 'Email',
                'name'          => 'site_email',
                'type'          => 'email',
                'default_value' => 'info@pse.ru',
            ),
            array(
                'key'           => 'field_opt_address',
                'label'         => 'Адрес',
                'name'          => 'site_address',
                'type'          => 'textarea',
                'rows'          => 3,
                'default_value' => "125130, г. Москва,\nСтаропетровский пр., д. 7А, стр. 6",
            ),
            array(
                'key'           => 'field_opt_schedule',
                'label'         => 'График работы',
                'name'          => 'site_schedule',
                'type'          => 'text',
                'default_value' => 'ежедневно: 9:00-18:00',
            ),

            // --- Tab: Social ---
            array(
                'key'   => 'field_opt_social_tab',
                'label' => 'Соцсети и маркетплейсы',
                'type'  => 'tab',
            ),
            array(
                'key'   => 'field_opt_telegram',
                'label' => 'Telegram',
                'name'  => 'social_telegram',
                'type'  => 'url',
            ),
            array(
                'key'   => 'field_opt_yandex_market',
                'label' => 'Яндекс Маркет',
                'name'  => 'social_yandex_market',
                'type'  => 'url',
            ),
            array(
                'key'   => 'field_opt_ozon',
                'label' => 'Ozon',
                'name'  => 'social_ozon',
                'type'  => 'url',
            ),
            array(
                'key'   => 'field_opt_dzen',
                'label' => 'Дзен',
                'name'  => 'social_dzen',
                'type'  => 'url',
            ),

            // --- Tab: Map ---
            array(
                'key'   => 'field_opt_map_tab',
                'label' => 'Карта',
                'type'  => 'tab',
            ),
            array(
                'key'           => 'field_opt_map_image',
                'label'         => 'Изображение карты',
                'name'          => 'map_image',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'medium',
            ),
            array(
                'key'   => 'field_opt_map_link',
                'label' => 'Ссылка на карту',
                'name'  => 'map_link',
                'type'  => 'url',
            ),

        ),
        'location' => array(
            array(
                array(
                    'param'    => 'options_page',
                    'operator' => '==',
                    'value'    => 'azs-site-settings',
                ),
            ),
        ),
    ));

    // =============================================
    // PRODUCT DOCUMENTS
    // =============================================
    acf_add_local_field_group(array(
        'key'    => 'group_product_documents',
        'title'  => 'Документы товара',
        'fields' => array(
            array(
                'key'           => 'field_prod_passport',
                'label'         => 'Паспорт продукта',
                'name'          => 'product_passport',
                'type'          => 'file',
                'return_format' => 'array',
                'mime_types'    => 'pdf,doc,docx',
                'instructions'  => 'PDF или документ для кнопки «Скачать паспорт продукта»',
            ),
            array(
                'key'           => 'field_prod_mfr_catalog',
                'label'         => 'Каталог производителя',
                'name'          => 'product_manufacturer_catalog',
                'type'          => 'file',
                'return_format' => 'array',
                'mime_types'    => 'pdf,doc,docx',
                'instructions'  => 'PDF или документ для кнопки «Скачать каталог производителя»',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'product',
                ),
            ),
        ),
        'menu_order' => 10,
        'position'   => 'normal',
        'style'      => 'default',
    ));

    // =============================================
    // ARTICLE PAGE (ABOUT US) — TABBED CONTENT
    // =============================================
    acf_add_local_field_group(array(
        'key'    => 'group_article_page',
        'title'  => 'Контент страницы (табы)',
        'fields' => array(
            array(
                'key'          => 'field_article_tabs',
                'label'        => 'Табы',
                'name'         => 'article_tabs',
                'type'         => 'repeater',
                'layout'       => 'block',
                'button_label' => 'Добавить таб',
                'min'          => 1,
                'sub_fields'   => array(
                    array(
                        'key'      => 'field_article_tab_title',
                        'label'    => 'Заголовок таба',
                        'name'     => 'tab_title',
                        'type'     => 'text',
                        'required' => 1,
                        'instructions' => 'Displayed in sidebar navigation',
                    ),
                    array(
                        'key'           => 'field_article_tab_image',
                        'label'         => 'Изображение',
                        'name'          => 'tab_image',
                        'type'          => 'image',
                        'return_format' => 'array',
                        'preview_size'  => 'medium',
                        'instructions'  => 'Banner image at the top of the tab content',
                    ),
                    array(
                        'key'          => 'field_article_tab_content',
                        'label'        => 'Контент',
                        'name'         => 'tab_content',
                        'type'         => 'wysiwyg',
                        'toolbar'      => 'full',
                        'media_upload' => 1,
                        'required'     => 1,
                        'instructions' => 'Rich-text content for this tab (headings, lists, links, etc.)',
                    ),
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'page_template',
                    'operator' => '==',
                    'value'    => 'page-article.php',
                ),
            ),
        ),
        'menu_order'      => 0,
        'position'        => 'normal',
        'style'           => 'default',
        'label_placement' => 'top',
    ));

    // =============================================
    // PRODUCT CATEGORY — CONSULTATION FORM
    // =============================================
    acf_add_local_field_group(array(
        'key'    => 'group_product_cat_form',
        'title'  => 'Форма консультации на категории',
        'fields' => array(
            array(
                'key'           => 'field_cat_show_form',
                'label'         => 'Показать форму',
                'name'          => 'category_show_form',
                'type'          => 'true_false',
                'ui'            => 1,
                'default_value' => 0,
                'instructions'  => 'Вывести баннер с формой после SEO-текста категории',
            ),
            array(
                'key'               => 'field_cat_form_title',
                'label'             => 'Заголовок формы',
                'name'              => 'category_form_title',
                'type'              => 'textarea',
                'rows'              => 2,
                'new_lines'         => 'br',
                'instructions'      => 'Можно использовать перенос строки',
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_cat_show_form',
                            'operator' => '==',
                            'value'    => '1',
                        ),
                    ),
                ),
            ),
            array(
                'key'               => 'field_cat_form_subtitle',
                'label'             => 'Подзаголовок формы',
                'name'              => 'category_form_subtitle',
                'type'              => 'textarea',
                'rows'              => 3,
                'conditional_logic' => array(
                    array(
                        array(
                            'field'    => 'field_cat_show_form',
                            'operator' => '==',
                            'value'    => '1',
                        ),
                    ),
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'taxonomy',
                    'operator' => '==',
                    'value'    => 'product_cat',
                ),
            ),
        ),
        'menu_order' => 20,
        'position'   => 'normal',
        'style'      => 'default',
    ));

    // =============================================
    // REVIEW CPT FIELDS
    // =============================================
    acf_add_local_field_group(array(
        'key'    => 'group_review_fields',
        'title'  => 'Данные отзыва',
        'fields' => array(
            array(
                'key'      => 'field_rev_author_name',
                'label'    => 'Имя автора',
                'name'     => 'review_author_name',
                'type'     => 'text',
                'required' => 1,
            ),
            array(
                'key'           => 'field_rev_author_avatar',
                'label'         => 'Аватар автора',
                'name'          => 'review_author_avatar',
                'type'          => 'image',
                'return_format' => 'array',
                'preview_size'  => 'thumbnail',
            ),
            array(
                'key'           => 'field_rev_rating',
                'label'         => 'Рейтинг (1-5)',
                'name'          => 'review_rating',
                'type'          => 'number',
                'min'           => 1,
                'max'           => 5,
                'default_value' => 5,
            ),
            array(
                'key'      => 'field_rev_text',
                'label'    => 'Текст отзыва',
                'name'     => 'review_text',
                'type'     => 'textarea',
                'rows'     => 4,
                'required' => 1,
            ),
            array(
                'key'           => 'field_rev_gallery',
                'label'         => 'Фотографии',
                'name'          => 'review_gallery',
                'type'          => 'gallery',
                'return_format' => 'array',
                'preview_size'  => 'thumbnail',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param'    => 'post_type',
                    'operator' => '==',
                    'value'    => 'azs_review',
                ),
            ),
        ),
    ));
}
