<?php

defined('ABSPATH') or die;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables in view files
?>
<table style="width: 100%;border: 1px solid #cbcbcb;" class="table ffp_order_items_table ffp_table table_bordered">
    <thead>
    <tr>
        <th><?php esc_html_e('Item', 'fluentform'); ?></th>
        <th><?php esc_html_e('Quantity', 'fluentform'); ?></th>
        <th><?php esc_html_e('Price', 'fluentform'); ?></th>
        <th><?php esc_html_e('Line Total', 'fluentform'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $order_item): ?>
        <tr>

            <td><?php echo fluentform_sanitize_html($order_item->item_name); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped using wp_kses ?></td>
            <td><?php echo esc_html($order_item->quantity); ?></td>
            <td><?php echo fluentform_sanitize_html($order_item->formatted_item_price); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Formatted price contains decoded currency symbols ?></td>
            <td><?php echo fluentform_sanitize_html($order_item->formatted_line_total); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Formatted amount contains decoded currency symbols ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
    <?php if($discount_items): ?>
        <tr class="ffp_total_row">
            <th style="text-align: right" colspan="3"><?php esc_html_e('Sub-Total', 'fluentform'); ?></th>
            <td><?php echo fluentform_sanitize_html($subTotal); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Formatted amount contains decoded currency symbols ?></td>
        </tr>
        <?php foreach ($discount_items as $discount_item): ?>
        <tr class="ffp_total_row">
            <th style="text-align: right" colspan="3"><?php esc_html_e('Discount:', 'fluentform'); ?> <?php echo fluentform_sanitize_html($discount_item->item_name); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></th>
            <td>-<?php echo fluentform_sanitize_html($discount_item->formatted_line_total); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Formatted amount contains decoded currency symbols ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>

        <tr class="ffp_total_row">
            <th style="text-align: right" colspan="3"><?php esc_html_e('Total', 'fluentform'); ?></th>
            <td><?php echo fluentform_sanitize_html($orderTotal); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Formatted amount contains decoded currency symbols ?></td>
        </tr>
    </tfoot>

</table>
