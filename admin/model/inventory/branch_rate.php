<?php

class ModelInventoryBranchRate extends HModel{

    protected function getTable() {
        return 'in0_product_branch_rate';
    }

    public function GetBranchLastRate($branchID, $productID) {
        $sql  = "  SELECT rate from `in0_product_branch_rate`  ";
        $sql .= " WHERE branch_id ='" . $branchID . "' AND  product_id ='" . $productID . "'";
        $sql .= " ORDER BY product_id,created_at desc LIMIT 1";
        $query = $this->conn->query($sql);

        return $query->row;
    }

}