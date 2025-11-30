<?php

class ModelCommonDocument extends HModel {

    protected function getTable() {
        return 'core_document';
    }

    protected function getView() {
        return 'vw_core_document';
    }

    protected function getPrimaryKey() {
        return 'document_id';
    }

    public function getPendingDocuments($filter = array(), $sort_order = array()) {

        $sql = "SELECT ";
        $sql .= " `l`.`company_id`            AS `company_id`,";
        $sql .= " `l`.`company_branch_id`     AS `company_branch_id`,";
        $sql .= " `l`.`fiscal_year_id`        AS `fiscal_year_id`,";
        $sql .= " `d`.`is_post`               AS `is_post`,";
        $sql .= " `l`.`partner_type_id`       AS `partner_type_id`,";
        $sql .= " `l`.`partner_id`            AS `partner_id`,";
        $sql .= " `p`.`name`                  AS `name`,";
        $sql .= " `l`.`ref_document_type_id`  AS `ref_document_type_id`,";
        $sql .= " `l`.`ref_document_identity` AS `ref_document_identity`,";
        $sql .= " `d`.`document_id`           AS `document_id`,";
        $sql .= " `d`.`document_date`         AS `document_date`,";
        $sql .= " `d`.`base_amount`           AS `document_amount`,";
        $sql .= " `d`.`route`                 AS `route`,";
        $sql .= " `d`.`primary_key_field`     AS `primary_key_field`,";
        $sql .= " `d`.`primary_key_value`     AS `primary_key_value`,";
        $sql .= " `l`.`po_no`		      AS `po_no`,";
        $sql .= " `l`.`dc_no`		      AS `dc_no`,";
        $sql .= " `l`.`coa_id`            AS `coa_id`,";
        $sql .= " `p`.partner_category_id       AS `partner_category_id`,";
        $sql .= " SUM((CASE WHEN (`l`.`ref_document_type_id` IN (1,3,24)) THEN (`l`.`credit` - `l`.`debit`) ELSE (`l`.`debit` - `l`.`credit`) END)) AS `outstanding_amount`,";
        $sql .= " SUM((CASE WHEN (`l`.`ref_document_type_id` IN (2,11,34,39)) THEN (`l`.`debit` - `l`.`credit`) ELSE 0 END)) AS `debit_amount`,";
        $sql .= " SUM((CASE WHEN (`l`.`ref_document_type_id` IN (1,24)) THEN (`l`.`credit` - `l`.`debit`) ELSE 0 END)) AS `credit_amount`";
        $sql .= " FROM `core_ledger` `l`";
        $sql .= " JOIN `core_partner` `p`	ON `l`.`company_id` = `p`.`company_id` AND `l`.`partner_type_id` = `p`.`partner_type_id` AND `l`.`partner_id` = `p`.`partner_id` AND `l`.`coa_id` = `p`.`outstanding_account_id`";
        $sql .= " LEFT JOIN `core_document` `d` ON `d`.`document_type_id` = `l`.`ref_document_type_id` AND `d`.`document_identity` = `l`.`ref_document_identity`";

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

        $sql .= "  GROUP BY `l`.`company_id`,`l`.`fiscal_year_id`,`l`.`partner_type_id`,`l`.`partner_id`,`l`.`ref_document_type_id`,`l`.`ref_document_identity`";
        $sql .= "  HAVING  outstanding_amount > 0 ";
        $sql .= " ORDER BY `d`.`document_date`,`l`.`ref_document_identity` " ;

//        if($sort_order) {
//            $sql .= " ORDER BY l.document_date" . implode(',',$sort_order);
//        }
        $query = $this->conn->query($sql);
        return $query->rows;

    }

    public function getOutstandingDocuments($filter, $sort_order='') {
        $sql = "SELECT *";
        $sql .= " FROM " . DB_PREFIX . "vw_core_outstanding_document";
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
        // d(array($sql,$query),true);
        return $query->rows;

    }

    public function getBranchTrasferDocuments($filter, $sort_order) {
        $sql = "SELECT *";
        $sql .= " FROM " . DB_PREFIX . "vw_transfer_outstanding";
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

        return $query->rows;

    }
}

?>