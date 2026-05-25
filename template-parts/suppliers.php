<?php
$suppliers = function_exists('get_field') ? get_field('supplier_items') : null;
if (!$suppliers) return;
?>
<div class="block suppliers">
    <div class="block--wrapper">
        <div class="block--title showInUp">
            <div class="block--title_subtitle"><span>ПОСТАВЩИКИ</span></div>
            <h2 class="block--title_title">Наши поставщики</h2>
        </div>
        <div class="block--list">
            <div class="marquee">
                <div class="marquee__wrapper">
                    <?php foreach ($suppliers as $supplier) :
                        $logo = $supplier['supplier_logo'];
                    ?>
                    <div class="marquee__item">
                        <div class="card">
                            <div class="card--image">
                                <img src="<?php echo esc_url($logo['url']); ?>" alt="<?php echo esc_attr($logo['alt'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
