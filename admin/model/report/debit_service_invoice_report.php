<?php

class ModelReportDebitServiceInvoiceReport extends HModel {

    protected function getTable() {
        return 'vw_debit_invoice_detail';
    }

    public function getDebitInvoiceReport($filter)
    {
    	$sql =  " SELECT  di.document_identity,
    					  di.document_date,
    					  di.remarks,
    					  di.tax_amount,
    					  di.net_amount,
    					  di.base_amount,
    					  di.partner_name,
    					  did.remarks as detail_remarks,
    					  did.amount,
    					  did.account";
        $sql .= " FROM `vw_gli_debit_invoice` di ";
        $sql .= " INNER JOIN `vw_gli_debit_invoice_detail` did ON `di`.`debit_invoice_id` = `did`.`debit_invoice_id`";        
        $sql .= " WHERE di.company_id = '".$this->session->data['company_id']."'";
        $sql .= " AND di.company_branch_id = '".$this->session->data['company_branch_id']."'";
        $sql .= " AND di.fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
        $sql .= " AND di.`document_date` >= '".$filter['date_from']."' AND di.`document_date` <= '".$filter['date_to']."'";
        if($filter['di.partner_id'] !='')
        {
            $sql .= " AND di.partner_id = '".$filter['partner_id']."'";
        }
        $sql .= " ORDER BY di.`document_date`,di.`document_identity` ";
        $query = $this->conn->query($sql);
        // d($query,true);
         return $query->rows;
    }
}

?>