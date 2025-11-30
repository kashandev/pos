<?php

class ModelInventoryProduct extends HModel {

    protected function getTable() {
        return 'in0_product';
    }

    protected function getView() {
        return 'vw_in0_product';
    }

    public function getMaxProductCode() {
        $sql = "SELECT MAX(CONVERT(product_code,UNSIGNED INTEGER)) as max_no";
        $sql .= " FROM `".DB_PREFIX."in0_product`";
        $query = $this->conn->query($sql);
        $row = $query->row;
        //d($sql,true);
        return (is_null($row['max_no'])?'1':$row['max_no']+1);
    }

    public function getProductJson($search, $page, $limit=25, $filter=array()) {
        if($page=='') {
            $page = 0;
        }
        $offset = $page*$limit;

        $arrWhere = array();
        $arrWhere[] = "(`product_code` LIKE '".$search."%' OR `name` LIKE '".$search."%')";
        if(isset($filter['product_category_id']) && $filter['product_category_id']) {
            $arrWhere[] = "`product_category_id` = '".$filter['product_category_id']."'";
        }
        $sql = "SELECT count(*) as total_records";
        $sql .= " FROM `vw_in0_product`";
        $sql .= " WHERE " . implode(' AND ', $arrWhere);
        $query = $this->conn->query($sql);
        $row = $query->row;
        $total_records = $row['total_records'];

        $sql = "SELECT *, product_id as id";
        $sql .= " FROM `vw_in0_product`";
        $sql .= " WHERE " . implode(' AND ', $arrWhere);
        $sql .= " LIMIT " . $offset . "," . $limit;
        $query = $this->conn->query($sql);
        $rows = $query->rows;

        return array(
            'total_count' => $total_records,
            'sql' => $sql,
            'items' => $rows
        );
    }

    public function getTotalProduct() {
        $sql = "SELECT COUNT(product_id) total_product  FROM `in0_product` ";

        $query = $this->conn->query($sql);
        return $query->row;
    }
}
?>