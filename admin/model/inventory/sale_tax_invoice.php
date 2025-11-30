<?php

class ModelInventorySaleTaxInvoice extends HModel {

    protected function getTable() {
        return 'ins_sale_tax_invoice';
    }

    protected function getView() {
        return 'vw_ins_sale_tax_invoice';
    }

    protected function getListRecord() {

        return 'vw_ins_sale_tax_invoice';
    }

    public function getLists($data) {
        if(!isset($data['filter'])) {
            $data['filter'] = array();
        }
        if(!isset($data['criteria'])) {
            $data['criteria'] = array();
        }
        $filterSQL = $this->getFilterString($data['filter']);
        $criteriaSQL = $this->getCriteriaString($data['criteria']);

        $sql = "SELECT count(*) as total";
        $sql .= " FROM " . DB_PREFIX . $this->getListRecord();
        if($filterSQL) {
            $sql .= " WHERE " . $filterSQL;
        }
        $query = $this->conn->query($sql);
        $table_total = $query->row['total'];

        $sql = "SELECT count(*) as total";
        $sql .= " FROM " . DB_PREFIX . $this->getListRecord();
        if($filterSQL) {
            $sql .= " WHERE " . $filterSQL;
        }
        $query = $this->conn->query($sql);
        $total = $query->row['total'];

        $sql = "SELECT *";
        $sql .= " FROM " . DB_PREFIX . $this->getListRecord();
        if($filterSQL) {
            $sql .= " WHERE " . $filterSQL;
        }
        if($criteriaSQL) {
            $sql .= $criteriaSQL;
        }

//        d($sql,true);
        $query = $this->conn->query($sql);
        $lists = $query->rows;

        return array('table_total' => $table_total, 'total' => $total, 'lists' => $lists);
    }

    public function getNextDocument($document_type_id) {
        $sql = "SELECT * FROM `core_company_branch_document_prefix`";
        $sql .= " where `company_id`='".$this->session->data['company_id']."'";
        $sql .= " AND `company_branch_id`='".$this->session->data['company_branch_id']."'";
        $sql .= " AND `document_type_id` = '".$document_type_id."'";
        //d($sql,true);
        $query = $this->conn->query($sql);
        $row = $query->row;
        if(empty($row)) {
            // d($sql,true);
            $sql = "SELECT * FROM `const_document_type` where `document_type_id` = '".$document_type_id."'";
            $query = $this->conn->query($sql);
            $row = $query->row;
        }

        $table_name = $row['table_name'];
        $aSearch = array('{FY}','{BC}');
        $aReplace = array($this->session->data['fy_code'], $this->session->data['branch_code']);
        $prefix = str_replace($aSearch, $aReplace, $row['document_prefix']);

        $sql = "SELECT MAX(document_no) as max_no FROM `".$table_name."`";
        $sql .= " WHERE `company_id` = '".$this->session->data['company_id']."'";
        $sql .= " AND `company_branch_id`='".$this->session->data['company_branch_id']."'";
        // $sql .= " AND `document_prefix` = '".$prefix."'";
        $query = $this->conn->query($sql);
        $record = $query->row;

        if(empty($record['max_no'])) {
            $max_no =  1;
        } else {
            $max_no =  $record['max_no']+1;
        }
        $document_identity = $prefix . str_pad($max_no,$row['zero_padding'],"0",STR_PAD_LEFT);

        return array(
            'sql' => $sql,
            'document_type' => $row['document_name'],
            'document_no' => $max_no,
            'document_prefix' => $prefix,
            'document_identity' => $document_identity,
            'route' => $row['route'],
            'table_name' => $row['table_name'],
            'primary_key' => $row['primary_key']
        );
    }

    public function getSaleInvNextDocument($document_type_id)
    {
        $sql = "SELECT * FROM `core_company_branch_document_prefix`";
        $sql .= " where `company_id`='".$this->session->data['company_id']."'";
        $sql .= " AND `company_branch_id`='".$this->session->data['company_branch_id']."'";
        $sql .= " AND `document_type_id` = '".$document_type_id."'";
        $query = $this->conn->query($sql);
        $row = $query->row;
        if(empty($row)) {
            $sql = "SELECT * FROM `const_document_type` where `document_type_id` = '".$document_type_id."'";
            $query = $this->conn->query($sql);
            $row = $query->row;
        }

        $table_name = 'ins_sale_tax_invoice';
        $aSearch = array('{FY}','{BC}');
        $aReplace = array($this->session->data['fy_code'], $this->session->data['branch_code']);
        $prefix = str_replace($aSearch, $aReplace, $row['document_prefix']);

        $sql = "SELECT MAX(document_no) as max_no FROM `".$table_name."`";
        $sql .= " WHERE `company_id` = '".$this->session->data['company_id']."'";
        $sql .= " AND `company_branch_id`='".$this->session->data['company_branch_id']."'";
        $sql .= " AND `sale_type` = 'sale_invoice'";
        $query = $this->conn->query($sql);
        $record = $query->row;

        if(empty($record['max_no'])) {
            $max_no =  1;
        } else {
            $max_no =  $record['max_no']+1;
        }
        $document_identity = $prefix . str_pad($max_no,$row['zero_padding'],"0",STR_PAD_LEFT);

        return array(
            'sql' => $sql,
            'document_type' => 'Sales Tax Invoice',
            'document_no' => $max_no,
            'document_prefix' => $prefix,
            'document_identity' => $document_identity,
            'route' => 'inventory/sale_tax_invoice',
            'table_name' => 'ins_sale_tax_invoice',
            'primary_key' => 'sale_tax_invoice_id'
        );
    }

    public function getSaleTaxNo()
    {
        $sql = "SELECT MAX(sale_tax_no) as sale_tax_no FROM `ins_sale_tax_invoice`";
        $sql .= " WHERE `company_id` = '".$this->session->data['company_id']."'";
        $sql .= " AND `sale_type` = 'sale_tax_invoice'";
        $query = $this->conn->query($sql);
        return $record = $query->row;
    }

    public function getPreviousBalance($filter) {
        
        $sql =  "SELECT";
        $sql .= " SUM(`l`.`debit`) - SUM(`l`.`credit`) AS `previous_balance`";
        $sql .= " FROM `core_ledger` `l`";
        $sql .= " INNER JOIN `core_partner` `p` ON `l`.`partner_id` = `p`.`partner_id`";
        $sql .= " AND `l`.`coa_id` = `p`.`outstanding_account_id`";
        $sql .= " {$filter}";
        $query = $this->conn->query($sql);
        if( $query->row ){
            return $query->row['previous_balance'];
        }
        return 0;
    }

     public function get_sale_tax_invoice($filter=array(), $sort_order=array()){
        $sql = "SELECT *";
        $sql .= " FROM " . DB_PREFIX . $this->getTable();
        if($filter) {
            if(is_array($filter)) {
                $implode = array();
                foreach($filter as $column => $value) {
                    $implode[] = "`$column`='$value'";
                }
                if($implode)
                    $sql .= " WHERE " . implode(" AND ", $implode);
            } else {
                $sql .= " WHERE " . $filter;
            }
        }
        if($sort_order) {
            $sql .= " ORDER BY " . implode(',',$sort_order);
        }
        $query = $this->conn->query($sql);
        return $query->row;
    }


}

?>