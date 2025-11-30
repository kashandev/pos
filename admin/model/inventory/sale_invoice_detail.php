<?php

class ModelInventorySaleInvoiceDetail extends HModel
{

    protected function getTable()
    {
        return 'ins_sale_invoice_detail';
    }

    protected function getView()
    {
        return 'vw_ins_sale_invoice_detail';
    }

    public function get_dead_items($filter)
    {
        $sql = "SELECT * FROM vw_in0_product WHERE product_id ";
        $sql .= "NOT IN(SELECT product_id FROM vw_ins_sale_invoice_detail si WHERE ";
        $sql .= "si.document_date BETWEEN NOW() AND NOW()-INTERVAL ".$filter['month']." MONTH ";
        $sql .= "and si.company_id = '" . $filter['company_id'] . "' AND ";
        $sql .= "si.fiscal_year_id = '" . $filter['fiscal_year_id'] . "' ";
        $sql .= "AND si.company_branch_id = '" . $filter['company_branch_id'] . "') ";
        $query = $this->conn->query($sql);
        return $query->rows;

    }

}

?>