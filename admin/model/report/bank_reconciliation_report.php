<?php

class ModelReportBankReconciliationReport extends HModel{

    protected function getTable()
    {
        return 'vw_ledger';

    }

    public function getUnclearedLedgerReport($filter) {
        $sql = "SELECT 1 as sort_order, l.coa_id, l.account as display_name";
        $sql .= ", '' AS document_type_id, '' AS document_id";
        $sql .= ", '".$filter['date_from']."' AS document_date, 'OPENING' AS document_identity, '' as remarks, '".$filter['date_from']."' AS created_at";
        $sql .= ", SUM(debit) AS debit, SUM(credit) AS credit";
        $sql .= " FROM vw_core_ledger l";
        $sql .= " INNER JOIN vw_gl0_coa_all coa ON coa.coa_level3_id = l.coa_id AND coa.company_id = l.company_id ";
        $sql .= " WHERE l.company_id = '".$filter['company_id']."'";
        $sql .= " AND l.company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND l.fiscal_year_id = '".$filter['fiscal_year_id']."'";
        $sql .= " AND l.document_date >= '".$filter['session_from']."' AND l.document_date < '".$filter['date_from']."'";
       // $sql .= " AND l.clearing_date IS NULL";
        if($filter['coa_level1_id']) {
            $sql .= " AND coa.coa_level1_id = '".$filter['coa_level1_id']."'";
        }
        if($filter['coa_level2_id']) {
            $sql .= " AND coa.coa_level2_id = '".$filter['coa_level2_id']."'";
        }
        if($filter['coa_level3_id']) {
            $sql .= " AND l.coa_id = '".$filter['coa_level3_id']."'";
        }
        $sql .= " GROUP BY display_name, coa_id";
        $sql .= " UNION All";
        $sql .= " SELECT 2 as sort_order, l.coa_id, l.account as display_name, l.document_type_id, l.document_id, l.document_date, l.document_identity, l.remarks, l.created_at, debit, credit";
        $sql .= " FROM vw_core_ledger l";
        $sql .= " INNER JOIN vw_gl0_coa_all coa ON coa.coa_level3_id = l.coa_id AND coa.company_id = l.company_id ";
        $sql .= " WHERE l.company_id = '".$filter['company_id']."'";
        $sql .= " AND l.company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND l.fiscal_year_id = '".$filter['fiscal_year_id']."'";
        $sql .= " AND l.document_date >= '".$filter['date_from']."' AND l.document_date <= '".$filter['date_to']."'";
       // $sql .= " AND l.clearing_date IS NULL";
        if($filter['coa_level1_id']) {
            $sql .= " AND coa.coa_level1_id = '".$filter['coa_level1_id']."'";
        }
        if($filter['coa_level2_id']) {
            $sql .= " AND coa.coa_level2_id = '".$filter['coa_level2_id']."'";
        }
        if($filter['coa_level3_id']) {
            $sql .= " AND l.coa_id = '".$filter['coa_level3_id']."'";
        }
        $sql .= " ORDER BY display_name, coa_id, sort_order, document_date, created_at ,document_identity";

        $query = $this->conn->query($sql);
        $rows = $query->rows;
        //d(array($sql, $rows),true);
        return $rows;
    }

    public function getClearedLedgerReport($filter) {
        $sql = "SELECT 1 as sort_order, l.coa_id, l.account as display_name";
        $sql .= ", '' AS document_type_id, '' AS document_id";
        $sql .= ", '".$filter['date_from']."' AS document_date, 'OPENING' AS document_identity, '' as remarks, '".$filter['date_from']."' AS created_at";
        $sql .= ", SUM(debit) AS debit, SUM(credit) AS credit";
        $sql .= " FROM vw_core_ledger l";
        $sql .= " INNER JOIN vw_gl0_coa_all coa ON coa.coa_level3_id = l.coa_id AND coa.company_id = l.company_id ";
        $sql .= " WHERE l.company_id = '".$filter['company_id']."'";
        $sql .= " AND l.company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND l.fiscal_year_id = '".$filter['fiscal_year_id']."'";
        $sql .= " AND l.document_date >= '".$filter['session_from']."' AND l.document_date < '".$filter['date_from']."'";
        //$sql .= " AND l.clearing_date IS NOT NULL";
        if($filter['coa_level1_id']) {
            $sql .= " AND coa.coa_level1_id = '".$filter['coa_level1_id']."'";
        }
        if($filter['coa_level2_id']) {
            $sql .= " AND coa.coa_level2_id = '".$filter['coa_level2_id']."'";
        }
        if($filter['coa_level3_id']) {
            $sql .= " AND l.coa_id = '".$filter['coa_level3_id']."'";
        }
        $sql .= " GROUP BY display_name, coa_id";
        $sql .= " UNION All";
        $sql .= " SELECT 2 as sort_order, l.coa_id, l.account as display_name, l.document_type_id, l.document_id, l.document_date, l.document_identity, l.remarks, l.created_at, debit, credit";
        $sql .= " FROM vw_core_ledger l";
        $sql .= " INNER JOIN vw_gl0_coa_all coa ON coa.coa_level3_id = l.coa_id AND coa.company_id = l.company_id ";
        $sql .= " WHERE l.company_id = '".$filter['company_id']."'";
        $sql .= " AND l.company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND l.fiscal_year_id = '".$filter['fiscal_year_id']."'";
        $sql .= " AND l.document_date >= '".$filter['date_from']."' AND l.document_date <= '".$filter['date_to']."'";
        //$sql .= " AND l.clearing_date IS NOT NULL";
        if($filter['coa_level1_id']) {
            $sql .= " AND coa.coa_level1_id = '".$filter['coa_level1_id']."'";
        }
        if($filter['coa_level2_id']) {
            $sql .= " AND coa.coa_level2_id = '".$filter['coa_level2_id']."'";
        }
        if($filter['coa_level3_id']) {
            $sql .= " AND l.coa_id = '".$filter['coa_level3_id']."'";
        }
        $sql .= " ORDER BY display_name, coa_id, sort_order, document_date, created_at ,document_identity";

        $query = $this->conn->query($sql);
        $rows = $query->rows;
        //d(array($sql, $rows),true);
        return $rows;
    }

}

?>