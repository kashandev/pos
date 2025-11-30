<?php

class ModelCommonDocumentType extends HModel {

    protected function getTable() {
        return 'const_document_type';
    }
    
    public function getNextDocument($document_type_id) {
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
        $aSearch = array('{FY}','{BC}');
        $aReplace = array($this->session->data['fy_code'], $this->session->data['branch_code']);
        $prefix = str_replace($aSearch, $aReplace, $row['document_prefix']);

        $sql = "SELECT MAX(document_no) as max_no FROM `".$table_name."`";
        $sql .= " WHERE `company_id` = '".$this->session->data['company_id']."'";
        $sql .= " AND `document_prefix` = '".$prefix."'";
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