<?php

class ModelReportPartyLedger extends HModel{

    protected function getTable()
    {
        return 'vw_ledger';
    }

    public function getPartyOpening($filter = array(),$sort_order = array()) {
        $sql = "SELECT l.company_id, l.company_branch_id, l.fiscal_year_id, l.partner_type_id, p.partner_type, l.partner_id, p.name as partner_name";
        $sql .= ", '' as document_date, '' as document_identity, '' as ref_document_identity, coa_id, account, 'Previous Balance' as remarks";
        $sql .= ", CASE WHEN SUM(debit - credit) > 0 THEN SUM(debit - credit) ELSE 0 END as debit";
        $sql .= ", CASE WHEN SUM(debit - credit) < 0 THEN SUM(credit - debit) ELSE 0 END as credit";
        $sql .= " FROM vw_core_ledger l";
        $sql .= " INNER JOIN `core_partner` p ON p.`company_id` = l.`company_id` AND p.`partner_type_id` = l.`partner_type_id` AND p.`partner_id` = l.`partner_id` AND l.`coa_id` IN (p.`outstanding_account_id`, p.`advance_account_id`)";
        if($filter) {
            if(is_array($filter)) {
                //$table_columns = $this->getTableColumns($this->getTable());
                $implode = array();
                foreach($filter as $column => $value) {
                    //if(in_array($column,$table_columns)) {
                    $implode[] = "`$column`='$value'";
                    //}
                }
                if($implode)
                    $sql .= " WHERE " . implode(" AND ", $implode);
            } else {
                $sql .= " WHERE " . $filter;
            }
        }
        $sql .= " GROUP BY l.company_id, l.company_branch_id, l.fiscal_year_id, l.partner_type_id, p.partner_type, l.partner_id, p.name, coa_id";
        if($sort_order) {
            $sql .= " ORDER BY " . implode(',',$sort_order);
        }

        $query = $this->conn->query($sql);
        $rows = $query->rows;
        return $rows;
    }

    public function getPartyLedger($filter = array(),$sort_order = array()) {
        $sql  = "SELECT l.company_id, l.company_branch_id, l.fiscal_year_id, l.partner_type_id, p.partner_type, l.partner_id, p.name as partner_name";
        $sql .= ", l.document_date, l.document_identity, l.ref_document_identity, coa_id, account, debit, credit, l.remarks,l.po_no";
        $sql .= " FROM vw_core_ledger l";
        $sql .= " INNER JOIN `core_partner` p ON p.`company_id` = l.`company_id` AND p.`partner_type_id` = l.`partner_type_id` AND p.`partner_id` = l.`partner_id` AND l.`coa_id` IN (p.`outstanding_account_id`, p.`advance_account_id`)";
        if($filter) {
            if(is_array($filter)) {
                //$table_columns = $this->getTableColumns($this->getTable());
                $implode = array();
                foreach($filter as $column => $value) {
                    //if(in_array($column,$table_columns)) {
                    $implode[] = "`$column`='$value'";
                    //}
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
//        d($query,true);
        $rows = $query->rows;

        return $rows;
    }

    public function getPartySummary($filter = array()) {
        $sql = "SELECT l.partner_type, l.partner_name";
        $sql .= " , SUM(IF(`document_date` < '".$filter['from_date']."', (`Debit` - `Credit`), 0)) AS previous";
        $sql .= " , SUM(IF(`document_date` >= '".$filter['from_date']."', `Debit`, 0)) AS debit";
        $sql .= " , SUM(IF(`document_date` >= '".$filter['from_date']."', `Credit`, 0)) AS credit";
        $sql .= " FROM `vw_core_ledger` l";
        $sql .= " INNER JOIN `core_partner` p ON p.`partner_id` = l.`partner_id` AND (p.`outstanding_account_id` = l.`coa_id` OR p.`advance_account_id` = l.`coa_id`)";
        $sql .= " WHERE l.`company_id` = '".$filter['company_id']."'";
        $sql .= " AND l.`company_branch_id` = '".$filter['company_branch_id']."'";
        $sql .= " AND l.`fiscal_year_id` = '".$filter['fiscal_year_id']."'";
        $sql .= " AND `document_date` <= '".$filter['to_date']."'";
        if($filter['partner_type_id'] != '') {
            $sql .= " AND l.`partner_type_id` = '".$filter['partner_type_id']."'";
        }
        if($filter['partner_id'] != '') {
            $sql .= " AND l.`partner_id` = '".$filter['partner_id']."'";
        }
        $sql .= " GROUP BY l.`partner_type`, l.`partner_name`";
        $sql .= " ORDER BY l.`partner_type`, l.`partner_name`";

        $query = $this->conn->query($sql);
        $rows = $query->rows;
        return $rows;
    }

    public function getOutstanding($filter = array(),$sort_order = array()) {
        $sql  = "SELECT l.company_id, l.company_branch_id, l.fiscal_year_id, l.partner_type_id, p.partner_type";
        $sql .= " , l.partner_id, p.name AS partner_name, l.document_date, account,SUM(debit- credit) outstanding";
        $sql .= " FROM vw_core_ledger l";
        $sql .= " INNER JOIN `vw_core_partner` p ON p.`company_id` = l.`company_id`  AND p.`partner_type_id` = 2 AND p.`partner_id` = l.`partner_id` AND l.`coa_id` IN (p.`outstanding_account_id`, p.`advance_account_id`)";
        if($filter) {
            if(is_array($filter)) {
                //$table_columns = $this->getTableColumns($this->getTable());
                $implode = array();
                foreach($filter as $column => $value) {
                    //if(in_array($column,$table_columns)) {
                    $implode[] = "`$column`='$value'";
                    //}
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
        $rows = $query->rows;
        return $rows;
    }

}

?>