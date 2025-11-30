<?php

class ModelGLReceipts extends HModel {

    protected function getTable() {
        return 'glt_receipts';
    }

    protected function getView() {
        return 'vw_glt_receipts';
    }

    public function getLatestReceipts() {
        $sql =  " SELECT  receipts_id,`document_identity`,`partner_name`,`total_net_amount`  ";
        $sql .= " FROM vw_glt_receipts ORDER BY `created_at` DESC ";
        $sql .= " LIMIT 10 ";

        $query = $this->conn->query($sql);
        return $query->rows;
    }

}

?>