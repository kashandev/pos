<?php

class ModelInventoryStockTransfer extends HModel {

    protected function getTable() {
        return 'ina_stock_transfer';
    }

    protected function getView() {
        return 'vw_ina_stock_transfer';
    }

     public function get_stock_transfer($filter=array(), $sort_order=array()){
        $sql = "SELECT *";
        $sql .= " FROM " . DB_PREFIX . $this->getTable();
        if($filter) {
            if(is_array($filter)) {
                $implode = array();
                foreach($filter as $column => $value) {
                    $implode[] = "`$column`='$value'";
                }
                if($implode)
                    $sql .= " WHERE " . implode(" AND ", $implode);
            } else {
                $sql .= " WHERE " . $filter;
            }
        }
        if($sort_order) {
            $sql .= " ORDER BY " . implode(',',$sort_order);
        }
        $query = $this->conn->query($sql);
        return $query->row;
    }

}

?>