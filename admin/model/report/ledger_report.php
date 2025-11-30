<?php

class ModelReportLedgerReport extends HModel{

    protected function getTable()
    {
        return 'vw_ledger';

    }

    public function getReport($filter) {
        $sql = "SELECT 1 as sort_order, coa_id, display_name";
        $sql .= ", '' AS document_type_id, '' AS document_id";
        $sql .= ", '".$filter['date_from']."' AS document_date, 'OPENING' AS document_identity";
        $sql .= ", SUM(debit) AS debit, SUM(credit) AS credit";
        $sql .= " FROM vw_ledger";
        $sql .= " WHERE company_id = '".$filter['company_id']."'";
        $sql .= " AND company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND fiscal_year_id = '".$filter['fiscal_year_id']."'";
        $sql .= " AND document_date >= '".$filter['session_from']."' AND document_date < '".$filter['date_from']."'";
        if($filter['coa_id']) {
            $sql .= " AND coa_id = '".$filter['coa_id']."'";
        }
        $sql .= " GROUP BY coa_id, display_name";
        $sql .= " UNION";
        $sql .= " SELECT 2 as sort_order, coa_id, display_name, document_type_id, document_id, document_date, document_identity,  debit, credit";
        $sql .= " FROM vw_ledger";
        $sql .= " WHERE company_id = '".$filter['company_id']."'";
        $sql .= " AND company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND fiscal_year_id = '".$filter['fiscal_year_id']."'";
        $sql .= " AND document_date >= '".$filter['date_from']."' AND document_date <= '".$filter['date_to']."'";
        if($filter['coa_id']) {
            $sql .= " AND coa_id = '".$filter['coa_id']."'";
        }
        $sql .= " ORDER BY coa_id, sort_order, document_date";

        $query = $this->conn->query($sql);
        $rows = $query->rows;
//        d(array($sql, $rows),true);
        return $rows;
    }
}

?>