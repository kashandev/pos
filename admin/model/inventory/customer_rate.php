<?php

class ModelInventoryCustomerRate extends HModel{

    protected function getTable() {
        return 'in0_product_customer_rate';
    }

    public function GetCustomerLastRate($customerID, $productID) {
        $sql  = "  SELECT rate from `in0_product_customer_rate`  ";
        $sql .= " WHERE customer_id ='" . $customerID . "' AND  product_id ='" . $productID . "'";
        $sql .= " ORDER BY product_id,created_at desc LIMIT 1";
        $query = $this->conn->query($sql);

        return $query->row;
    }

}