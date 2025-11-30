<?php

class ModelReportDeliveryChallanAgainstSales extends HModel {

    protected function getTable() {
        return 'vw_delivery_challan_detail';
    }


    public function getNonGstWithInvoice($filter=array()) {

        $sql =  " SELECT DISTINCT dc.`document_identity`,dc.`document_date`,dc.`po_no`,dc.`po_date` ";
        $sql .= " ,si.`document_identity` sale_no,si.`document_date` sale_date,c.`name`";
        $sql .= " FROM `ins_delivery_challan` dc";
        $sql .= " INNER JOIN `ins_sale_invoice_detail` sid ON sid.ref_document_identity = dc.document_identity";
        $sql .= " INNER JOIN `ins_sale_invoice` si ON si.`sale_invoice_id` = sid.`sale_invoice_id`";
        $sql .= " INNER JOIN `core_partner` c ON c.`partner_id` = dc.`partner_id` ";
        $sql .= " WHERE sid.ref_document_type_id = 16 AND dc.challan_type = 'Non GST' ";
        $sql .= " AND dc.company_id = '".$filter['company_id']."'";
        $sql .= " AND dc.company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND dc.fiscal_year_id = '".$filter['fiscal_year_id']."'";
        $sql .= " AND dc.`document_date` >= '".$filter['from_date']."' AND dc.`document_date` <= '".$filter['to_date']."'";

        if($filter['partner_id'] !='')
        {
            $sql .= " AND dc.partner_id = '".$filter['partner_id']."'";
        }
        $sql .= " ORDER BY dc.document_date,dc.`document_identity` ";
        $query = $this->conn->query($sql);
         return $query->rows;
    }
    public function getNonGstWithOutInvoice($filter=array()) {

        $sql =  " SELECT DISTINCT dc.`document_identity`,dc.`document_date`,dc.`po_no`,dc.`po_date` ";
        $sql .= " ,si.`document_identity` sale_no,si.`document_date` sale_date,c.`name`";
        $sql .= " FROM `ins_delivery_challan` dc";
        $sql .= " left JOIN `ins_sale_invoice_detail` sid ON sid.ref_document_identity = dc.document_identity";
        $sql .= " left JOIN `ins_sale_invoice` si ON si.`sale_invoice_id` = sid.`sale_invoice_id`";
        $sql .= " INNER JOIN `core_partner` c ON c.`partner_id` = dc.`partner_id` ";
        $sql .= "  WHERE sid.ref_document_type_id IS NULL AND sid.ref_document_identity IS NULL  AND dc.challan_type = 'Non GST' ";
        $sql .= " AND dc.company_id = '".$filter['company_id']."'";
        $sql .= " AND dc.company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND dc.fiscal_year_id = '".$filter['fiscal_year_id']."'";
        $sql .= " AND dc.`document_date` >= '".$filter['from_date']."' AND dc.`document_date` <= '".$filter['to_date']."'";

        if($filter['partner_id'] !='')
        {
            $sql .= " AND dc.partner_id = '".$filter['partner_id']."'";
        }
        $sql .= " ORDER BY dc.document_date,dc.`document_identity` ";
//        d($sql,true);
//        echo $sql;
//        exit;
        $query = $this->conn->query($sql);
         return $query->rows;
    }
    public function getGstWithInvoice($filter=array()) {

        $sql =  " SELECT DISTINCT dc.`document_identity`,dc.`document_date`,dc.`po_no`,dc.`po_date` ";
        $sql .= " ,si.`document_identity` sale_no,si.`document_date` sale_date,c.`name`";
        $sql .= " FROM `ins_delivery_challan` dc";
        $sql .= " INNER JOIN `ins_sale_tax_invoice_detail` sid ON sid.ref_document_identity = dc.document_identity";
        $sql .= " INNER JOIN `ins_sale_tax_invoice` si ON si.`sale_tax_invoice_id` = sid.`sale_tax_invoice_id`";
        $sql .= " INNER JOIN `core_partner` c ON c.`partner_id` = dc.`partner_id` ";
        $sql .= " WHERE sid.ref_document_type_id = 16 AND dc.challan_type = 'GST' ";
        $sql .= " AND dc.company_id = '".$filter['company_id']."'";
        $sql .= " AND dc.company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND dc.fiscal_year_id = '".$filter['fiscal_year_id']."'";
        $sql .= " AND dc.`document_date` >= '".$filter['from_date']."' AND dc.`document_date` <= '".$filter['to_date']."'";

        if($filter['partner_id'] !='')
        {
            $sql .= " AND dc.partner_id = '".$filter['partner_id']."'";
        }
        $sql .= " ORDER BY dc.document_date,dc.`document_identity` ";
        $query = $this->conn->query($sql);
         return $query->rows;
    }
    public function getGstWithOutInvoice($filter=array()) {

        $sql =  " SELECT DISTINCT dc.`document_identity`,dc.`document_date`,dc.`po_no`,dc.`po_date` ";
        $sql .= " ,si.`document_identity` sale_no,si.`document_date` sale_date,c.`name`";
        $sql .= " FROM `ins_delivery_challan` dc";
        $sql .= " left JOIN `ins_sale_tax_invoice_detail` sid ON sid.ref_document_identity = dc.document_identity";
        $sql .= " left JOIN `ins_sale_tax_invoice` si ON si.`sale_tax_invoice_id` = sid.`sale_tax_invoice_id`";
        $sql .= " INNER JOIN `core_partner` c ON c.`partner_id` = dc.`partner_id` ";
        $sql .= "  WHERE sid.ref_document_type_id IS NULL AND sid.ref_document_identity IS NULL  AND dc.challan_type = 'GST' ";
        $sql .= " AND dc.company_id = '".$filter['company_id']."'";
        $sql .= " AND dc.company_branch_id = '".$filter['company_branch_id']."'";
        $sql .= " AND dc.fiscal_year_id = '".$filter['fiscal_year_id']."'";
        $sql .= " AND dc.`document_date` >= '".$filter['from_date']."' AND dc.`document_date` <= '".$filter['to_date']."'";

        if($filter['partner_id'] !='')
        {
            $sql .= " AND dc.partner_id = '".$filter['partner_id']."'";
        }
        $sql .= " ORDER BY dc.document_date,dc.`document_identity` ";
//        d($sql,true);
        $query = $this->conn->query($sql);
         return $query->rows;
    }

}

?>