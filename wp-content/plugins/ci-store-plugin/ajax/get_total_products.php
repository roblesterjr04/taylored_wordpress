<?php

namespace AjaxHandlers;

include_once WP_PLUGIN_DIR . '/ci-store-plugin/suppliers/get_supplier.php';
include_once WP_PLUGIN_DIR . '/ci-store-plugin/utils/AjaxManager.php';

function get_total_products($params)
{
    $supplier_key = \AjaxManager::get_param('supplier_key', null, $params);
    if (!$supplier_key) {
        return ['error' => 'missing supplier'];
    }

    $supplier = \CI\Admin\get_supplier($supplier_key);
    if (!$supplier) {
        return ['error' => 'supplier not found', 'supplier_key' => $supplier_key];
    }

    return ['data'=>$supplier->get_total_products()];
}
