<?php

class ModelReportReceipt extends HModel {

    public function getLists($data) {
        $filterSQL = $this->getFilterString($data['filter']);
        $criteriaSQL = $this->getCriteriaString($data['criteria']);
        
        $sql = "SELECT count(*) as total";
        $sql .= " FROM " . DB_PREFIX . 'vw_receipt_payments';
        if($filterSQL) {
            $sql .= " WHERE " . $filterSQL;
        }
        
        $query = $this->conn->query($sql);
        $total = $query->row['total'];
        
        $sql = "SELECT *";
        $sql .= " FROM " . DB_PREFIX . 'vw_receipt_payments';
        if($filterSQL) {
            $sql .= " WHERE " . $filterSQL;
        }
        if($criteriaSQL) {
            $sql .= $criteriaSQL;
        }
        
        $query = $this->conn->query($sql);
        $lists = $query->rows;
        
        return array('total' => $total, 'lists' => $lists);
        
    }

}

?>