<?php

class ModelReportIncomeStatement extends HModel {

    protected function getTable() {
        return 'temp_income_statement';
    }

    public function getIncomeStatement($filter=array(), $sort_order=array()) {
        $sql = "SELECT l.company_id, l.company_branch_id, l.fiscal_year_id, gl.name as gl_type";
        $sql .= ",c.coa_level1_id, c.level1_code, c.level1_display_name";
        $sql .= ", c.coa_level2_id, c.level2_code, c.level2_display_name";
        $sql .= ", c.coa_level3_id, c.level3_code, c.level3_display_name";
        $sql .= ", SUM(l.debit) AS debit, SUM(l.credit) AS credit, SUM(l.debit-l.credit) AS balance ";
        $sql .= " FROM `vw_core_ledger` l";
        $sql .= " INNER JOIN `vw_gl0_coa_all` c ON c.company_id = l.company_id AND c.coa_level3_id = l.coa_id";
        $sql .= " INNER JOIN const_gl_type gl ON gl.gl_type_id = c.gl_type_id";
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
        $sql .= " GROUP BY c.coa_level3_id, c.level3_code, c.level3_display_name";
        // $sql .= " HAVING SUM(l.debit-l.credit) != 0";

        if($sort_order) {
            $sql .= " ORDER BY " . implode(',',$sort_order);
        }

        $query = $this->conn->query($sql);
// d(array($query,$sql,true));
        return $query->rows;
    }

    public function getSaleRevenue($filter, $heading){
        $sql = "";
        $sql .= "SELECT";
        $sql .= " '{$heading['title']}' as `type`,";
        $sql .= " CONCAT(`l1`.`level1_code`,'-',`l2`.`level2_code`,'-',`l3`.`level3_code`,': ',`l3`.`name`) AS `level3_display_name`,";
        $sql .= " IFNULL(SUM(`l`.`credit`-`l`.`debit`),0.00) AS `balance`";
        $sql .= " FROM `core_ledger` l";
        $sql .= " RIGHT JOIN `gl0_coa_level3` `l3`";
        $sql .= " ON `l3`.`company_id` = `l`.`company_id`";
        $sql .= " AND `l3`.`coa_level3_id` = `l`.`coa_id`";
        $sql .= " RIGHT JOIN `gl0_coa_level2` `l2`";
        $sql .= " ON `l2`.`company_id` = `l3`.`company_id`";
        $sql .= " AND `l2`.`coa_level2_id` = `l3`.`coa_level2_id`";
        $sql .= " RIGHT JOIN `gl0_coa_level1` `l1`";
        $sql .= " ON `l1`.`company_id` = `l2`.`company_id`";
        $sql .= " AND `l1`.`coa_level1_id` = `l2`.`coa_level1_id`";

        $sql .= " AND `l`.`coa_id` IN ({$filter['sale_revenue_account_id']})";
        $sql .= " WHERE `l`.`company_id` = '{$filter['company_id']}'";
        $sql .= " AND `l`.`company_branch_id` = '{$filter['company_branch_id']}'";
        $sql .= " AND `l`.`fiscal_year_id` = '{$filter['fiscal_year_id']}'";
        $sql .= " AND `l`.`document_date` >= '{$filter['from_date']}'";
        $sql .= " AND `l`.`document_date` <= '{$filter['to_date']}'";

        if(!empty($filter['project_id'])){
            $sql .= " AND `l`.`project_id` = '{$filter['project_id']}'";
        }

        if(!empty($filter['sub_project_id'])){
            $sql .= " AND `l`.`sub_project_id` = '{$filter['sub_project_id']}'";
        }

        if(!empty($filter['job_order_id'])){
            $sql .= " AND `l`.`job_order_id` = '{$filter['job_order_id']}'";
        }
        
        $sql .= " GROUP BY `l3`.`coa_level3_id`, `l3`.`level3_code`, `level3_display_name`";
        $sql .= " HAVING SUM(`l`.`credit`-`l`.`debit`) != 0";
        $sql .= " ORDER BY `l1`.`level1_code`, `l2`.`level2_code`, `l3`.`level3_code` ASC";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getSaleReturnAndDiscount($filter, $heading){
        $sql = "";
        $sql .= "SELECT";
        $sql .= " '{$heading['title']}' as `type`,";
        $sql .= " CONCAT(`l1`.`level1_code`,'-',`l2`.`level2_code`,'-',`l3`.`level3_code`,': ',`l3`.`name`) AS `level3_display_name`,";
        $sql .= " SUM(`l`.`debit`-`l`.`credit`) AS `balance`";
        $sql .= " FROM `core_ledger` l";
        $sql .= " RIGHT JOIN `gl0_coa_level3` `l3`";
        $sql .= " ON `l3`.`company_id` = `l`.`company_id`";
        $sql .= " AND `l3`.`coa_level3_id` = `l`.`coa_id`";
        $sql .= " RIGHT JOIN `gl0_coa_level2` `l2`";
        $sql .= " ON `l2`.`company_id` = `l3`.`company_id`";
        $sql .= " AND `l2`.`coa_level2_id` = `l3`.`coa_level2_id`";
        $sql .= " RIGHT JOIN `gl0_coa_level1` `l1`";
        $sql .= " ON `l1`.`company_id` = `l2`.`company_id`";
        $sql .= " AND `l1`.`coa_level1_id` = `l2`.`coa_level1_id`";
        $sql .= " AND `l`.`coa_id` IN ({$filter['sale_return_and_discount_account_id']})";
        $sql .= " WHERE `l`.`company_id` = '{$filter['company_id']}'";
        $sql .= " AND `l`.`company_branch_id` = '{$filter['company_branch_id']}'";
        $sql .= " AND `l`.`fiscal_year_id` = '{$filter['fiscal_year_id']}'";
        $sql .= " AND `l`.`document_date` >= '{$filter['from_date']}'";
        $sql .= " AND `l`.`document_date` <= '{$filter['to_date']}'";

        if(!empty($filter['project_id'])){
            $sql .= " AND `l`.`project_id` = '{$filter['project_id']}'";
        }

        if(!empty($filter['sub_project_id'])){
            $sql .= " AND `l`.`sub_project_id` = '{$filter['sub_project_id']}'";
        }

        if(!empty($filter['job_order_id'])){
            $sql .= " AND `l`.`job_order_id` = '{$filter['job_order_id']}'";
        }

        $sql .= " GROUP BY `l3`.`coa_level3_id`, `l3`.`level3_code`, `level3_display_name`";
        $sql .= " HAVING SUM(`l`.`debit`-`l`.`credit`) != 0";
        $sql .= " ORDER BY `l1`.`level1_code`, `l2`.`level2_code`, `l3`.`level3_code` ASC";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getCOGS($filter, $heading){
        $sql = "";
        $sql .= "SELECT";
        $sql .= " '{$heading['title']}' as `type`,";
        $sql .= " CONCAT(`l1`.`level1_code`,'-',`l2`.`level2_code`,'-',`l3`.`level3_code`,': ',`l3`.`name`) AS `level3_display_name`,";
        $sql .= " SUM(`l`.`debit`-`l`.`credit`) AS `balance`";

        $sql .= " FROM `core_ledger` l";
        $sql .= " RIGHT JOIN `gl0_coa_level3` `l3`";
        $sql .= " ON `l3`.`company_id` = `l`.`company_id`";
        $sql .= " AND `l3`.`coa_level3_id` = `l`.`coa_id`";
        $sql .= " RIGHT JOIN `gl0_coa_level2` `l2`";
        $sql .= " ON `l2`.`company_id` = `l3`.`company_id`";
        $sql .= " AND `l2`.`coa_level2_id` = `l3`.`coa_level2_id`";
        $sql .= " RIGHT JOIN `gl0_coa_level1` `l1`";
        $sql .= " ON `l1`.`company_id` = `l2`.`company_id`";
        $sql .= " AND `l1`.`coa_level1_id` = `l2`.`coa_level1_id`";
        $sql .= " AND `l`.`coa_id` IN ({$filter['cogs_account_id']})";
        $sql .= " WHERE `l`.`company_id` = '{$filter['company_id']}'";
        $sql .= " AND `l`.`company_branch_id` = '{$filter['company_branch_id']}'";
        $sql .= " AND `l`.`fiscal_year_id` = '{$filter['fiscal_year_id']}'";
        $sql .= " AND `l`.`document_date` >= '{$filter['from_date']}'";
        $sql .= " AND `l`.`document_date` <= '{$filter['to_date']}'";

        if(!empty($filter['project_id'])){
            $sql .= " AND `l`.`project_id` = '{$filter['project_id']}'";
        }

        if(!empty($filter['sub_project_id'])){
            $sql .= " AND `l`.`sub_project_id` = '{$filter['sub_project_id']}'";
        }

        if(!empty($filter['job_order_id'])){
            $sql .= " AND `l`.`job_order_id` = '{$filter['job_order_id']}'";
        }

        $sql .= " GROUP BY `l3`.`coa_level3_id`, `l3`.`level3_code`, `level3_display_name`";
        $sql .= " HAVING SUM(`l`.`debit`-`l`.`credit`) != 0";
        $sql .= " ORDER BY `l1`.`level1_code`, `l2`.`level2_code`, `l3`.`level3_code` ASC";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getAdminExpense($filter, $heading){
        $sql = "";
        $sql .= "SELECT";
        $sql .= " '{$heading['title']}' as `type`,";
        $sql .= " CONCAT(`l1`.`level1_code`,'-',`l2`.`level2_code`,'-',`l3`.`level3_code`,': ',`l3`.`name`) AS `level3_display_name`,";
        $sql .= " SUM(`l`.`debit`-`l`.`credit`) AS `balance`";

        $sql .= " FROM `core_ledger` l";
        $sql .= " RIGHT JOIN `gl0_coa_level3` `l3`";
        $sql .= " ON `l3`.`company_id` = `l`.`company_id`";
        $sql .= " AND `l3`.`coa_level3_id` = `l`.`coa_id`";
        $sql .= " RIGHT JOIN `gl0_coa_level2` `l2`";
        $sql .= " ON `l2`.`company_id` = `l3`.`company_id`";
        $sql .= " AND `l2`.`coa_level2_id` = `l3`.`coa_level2_id`";
        $sql .= " RIGHT JOIN `gl0_coa_level1` `l1`";
        $sql .= " ON `l1`.`company_id` = `l2`.`company_id`";
        $sql .= " AND `l1`.`coa_level1_id` = `l2`.`coa_level1_id`";
        $sql .= " AND `l`.`coa_id` IN ({$filter['admin_expense_account_id']})";
        $sql .= " WHERE `l`.`company_id` = '{$filter['company_id']}'";
        $sql .= " AND `l`.`company_branch_id` = '{$filter['company_branch_id']}'";
        $sql .= " AND `l`.`fiscal_year_id` = '{$filter['fiscal_year_id']}'";
        $sql .= " AND `l`.`document_date` >= '{$filter['from_date']}'";
        $sql .= " AND `l`.`document_date` <= '{$filter['to_date']}'";
        
        if(!empty($filter['project_id'])){
            $sql .= " AND `l`.`project_id` = '{$filter['project_id']}'";
        }

        if(!empty($filter['sub_project_id'])){
            $sql .= " AND `l`.`sub_project_id` = '{$filter['sub_project_id']}'";
        }

        if(!empty($filter['job_order_id'])){
            $sql .= " AND `l`.`job_order_id` = '{$filter['job_order_id']}'";
        }

        $sql .= " GROUP BY `l3`.`coa_level3_id`, `l3`.`level3_code`, `level3_display_name`";
        $sql .= " HAVING SUM(`l`.`debit`-`l`.`credit`) != 0";
        $sql .= " ORDER BY `l1`.`level1_code`, `l2`.`level2_code`, `l3`.`level3_code` ASC";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getFinancialCharges($filter, $heading){
        $sql = "";
        $sql .= "SELECT";
        $sql .= " '{$heading['title']}' as `type`,";
        $sql .= " CONCAT(`l1`.`level1_code`,'-',`l2`.`level2_code`,'-',`l3`.`level3_code`,': ',`l3`.`name`) AS `level3_display_name`,";
        $sql .= " SUM(`l`.`debit`-`l`.`credit`) AS `balance`";

        $sql .= " FROM `core_ledger` l";
        $sql .= " RIGHT JOIN `gl0_coa_level3` `l3`";
        $sql .= " ON `l3`.`company_id` = `l`.`company_id`";
        $sql .= " AND `l3`.`coa_level3_id` = `l`.`coa_id`";
        $sql .= " RIGHT JOIN `gl0_coa_level2` `l2`";
        $sql .= " ON `l2`.`company_id` = `l3`.`company_id`";
        $sql .= " AND `l2`.`coa_level2_id` = `l3`.`coa_level2_id`";
        $sql .= " RIGHT JOIN `gl0_coa_level1` `l1`";
        $sql .= " ON `l1`.`company_id` = `l2`.`company_id`";
        $sql .= " AND `l1`.`coa_level1_id` = `l2`.`coa_level1_id`";
        $sql .= " AND `l`.`coa_id` IN ({$filter['financial_charges_account_id']})";
        $sql .= " WHERE `l`.`company_id` = '{$filter['company_id']}'";
        $sql .= " AND `l`.`company_branch_id` = '{$filter['company_branch_id']}'";
        $sql .= " AND `l`.`fiscal_year_id` = '{$filter['fiscal_year_id']}'";
        $sql .= " AND `l`.`document_date` >= '{$filter['from_date']}'";
        $sql .= " AND `l`.`document_date` <= '{$filter['to_date']}'";

        if(!empty($filter['project_id'])){
            $sql .= " AND `l`.`project_id` = '{$filter['project_id']}'";
        }

        if(!empty($filter['sub_project_id'])){
            $sql .= " AND `l`.`sub_project_id` = '{$filter['sub_project_id']}'";
        }

        if(!empty($filter['job_order_id'])){
            $sql .= " AND `l`.`job_order_id` = '{$filter['job_order_id']}'";
        }

        $sql .= " GROUP BY `l3`.`coa_level3_id`, `l3`.`level3_code`, `level3_display_name`";
        $sql .= " HAVING SUM(`l`.`debit`-`l`.`credit`) != 0";
        $sql .= " ORDER BY `l1`.`level1_code`, `l2`.`level2_code`, `l3`.`level3_code` ASC";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getSaleMarkiting($filter, $heading){
        $sql = "";
        $sql .= "SELECT";
        $sql .= " '{$heading['title']}' as `type`,";
        $sql .= " CONCAT(`l1`.`level1_code`,'-',`l2`.`level2_code`,'-',`l3`.`level3_code`,': ',`l3`.`name`) AS `level3_display_name`,";
        $sql .= " SUM(`l`.`debit`-`l`.`credit`) AS `balance`";

        $sql .= " FROM `core_ledger` l";
        $sql .= " RIGHT JOIN `gl0_coa_level3` `l3`";
        $sql .= " ON `l3`.`company_id` = `l`.`company_id`";
        $sql .= " AND `l3`.`coa_level3_id` = `l`.`coa_id`";
        $sql .= " RIGHT JOIN `gl0_coa_level2` `l2`";
        $sql .= " ON `l2`.`company_id` = `l3`.`company_id`";
        $sql .= " AND `l2`.`coa_level2_id` = `l3`.`coa_level2_id`";
        $sql .= " RIGHT JOIN `gl0_coa_level1` `l1`";
        $sql .= " ON `l1`.`company_id` = `l2`.`company_id`";
        $sql .= " AND `l1`.`coa_level1_id` = `l2`.`coa_level1_id`";
        $sql .= " AND `l`.`coa_id` IN ({$filter['sale_marketing_account_id']})";
        $sql .= " WHERE `l`.`company_id` = '{$filter['company_id']}'";
        $sql .= " AND `l`.`company_branch_id` = '{$filter['company_branch_id']}'";
        $sql .= " AND `l`.`fiscal_year_id` = '{$filter['fiscal_year_id']}'";
        $sql .= " AND `l`.`document_date` >= '{$filter['from_date']}'";
        $sql .= " AND `l`.`document_date` <= '{$filter['to_date']}'";

        if(!empty($filter['project_id'])){
            $sql .= " AND `l`.`project_id` = '{$filter['project_id']}'";
        }

        if(!empty($filter['sub_project_id'])){
            $sql .= " AND `l`.`sub_project_id` = '{$filter['sub_project_id']}'";
        }

        if(!empty($filter['job_order_id'])){
            $sql .= " AND `l`.`job_order_id` = '{$filter['job_order_id']}'";
        }

        $sql .= " GROUP BY `l3`.`coa_level3_id`, `l3`.`level3_code`, `level3_display_name`";
        $sql .= " HAVING SUM(`l`.`debit`-`l`.`credit`) != 0";
        $sql .= " ORDER BY `l1`.`level1_code`, `l2`.`level2_code`, `l3`.`level3_code` ASC";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getNonOperatingIncome($filter, $heading){
        $sql = "";
        $sql .= "SELECT";
        $sql .= " '{$heading['title']}' as `type`,";
        $sql .= " CONCAT(`l1`.`level1_code`,'-',`l2`.`level2_code`,'-',`l3`.`level3_code`,': ',`l3`.`name`) AS `level3_display_name`,";
        $sql .= " SUM(`l`.`credit`-`l`.`debit`) AS `balance`";

        $sql .= " FROM `core_ledger` l";
        $sql .= " RIGHT JOIN `gl0_coa_level3` `l3`";
        $sql .= " ON `l3`.`company_id` = `l`.`company_id`";
        $sql .= " AND `l3`.`coa_level3_id` = `l`.`coa_id`";
        $sql .= " RIGHT JOIN `gl0_coa_level2` `l2`";
        $sql .= " ON `l2`.`company_id` = `l3`.`company_id`";
        $sql .= " AND `l2`.`coa_level2_id` = `l3`.`coa_level2_id`";
        $sql .= " RIGHT JOIN `gl0_coa_level1` `l1`";
        $sql .= " ON `l1`.`company_id` = `l2`.`company_id`";
        $sql .= " AND `l1`.`coa_level1_id` = `l2`.`coa_level1_id`";
        $sql .= " AND `l`.`coa_id` IN ({$filter['non_operating_income_account_id']})";
        $sql .= " WHERE `l`.`company_id` = '{$filter['company_id']}'";
        $sql .= " AND `l`.`company_branch_id` = '{$filter['company_branch_id']}'";
        $sql .= " AND `l`.`fiscal_year_id` = '{$filter['fiscal_year_id']}'";
        $sql .= " AND `l`.`document_date` >= '{$filter['from_date']}'";
        $sql .= " AND `l`.`document_date` <= '{$filter['to_date']}'";

        if(!empty($filter['project_id'])){
            $sql .= " AND `l`.`project_id` = '{$filter['project_id']}'";
        }

        if(!empty($filter['sub_project_id'])){
            $sql .= " AND `l`.`sub_project_id` = '{$filter['sub_project_id']}'";
        }

        if(!empty($filter['job_order_id'])){
            $sql .= " AND `l`.`job_order_id` = '{$filter['job_order_id']}'";
        }

        $sql .= " GROUP BY `l3`.`coa_level3_id`, `l3`.`level3_code`, `level3_display_name`";
        $sql .= " HAVING SUM(`l`.`credit`-`l`.`debit`) != 0";
        $sql .= " ORDER BY `l1`.`level1_code`, `l2`.`level2_code`, `l3`.`level3_code` ASC";

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getTaxPaid($filter, $heading){
        $sql = "";
        $sql .= "SELECT";
        $sql .= " '{$heading['title']}' as `type`,";
        $sql .= " CONCAT(`l1`.`level1_code`,'-',`l2`.`level2_code`,'-',`l3`.`level3_code`,': ',`l3`.`name`) AS `level3_display_name`,";
        $sql .= " SUM(`l`.`debit`-`l`.`credit`) AS `balance`";

        $sql .= " FROM `core_ledger` l";
        $sql .= " RIGHT JOIN `gl0_coa_level3` `l3`";
        $sql .= " ON `l3`.`company_id` = `l`.`company_id`";
        $sql .= " AND `l3`.`coa_level3_id` = `l`.`coa_id`";
        $sql .= " RIGHT JOIN `gl0_coa_level2` `l2`";
        $sql .= " ON `l2`.`company_id` = `l3`.`company_id`";
        $sql .= " AND `l2`.`coa_level2_id` = `l3`.`coa_level2_id`";
        $sql .= " RIGHT JOIN `gl0_coa_level1` `l1`";
        $sql .= " ON `l1`.`company_id` = `l2`.`company_id`";
        $sql .= " AND `l1`.`coa_level1_id` = `l2`.`coa_level1_id`";
        $sql .= " AND `l`.`coa_id` IN ({$filter['tax_paid_account_id']})";
        $sql .= " WHERE `l`.`company_id` = '{$filter['company_id']}'";
        $sql .= " AND `l`.`company_branch_id` = '{$filter['company_branch_id']}'";
        $sql .= " AND `l`.`fiscal_year_id` = '{$filter['fiscal_year_id']}'";
        $sql .= " AND `l`.`document_date` >= '{$filter['from_date']}'";
        $sql .= " AND `l`.`document_date` <= '{$filter['to_date']}'";

        if(!empty($filter['project_id'])){
            $sql .= " AND `l`.`project_id` = '{$filter['project_id']}'";
        }

        if(!empty($filter['sub_project_id'])){
            $sql .= " AND `l`.`sub_project_id` = '{$filter['sub_project_id']}'";
        }

        if(!empty($filter['job_order_id'])){
            $sql .= " AND `l`.`job_order_id` = '{$filter['job_order_id']}'";
        }

        $sql .= " GROUP BY `l3`.`coa_level3_id`, `l3`.`level3_code`, `level3_display_name`";
        $sql .= " HAVING SUM(`l`.`debit`-`l`.`credit`) != 0";
        $sql .= " ORDER BY `l1`.`level1_code`, `l2`.`level2_code`, `l3`.`level3_code` ASC";

        $query = $this->db->query($sql);
        return $query->rows;
    }

}

?>