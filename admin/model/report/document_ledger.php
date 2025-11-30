<?php

class ModelReportDocumentLedger extends HModel{

    protected function getTable()
    {
        return 'vw_ledger';

    }

    public function getPartyLedger($filter = array(),$sort_order = array()) {
        $sql = "SELECT l.company_id, l.company_branch_id, l.fiscal_year_id, l.partner_type_id, p.partner_type, l.partner_id, p.name as partner_name";
        $sql .= ", l.document_date, l.document_identity, l.ref_document_identity, coa_id, account, debit, credit, l.remarks";
        $sql .= " FROM vw_ledger l";
        $sql .= " INNER JOIN `partner` p ON p.`company_id` = l.`company_id` AND p.`partner_type_id` = l.`partner_type_id` AND p.`partner_id` = l.`partner_id` AND l.`coa_id` IN (p.`outstanding_account_id`, p.`advance_account_id`)";
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

    public function getPartyOpening($filter = array(),$sort_order = array()) {
        $sql = "SELECT l.company_id, l.company_branch_id, l.fiscal_year_id, l.partner_type_id, p.partner_type, l.partner_id, p.name as partner_name";
        $sql .= ", '' as document_date, '' as document_identity, '' as ref_document_identity, '' as coa_id, '' as account, 'Previous Balance' as remarks";
        $sql .= ", CASE WHEN SUM(debit - credit) > 0 THEN SUM(debit - credit) ELSE 0 END as Debit";
        $sql .= ", CASE WHEN SUM(debit - credit) < 0 THEN SUM(credit - debit) ELSE 0 END as Credit";
        $sql .= " FROM vw_ledger l";
        $sql .= " INNER JOIN `partner` p ON p.`company_id` = l.`company_id` AND p.`partner_type_id` = l.`partner_type_id` AND p.`partner_id` = l.`partner_id` AND l.`coa_id` IN (p.`outstanding_account_id`, p.`advance_account_id`)";
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
        $sql .= " GROUP BY l.company_id, l.company_branch_id, l.fiscal_year_id, l.partner_type_id, p.partner_type, l.partner_id, p.name";
        if($sort_order) {
            $sql .= " ORDER BY " . implode(',',$sort_order);
        }

        $query = $this->conn->query($sql);
        $rows = $query->rows;
        return $rows;
    }

}

?>