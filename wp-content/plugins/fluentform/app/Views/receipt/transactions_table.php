<?php

defined('ABSPATH') or die;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables in view files
?>
<div class="ff_transactions">
    <?php
        do_action_deprecated(
            'fluentform_transactions_before_table',
            [
                $transactions
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/transactions_before_table',
            'Use fluentform/transactions_before_table instead of fluentform_transactions_before_table.'
        );
        do_action('fluentform/transactions_before_table', $transactions);
    ?>
    <table style="width: 100%;border: 1px solid #cbcbcb;margin-top: 0;" class="table ffp_order_items_table ffp_table table_bordered">
        <thead>
            <tr>
                <th class="ff_th_id"><?php esc_html_e('ID', 'fluentform'); ?></th>
                <th class="ff_th_amount"><?php esc_html_e('Amount', 'fluentform'); ?></th>
                <th class="ff_th_status"><?php esc_html_e('Status', 'fluentform'); ?></th>
                <th class="ff_th_payment_method"><?php esc_html_e('Payment Method', 'fluentform'); ?></th>
                <th class="ff_th_date"><?php esc_html_e('Date', 'fluentform'); ?></th>
                <th class="ff_th_action"><?php esc_html_e('Action', 'fluentform'); ?></th>
                <?php
                    do_action_deprecated(
                        'fluentform_transaction_table_thead_row',
                        [
                            $transactions
                        ],
                        FLUENTFORM_FRAMEWORK_UPGRADE,
                        'fluentform/transaction_table_thead_row',
                        'Use fluentform/transaction_table_thead_row instead of fluentform_transaction_table_thead_row.'
                    );

                    do_action('fluentform/transaction_table_thead_row', $transactions);
                ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transaction): ?>
            <tr class="ff_row_status_<?php echo esc_attr($transaction->status); ?>">
                <td class="ff_td_id">#<?php echo esc_html($transaction->id);?></td>
                <td class="ff_td_amount"><?php echo fluentform_sanitize_html($transaction->formatted_amount); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                <td class="ff_td_status"><span class="ff_pay_status ff_pay_status_<?php echo esc_attr($transaction->status); ?>"><?php echo esc_html(ucfirst($transaction->status)); ?></span></td>
                <td class="ff_td_payment_method">
                    <span class="ff_pay_method ff_pay_method_<?php echo esc_attr($transaction->payment_method); ?>">
                        <?php echo esc_html(ucfirst(apply_filters('fluentform/payment_method_public_name_' . $transaction->payment_method, $transaction->payment_method))); ?>
                    </span>
                </td>
                <td class="ff_td_date"><?php echo esc_html($transaction->formatted_date); ?></td>
                <td class="ff_td_action">
                    <a class="ff_pat_action_view" href="<?php echo esc_url($transaction->view_url) ?>"><?php echo esc_html($config['view_text']); ?></a>
                    <?php
                        do_action_deprecated(
                            'fluentform_transactions_actions',
                            [
                                $transactions
                            ],
                            FLUENTFORM_FRAMEWORK_UPGRADE,
                            'fluentform/transactions_actions',
                            'Use fluentform/transactions_actions instead of fluentform_transactions_actions.'
                        );
                        do_action('fluentform/transactions_actions', $transaction);
                    ?>
                </td>
                <?php
                    do_action_deprecated(
                        'fluentform_transaction_table_tbody_row',
                        [
                            $transactions
                        ],
                        FLUENTFORM_FRAMEWORK_UPGRADE,
                        'fluentform/transaction_table_tbody_row',
                        'Use fluentform/transaction_table_tbody_row instead of fluentform_transaction_table_tbody_row.'
                    );
                    do_action('fluentform/transaction_table_tbody_row', $transaction, $transactions);
                ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <?php
            do_action_deprecated(
                'fluentform_transactions_before_table_close',
                [
                    $transactions
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/transactions_before_table_close',
                'Use fluentform/transactions_before_table_close instead of fluentform_transactions_before_table_close.'
            );
            do_action('fluentform/transactions_before_table_close', $transactions); ?>
    </table>
    <?php
        do_action_deprecated(
            'fluentform_transactions_after_table',
            [
                $transactions
            ],
            FLUENTFORM_FRAMEWORK_UPGRADE,
            'fluentform/transactions_after_table',
            'Use fluentform/transactions_after_table instead of fluentform_transactions_after_table.'
        );
        do_action('fluentform/transactions_after_table', $transactions);
    ?>
</div>
