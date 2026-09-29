<?php
/**
 * Журнал согласий на обработку персональных данных (152-ФЗ).
 *
 * Каждая отправка формы с персональными данными пишется одной JSON-строкой
 * в файл за месяц: consent-YYYY-MM.log. По умолчанию каталог лежит на уровень
 * выше корня сайта, чтобы файлы нельзя было скачать по ссылке; переопределяется
 * константой AZS_CONSENT_LOG_DIR в wp-config.php (каталог не должен быть
 * доступен из веба).
 */

if (!defined('ABSPATH')) exit;

// ===========================================
// Consent flags from the submitted form
// ===========================================
function azs_consent_privacy_given() {
    return !empty($_POST['privacy']);
}

// В попапах и корзине чекбокс рассылки называется privacy2, в футере — mailing.
function azs_consent_mailing_given() {
    return !empty($_POST['mailing']) || !empty($_POST['privacy2']);
}

// ===========================================
// Consent texts shown next to the checkboxes
// ===========================================
// Тексты скопированы из шаблонов форм: popups.php, woocommerce/cart-page.php,
// template-parts/consultation.php, footer.php. Меняете текст в форме —
// обновите его здесь, иначе в журнал попадёт не то, что видел посетитель.
function azs_consent_texts($form_id) {
    $privacy_url = home_url('/privacy-policy/');

    if ('consultation' === $form_id) {
        return array(
            'privacy' => array(
                'text' => 'Нажимая на кнопку «Оставить заявку», вы соглашаетесь на обработку персональных данных в соответствии с политикой обработки персональных данных',
                'url'  => $privacy_url,
            ),
        );
    }

    // Подписка в футере и попап подписки шлют один form_type=news;
    // футер отличается обязательным чекбоксом mailing.
    if ('news' === $form_id && isset($_POST['mailing'])) {
        return array(
            'privacy' => array(
                'text' => 'Согласен на обработку персональных данных в соответствии с Политикой конфиденциальности',
                'url'  => $privacy_url,
            ),
            'mailing' => array(
                'text' => 'Согласен на получение рекламных и информационных сообщений в соответствии с Офертой',
            ),
        );
    }

    // Попапы (callback, product, news) и корзина (order).
    return array(
        'privacy' => array(
            'text' => 'Ознакомлен (-на) с Политикой в отношении обработки персональных данных и даю Согласие на их обработку',
            'url'  => $privacy_url,
        ),
        'mailing' => array(
            'text' => 'Согласен (-на) на получение полезной информации и информации об акциях',
            'url'  => home_url('/poleznaya-informacziya/'),
        ),
    );
}

// ===========================================
// Consent log writer
// ===========================================
function azs_consent_log_dir() {
    if (defined('AZS_CONSENT_LOG_DIR') && AZS_CONSENT_LOG_DIR) {
        return rtrim(AZS_CONSENT_LOG_DIR, '/\\');
    }
    return dirname(rtrim(ABSPATH, '/\\')) . '/consent-logs';
}

/**
 * @param string $form_id     Тип формы: callback, consultation, news, product, order.
 * @param int    $status_code 200 — заявка принята, 400 — нет согласия, 500 — ошибка обработки.
 * @param array  $payload     Поля формы; без согласия на обработку ПДн не записываются.
 * @param array  $extra       Дополнительные поля записи (например, order_id).
 */
function azs_consent_log($form_id, $status_code, array $payload = array(), array $extra = array()) {
    $privacy_given = azs_consent_privacy_given();

    $consents = array();
    foreach (azs_consent_texts($form_id) as $key => $consent) {
        $given = 'privacy' === $key ? $privacy_given : azs_consent_mailing_given();
        $consents[$key] = array('given' => $given) + $consent;
    }

    $entry = array(
        'timestamp'   => wp_date('c'),
        'event'       => 'form_submission',
        'form_id'     => $form_id,
        'ip'          => sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'] ?? '')),
        'user_agent'  => mb_substr(sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 512),
        'method'      => sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'] ?? '')),
        'page_url'    => esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'] ?? '')),
        'status_code' => (int) $status_code,
        'consents'    => $consents,
    );

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $entry['forwarded_for'] = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
    }

    if ($privacy_given) {
        $entry['payload'] = array_filter($payload, 'strlen');
    }

    $entry = array_merge($entry, $extra);

    $dir  = azs_consent_log_dir();
    $file = $dir . '/consent-' . wp_date('Y-m') . '.log';
    $line = wp_json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";

    // Сбой записи не должен ломать JSON-ответ формы — только сообщение в error_log.
    if (!wp_mkdir_p($dir) || false === @file_put_contents($file, $line, FILE_APPEND | LOCK_EX)) {
        error_log('[azs consent log] cannot write to ' . $file);
    }
}
