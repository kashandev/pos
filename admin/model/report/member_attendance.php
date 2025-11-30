<?php

class ModelReportMemberAttendance extends HModel {

    public function getRows($display_columns, $where = '', $show_empty) {
        $columns = array();
        $sort_columns = array();
        foreach($display_columns as $display_column) {
            if($display_column['column_name'] == 'total_present') {
                $columns[] = "SUM(IF(`status`='P',1,0)) AS `".$display_column['display_name']."`";
            } elseif($display_column['column_name'] == 'total_absent') {
                $columns[] = "SUM(IF(`status`='A',1,0)) AS `".$display_column['display_name']."`";
            } else {
                $columns[] = "`".$display_column['column_name']."` AS `".$display_column['display_name']."`";
                $sort_columns[] = $display_column['column_name'];
            }
        }
        $sql = "";
        $sql .= "SELECT " . implode(',', $columns) . PHP_EOL;
        $sql .= " FROM `vw_member_attendance_detail` " . PHP_EOL;
        if($where != '') {
            $sql .= " WHERE " . $where;
        }
        $sql .= " GROUP BY " . implode(',', $sort_columns) . PHP_EOL;
        $sql .= " ORDER BY " . implode(',', $sort_columns);
        $query = $this->conn->query($sql);
        //d(array($where, $sql, $query), true);
        return $query->rows;
    }

}

?>