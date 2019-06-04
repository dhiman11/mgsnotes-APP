<?php
defined('BASEPATH') or exit('No direct script access allowed');

include_once './application/libraries/Update_model.php';

class Product_update extends Update_model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function product_update($product_id, $data)
    {
        $this->admin_ctrl(null, 'products');
        return $this->update('products', 'product_id', $product_id, $data);
    }

    public function product_cat_update($product_id, $cid)
    {
        $this->admin_ctrl(null, 'products');
        return $this->update('products', 'product_id', $product_id, array(
            'product_cat_id' => $cid,
        ), null, true);
    }
}
