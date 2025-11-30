<?php

class ModelReportSaleProfit extends HModel {

    protected function getTable() {
        return 'vw_sale_invoice';
    }

    public function getWarehouseSales($filter=array(), $sort_order=array()) {
        $sql = "SELECT warehouse_id, warehouse, product_category_id, product_category, product_id, product_code, product, unit_id, unit, sum(qty) as qty, sum(amount) as amount, round(sum(amount)/sum(qty),2) as rate";
        $sql .= " FROM `" . DB_PREFIX . $this->getTable() . "`";
        if($filter) {
            if(is_array($filter)) {
                $table_columns = $this->getTableColumns($this->getTable());
                $implode = array();
                foreach($filter as $column => $value) {
                    if(in_array($column,$table_columns)) {
                        $implode[] = "`$column`='$value'";
                    }
                }
                if($implode)
                    $sql .= " WHERE " . implode(" AND ", $implode);
            } else {
                $sql .= " WHERE " . $filter;
            }
        }
        $sql .= "GROUP BY warehouse_id, warehouse, product_category_id, product_category, product_id, product_code, product, unit_id, unit";

        if($sort_order) {
            $sql .= " ORDER BY " . implode(',',$sort_order);
        }

        $query = $this->conn->query($sql);
//d($query,$sql,true);
        return $query->rows;
    }



}

?>