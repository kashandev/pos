<?php

class ModelInventoryPurchaseInvoice extends HModel {

    protected function getTable() {
        return 'inp_purchase_invoice';
    }

    protected function getView() {
        return 'vw_inp_purchase_invoice';
    }

    public function getPreviousBalance($filter) {
        
        $sql =  "SELECT";
        $sql .= " SUM(`l`.`credit`) - SUM(`l`.`debit`) AS `previous_balance`";
        $sql .= " FROM `core_ledger` `l`";
        $sql .= " INNER JOIN `core_partner` `p` ON `l`.`partner_id` = `p`.`partner_id`";
        $sql .= " AND `l`.`coa_id` = `p`.`outstanding_account_id`";
        $sql .= " {$filter}";
        $query = $this->conn->query($sql);
        if( $query->row ){
            return $query->row['previous_balance'];
        }
        return 0;
    }

}

?>