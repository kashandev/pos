<?php

class ModelInventorySaleInvoice extends HModel {

    protected function getTable() {
        return 'ins_sale_invoice';
    }

    protected function getView() {
        return 'vw_ins_sale_invoice';
    }

    protected function getListRecord() {

        return 'vw_ins_sale_invoice_pending_status';
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



    public function getNextDocument($document_type_id,$customer_code='',$customerID='') {
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

        $table_name = $row['table_name'];
        $aSearch = array('{FY}','{BC}','{CC}');
        $aReplace = array($this->session->data['fy_code'], $this->session->data['branch_code'],$customer_code);
        $prefix = str_replace($aSearch, $aReplace, $row['document_prefix']);

        $sql = "SELECT MAX(document_no) as max_no FROM `".$table_name."`";
        $sql .= " WHERE `company_id` = '".$this->session->data['company_id']."'";
        $sql .= " AND `document_prefix` = '".$prefix."'";
        $sql .= " AND `partner_id` = '".$customerID."'";
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


}

?>