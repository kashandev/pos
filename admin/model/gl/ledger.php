<?php

  class ModelGLLedger extends HModel {

    protected function getTable() {
        return 'core_ledger';
    }

    protected function getView() {
        return 'vw_core_ledger';
    }

    public function getDocumentLedger($document_type_id, $document_id) {
        $sql  = "SELECT account";
        $sql .= ", CASE WHEN sum(debit-credit) > 0 THEN sum(debit-credit) ELSE 0 END as debit";
        $sql .= ", CASE WHEN sum(debit-credit) < 0 THEN sum(credit-debit) ELSE 0 END as credit";
        $sql .= " FROM `vw_core_ledger`";
        $sql .= " WHERE `company_id` = '" . $this->session->data['company_id'] . "'";
        $sql .= " AND `company_branch_id` = '" . $this->session->data['company_branch_id'] . "'";
        $sql .= " AND `fiscal_year_id` = '" . $this->session->data['fiscal_year_id'] . "'";
        $sql .= " AND `document_type_id` = '" . $document_type_id . "'";
        $sql .= " AND `document_id` = '" . $document_id . "'";
        $sql .= " GROUP BY `account`";
        $sql .= " ORDER BY `debit` DESC,`credit`,`sort_order`";

        $query = $this->conn->query($sql);
        $rows = $query->rows;
        return $query;
    }

    public function getLedger($document_type_id, $document_id) {
        $sql  = "SELECT account, SUM(debit) as debit, SUM(credit) as credit";
        $sql .= " FROM `vw_core_ledger`";
        $sql .= " WHERE `company_id` = '" . $this->session->data['company_id'] . "'";
        $sql .= " AND `company_branch_id` = '" . $this->session->data['company_branch_id'] . "'";
        $sql .= " AND `fiscal_year_id` = '" . $this->session->data['fiscal_year_id'] . "'";
        $sql .= " AND `document_type_id` = '" . $document_type_id . "'";
        $sql .= " AND `document_id` = '" . $document_id . "'";
        $sql .= " GROUP BY `account`";
        $sql .= " ORDER BY `debit` DESC,`credit`,`sort_order`";

        $query = $this->conn->query($sql);
        $rows = $query->rows;
        return $query;
    }

    public function getOutStanding($filter=array(),$sort_order=array()) {
        // This Function is Used to get All OutStanding Documents of Particular Partner.
        //$sql ="SELECT l.company_id, l.fiscal_year_id, l.people_type_id, l.people_id, l.ref_document_type_id, l.ref_document_id, l.ref_document_identity, l.coa_id";
        $sql ="SELECT l.company_id, l.fiscal_year_id, l.people_type_id, l.people_id, l.ref_document_type_id, l.ref_document_identity" . "\n";
        $sql .= " , CASE WHEN l.people_type_id = 2 THEN SUM(l.debit-l.credit) ELSE SUM(l.credit-l.debit) END as outstanding" . "\n";
        $sql .= " FROM `" . DB_PREFIX . "core_ledger` l" . "\n";
        $sql .= " INNER JOIN `" . DB_PREFIX . "vw_people` p ON l.people_type_id = p.people_type_id AND l.people_id = p.people_id" . "\n";
        $sql .= " AND (l.coa_id = p.outstanding_account_id OR l.coa_id = p.advance_account_id)" . "\n";
        $sql .= " WHERE l.company_id = '".$filter['company_id']."'";
        $sql .= " AND l.company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND l.fiscal_year_id = '".$filter['fiscal_year_id']."'";
        $sql .= " AND l.people_type_id = '".$filter['people_type_id']."'";
        $sql .= " AND l.people_id = '".$filter['people_id']."'";
        if(isset($filter['document_id']) && $filter['document_id']) {
            $sql .= " AND l.document_id != '".$filter['document_id']."'";
        }
        $sql .= " GROUP BY l.company_id, l.fiscal_year_id, l.people_type_id, l.people_id, l.ref_document_type_id, l.ref_document_identity" . "\n";
        $sql .= " HAVING outstanding != 0 " . "\n";
        if($sort_order) {
            $sql .= " ORDER BY " . implode(',',$sort_order);
        }
        $query = $this->conn->query($sql);
//        d(array($sql, $query),true);

        return $query->rows;
    }

    public function getBranchOutstanding($branch_id,$coa_id,$document_id) {
        $sql  = " SELECT sum(debit-credit) outstanding_amount FROM";
        $sql .= " core_ledger";
        $sql .= " WHERE coa_id = '".$coa_id."' AND company_branch_id = '".$branch_id."'";
        if($document_id != '')
        {
            $sql .= " AND document_id != '".$document_id."'";
        }
        $query = $this->conn->query($sql);
//        d(array($sql, $query),true);

        return $query->row;
    }



//    Public function getDocumentOutstanding($filter){
//        // This Function is Used to get the OutStanding of Particular Document through Document Identity.
//        $sql  = "SELECT l.company_id, l.fiscal_year_id,l.company_branch_id, l.people_type_id,p.people_type, l.people_id,p.people_name, l.ref_document_type_id, l.ref_document_identity,l.ref_document_id, l.coa_id";
//        $sql .= " , CASE WHEN l.people_type_id = 2 THEN SUM(l.debit-l.credit) ELSE SUM(l.credit-l.debit) END as outstanding";
//        //$sql .= " , SUM(IF(l.ref_document_type_id=1,credit-debit,IF(l.ref_document_type_id=2,debit-credit,0))) AS outstanding";
//        $sql .= " FROM core_ledger l";
//        $sql .= " INNER JOIN vw_core_people p ON l.people_type_id = p.people_type_id AND l.people_id = p.people_id";
//        //$sql .= " AND l.coa_id = p.outstanding_account_id ";
//        $sql .= " AND (l.coa_id = p.outstanding_account_id OR l.coa_id = p.advance_account_id)";
//        $sql .= " WHERE l.company_id = '".$filter['company_id']."'";
//        $sql .= " AND l.company_branch_id = '".$filter['company_branch_id']."'";
//        $sql .= " AND l.fiscal_year_id = '".$filter['fiscal_year_id']."'";
//        $sql .= " AND l.people_type_id = '".$filter['people_type_id']."'";
//        $sql .= " AND l.people_id = '".$filter['people_id']."'";
//        $sql .= " AND l.ref_document_identity = '".$filter['ref_document_id']."'";
//        if(isset($filter['document_id']) && $filter['document_id']) {
//            $sql .= " AND l.document_id != '".$filter['document_id']."'";
//        }
//
//        $sql .= " GROUP BY l.company_id, l.fiscal_year_id,l.company_branch_id, l.people_type_id,p.people_type, l.people_id,p.people_name,l.ref_document_type_id, l.ref_document_identity, l.coa_id;";
//
//        $query = $this->conn->query($sql);
//        $row = $query->row;
////        d(array($sql, $row),true);
//        return $row;
//
//    }

    public function getLedgerReport($filter) {
        $sql = "SELECT 1 as sort_order, l.coa_id, l.cheque_no , l.cheque_date , l.account as display_name";
        $sql .= ", '' AS document_type_id, '' AS document_id";
        $sql .= ", '".$filter['date_from']."' AS document_date, 'OPENING' AS document_identity, '' as remarks, '".$filter['date_from']."' AS created_at";
        $sql .= ", SUM(debit) AS debit, SUM(credit) AS credit";
        $sql .= " FROM vw_core_ledger l";
        $sql .= " INNER JOIN vw_gl0_coa_all coa ON coa.coa_level3_id = l.coa_id AND coa.company_id = l.company_id ";
        $sql .= " WHERE l.company_id = '".$filter['company_id']."'";
        $sql .= " AND l.company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND l.fiscal_year_id = '".$filter['fiscal_year_id']."'";
        $sql .= " AND l.document_date >= '".$filter['session_from']."' AND l.document_date < '".$filter['date_from']."'";
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
        $sql .= " SELECT 2 as sort_order, l.coa_id, l.cheque_no , l.cheque_date, l.account as display_name, l.document_type_id, l.document_id, l.document_date, l.document_identity, l.remarks, l.created_at, debit, credit";
        $sql .= " FROM vw_core_ledger l";
        $sql .= " INNER JOIN vw_gl0_coa_all coa ON coa.coa_level3_id = l.coa_id AND coa.company_id = l.company_id ";
        $sql .= " WHERE l.company_id = '".$filter['company_id']."'";
        $sql .= " AND l.company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND l.fiscal_year_id = '".$filter['fiscal_year_id']."'";
        $sql .= " AND l.document_date >= '".$filter['date_from']."' AND l.document_date <= '".$filter['date_to']."'";
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
        // d(array($sql, $rows),true);
        return $rows;
    }

//    public function getEntityLedgerReport($filter) {
//        // This will show the outstanding ledger of an Entity
//        // Used in EntityLedger Report.
//
//        $sql = "SELECT 1 AS sort_order, coa_id, display_name, '' AS people_type_id,'' AS people_type". "\n";
//        $sql .= ", '' AS people_id, '' AS people_name, '' AS document_type_id, '' AS document_id". "\n";
//        $sql .= ", '".$filter['date_from']."' AS document_date, 'OPENING' AS document_identity". "\n";
//        $sql .= ", '' AS ref_document_type_id, '' AS ref_document_id, '' AS ref_document_identity, '' AS remarks". "\n";
//        $sql .= ", SUM(debit) AS debit, SUM(credit) AS credit, '".$filter['date_from']." 00:00:00' AS created_at". "\n";
//        $sql .= " FROM vw_ledger l". "\n";
//        $sql .= " INNER JOIN vw_people p ON l.people_type_id = p.people_type_id AND l.people_id = p.people_id". "\n";
//        $sql .= " AND (l.coa_id = p.outstanding_account_id OR l.coa_id = p.advance_account_id) ". "\n";
//        $sql .= " WHERE l.company_id = '".$filter['company_id']."'". "\n";
//        $sql .= " AND l.company_branch_id = '".$filter['company_branch_id']."'". "\n";
//        $sql .= " AND l.fiscal_year_id = '".$filter['fiscal_year_id']."'". "\n";
//        $sql .= " AND l.document_date >= '".$filter['session_from']."' AND l.document_date < '".$filter['date_from']."'". "\n";
//        $sql .= " AND l.people_type_id = '".$filter['people_type_id']."'". "\n";
//        if($filter['people_id']) {
//            $sql .= " AND l.people_id = '".$filter['people_id']."'". "\n";
//        }
//        $sql .= " GROUP BY l.people_type_id, l.people_id, l.display_name". "\n";
//        $sql .= " UNION ALL". "\n";
//        $sql .= " SELECT 2 AS sort_order, coa_id, display_name, p.people_type_id,p.people_type,p.people_id,p.people_name, document_type_id, document_id, document_date, document_identity ". "\n";
//        $sql .= ", ref_document_type_id, ref_document_id, ref_document_identity, remarks,  debit, credit, created_at ". "\n";
//        $sql .= " FROM vw_ledger l". "\n";
//        $sql .= " INNER JOIN vw_people p ON l.people_type_id = p.people_type_id AND l.people_id = p.people_id". "\n";
//        $sql .= " AND (l.coa_id = p.outstanding_account_id OR l.coa_id = p.advance_account_id) ". "\n";
//        $sql .= " WHERE l.company_id = '".$filter['company_id']."'". "\n";
//        $sql .= " AND l.company_branch_id = '".$filter['company_branch_id']."'". "\n";
//        $sql .= " AND l.fiscal_year_id = '".$filter['fiscal_year_id']."'". "\n";
//        if(isset($filter['date_from']) && $filter['date_from'] != '')
//            $sql .= " AND l.document_date >= '".$filter['date_from']."'";
//        if(isset($filter['date_to']) && $filter['date_to'] != '')
//            $sql .= " AND l.document_date <= '".$filter['date_to']."'";
//        $sql .= " AND (l.credit > 0 OR l.debit > 0) ". "\n";
//        $sql .= " AND l.people_type_id = '".$filter['people_type_id']."'". "\n";
//        if($filter['people_id']) {
//            $sql .= " AND l.people_id = '".$filter['people_id']."'". "\n";
//        }
//        $sql .= " GROUP BY people_type,people_name,sort_order,document_date, document_identity". "\n";
//        $sql .= " Order BY people_type,people_name,sort_order,document_date, document_identity, created_at". "\n";
//
//        $query = $this->conn->query($sql);
//        $rows = $query->rows;
//        //d(array($filter,$sql, $rows),true);
//        return $rows;
//    }

//    Public function getPartyLedger($filter){
//
//        $sql  = "CALL ";
//        $sql .= "sp_party_ledger('".$filter['company_id']."','".$filter['company_branch_id']."','".$filter['fiscal_year_id']."','".$filter['people_type_id']."','".$filter['people_id']."','".$filter['date_from']."','".$filter['date_to']."')";
//
//        $query = $this->conn->query($sql);
//        $rows = $query->rows;
//        return $rows;
//
//    }



    public function getStockTransferLedger($document_type_id,$document_id,$company_branch_id) {
        $sql = "SELECT";
        $sql .= " `document_type_id`                                       AS `document_type_id`,";
        $sql .= " `document_id`                                            AS `document_id`,";
        $sql .= " `coa_id`                                                 AS `coa_id`,";
        $sql .= " CONCAT(level1_code, '-',  level2_code, '-', level3_code) AS `levels_code`,";
        $sql .= " `level3_name`                                            AS `level3_name`,";
        $sql .= " `debit`                                                  AS `debit`,";
        $sql .= " `credit`                                                 AS `credit`";
        $sql .= " FROM `vw_core_ledger`";
        $sql .= " WHERE `document_type_id` = '".$document_type_id."'";
        $sql .= " AND `document_id` = '".$document_id."'";
        $sql .= " AND `company_branch_id` = '".$company_branch_id."'";
        $sql .= " AND (`debit` <> '0.0000' OR `credit` <> '0.0000')";
        $sql .= " ORDER BY `debit` desc, `credit`";
        $query = $this->conn->query($sql);
        $rows = $query->rows;
        return $rows;
    }

    // Copy from speedy for vouchers print
    public function getTransactionLedger($document_type_id,$document_id) {
        $sql = "SELECT l.document_type_id,l.document_id,l.cheque_no,l.coa_id,l.level2_code,l.level1_code,l.level3_code,l.level3_name,l.remarks,l.debit debit,l.credit credit";
        $sql .= " FROM vw_core_ledger l ";
        $sql .= " WHERE l.document_type_id = '".$document_type_id."'";
        $sql .= " AND l.document_id = '".$document_id."'";
        $sql .= " ORDER BY l.debit desc,l.credit,l.level3_code";
        $query = $this->conn->query($sql);
        $rows = $query->rows;
        return $rows;
    }

        // Copy from speedy for vouchers print
    public function getPurchaseLedger($document_type_id,$document_id) {
        $sql = "SELECT document_type_id,document_id,coa_id,level3_code,level1_code,level2_code,level3_name,remarks,debit debit,credit credit";
        $sql .= " FROM vw_core_ledger ";
        $sql .= " WHERE document_type_id = '".$document_type_id."'";
        $sql .= " AND document_id = '".$document_id."'";
        $sql .= " ORDER BY debit desc,credit,level3_code";
        $query = $this->conn->query($sql);
        $rows = $query->rows;
        return $rows;
    }

    public function getTransactionLedgerWithPartner($document_type_id,$document_id) {
        $sql = "SELECT l.partner_type_id,l.partner_id,l.document_type_id,l.document_id,bp.cheque_no,l.coa_id,l.level2_code,l.level1_code,l.level3_code,l.level3_name,l.remarks,l.debit debit,l.credit credit";
        $sql .= " FROM vw_core_ledger l ";
        $sql .= " LEFT JOIN glt_bank_payment_detail bp ON bp.bank_payment_id = l.document_id";
        $sql .= " WHERE l.document_type_id = '".$document_type_id."'";
        $sql .= " AND l.document_id = '".$document_id."'";
        $sql .= " ORDER BY l.debit desc,l.credit,l.level3_code";
        $query = $this->conn->query($sql);
        $rows = $query->rows;
        return $rows;
    }


}

?>