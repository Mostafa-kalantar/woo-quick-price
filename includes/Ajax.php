<?php

class WQP_Ajax
{
    public function __construct()
    {
        add_action('wp_ajax_wqp_save_product', [$this, 'save']);
    }

    public function save()
    {
        check_ajax_referer('wqp_nonce', 'nonce');

        $id = intval($_POST['id']);
        $regular = $_POST['regular'];
        $sale = $_POST['sale'];
        $stock = $_POST['stock'];

        $product = wc_get_product($id);

        if (!$product) {
            wp_send_json_error('Invalid product');
        }

        $product->set_regular_price($regular);
        $product->set_sale_price($sale);

        $product->set_manage_stock(true);
        $product->set_stock_quantity($stock);

        $product->save();

        wp_send_json_success('Saved');
    }
}