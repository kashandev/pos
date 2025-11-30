<?php

class ModelReportTrialBalance extends HModel {

    protected function getTable() {
        return 'temp_trial_balance';
    }

    public function getTrialBalance($filter=array(), $sort_order=array()) {
        $sql = "SELECT l.company_id, l.company_branch_id, l.fiscal_year_id";
        $sql .= ", c.coa_level1_id, c.level1_code, c.level1_display_name";
        $sql .= ", c.coa_level2_id, c.level2_code, c.level2_display_name";
        $sql .= ", c.coa_level3_id, c.level3_code, c.level3_display_name";
        $sql .= ", SUM(l.debit) AS debit, SUM(l.credit) AS credit, SUM(l.debit-l.credit) AS balance";
        $sql .= " FROM `vw_core_ledger` l";
        $sql .= " INNER JOIN `vw_gl0_coa_all` c ON c.company_id = l.company_id AND c.coa_level3_id = l.coa_id";
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

        $sql .= " GROUP BY c.coa_level1_id, c.level1_code, c.level1_display_name";
        $sql .= ", c.coa_level2_id, c.level2_code, c.level2_display_name";
        $sql .= ", c.coa_level3_id, c.level3_code, c.level3_display_name";
        $sql .= " HAVING SUM(l.debit-l.credit) != 0";

        if($sort_order) {
            $sql .= " ORDER BY " . implode(',',$sort_order);
        }

        $query = $this->conn->query($sql);
//d(array($query,$sql,true));
        return $query->rows;
    }

    public function getTrailBalanceConsolidate($filter) {
        $sql = "  SELECT coa_level1_id,level1_code,level1_display_name,coa_level2_id,level2_code,level2_display_name,coa_id,level3_code,account,level3_name ";
        $sql .= " , CASE WHEN (op_debit - op_credit)> 0 THEN (op_debit-op_credit) ELSE 0 END AS op_debit";
        $sql .= " , CASE WHEN (op_debit - op_credit)< 0 THEN (op_credit-op_debit) ELSE 0 END AS op_credit";
        $sql .= " , cur_debit , cur_credit    ";
        $sql .= " , CASE WHEN (tot_debit - tot_credit)> 0 THEN (tot_debit-tot_credit) ELSE 0 END AS tot_debit";
        $sql .= " , CASE WHEN (tot_debit - tot_credit)< 0 THEN (tot_credit-tot_debit) ELSE 0 END AS tot_credit";
        $sql .= " FROM ( ";
        $sql .= " SELECT vl.coa_level1_id,vl.level1_code,vl.level1_display_name,vl.coa_level2_id,vl.level2_code,vl.level2_display_name,vl.coa_id,vl.level3_code,vl.account,level3_name,company_id";
        $sql .= " , SUM(CASE WHEN vl.document_date >= '2015-07-01' AND vl.document_date < '".$filter['from_date']."' THEN vl.debit ELSE 0 END) AS op_debit ";
        $sql .= " , SUM(CASE WHEN vl.document_date >= '2015-07-01' AND vl.document_date < '".$filter['from_date']."' THEN vl.credit ELSE 0 END) AS op_credit ";
        $sql .= " , SUM(CASE WHEN vl.document_date >= '".$filter['from_date']."' AND vl.document_date <= '".$filter['to_date']."' THEN vl.debit ELSE 0 END) AS cur_debit  ";
        $sql .= " , SUM(CASE WHEN vl.document_date >= '".$filter['from_date']."' AND vl.document_date <= '".$filter['to_date']."' THEN vl.credit ELSE 0 END) AS cur_credit ";
        $sql .= " , SUM(vl.debit) AS tot_debit, SUM(vl.credit) AS tot_credit";
        $sql .= " FROM vw_core_ledger vl";
        $sql .= " WHERE vl.document_date <= '".$filter['to_date']."' AND  vl.company_id = '".$filter['company_id']."' ";
        if($filter['branch_id'] != '')
        {
            $sql .= " AND  vl.company_branch_id = '".$filter['branch_id']."' ";
        }
        $sql .= " GROUP BY vl.coa_level1_id,vl.level1_code,vl.level1_display_name,vl.coa_level2_id,vl.level2_code,vl.level2_display_name,vl.coa_id,vl.account,level3_name,company_id";
        $sql .= " )a";
        $sql .= " Order by level1_code,level2_code,level3_code";

        $query = $this->conn->query($sql);
        // d($query,true);
        $rows = $query->rows;
        return $rows;
    }
}
?>