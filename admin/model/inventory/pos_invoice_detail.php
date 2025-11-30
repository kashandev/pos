<?php

class ModelInventoryPOSInvoiceDetail extends HModel {

    protected function getTable() {
        return 'ins_pos_invoice_detail';
    }

    protected function getView() {
        return 'vw_ins_pos_invoice_detail';
    }
    public function getTotalSaleMonth($branchID) {
        $sql = " SELECT DATE_FORMAT(`document_date`,'%M %Y')date_month,SUM(total_amount) total";
        $sql .= " FROM `vw_ins_pos_invoice_detail`";
        $sql .= " WHERE `company_branch_id` = '".$branchID."' ";
        $sql .= " GROUP BY DATE_FORMAT(`document_date`,'%M %Y')";
        $sql .= " ORDER BY `document_date`";
        $query = $this->conn->query($sql);
        return $query->rows;
    }
}

?>