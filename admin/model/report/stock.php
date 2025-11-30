<?php

class ModelReportStock extends HModel {
    // Please Use model/common/stock instead.

//    protected function getTable() {
//        return 'vw_stock_ledger';
//    }
//
//    public function getWarehouseStocks($filter=array(), $sort_order=array()) {
//        $sql = "SELECT warehouse_id, product_id, product_code, unit_id,conversion_rate, sum(qty) as qty, sum(amount) as amount, round(sum(amount)/sum(qty),2) as rate";
//        $sql .= " FROM `" . DB_PREFIX . $this->getTable() . "`";
//        if($filter) {
//            if(is_array($filter)) {
//                $table_columns = $this->getTableColumns($this->getTable());
//                $implode = array();
//                foreach($filter as $column => $value) {
//                    if(in_array($column,$table_columns)) {
//                        $implode[] = "`$column`='$value'";
//                    }
//                }
//                if($implode)
//                    $sql .= " WHERE " . implode(" AND ", $implode);
//            } else {
//                $sql .= " WHERE " . $filter;
//            }
//        }
//        $sql .= "GROUP BY warehouse_id, product_id, product_code, unit_id";
//
//        if($sort_order) {
//            $sql .= " ORDER BY " . implode(',',$sort_order);
//        }
//
//        $query = $this->conn->query($sql);
//
//        return $query->rows;
//    }
//
//    public function getStockLedger($filter=array(), $sort_order=array()) {
//        $sql = " SELECT 0 AS sort_order, warehouse_id, sl.product_id,p.name AS product_name,p.product_code, sl.unit_id ,conversion_rate";
//        $sql .= " , '' AS document_type_id, '' AS document_id";
//        $sql .= " , 'OPENING' AS document_identity, '".$filter['from_date']."' AS document_date";
//        $sql .= " , SUM(qty) AS qty";
//        $sql .= " , SUM(amount) AS amount";
//        $sql .= " FROM vw_stock_ledger sl";
//        $sql .= " INNER JOIN product p  ON p.product_id = sl.product_id ";
//        $sql .= " WHERE sl.company_id ='".$filter['company_id']."' AND sl.company_branch_id='".$filter['company_branch_id']."' AND sl.fiscal_year_id='".$filter['fiscal_year_id']."'";
//        $sql .= " AND document_date >= '".$filter['fiscal_from_date']."' AND document_date < '".$filter['from_date']."' ";
//        if(isset($filter['product_service'])) {
//            $sql .= "";
//        }
//        else {
//            $sql .= " AND sl.product_service = '".$filter['product_service']."'";
//
//        }
//        if(isset($filter['product_id'])) {
//            $sql .= " AND sl.product_id = '".$filter['product_id']."'";
//        }
//        if(isset($filter['warehouse_id'])) {
//            $sql .= " AND warehouse_id = '".$filter['warehouse_id']."'";
//        }
//        $sql .= " GROUP BY warehouse_id, sl.product_id";
//        $sql .= " UNION ALL";
//        $sql .= " SELECT 1 AS sort_order, warehouse_id, sl.product_id,p.name AS product_name,p.product_code, sl.unit_id ,conversion_rate";
//        $sql .= " , document_type_id, document_id";
//        $sql .= " , document_identity, document_date";
//        $sql .= " , qty";
//        $sql .= " , amount";
//        $sql .= " FROM vw_stock_ledger sl";
//        $sql .= " INNER JOIN product p  ON p.product_id = sl.product_id ";
//        $sql .= " WHERE sl.company_id ='".$filter['company_id']."' AND sl.company_branch_id='".$filter['company_branch_id']."' AND sl.fiscal_year_id='".$filter['fiscal_year_id']."'";
//        $sql .= " AND document_date >= '".$filter['from_date']."' AND document_date <= '".$filter['to_date']."' ";
//        if(isset($filter['product_service'])) {
//            $sql .= "";
//        }
//        else {
//            $sql .= " AND sl.product_service = '".$filter['product_service']."'";
//
//        }
//        if(isset($filter['product_id'])) {
//            $sql .= " AND sl.product_id = '".$filter['product_id']."'";
//        }
//        if(isset($filter['warehouse_id'])) {
//            $sql .= " AND warehouse_id = '".$filter['warehouse_id']."'";
//        }
//        $sql .= " ORDER BY product_code,product_name,document_date,sort_order,document_identity ";
//
//        $query = $this->conn->query($sql);
////        d(array($query,$sql),true);
//        return $query->rows;
//    }
//
//    public function getStockQty($filter=array(), $sort_order=array()) {
//
//    }
}

?>