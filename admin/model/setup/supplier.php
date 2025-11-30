<?php

class ModelSetupSupplier extends HModel {

    protected function getTable() {
        return 'core_supplier';
    }

    protected function getView() {
        return 'vw_core_supplier';
    }

    public function getTotalSupplier() {
        $sql = "SELECT COUNT(supplier_id) total_supplier  FROM `core_supplier`";

        $query = $this->conn->query($sql);
        return $query->row;
    }

}

?>