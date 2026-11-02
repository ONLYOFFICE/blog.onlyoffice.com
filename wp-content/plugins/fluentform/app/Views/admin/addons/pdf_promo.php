<?php

defined('ABSPATH') or die;

?>
<div class="add_on_modules">
    <div class="modules_header mb-6">
        <h4 class="title mb-2"><?php esc_html_e('Fluent Forms PDF Modules', 'fluentform'); ?></h4>
        <p class="text"><?php esc_html_e('Generate PDF from your form submissions. You can create PDF templates and download / send via email too.', 'fluentform'); ?></p>
    </div>
    <div class="modules_body">
        <?php if(!$is_installed): ?>
            <div class="install_wrapper text-center mb-5">
                <img class="mb-5" src="<?php echo esc_url($public_url . 'img/pdf-promo-img.png'); ?>" alt="">
                <h2 class="mb-4"><?php esc_html_e('PDF Module is not installed yet. Please install now', 'fluentform'); ?> <span class="text-danger"><?php esc_html_e('(it\'s free)', 'fluentform'); ?></span></h2>
                <p class="fs-15 mb-5" style="width: 540px; margin-left: auto; margin-right: auto;"><?php esc_html_e('Generate PDF from your form submissions. You can create PDF templates and download / send via email too.', 'fluentform'); ?></p>
                <a class="el-button el-button--primary" href="<?php echo esc_url($install_url); ?>">
                    <?php esc_html_e('Install Fluent Forms PDF Addon', 'fluentform'); ?>
                </a>
            </div>
        <?php else:
            do_action('fluentform/addons_page_render_fluentform_pdf_settings');
        endif; ?>

    </div>
</div>
