<?php

defined('ABSPATH') or die;

/** @var object $transaction */
/** @var string $transactionTotal */
/** @var array $items */
/** @var array $discountItems */
/** @var string $subTotal */
/** @var string $orderTotal */
?>
<div class="ff_payment_transaction">
    <?php
    // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Content is escaped in the loaded view files
    echo \FluentForm\App\Modules\Payments\PaymentHelper::loadView('transaction_info', [
        'transaction' => $transaction,
        'transactionTotal' => $transactionTotal
    ]);

    echo \FluentForm\App\Modules\Payments\PaymentHelper::loadView('order_items_table', [
        'items' => $items,
        'discount_items' => $discountItems,
        'subTotal' => $subTotal,
        'orderTotal' => $orderTotal
    ]);

    echo \FluentForm\App\Modules\Payments\PaymentHelper::loadView('customer_details', [
        'transaction' => $transaction
    ]);

    echo \FluentForm\App\Modules\Payments\PaymentHelper::loadView('custom_css', []);
    // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

    ?>
</div>
