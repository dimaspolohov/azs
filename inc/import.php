<?php
/**
 * WooCommerce CSV Product Import — admin page + AJAX batch processor.
 */

if (!defined('ABSPATH')) exit;

if (!defined('AZS_IMPORT_GOOGLE_SHEET_CSV_URL')) {
    define('AZS_IMPORT_GOOGLE_SHEET_CSV_URL', 'https://docs.google.com/spreadsheets/d/1KPX299zfUISv42WMQj-L8NrWa5y2lK_M4oUQNiYBq0U/export?format=csv&gid=0');
}

if (!defined('AZS_IMPORT_GOOGLE_SHEET_EDIT_URL')) {
    define('AZS_IMPORT_GOOGLE_SHEET_EDIT_URL', 'https://docs.google.com/spreadsheets/d/1KPX299zfUISv42WMQj-L8NrWa5y2lK_M4oUQNiYBq0U/edit?usp=sharing');
}

// ======================================================================
// Admin menu
// ======================================================================
add_action('admin_menu', 'azs_import_admin_menu');
function azs_import_admin_menu() {
    add_management_page(
        'Импорт товаров из CSV',
        'Импорт CSV товаров',
        'manage_options',
        'azs-csv-import',
        'azs_import_admin_page'
    );
}

// ======================================================================
// Admin page markup
// ======================================================================
function azs_import_admin_page() {
    $csv_url = AZS_IMPORT_GOOGLE_SHEET_CSV_URL;
    $csv_error = '';
    $row_count = 0;

    $row_count = azs_import_count_csv_rows($csv_error);

    ?>
    <div class="wrap">
        <h1>Импорт товаров из CSV</h1>

        <?php if ($csv_error) : ?>
            <div class="notice notice-error"><p>Не удалось загрузить CSV из Google Sheets: <?php echo esc_html($csv_error); ?></p></div>
        <?php else : ?>
            <div class="notice notice-info">
                <p>Google Sheets: <a href="<?php echo esc_url(AZS_IMPORT_GOOGLE_SHEET_EDIT_URL); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html(AZS_IMPORT_GOOGLE_SHEET_EDIT_URL); ?></a></p>
                <p>CSV export: <code><?php echo esc_html($csv_url); ?></code></p>
                <p>Найдено записей: <strong><?php echo intval($row_count); ?></strong></p>
            </div>

            <table class="form-table">
                <tr>
                    <th scope="row">Обновлять существующие</th>
                    <td>
                        <label>
                            <input type="checkbox" id="azs-import-update" value="1">
                            Обновлять товары с совпадающим артикулом (SKU). Если не отмечено — будут пропущены.
                        </label>
                    </td>
                </tr>
            </table>

            <p>
                <button id="azs-import-start" class="button button-primary button-hero">Начать импорт</button>
            </p>

            <div id="azs-import-progress" style="display:none;">
                <h2>Прогресс</h2>
                <div style="background:#f0f0f1;border:1px solid #c3c4c7;border-radius:4px;padding:2px;margin-bottom:12px;">
                    <div id="azs-import-bar" style="background:#2271b1;height:24px;border-radius:3px;width:0;transition:width .3s;"></div>
                </div>
                <p id="azs-import-status">Подготовка…</p>
                <div id="azs-import-log" style="max-height:400px;overflow-y:auto;background:#fff;border:1px solid #c3c4c7;padding:8px 12px;font-family:monospace;font-size:13px;line-height:1.6;"></div>
            </div>

            <div id="azs-import-summary" style="display:none;margin-top:16px;">
                <div class="notice notice-success"><p id="azs-import-summary-text"></p></div>
            </div>
        <?php endif; ?>
    </div>

    <script>
    (function() {
        var startBtn   = document.getElementById('azs-import-start');
        if (!startBtn) return;

        var updateChk  = document.getElementById('azs-import-update');
        var progressEl = document.getElementById('azs-import-progress');
        var barEl      = document.getElementById('azs-import-bar');
        var statusEl   = document.getElementById('azs-import-status');
        var logEl      = document.getElementById('azs-import-log');
        var summaryEl  = document.getElementById('azs-import-summary');
        var summaryTxt = document.getElementById('azs-import-summary-text');

        var stats = {created: 0, updated: 0, skipped: 0, errors: 0};

        startBtn.addEventListener('click', function() {
            startBtn.disabled = true;
            progressEl.style.display = '';
            summaryEl.style.display  = 'none';
            logEl.innerHTML = '';
            stats = {created: 0, updated: 0, skipped: 0, errors: 0};
            runBatch(0);
        });

        function runBatch(offset) {
            var fd = new FormData();
            fd.append('action', 'azs_import_batch');
            fd.append('nonce', '<?php echo wp_create_nonce('azs_import'); ?>');
            fd.append('offset', offset);
            fd.append('batch_size', 10);
            fd.append('update_existing', updateChk.checked ? '1' : '0');

            fetch(ajaxurl, {method: 'POST', body: fd})
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (!res.success) {
                        appendLog('ОШИБКА: ' + (res.data && res.data.message ? res.data.message : 'Неизвестная ошибка'), 'red');
                        finish();
                        return;
                    }

                    var d = res.data;

                    if (d.log && d.log.length) {
                        d.log.forEach(function(entry) {
                            appendLog(entry.message, entry.type === 'error' ? 'red' : (entry.type === 'skip' ? '#996800' : 'green'));
                        });
                    }

                    stats.created += d.created || 0;
                    stats.updated += d.updated || 0;
                    stats.skipped += d.skipped || 0;
                    stats.errors  += d.errors  || 0;

                    var pct = d.total > 0 ? Math.min(100, Math.round(d.processed / d.total * 100)) : 100;
                    barEl.style.width = pct + '%';
                    statusEl.textContent = 'Обработано ' + d.processed + ' из ' + d.total + ' (' + pct + '%)';

                    if (d.done) {
                        finish();
                    } else {
                        runBatch(d.next_offset);
                    }
                })
                .catch(function(err) {
                    appendLog('Fetch error: ' + err, 'red');
                    finish();
                });
        }

        function appendLog(text, color) {
            var line = document.createElement('div');
            line.style.color = color || '';
            line.textContent = text;
            logEl.appendChild(line);
            logEl.scrollTop = logEl.scrollHeight;
        }

        function finish() {
            startBtn.disabled = false;
            summaryEl.style.display = '';
            summaryTxt.textContent =
                'Импорт завершён. Создано: ' + stats.created +
                ', обновлено: ' + stats.updated +
                ', пропущено: ' + stats.skipped +
                ', ошибок: ' + stats.errors;
        }
    })();
    </script>
    <?php
}

// ======================================================================
// Load CSV from Google Sheets and expose it as a readable stream
// ======================================================================
function azs_import_fetch_csv_content(&$error = '') {
    $error = '';

    $response = wp_remote_get(AZS_IMPORT_GOOGLE_SHEET_CSV_URL, array(
        'timeout'     => 30,
        'redirection' => 5,
    ));

    if (is_wp_error($response)) {
        $error = $response->get_error_message();
        return '';
    }

    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        $error = 'HTTP ' . $code;
        return '';
    }

    $body = wp_remote_retrieve_body($response);
    if (trim($body) === '') {
        $error = 'пустой ответ от Google Sheets';
        return '';
    }

    return $body;
}

function azs_import_open_csv_handle(&$error = '') {
    $csv = azs_import_fetch_csv_content($error);
    if ($csv === '') {
        return false;
    }

    $handle = fopen('php://temp', 'r+');
    if (!$handle) {
        $error = 'не удалось открыть временный поток CSV';
        return false;
    }

    fwrite($handle, $csv);
    rewind($handle);

    return $handle;
}

// ======================================================================
// Count logical CSV rows (respects quoted multiline fields)
// ======================================================================
function azs_import_count_csv_rows(&$error = '') {
    $handle = azs_import_open_csv_handle($error);
    if (!$handle) return 0;

    $count = 0;
    fgetcsv($handle, 0, ',', '"', '\\'); // skip header
    while (fgetcsv($handle, 0, ',', '"', '\\') !== false) {
        $count++;
    }
    fclose($handle);
    return $count;
}

// ======================================================================
// Parse all CSV rows into an array (cached in transient for duration of import)
// ======================================================================
function azs_import_parse_csv(&$error = '') {
    $cached = get_transient('azs_import_rows');
    if ($cached !== false) return $cached;

    $handle = azs_import_open_csv_handle($error);
    if (!$handle) return array();

    $headers = fgetcsv($handle, 0, ',', '"', '\\');
    if (!$headers) {
        fclose($handle);
        $error = 'не найдена строка заголовков CSV';
        return array();
    }

    $headers = array_map('trim', $headers);

    $rows = array();
    while (($line = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
        if (count($line) < count($headers)) {
            $line = array_pad($line, count($headers), '');
        }
        $row = array();
        foreach ($headers as $i => $h) {
            $row[$h] = isset($line[$i]) ? trim($line[$i]) : '';
        }
        if (empty($row['Артикул']) && empty($row['Название товара'])) continue;
        $rows[] = $row;
    }
    fclose($handle);

    set_transient('azs_import_rows', $rows, 3600);
    return $rows;
}

// ======================================================================
// AJAX handler — process a batch
// ======================================================================
add_action('wp_ajax_azs_import_batch', 'azs_import_batch_handler');
function azs_import_batch_handler() {
    check_ajax_referer('azs_import', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Недостаточно прав'));
    }

    $offset     = intval($_POST['offset'] ?? 0);
    $batch_size = intval($_POST['batch_size'] ?? 10);
    $update_existing = !empty($_POST['update_existing']);

    if ($offset === 0) {
        delete_transient('azs_import_rows');
    }

    $csv_error = '';
    $rows  = azs_import_parse_csv($csv_error);
    if ($csv_error) {
        wp_send_json_error(array('message' => $csv_error));
    }

    $total = count($rows);
    $batch = array_slice($rows, $offset, $batch_size);

    $log     = array();
    $created = 0;
    $updated = 0;
    $skipped = 0;
    $errors  = 0;

    foreach ($batch as $idx => $row) {
        $row_num = $offset + $idx + 1;
        $result  = azs_import_process_row($row, $update_existing);

        switch ($result['status']) {
            case 'created':
                $created++;
                $log[] = array('type' => 'ok',   'message' => "#{$row_num} Создан: {$result['title']} (SKU: {$result['sku']})");
                break;
            case 'updated':
                $updated++;
                $log[] = array('type' => 'ok',   'message' => "#{$row_num} Обновлён: {$result['title']} (SKU: {$result['sku']})");
                break;
            case 'skipped':
                $skipped++;
                $log[] = array('type' => 'skip', 'message' => "#{$row_num} Пропущен (уже существует): {$result['title']} (SKU: {$result['sku']})");
                break;
            case 'error':
                $errors++;
                $log[] = array('type' => 'error', 'message' => "#{$row_num} Ошибка: {$result['message']}");
                break;
        }
    }

    $processed    = min($offset + $batch_size, $total);
    $done         = $processed >= $total;
    $next_offset  = $offset + $batch_size;

    if ($done) {
        delete_transient('azs_import_rows');
    }

    wp_send_json_success(array(
        'log'         => $log,
        'created'     => $created,
        'updated'     => $updated,
        'skipped'     => $skipped,
        'errors'      => $errors,
        'processed'   => $processed,
        'total'       => $total,
        'done'        => $done,
        'next_offset' => $next_offset,
    ));
}

// ======================================================================
// Process a single CSV row
// ======================================================================
function azs_import_process_row($row, $update_existing) {
    $title = $row['Название товара'] ?? '';
    $sku   = $row['Артикул'] ?? '';

    if (empty($title)) {
        return array('status' => 'error', 'message' => 'Пустое название товара');
    }

    $category    = trim($row['Категория'] ?? '');
    $subcategory = trim($row['Подкатегория'] ?? '');

    $existing_id = 0;
    if (!empty($sku)) {
        $existing_id = wc_get_product_id_by_sku($sku);
    }

    if ($existing_id && !$update_existing) {
        return array('status' => 'skipped', 'title' => $title, 'sku' => $sku);
    }

    $is_update = $existing_id > 0;

    try {
        if ($is_update) {
            $product = wc_get_product($existing_id);
            if (!$product) {
                return array('status' => 'error', 'message' => "Не удалось загрузить товар ID {$existing_id}");
            }
        } else {
            $product = new WC_Product_Simple();
        }

        $product->set_name($title);

        if (!empty($sku)) {
            $product->set_sku($sku);
        }

        $regular_price = azs_import_parse_price($row['Цена'] ?? '');
        if ($regular_price > 0) {
            $product->set_regular_price($regular_price);
        }

        $is_sale  = strtoupper(trim($row['Скидка'] ?? '')) === 'TRUE';
        $sale_price = azs_import_parse_price($row['Цена со скидкой'] ?? '');
        if ($is_sale && $sale_price > 0) {
            $product->set_sale_price($sale_price);
        } else {
            $product->set_sale_price('');
        }

        $in_stock = strtoupper(trim($row['В наличии'] ?? '')) === 'TRUE';
        $product->set_manage_stock(false);
        $product->set_stock_status($in_stock ? 'instock' : 'outofstock');

        $description = $row['Описание'] ?? '';
        if ($description) {
            $product->set_description(wpautop($description));
        }

        $product->set_status('publish');

        $product_id = $product->save();

        if (!$product_id) {
            return array('status' => 'error', 'message' => "Не удалось сохранить товар: {$title}");
        }

        $is_new = strtoupper(trim($row['Новинка'] ?? '')) === 'TRUE';
        update_post_meta($product_id, 'is_new', $is_new ? 'yes' : 'no');

        azs_import_set_categories($product_id, $category, $subcategory);
        azs_import_set_brand($product_id, $row['Производитель'] ?? '');
        azs_import_set_liquid_type($product_id, $row['Види жидкости'] ?? '');

        $passport_url = trim($row['Паспорт продукта'] ?? '');
        $catalog_url  = trim($row['Каталог производителя'] ?? '');
        if ($passport_url) update_post_meta($product_id, '_product_passport_url', esc_url_raw($passport_url));
        if ($catalog_url)  update_post_meta($product_id, '_product_mfr_catalog_url', esc_url_raw($catalog_url));

        azs_import_attach_image($product_id, $sku, $category, $subcategory);

        return array(
            'status' => $is_update ? 'updated' : 'created',
            'title'  => $title,
            'sku'    => $sku,
        );

    } catch (Exception $e) {
        return array('status' => 'error', 'message' => $e->getMessage());
    }
}

// ======================================================================
// Parse price string like "65 510,08" -> 65510.08
// ======================================================================
function azs_import_parse_price($value) {
    $value = trim($value);
    if ($value === '') return 0;

    $value = str_replace("\xC2\xA0", '', $value); // non-breaking space
    $value = str_replace(' ', '', $value);
    $value = str_replace(',', '.', $value);

    return floatval($value);
}

// ======================================================================
// Create / assign product categories (parent > child hierarchy)
// ======================================================================
function azs_import_set_categories($product_id, $category, $subcategory) {
    $term_ids = array();

    if (empty($category)) {
        return;
    }

    $parent_id = azs_import_get_or_create_term($category, 'product_cat', 0);
    if ($parent_id) {
        $term_ids[] = $parent_id;
    }

    if (!empty($subcategory) && $parent_id) {
        $child_id = azs_import_get_or_create_term($subcategory, 'product_cat', $parent_id);
        if ($child_id) {
            $term_ids[] = $child_id;
        }
    }

    if (!empty($term_ids)) {
        wp_set_object_terms($product_id, $term_ids, 'product_cat');
    }
}

// ======================================================================
// Set brand taxonomy
// ======================================================================
function azs_import_set_brand($product_id, $brand_name) {
    $brand_name = trim($brand_name);
    if (empty($brand_name)) return;

    $taxonomy = 'product_brand';
    if (!taxonomy_exists($taxonomy)) {
        if (function_exists('azs_get_brand_taxonomy')) {
            $detected = azs_get_brand_taxonomy();
            if ($detected) $taxonomy = $detected;
        }
    }

    if (!taxonomy_exists($taxonomy)) return;

    $term_id = azs_import_get_or_create_term($brand_name, $taxonomy, 0);
    if ($term_id) {
        wp_set_object_terms($product_id, array($term_id), $taxonomy);
    }
}

// ======================================================================
// Set liquid type taxonomy
// ======================================================================
function azs_import_set_liquid_type($product_id, $liquid_type) {
    $liquid_type = trim($liquid_type);
    if (empty($liquid_type)) return;

    $taxonomy = 'product_liquid_type';
    if (!taxonomy_exists($taxonomy)) return;

    $parts = array_map('trim', explode(',', $liquid_type));
    $term_ids = array();

    foreach ($parts as $part) {
        if ($part === '') continue;
        $tid = azs_import_get_or_create_term($part, $taxonomy, 0);
        if ($tid) $term_ids[] = $tid;
    }

    if (!empty($term_ids)) {
        wp_set_object_terms($product_id, $term_ids, $taxonomy);
    }
}

// ======================================================================
// Get or create a taxonomy term; returns term_id or 0
// ======================================================================
function azs_import_get_or_create_term($name, $taxonomy, $parent = 0) {
    static $cache = array();
    $cache_key = $taxonomy . '|' . $parent . '|' . $name;

    if (isset($cache[$cache_key])) return $cache[$cache_key];

    $existing = get_terms(array(
        'taxonomy'   => $taxonomy,
        'name'       => $name,
        'parent'     => $parent,
        'hide_empty' => false,
        'number'     => 1,
    ));

    if (!is_wp_error($existing) && !empty($existing)) {
        $cache[$cache_key] = $existing[0]->term_id;
        return $existing[0]->term_id;
    }

    $slug = azs_import_make_slug($name);
    $result = wp_insert_term($name, $taxonomy, array(
        'slug'   => $slug,
        'parent' => $parent,
    ));

    if (is_wp_error($result)) {
        if ($result->get_error_code() === 'term_exists') {
            $tid = $result->get_error_data('term_exists');
            $cache[$cache_key] = intval($tid);
            return intval($tid);
        }
        return 0;
    }

    $cache[$cache_key] = intval($result['term_id']);
    return intval($result['term_id']);
}

// ======================================================================
// Transliterate + slugify for Cyrillic term names
// ======================================================================
function azs_import_make_slug($text) {
    $tr = array(
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo','ж'=>'zh',
        'з'=>'z','и'=>'i','й'=>'j','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o',
        'п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'kh','ц'=>'ts',
        'ч'=>'ch','ш'=>'sh','щ'=>'shch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu',
        'я'=>'ya','і'=>'i','ї'=>'yi','є'=>'ye','ґ'=>'g',
        'А'=>'A','Б'=>'B','В'=>'V','Г'=>'G','Д'=>'D','Е'=>'E','Ё'=>'Yo','Ж'=>'Zh',
        'З'=>'Z','И'=>'I','Й'=>'J','К'=>'K','Л'=>'L','М'=>'M','Н'=>'N','О'=>'O',
        'П'=>'P','Р'=>'R','С'=>'S','Т'=>'T','У'=>'U','Ф'=>'F','Х'=>'Kh','Ц'=>'Ts',
        'Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Shch','Ъ'=>'','Ы'=>'Y','Ь'=>'','Э'=>'E','Ю'=>'Yu',
        'Я'=>'Ya','І'=>'I','Ї'=>'Yi','Є'=>'Ye','Ґ'=>'G',
    );

    $text = strtr($text, $tr);
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');

    return $text;
}

// ======================================================================
// Find and attach product image from import/images/ by SKU
// Searches: {category}/{subcategory}/, {category}/, root, then full recursive scan
// ======================================================================
function azs_import_attach_image($product_id, $sku, $category = '', $subcategory = '') {
    if (empty($sku)) return;
    if (has_post_thumbnail($product_id)) return;

    $base_dir = get_stylesheet_directory() . '/import/images';
    if (!is_dir($base_dir)) return;

    $extensions = array('jpg', 'jpeg', 'png', 'webp', 'gif');

    $search_dirs = array();
    if ($category && $subcategory) {
        $search_dirs[] = $base_dir . '/' . $category . '/' . $subcategory;
    }
    if ($category) {
        $search_dirs[] = $base_dir . '/' . $category;
    }
    $search_dirs[] = $base_dir;

    $image_path = azs_import_find_image_in_dirs($sku, $search_dirs, $extensions);

    if (!$image_path) {
        $image_path = azs_import_find_image_recursive($sku, $base_dir, $extensions);
    }

    if (!$image_path || !file_exists($image_path)) return;

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp = wp_tempnam($image_path);
    copy($image_path, $tmp);

    $file_array = array(
        'name'     => basename($image_path),
        'tmp_name' => $tmp,
    );

    $attach_id = media_handle_sideload($file_array, $product_id);

    if (is_wp_error($attach_id)) {
        @unlink($tmp);
        return;
    }

    set_post_thumbnail($product_id, $attach_id);
}

// ======================================================================
// Search for {sku}.{ext} in a list of directories (non-recursive)
// ======================================================================
function azs_import_find_image_in_dirs($sku, $dirs, $extensions) {
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) continue;

        foreach ($extensions as $ext) {
            $candidate = $dir . '/' . $sku . '.' . $ext;
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        $sku_lower = mb_strtolower($sku, 'UTF-8');
        $handle = opendir($dir);
        if (!$handle) continue;
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') continue;
            $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($file_ext, $extensions)) continue;
            if (mb_strtolower(pathinfo($file, PATHINFO_FILENAME), 'UTF-8') === $sku_lower) {
                closedir($handle);
                return $dir . '/' . $file;
            }
        }
        closedir($handle);
    }
    return '';
}

// ======================================================================
// Recursive scan of all subdirectories for {sku}.{ext}
// Uses a static index built once and reused across all calls
// ======================================================================
function azs_import_find_image_recursive($sku, $base_dir, $extensions) {
    static $index = null;

    if ($index === null) {
        $index = array();
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($iterator as $file) {
            if (!$file->isFile()) continue;
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, $extensions)) continue;
            $name_lower = mb_strtolower($file->getBasename('.' . $file->getExtension()), 'UTF-8');
            $index[$name_lower] = $file->getPathname();
        }
    }

    $sku_lower = mb_strtolower($sku, 'UTF-8');
    return isset($index[$sku_lower]) ? $index[$sku_lower] : '';
}
