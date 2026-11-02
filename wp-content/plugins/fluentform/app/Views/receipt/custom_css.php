<?php

defined('ABSPATH') or die;

// phpcs:ignore Internal.NoCodeFound -- View file contains CSS, not PHP code
?>
<style type="text/css">
    .ffp_table {
        empty-cells: show;
        font-size: 14px;
        border-collapse: collapse;
        border: 1px solid #cbcbcb;
        width: 100%;
    }
    .ffp_table td,.ffp_table th {
        border-left: 1px solid #cbcbcb;
        font-size: inherit;
        margin: 0;
        overflow: visible;
        padding: .5em 1em;
        text-align: left;
    }
    .ffp_table td:first-child,.ffp_table th:first-child {
        border-left-width: 0
    }

    .ffp_order_items_table tr {
        border: 1px solid #cbcbcb;
    }

    .ffp_table thead {
        background-color: #e3e8ee;
        color: #000;
        text-align: left;
        vertical-align: bottom
    }

    .ffp_table td {
        background-color: transparent
    }

    .ffp_table tfoot {
        border-top: 1px solid #cbcbcb;
    }

    table.input_items_table {
        border-collapse: collapse;
    }

    table.input_items_table tr td, table.input_items_table tr th {
        border: 1px solid #cbcbcb;
        text-align: left;
        width: auto;
        word-break: normal;
    }
    table.input_items_table tr th {
        min-width: 20%;
    }

    .ffp_payment_info {
        width: 100%;
        -webkit-box-shadow: 0px -2px #e3e8ee;
        box-shadow: 0px -2px #e3e8ee;
        background-color: rgb(247, 250, 252);
        color: rgb(56, 56, 56);
        margin-bottom: 20px;
    }
    .ffp_payment_info_item {
        display: inline-block;
        margin-right: 0px;
        -webkit-box-shadow: inset -1px 0 #e3e8ee;
        box-shadow: inset -1px 0 #e3e8ee;
        padding: 12px;
    }
    .ffp_payment_info_item:last-child {
        box-shadow: none;
    }
    .ffp_payment_info_item .ffp_item_heading {
        font-size: 14px;
        font-weight: bold;
    }

    .ffp_payment_info_item .ffp_item_value {
        font-size: 14px;
    }

    .ff_payment_receipt h4 {
        font-size: 18px;
        margin: 0;
        padding-top: 15px;
        padding-bottom: 7px;
    }
</style>