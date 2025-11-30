<?php

class ModelReportAcademyReceivable extends HModel {

    public function getRows($display_columns, $where = '', $having = '') {
        $columns = array();
        $sort_columns = array();
        foreach($display_columns as $display_column) {
            if(in_array($display_column['column_name'],array('member_amount','donor_amount','discount_amount','receivable_amount','receipt_amount','balance_amount'))) {
                $columns[] = "SUM(`".$display_column['column_name']."`) AS `".$display_column['display_name']."`";
            } else {
                $columns[] = "`".$display_column['column_name']."` AS `".$display_column['display_name']."`";
                $sort_columns[] = $display_column['column_name'];
            }
        }
        $sql = "";
        $sql .= "SELECT " . implode(',', $columns) . PHP_EOL;
        $sql .= " FROM `vw_member_receivable` " . PHP_EOL;
        if($where != '') {
            $sql .= " WHERE " . $where;
        }
        $sql .= " GROUP BY " . implode(',', $sort_columns) . PHP_EOL;
        if($having != '') {
            $sql .= " HAVING " . $having;
        }
        $sql .= " ORDER BY " . implode(',', $sort_columns);
        $query = $this->conn->query($sql);
        //d(array($where, $sql, $query), true);
        return $query->rows;
    }

}

?>