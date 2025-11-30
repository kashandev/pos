<?php

class ModelCommonPartner extends HModel {

    protected function getTable() {
        return 'core_partner';
    }

    protected function getView() {
        return 'vw_core_partner';
    }

    public function getPartners($filter=array(), $sort_order=array()) {
        $sql = "SELECT *";
        $sql .= " FROM " . DB_PREFIX . $this->getView();
        if($filter) {
            if(is_array($filter)) {
                //$table_columns = $this->getTableColumns($this->getTable());
                $implode = array();
                foreach($filter as $column => $value) {
                    //if(in_array($column,$table_columns)) {
                    $implode[] = "`$column`='$value'";
                    //}
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

        $partners =  $query->rows;
        $arrPartners = array();
        $model_document = $this->load->model('common/document');
        foreach($partners as $partner) {
            $filter= array();
            $filter['company_id'] = $this->session->data['company_id'];
            $filter['company_branch_id'] = $this->session->data['company_branch_id'];
            $filter['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $filter['partner_id'] = $partner['partner_id'];
            $arrDocuments = array();
            $documents = $model_document->getOutstandingDocuments($filter);
            $outstanding = $this->getOutstanding("l.company_id='".$filter['company_id']."' AND l.company_branch_id='".$filter['company_branch_id']."' AND l.fiscal_year_id='".$filter['fiscal_year_id']."' AND l.partner_id='".$filter['partner_id']."'");
            $partner['total_outstanding'] = $outstanding['outstanding'];
            foreach($documents as $document) {
                if($document['ref_document_identity'] != '') {
                    $arrDocuments[$document['ref_document_identity']] = $document;
                    $arrDocuments[$document['ref_document_identity']]['href'] = $this->url->link($document['route'] . '/update', 'token=' . $this->session->data['token'] . '&' . $document['primary_key_field'] . '=' . $document['primary_key_value'], 'SSL');
                }
            }
            $COAS = array();
            $COAS[$partner['outstanding_account_id']] = array(
                'coa_level3_id' => $partner['outstanding_account_id'],
                'level3_display_name' => $partner['outstanding_account']
            );
//            $COAS[$partner['cash_account_id']] = array(
//                'coa_level3_id' => $partner['cash_account_id'],
//                'level3_display_name' => $partner['cash_account']
//            );
            $COAS[$partner['advance_account_id']] = array(
                'coa_level3_id' => $partner['advance_account_id'],
                'level3_display_name' => $partner['advance_account']
            );
            $arrPartners[$partner['partner_id']] = $partner;
            $arrPartners[$partner['partner_id']]['coas'] = $COAS;
            $arrPartners[$partner['partner_id']]['documents'] = $arrDocuments;
        }
        return $arrPartners;
    }

    public function getPartner($filter=array(), $sort_order=array()) {
        $sql = "SELECT *";
        $sql .= " FROM " . DB_PREFIX . $this->getView();
        if($filter) {
            if(is_array($filter)) {
                //$table_columns = $this->getTableColumns($this->getTable());
                $implode = array();
                foreach($filter as $column => $value) {
                    //if(in_array($column,$table_columns)) {
                    $implode[] = "`$column`='$value'";
                    //}
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
        $partner =  $query->row;
        if(!empty($partner)) {
            $model_document = $this->load->model('common/document');
            $filter['company_id'] = $this->session->data['company_id'];
            $filter['company_branch_id'] = $this->session->data['company_branch_id'];
            $filter['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $filter['partner_id'] = $partner['partner_id'];
            $arrDocuments = array();
            $documents = $model_document->getOutstandingDocuments($filter);
            $outstanding = $this->getOutstanding(array('l.company_id' => $filter['company_id'], 'l.company_branch_id' => $filter['company_branch_id'], 'l.fiscal_year_id' => $filter['fiscal_year_id'], 'l.partner_id' => $filter['partner_id']));
            $partner['total_outstanding'] = $outstanding['outstanding'];
            foreach($documents as $document) {
                $arrDocuments[$document['ref_document_identity']] = $document;
                $arrDocuments[$document['ref_document_identity']]['href'] = $this->url->link($document['route'] . '/update', 'token=' . $this->session->data['token'] . '&' . $document['primary_key_field'] . '=' . $document['primary_key_value'], 'SSL');
            }
            $COAS = array();
            $COAS[$partner['outstanding_account_id']] = array(
                'coa_level3_id' => $partner['outstanding_account_id'],
                'level3_display_name' => $partner['outstanding_account']
            );
            $COAS[$partner['cash_account_id']] = array(
                'coa_level3_id' => $partner['cash_account_id'],
                'level3_display_name' => $partner['cash_account']
            );
            $COAS[$partner['advance_account_id']] = array(
                'coa_level3_id' => $partner['advance_account_id'],
                'level3_display_name' => $partner['advance_account']
            );
            $partner['coas'] = $COAS;
            $partner['documents'] = $arrDocuments;
        }

        return $partner;
    }

    public function getPartnerJson($search, $page, $limit=25, $filter=array()) {
        if($page=='') {
            $page = 0;
        }
        $offset = $page*$limit;
        $arrWhere = array();
        $filter['company_branch_id'] = $this->session->data['company_branch_id'];
        $arrWhere[] = "(`name` LIKE '".$search."%' OR `name` LIKE '".$search."%')";
        $arrWhere[] = "`company_branch_id` = '".$filter['company_branch_id']."'";
        if(isset($filter['partner_category_id']) && $filter['partner_category_id']) {
            $arrWhere[] = "`partner_category_id` = '".$filter['partner_category_id']."'";
        }
        if(isset($filter['partner_type_id']) && $filter['partner_type_id']) {
            $arrWhere[] = "`partner_type_id` = '".$filter['partner_type_id']."'";
        }
        $sql = "SELECT count(*) as total_records";
        $sql .= " FROM `vw_core_partner`";
        $sql .= " WHERE " . implode(' AND ', $arrWhere);
        $query = $this->conn->query($sql);
        $row = $query->row;
        $total_records = $row['total_records'];

        $sql = "SELECT *, partner_id as id";
        $sql .= " FROM `vw_core_partner`";
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

    public function getOutstandings($filter = array(), $sort_order=array()) {
        $sql = "SELECT p.partner_type_id, p.partner_id, p.partner_type, p.name AS partner_name, SUM(debit-credit) AS outstanding";
        $sql .= " FROM core_ledger l";
        $sql .= " INNER JOIN `core_partner` p ON p.`partner_type_id` = l.`partner_type_id` AND p.`partner_id` = l.`partner_id` AND p.`outstanding_account_id` = l.`coa_id`";
        if($filter) {
            if(is_array($filter)) {
                //$table_columns = $this->getTableColumns($this->getTable());
                $implode = array();
                foreach($filter as $column => $value) {
                    //if(in_array($column,$table_columns)) {
                    $implode[] = "$column='$value'";
                    //}
                }
                if($implode)
                    $sql .= " WHERE " . implode(" AND ", $implode);
            } else {
                $sql .= " WHERE " . $filter;
            }
        }
        $sql .= " GROUP BY p.partner_type_id, p.partner_id, p.partner_type, p.name";
        if($sort_order) {
            $sql .= " ORDER BY " . implode(',',$sort_order);
        }
        $query = $this->conn->query($sql);
        return $query->rows;
    }

    public function getOutstanding($filter = array(), $sort_order=array()) {
        $sql = "SELECT p.partner_type_id, p.partner_id, p.partner_type, p.name AS partner_name, SUM(debit-credit) AS outstanding";
        $sql .= " FROM core_ledger l";
        $sql .= " INNER JOIN `core_partner` p ON p.`partner_type_id` = l.`partner_type_id` AND p.`partner_id` = l.`partner_id` AND p.`outstanding_account_id` = l.`coa_id`";
        if($filter) {
            if(is_array($filter)) {
                //$table_columns = $this->getTableColumns($this->getTable());
                $implode = array();
                foreach($filter as $column => $value) {
                    //if(in_array($column,$table_columns)) {
                    $implode[] = "$column='$value'";
                    //}
                }
                if($implode)
                    $sql .= " WHERE " . implode(" AND ", $implode);
            } else {
                $sql .= " WHERE " . $filter;
            }
        }
        $sql .= " GROUP BY p.partner_type_id, p.partner_id, p.partner_type, p.name";
        if($sort_order) {
            $sql .= " ORDER BY " . implode(',',$sort_order);
        }
        $query = $this->conn->query($sql);
        return $query->row;
    }

}

?>