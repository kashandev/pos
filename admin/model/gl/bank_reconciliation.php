<?php

class ModelGLBankReconciliation extends HModel {

    protected function getTable() {
        return 'glt_bank_reconciliation';
    }

    protected function getView() {
        return 'vw_bank_reconciliation';
    }

    public function deleteLedger($filter){
        $sql = "DELETE FROM";
        $sql .= " core_ledger";
        $sql .= " WHERE company_id = '".$filter['company_id']."'";
        $sql .= " AND company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND fiscal_year_id = '".$filter['fiscal_year_id']."'";
        $sql .= " AND document_date >= '".$filter['date_from']."'";
        $sql .= " AND document_date <= '".$filter['date_to']."'";
        $sql .= " AND coa_id = '".$filter['coa_level3_id']."'";
        $query = $this->conn->query($sql);

    }

    public function getLedgerReport($filter) {
        $sql = "SELECT 1 as sort_order, l.coa_id, l.account as display_name";
        $sql .= ", '' AS document_type_id, '' AS document_id";
        $sql .= ", '".$filter['date_from']."' AS document_date, 'OPENING' AS document_identity, '' as remarks, '".$filter['date_from']."' AS created_at";
        $sql .= ", SUM(debit) AS debit, SUM(credit) AS credit, l.cheque_no, l.cheque_date,l.qty,l.amount,l.document_amount,l.ref_document_type_id,l.ref_document_identity,l.base_currency_id,l.document_currency_id,l.conversion_rate,l.product_id";
        $sql .= " FROM vw_core_ledger l";
        $sql .= " INNER JOIN vw_gl0_coa_all coa ON coa.coa_level3_id = l.coa_id AND coa.company_id = l.company_id ";
        $sql .= " WHERE l.company_id = '".$filter['company_id']."'";
        $sql .= " AND l.company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND l.fiscal_year_id = '".$filter['fiscal_year_id']."'";
        $sql .= " AND l.document_date >= '".$filter['session_from']."' AND l.document_date < '".$filter['date_from']."'";
        // $sql .= " AND l.clearing_date IS NULL";
        if($filter['coa_level3_id']) {
            $sql .= " AND l.coa_id = '".$filter['coa_level3_id']."'";
        }
        $sql .= " GROUP BY display_name, coa_id";
        $sql .= " UNION All";
        $sql .= " SELECT 2 as sort_order, l.coa_id, l.account as display_name, l.document_type_id, l.document_id, DATE_FORMAT(l.document_date,'%d-%m-%Y') AS document_date, l.document_identity, l.remarks, l.created_at, l.debit, l.credit, l.cheque_no, l.cheque_date,l.qty,l.amount,l.document_amount,l.ref_document_type_id,l.ref_document_identity,l.base_currency_id,l.document_currency_id,l.conversion_rate,l.product_id";
        $sql .= " FROM vw_core_ledger l";
        $sql .= " INNER JOIN vw_gl0_coa_all coa ON coa.coa_level3_id = l.coa_id AND coa.company_id = l.company_id ";
        $sql .= " WHERE l.company_id = '".$filter['company_id']."'";
        $sql .= " AND l.company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND l.fiscal_year_id = '".$filter['fiscal_year_id']."'";
        $sql .= " AND l.document_date >= '".$filter['date_from']."' AND l.document_date <= '".$filter['date_to']."'";
        // $sql .= " AND l.clearing_date IS NULL";
        if($filter['coa_level3_id']) {
            $sql .= " AND l.coa_id = '".$filter['coa_level3_id']."'";
        }
        $sql .= " ORDER BY created_at ASC";

        $query = $this->conn->query($sql);
        $rows = $query->rows;
        //d($sql,true);
        return $rows;
    }


}

?>