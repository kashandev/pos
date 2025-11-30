<?php

class ModelGLBankReceipt extends HModel {

    protected function getTable() {
        return 'glt_bank_receipt';
    }

    protected function getView() {
        return 'vw_glt_bank_receipt';
    }

    public function getLatestReceipts() {
        $sql =  " SELECT  bank_receipt_id,`document_identity`,`partner_name`,`total_net_amount`  ";
        $sql .= " FROM vw_glt_bank_receipt ORDER BY `created_at` DESC ";
        $sql .= " LIMIT 10 ";

        $query = $this->conn->query($sql);
        return $query->rows;
    }

}

?>