<?php

class ModelInventoryProductSubCategory extends HModel {

    protected function getTable() {
        return 'in0_product_sub_category';
    }
    
//    public function validateName($name, $product_category_id) {
//        $sql = "SELECT * FROM `" . DB_PREFIX . $this->getTable() . "`";
//        $sql .= " WHERE LOWER(name)='" . $this->conn->escape(strtolower($name)) . "'";
//        if($product_category_id)
//            $sql .= " AND product_category_id != '" . $this->conn->escape($product_category_id) . "'";
//        $query = $this->conn->query($sql);
//
//        return $query->num_rows;
//    }

}

?>