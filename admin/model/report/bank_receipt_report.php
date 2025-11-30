<?php

class ModelReportBankReceiptReport extends HModel {

    protected function getTable() {
        return 'vw_bank_receipt';
    }


    public function getBankReceiptReport($filter=array()) {

        $sql =  " SELECT br.`document_date`,br.`document_identity`,brd.`ref_document_identity`,brd.`wht_percent` ";
        $sql .= " ,brd.`wht_amount`,brd.`balance_amount`,brd.`amount`,brd.`bank_amount`,brd.`net_amount`,p.`name` ";
        $sql .= " FROM `glt_bank_receipt` br";
        $sql .= " INNER JOIN `glt_bank_receipt_detail` brd ON br.`bank_receipt_id` = brd.`bank_receipt_id`";
        $sql .= " INNER JOIN `core_partner` p ON p.`partner_type_id` = br.`partner_type_id` AND p.`partner_id` = br.`partner_id`";
        $sql .= " WHERE br.company_id = '".$filter['company_id']."'";
        $sql .= " AND br.company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND br.fiscal_year_id = '".$filter['fiscal_year_id']."'";
        $sql .= " AND br.`document_date` >= '".$filter['from_date']."' AND br.`document_date` <= '".$filter['to_date']."'";

        if($filter['partner_id'])
        {
            $sql .= " AND br.partner_id = '".$filter['partner_id']."'";
        }
        if($filter['partner_type_id'])
        {
            $sql .= " AND br.partner_type_id = '".$filter['partner_type_id']."'";
        }
        $sql .= " ORDER BY p.`name`,br.`document_date`,br.`document_identity` ";
        $query = $this->conn->query($sql);
        // d($query,true);
         return $query->rows;
    }

}

?>