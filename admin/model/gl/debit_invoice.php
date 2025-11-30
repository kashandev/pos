<?php

class ModelGlDebitInvoice extends HModel {

    protected function getTable() {
        return 'gli_debit_invoice';
    }

    protected function getView() {
        return 'vw_gli_debit_invoice';
    }

    public function getSaleTaxNo() {
        $sql = "SELECT MAX(CONVERT(`manual_ref_no`,UNSIGNED INTEGER)) AS manual_ref_no";
        $sql .= " FROM `gli_debit_invoice`";
        $sql .= " WHERE sale_tax_invoice='Yes'";
        $sql .= " AND `company_id` = '".$this->session->data['company_id']."'";
        $query = $this->conn->query($sql);
        $row = $query->row;
        if($row['manual_ref_no']==NULL) {
            $manual_ref_no = '001';
        } else {
            $manual_ref_no = str_pad($row['manual_ref_no'] + 1,3,"0",STR_PAD_LEFT);
        }

        return $manual_ref_no;
    }

}

?>