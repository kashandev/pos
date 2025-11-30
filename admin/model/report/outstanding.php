<?php

class ModelReportOutstanding extends HModel {

    protected function getTable() {
        return 'vw_people_outstanding';
    }

    public function getWarehouseStocks($filter=array(), $sort_order=array()) {
        $sql = "SELECT warehouse_id, product_id, product_code, unit_id,conversion_rate, sum(qty) as qty, sum(amount) as amount, round(sum(amount)/sum(qty),2) as rate";
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
        $sql .= "GROUP BY warehouse_id, product_id, product_code, unit_id";

        if($sort_order) {
            $sql .= " ORDER BY " . implode(',',$sort_order);
        }

        $query = $this->conn->query($sql);

        return $query->rows;
    }

    public function getOutstanding($filter=array(), $sort_order=array()) {
        $sql = "SELECT l.`people_type_id`, p.`people_type`, l.`people_id`, p.`people_name`, d.`document_type_id`, d.`document_date`, d.`document_identity`, d.`base_amount`, SUM(l.debit-l.credit) AS outstanding" . "\n";
        $sql .= " FROM `vw_ledger` l" . "\n";
        $sql .= " INNER JOIN `vw_document` d ON d.company_id = l.company_id AND d.company_branch_id = l.company_branch_id AND d.fiscal_year_id = l.fiscal_year_id";
        $sql .= " AND d.`document_type_id` = l.`ref_document_type_id` AND d.`document_identity` = l.`ref_document_identity`" . "\n";
        $sql .= " AND d.document_type_id NOT IN (16,17)" . "\n";
        $sql .= " INNER JOIN `vw_people` p ON p.`people_type_id` = l.`people_type_id` AND p.`people_id` = l.`people_id` AND (l.coa_id = p.outstanding_account_id OR l.coa_id = p.advance_account_id)" . "\n";
        $sql .= " WHERE TRUE" . "\n";
        $sql .= " AND l.company_id = '".$filter['company_id']."'" . "\n";
        $sql .= " AND l.company_branch_id = '".$filter['company_branch_id']."'" . "\n";
        $sql .= " AND l.fiscal_year_id = '".$filter['fiscal_year_id']."'" . "\n";
        if(isset($filter['people_type_id'])) {
            $sql .= " AND l.people_type_id = '".$filter['people_type_id']."'" . "\n";
        }
        if(isset($filter['people_id'])) {
            $sql .= " AND l.people_id = '".$filter['people_id']."'" . "\n";
        }
        $sql .= " GROUP BY l.`people_type_id`, p.`people_type`, l.`people_id`, p.`people_name`, d.`document_type_id`, d.`document_date`, d.`document_identity`, d.`base_amount`" . "\n";
        $sql .= " HAVING outstanding != 0" . "\n";
        $sql .= " ORDER BY p.people_type,p.people_name,d.document_date,d.document_identity" . "\n";

        $query = $this->conn->query($sql);
        //d(array($sql, $query),true);
        return $query->rows;
    }

    public function getOutstandingSummary($filter = array()) {

        $sql = "";
        $sql .= " SELECT p.`people_type_id`, p.`people_type`, p.`people_id`, p.`people_name`, SUM(debit)-SUM(credit)  AS outstanding";
        $sql .= " FROM vw_ledger l";
        $sql .= " INNER JOIN vw_people p ON l.people_type_id = p.people_type_id AND l.people_id = p.people_id";
        $sql .= " AND (l.coa_id = p.outstanding_account_id OR l.coa_id = p.advance_account_id)";
        $sql .= " WHERE TRUE";
        $sql .= " AND l.company_id = '".$filter['company_id']."'";
        $sql .= " AND l.company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND l.fiscal_year_id = '".$filter['fiscal_year_id']."'";
        if(isset($filter['people_type_id'])) {
            $sql .= " AND p.people_type_id = '".$filter['people_type_id']."'";
        }

        if(isset($filter['people_id'])) {
            $sql .= " AND p.people_id = '".$filter['people_id']."'";
        }
        $sql .= " GROUP BY p.`people_type_id`, p.`people_type`, p.`people_id`, p.`people_name`";
        $query = $this->conn->query($sql);

        //d(array($sql, $query),true);
        return $query->rows;
    }
}

?>