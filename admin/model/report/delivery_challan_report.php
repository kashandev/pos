<?php

class ModelReportDeliveryChallanReport extends HModel {

    protected function getTable() {
        return 'vw_delivery_challan_detail';
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

        return $query->rows;
    }

    public function getDeliveryChallan($filter=array(), $sort_order=array()) {

        $sql = " SELECT dc.voucher_date,dc.voucher_no,dc.customer_id,c.name AS customer_name,p.product_code,dcd.product_id,p.name AS product_name,dcd.qty,sid.sale_invoice_detail_id,si.invoice_no ";
        $sql .= " FROM delivery_challan dc ";
        $sql .= " INNER JOIN delivery_challan_detail dcd ON dcd.delivery_challan_id = dc.delivery_challan_id";
        $sql .= " INNER JOIN customer c ON c.customer_id = dc.customer_id";
        $sql .= " INNER JOIN product p ON p.product_id = dcd.product_id";
        $sql .= " LEFT JOIN sale_invoice_detail sid ON sid.ref_document_id = dc.delivery_challan_id AND sid.ref_document_type_id = dc.document_type_id ";
        $sql .= " LEFT JOIN sale_invoice si ON si.sale_invoice_id = sid.sale_invoice_id ";
        $sql .= " Where dc.company_id = '".$filter['company_id']."'";
        $sql .= " AND dc.company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND dc.fiscal_year_id = '".$filter['fiscal_year_id']."'";

        if(isset($filter['customer_id'])) {
            $sql .= " AND dc.customer_id = '".$filter['customer_id']."'";
        }

        if($filter['isPost'] == 1) {
            $sql .= " AND dc.is_post = '".$filter['isPost']."'";
        }
        elseif($filter['isPost']== 2) {
            $sql .= " AND dc.is_post IS NULL";
        }
        if($filter['status']== 16) {
            $sql .= " AND sid.ref_document_type_id = '".$filter['status']."'";
        }
        elseif($filter['status']== 2) {
            $sql .= " AND sid.ref_document_type_id IS NULL";
        }
        $sql .= " ORDER BY dc.voucher_date,dc.voucher_no";
        $query = $this->conn->query($sql);

//        d(array($sql, $query),true);
         return $query->rows;
    }




}

?>