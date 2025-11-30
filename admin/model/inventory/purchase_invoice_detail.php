<?php

class ModelInventoryPurchaseInvoiceDetail extends HModel {

    protected function getTable() {
        return 'inp_purchase_invoice_detail';
    }

    protected function getView() {
        return 'vw_inp_purchase_invoice_detail';
    }

    public function getDocumentForPurchaseReturn($filter)
    {
    	$sql = " SELECT *, SUM(amount) as amount ,
    			 SUM(qty) as qty, 
    			 SUM(discount_amount) as discount_amount , 
    			 SUM(gross_amount) as gross_amount, 
    			 SUM(other_expence) as other_expence, 
    			 SUM(tax_amount) as tax_amount, 
    			 SUM(total_amount) as total_amount , 
    			 SUM(base_total) as base_total   
    			 FROM vw_inp_purchase_invoice_detail ";
    	$sql .= $filter;
    	$sql .= " GROUP BY product_id ";
    	$query = $this->conn->query($sql);
        return $query->rows;
    }

}

?>