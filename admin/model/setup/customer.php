<?php

class ModelSetupCustomer extends HModel {

    protected function getTable() {
        return 'core_customer';
    }

    protected function getView() {
        return 'vw_core_customer';
    }

    public function getTotalCustomer() {
        $sql = "SELECT COUNT(customer_id) total_customer  FROM `core_customer`";

//        if($this->session->data['user_permission'] == 2)
//        {
//
//        }else{
//            $sql .= " and `created_by_id` = '".$this->session->data['user_id']."'";
//        }

        $query = $this->conn->query($sql);
        return $query->row;
    }


    public function getMaxCustomerCode()
    {
        $sql = "SELECT MAX(customer_code) max_code FROM `core_customer`";
        $query = $this->conn->query($sql);
        return $query->row;
    }

}

?>