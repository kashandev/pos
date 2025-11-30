<?php

final class DocumentType {

    private $document_types = array();
    private $db;
    private $session;

    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->session = $registry->get('session');

        if (isset($this->session->data['user_id']) && isset($this->session->data['company_branch_id'])) {
            $sql = "SELECT *";
            $sql .= " FROM `" . DB_PREFIX . "company_branch_document_prefix`";
            $sql .= " WHERE company_branch_id = '".$this->session->data['company_branch_id']."'";
            $query = $this->db->query($sql);

            foreach ($query->rows as $result) {
                $this->document_types[] = array(
                    'document_type_id' => $result['document_type_id'],
                    'code' => $result['document_type_code'],
                    'zero_prefix' => $result['document_type_zero_prefix'],
                    'table_name' => $result['table_name'],
                );
            }
        }
    }

    public function getCodeByID($id) {
        $code = '';
        foreach($this->document_types as $document_type) {
            if($document_type['document_type_id'] == $id) {
                $code = $document_type['code'];
            }
        }
        return $code;
    }

    public function getNameByID($id) {
        $name = '';
        foreach($this->document_types as $document_type) {
            if($document_type['document_type_id'] == $id) {
                $name = $document_type['name'];
            }
        }
        return $name;
    }

    public function getNameByCode($code) {
        $name = '';
        foreach($this->document_types as $document_type) {
            if($document_type['code'] == $code) {
                $name = $document_type['name'];
            }
        }
        return $name;
    }

    public function getNextInvoiceNo($document_type_id) {
        $table_name = '';
        $prefix = '';
        $zero_prefix = 0;
        foreach($this->document_types as $document_type) {
            if($document_type['document_type_id'] == $document_type_id) {
                $table_name = $document_type['table_name'];
                $prefix = $document_type['code'];
                $zero_prefix = $document_type['zero_prefix'];
            }
        }
        
        $sql = "SELECT IF(ISNULL(MAX(document_no)),0,MAX(document_no)) AS document_no";
        $sql .= " FROM `" . DB_PREFIX . $table_name . "`";
        $sql .= " WHERE fiscal_year_id = '" . $this->session->data['fiscal_year_id'] . "'";
        $sql .= " AND company_id = '" . $this->session->data['company_id'] . "'";
        $sql .= " AND document_prefix = '" . $prefix . "'";
        
        $query = $this->db->query($sql);
        $document_no = $query->row['document_no'] + 1;
        $invoice_no = $prefix . str_pad($document_no, $zero_prefix, "0", STR_PAD_LEFT);
        return array('document_prefix' => $prefix, 'document_no' => $document_no, 'invoice_no' => $invoice_no);
    }
}

?>