<?php

class ModelReportProductReport extends HModel {

    public function getReports($product_category_id,$product_id) {


        $sql  = " SELECT * from  `vw_in0_product` ";


        if($product_category_id != '')
        {
            $sql .= " WHERE `product_category_id` = '".$product_category_id."' " ;
        }
        if($product_id != '')
        {
            $sql .= " and `product_id` = '".$product_id."' " ;
        }

        $sql .= "   ORDER BY `name`" ;

//        d($sql,true);


        $query = $this->conn->query($sql);
        return $query->rows;
    }
}

?>