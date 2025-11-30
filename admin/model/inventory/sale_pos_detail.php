<?php

class ModelInventorySalePOSDetail extends HModel {

    protected function getTable() {
        return 'sale_pos_detail';
    }

    protected function getView() {
        return 'vw_sale_pos_detail';
    }

    public function getTotalSaleMonth($branchID) {
        $sql = " SELECT DATE_FORMAT(`document_date`,'%M %Y')date_month,SUM(total_amount) total";
        $sql .= " FROM `vw_sale_pos_detail`";
        $sql .= " WHERE `company_branch_id` = '".$branchID."' ";
        $sql .= " GROUP BY DATE_FORMAT(`document_date`,'%M %Y')";
        $sql .= " ORDER BY `document_date`";
        $query = $this->conn->query($sql);
        return $query->rows;
    }


    public function getToDaySale($branchID) {
        $sql = " SELECT SUM(total_amount) total_sale";
        $sql .= " FROM `vw_sale_pos_detail`";
        $sql .= " WHERE `document_date` = '".date('Y-m-d')."' and `company_branch_id` = '".$branchID."'";
        $sql .= " GROUP BY document_date";

//        d($sql,true);
        $query = $this->conn->query($sql);
        return $query->row;
    }
    public function getBestSellingProduct($branchID) {
        $sql = " SELECT  COUNT(`product_id`) total_Product,`product_name`" ;
        $sql .= " FROM `vw_sale_pos_detail` ";
        $sql .= " WHERE `company_branch_id` = '".$branchID."' ";
        $sql .= "  GROUP BY `product_id` ";
        $sql .= "  ORDER BY `total_Product` DESC  LIMIT 10";

        $query = $this->conn->query($sql);
//        d($query,true);
        return $query->rows;
    }


}

?>