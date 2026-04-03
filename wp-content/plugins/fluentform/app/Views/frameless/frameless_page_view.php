<?php

defined('ABSPATH') or die;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
    <meta http-equiv="Imagetoolbar" content="No"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php if(!empty($data['result']) && !empty($data['result']['redirectUrl'])): ?>
    <!-- Fallback for jquery error for redirect -->
    <meta http-equiv="refresh" content="5;url=<?php echo esc_url($data['result']['redirectUrl']); ?>" />
    <?php endif; ?>

    <?php
        wp_head();
    ?>
</head>
<body class="ff_frameless_page_body ff_frameless_page_<?php echo esc_attr($form->id); ?> ff_frameless_status_<?php echo esc_attr($status); ?>">

<div class="ff_frameless_wrapper">
    <div class="ff_frameless_item">
        <div class="ff_frameless_header">
            <?php echo esc_html($title); ?>
        </div>
        <div class="ff_frameless_body">
            <?php
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $message is sanitized via fluentform_sanitize_html() in SubmissionHandlerService::getReturnData() before being passed to this view
            echo $message;
            ?>

            <?php if (isset($data['loader']) && $data['loader']): ?>
                <div class="ff_paypal_loader_svg" style="text-align: center">
                    <svg version="1.1"
                         id="L4"
                         xmlns="http://www.w3.org/2000/svg"
                         xmlns:xlink="http://www.w3.org/1999/xlink"
                         x="0px"
                         y="0px"
                         viewBox="0 0 100 100"
                         enable-background="new 0 0 0 0"
                         xml:space="preserve"
                         width="50px"
                         height="50px"
                         style="margin: 0px auto;"
                    >
                        <circle fill="#000" stroke="none" cx="6" cy="50" r="6">
                            <animate attributeName="opacity" dur="1s" values="0;1;0" repeatCount="indefinite" begin="0.1" />
                        </circle>
                        <circle fill="#000" stroke="none" cx="26" cy="50" r="6">
                            <animate attributeName="opacity" dur="1s" values="0;1;0" repeatCount="indefinite" begin="0.2" />
                        </circle>
                        <circle fill="#000" stroke="none" cx="46" cy="50" r="6">
                            <animate attributeName="opacity" dur="1s" values="0;1;0" repeatCount="indefinite" begin="0.3" />
                        </circle>
                    </svg>
                </div>
            <?php endif;?>
        </div>
    </div>
</div>
<?php
wp_footer();
?>

<?php if(!empty($data['result']) && !empty($data['result']['redirectUrl'])): ?>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        window.location.href = "<?php echo esc_url($data['result']['redirectUrl']); ?>";
    });
</script>
<?php endif; ?>

</body>
</html>

