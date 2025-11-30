<?php

class ModelInventoryWarehouse extends HModel {

    protected function getTable() {
        return 'in0_warehouse';
    }

     public function getWareHouseCode()
    {
        $sql = "SELECT MAX(code) as code FROM `in0_warehouse`";
        $sql .= " WHERE `company_id` = '".$this->session->data['company_id']."'";
        $sql .= " AND `company_branch_id` = '".$this->session->data['company_branch_id']."'";
        $query = $this->conn->query($sql);
        return $record = $query->row;
    }
    
}

?>