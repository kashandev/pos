<?php

class ModelInventorySaleTaxInvoiceDetail extends HModel {

    protected function getTable() {
        return 'ins_sale_tax_invoice_detail';
    }

    protected function getView() {
        return 'vw_ins_sale_tax_invoice_detail';
    }

    public function get_sale_tax_invoice_details($filter=array(), $sort_order=array()){
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
        return $query->rows;
    }

}

?>