<?php

class ModelReportBalanceSheet extends HModel {

    protected function getTable() {
        return 'temp_balance_sheet';
    }

    public function getBalanceSheet($filter=array(), $sort_order=array()) {
        $sql = "SELECT l.company_id, l.company_branch_id, l.fiscal_year_id";
        $sql .= ",c.coa_level1_id, c.level1_code, c.level1_display_name";
        $sql .= ", c.coa_level2_id, c.level2_code, c.level2_display_name";
        $sql .= ", c.coa_level3_id, c.level3_code, c.level3_display_name";
        $sql .= ", SUM(l.debit) AS debit, SUM(l.credit) AS credit, SUM(l.debit-l.credit) AS balance ";
        $sql .= " FROM `vw_core_ledger` l";
        $sql .= " INNER JOIN `vw_gl0_coa_all` c ON c.company_id = l.company_id AND c.coa_level3_id = l.coa_id";
       // $sql .= " INNER JOIN gl_type gl ON gl.gl_type_id = c.gl_type_id";
        if($filter) {
            if(is_array($filter)) {
//                $table_columns = $this->getTableColumns($this->getTable());
                $implode = array();
                foreach($filter as $column => $value) {
//                    if(in_array($column,$table_columns)) {
                    $implode[] = "`$column`='$value'";
//                    }
                }
                if($implode)
                    $sql .= " WHERE " . implode(" AND ", $implode);
            } else {
                $sql .= " WHERE " . $filter;
            }
        }
        $sql .= " AND (c.gl_type_id IN (1,2,3)) ";
        $sql .= " GROUP BY c.coa_level3_id, c.level3_code, c.level3_display_name";
        $sql .= " HAVING SUM(l.debit-l.credit) != 0";

        if($sort_order) {
            $sql .= " ORDER BY " . implode(',',$sort_order);
        }

        $query = $this->conn->query($sql);
//d(array($query,$sql,true));
        return $query->rows;
    }

}

?>