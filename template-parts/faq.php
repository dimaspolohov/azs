<?php
$faq_items = function_exists('get_field') ? get_field('faq_items') : null;
if (!$faq_items) return;
?>
<div class="block faq" itemscope itemtype="https://schema.org/FAQPage">
    <div class="block--wrapper">
        <div class="block--title showInUp">
            <div class="block--title_subtitle"><span>FAQ</span></div>
            <h2 class="block--title_title">Часто задаваемые вопросы</h2>
        </div>
        <div class="block--elements">
            <div class="content">
                <?php foreach ($faq_items as $item) : ?>
                <div class="accordion" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                    <div class="accordion--button">
                        <span class="icon-plus"></span>
                        <p class="accordion--button_text" itemprop="name"><?php echo esc_html($item['question']); ?></p>
                    </div>
                    <div class="accordion--content" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                        <?php echo wp_kses_post($item['answer']); ?>
			<meta itemprop="text" content="<?=strip_tags(wp_kses_post($item['answer']))?>">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
