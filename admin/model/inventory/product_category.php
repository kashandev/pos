<?php

class ModelInventoryProductCategory extends HModel {

    protected function getTable() {
        return 'in0_product_category';
    }
    
    public function validateName($name, $product_category_id) {
        $sql = "SELECT * FROM `" . DB_PREFIX . $this->getTable() . "`";
        $sql .= " WHERE LOWER(name)='" . $this->conn->escape(strtolower($name)) . "'";
        if($product_category_id)
            $sql .= " AND product_category_id != '" . $this->conn->escape($product_category_id) . "'";
        $query = $this->conn->query($sql);

        return $query->num_rows;
    }

    public function getTotalProductCategory() {
        $sql = "SELECT COUNT(product_category_id) total_product_category  FROM `in0_product_category` ";

        $query = $this->conn->query($sql);
        return $query->row;
    }

}

?>