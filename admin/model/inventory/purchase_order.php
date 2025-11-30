<?php

class ModelInventoryPurchaseOrder extends HModel {

    protected function getTable() {
        return 'inp_purchase_order';
    }
    protected function getView() {
        return 'vw_inp_purchase_order';
    }

    public function getPurchaseOrders($filter, $excl_document_id='') {
        $sql = "SELECT *";
        $sql .= " FROM `vw_inp_purchase_order_detail`";
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
        $sql.= " order by sort_order";
        $query = $this->conn->query($sql);
        // d($query);
        $purchase_orders = $query->rows;
        // d($purchase_orders);
        $arrPurchaseOrders = array();
        foreach($purchase_orders as $purchase_order) {
            $arrPurchaseOrders[$purchase_order['document_identity']]['purchase_order_id'] = $purchase_order['purchase_order_id'];
            $arrPurchaseOrders[$purchase_order['document_identity']]['company_id'] = $purchase_order['company_id'];
            $arrPurchaseOrders[$purchase_order['document_identity']]['company_branch_id'] = $purchase_order['company_branch_id'];
            $arrPurchaseOrders[$purchase_order['document_identity']]['fiscal_year_id'] = $purchase_order['fiscal_year_id'];
            $arrPurchaseOrders[$purchase_order['document_identity']]['document_id'] = $purchase_order['purchase_order_id'];
            $arrPurchaseOrders[$purchase_order['document_identity']]['document_date'] = $purchase_order['document_date'];
            $arrPurchaseOrders[$purchase_order['document_identity']]['document_identity'] = $purchase_order['document_identity'];
            $arrPurchaseOrders[$purchase_order['document_identity']]['manual_ref_no'] = $purchase_order['manual_ref_no'];
            $arrPurchaseOrders[$purchase_order['document_identity']]['partner_type_id'] = $purchase_order['partner_type_id'];
            $arrPurchaseOrders[$purchase_order['document_identity']]['partner_id'] = $purchase_order['partner_id'];
            $arrPurchaseOrders[$purchase_order['document_identity']]['document_currency_id'] = $purchase_order['document_currency_id'];
            $arrPurchaseOrders[$purchase_order['document_identity']]['conversion_rate'] = $purchase_order['conversion_rate'];
            $arrPurchaseOrders[$purchase_order['document_identity']]['base_currency_id'] = $purchase_order['base_currency_id'];
            $arrPurchaseOrders[$purchase_order['document_identity']]['discount'] = $purchase_order['discount'];
            //d(array($purchase_order, $arrPurchaseOrders), true);
            if(isset($arrPurchaseOrders[$purchase_order['document_identity']]['products'][$purchase_order['purchase_order_detail_id']])) {
                $arrPurchaseOrders[$purchase_order['document_identity']]['products'][$purchase_order['purchase_order_detail_id']]['order_qty'] += $purchase_order['qty'];
            } else {
                $arrPurchaseOrders[$purchase_order['document_identity']]['products'][$purchase_order['purchase_order_detail_id']] = array(
                    'sort_order' => $purchase_order['sort_order'],
                    'ref_document_id' => $purchase_order['purchase_order_detail_id'],
                    'ref_document_type_id' => $purchase_order['document_type_id'],
                    'ref_document_identity' => $purchase_order['document_identity'],
                    'product_id' => $purchase_order['product_id'],
                    'product_code' => $purchase_order['product_code'],
                    'product_name' => $purchase_order['product_name'],
                    'unit_id' => $purchase_order['unit_id'],
                    'unit' => $purchase_order['unit'],
                    'order_qty' => $purchase_order['qty'],
                    'utilized_qty' => 0,
                    'conversion_qty' => $purchase_order['conversion_qty'],
                    'base_unit_id' => $purchase_order['base_unit_id'],
                    'base_qty' => $purchase_order['base_qty'],
                    'document_currency_id' => $purchase_order['document_currency_id'],
                    'rate' => $purchase_order['rate'],
                    'amount' => $purchase_order['amount'],
                    'inc_tax_percent' => $purchase_order['inc_tax_percent'],
                    'inc_tax_amount' => $purchase_order['inc_tax_amount'],
                    'wht_percent' => $purchase_order['wht_percent'],
                    'wht_amount' => $purchase_order['wht_amount'],
                    'sales_tax_percent' => $purchase_order['sales_tax_percent'],
                    'sales_tax_amount' => $purchase_order['sales_tax_amount'],
                    'discount_amount' => $purchase_order['discount_amount'],
                    'discount_percent' => $purchase_order['discount_percent'],
                    'gross_amount' => $purchase_order['gross_amount'],
                    'net_rate' => $purchase_order['net_rate'],
                    'net_amount' => $purchase_order['net_amount'],
                    'total_amount' => $purchase_order['total_amount'],
                    'base_currency_id' => $purchase_order['base_currency_id'],
                    'conversion_rate' => $purchase_order['conversion_rate'],
                    'base_amount' => $purchase_order['base_amount'],
                );
            }

            //Purchase Invoice against Purchase Order
            $sql = "SELECT *";
            $sql .= " FROM `vw_inp_purchase_invoice_detail`";
            $sql .= " WHERE ref_document_type_id=4 AND ref_document_identity='" . $purchase_order['document_identity'] . "'";
            $sql .= " AND product_id = '".$purchase_order['product_id']."'";
            $sql .= " AND ref_document_detail_id = '".$purchase_order['purchase_order_detail_id']."'";
            if($excl_document_id !='') {
                $sql .= " AND purchase_invoice_id != '".$excl_document_id."'";
            }

            $query = $this->conn->query($sql);
            $rows = $query->rows;
            foreach($rows as $row) {
                $arrPurchaseOrders[$purchase_order['document_identity']]['products'][$row['ref_document_detail_id']]['utilized_qty'] += $row['qty'];
            }
        }
        return $arrPurchaseOrders;
    }

    
    function getSalesTaxAccount(){
        $sql = "SELECT *";
        $sql .= " FROM `gl0_coa_level3`";
        $sql .= " WHERE name='SALES TAX ADJUSTABLE - IMPORT' OR name='SALES TAX - IMPORT'";
        //d($sql,true);
        $query = $this->conn->query($sql);
        $rows = $query->rows;
        return $rows;
    }

}

?>