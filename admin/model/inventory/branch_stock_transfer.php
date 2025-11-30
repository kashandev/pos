<?php

class ModelInventoryBranchStockTransfer extends HModel {

    protected function getTable() {
        return 'branch_stock_transfer';
    }

    protected function getView() {
        return 'vw_branch_stock_transfer';
    }

    public function getLedger($document_type_id, $document_id) {
        $sql = "SELECT";
        $sql .= " CONCAT(level1_code, '-',  level2_code, '-', level3_code, ':', `level3_name`) AS `account`,";
        $sql .= " `debit`                                                                      AS `debit`,";
        $sql .= " `credit`                                                                     AS `credit`";
        $sql .= " FROM `vw_core_ledger`";
        $sql .= " WHERE `document_type_id` = '".$document_type_id."'";
        $sql .= " AND `document_id` = '".$document_id."'";
        $sql .= " AND `company_branch_id` = '".$this->session->data['company_branch_id']."'";
        $sql .= " AND (`debit` <> '0.0000' OR `credit` <> '0.0000')";
        $sql .= " ORDER BY `debit` DESC,`credit`,`sort_order`";
        $query = $this->conn->query($sql);
        $rows = $query->rows;
        return $query;
    }

}

?>