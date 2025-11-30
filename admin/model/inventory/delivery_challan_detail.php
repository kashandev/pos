<?php

class ModelInventoryDeliveryChallanDetail extends HModel {

    protected function getTable() {
        return 'ins_delivery_challan_detail';
    }

    protected function getView() {
        return 'vw_ins_delivery_challan_detail';
    }

    public function getTotalPendingDCNonGst() {
        $sql =  " SELECT DISTINCT COUNT(dc.`document_identity`) AS total_pending_dc ";
        $sql .= " FROM `ins_delivery_challan` dc";
        $sql .= " LEFT JOIN `ins_sale_invoice_detail` sid ON sid.ref_document_identity = dc.document_identity";
        $sql .= " LEFT JOIN `ins_sale_invoice` si ON si.`sale_invoice_id` = sid.`sale_invoice_id`";
        $sql .= " WHERE sid.ref_document_type_id IS NULL AND sid.ref_document_identity IS NULL  AND dc.challan_type = 'Non GST' ";

        $query = $this->conn->query($sql);
        return $query->row;
    }
    public function getTotalPendingDCGst() {
        $sql =  " SELECT DISTINCT COUNT(dc.`document_identity`) AS total_pending_dc ";
        $sql .= " FROM `ins_delivery_challan` dc";
        $sql .= " LEFT JOIN `ins_sale_invoice_detail` sid ON sid.ref_document_identity = dc.document_identity";
        $sql .= " LEFT JOIN `ins_sale_invoice` si ON si.`sale_invoice_id` = sid.`sale_invoice_id`";
        $sql .= " WHERE sid.ref_document_type_id IS NULL AND sid.ref_document_identity IS NULL  AND dc.challan_type = 'GST' ";

        $query = $this->conn->query($sql);
        return $query->row;
    }

    public function getLatestChallans() {
        $sql =  " SELECT  delivery_challan_id,`document_identity`,`partner_name`,`total_qty`  ";
        $sql .= " FROM vw_ins_delivery_challan ORDER BY `created_at` DESC ";
        $sql .= " LIMIT 10 ";

        $query = $this->conn->query($sql);
        return $query->rows;
    }

    public function getSaleOrders($filter, $excl_document_id='') {
        $sql = "SELECT *";
        $sql .= " FROM `vw_ins_delivery_challan_detail`";
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
        $query = $this->conn->query($sql);
        $quotations = $query->rows;
//        d(array($sql,$quotations),true);
        $arrQuotations = array();
        foreach($quotations as $quotation) {
            $arrQuotations[$quotation['document_identity']]['quotation_id'] = $quotation['quotation_id'];
            $arrQuotations[$quotation['document_identity']]['company_id'] = $quotation['company_id'];
            $arrQuotations[$quotation['document_identity']]['company_branch_id'] = $quotation['company_branch_id'];
            $arrQuotations[$quotation['document_identity']]['fiscal_year_id'] = $quotation['fiscal_year_id'];
            $arrQuotations[$quotation['document_identity']]['document_id'] = $quotation['quotation_id'];
            $arrQuotations[$quotation['document_identity']]['document_date'] = $quotation['document_date'];
            $arrQuotations[$quotation['document_identity']]['document_identity'] = $quotation['document_identity'];
            $arrQuotations[$quotation['document_identity']]['manual_ref_no'] = $quotation['manual_ref_no'];
            $arrQuotations[$quotation['document_identity']]['partner_type_id'] = $quotation['partner_type_id'];
            $arrQuotations[$quotation['document_identity']]['partner_id'] = $quotation['partner_id'];
            $arrQuotations[$quotation['document_identity']]['document_currency_id'] = $quotation['document_currency_id'];
            $arrQuotations[$quotation['document_identity']]['conversion_rate'] = $quotation['conversion_rate'];
            $arrQuotations[$quotation['document_identity']]['base_currency_id'] = $quotation['base_currency_id'];
            $arrQuotations[$quotation['document_identity']]['discount'] = $quotation['discount'];
//            d(array($quotation, $arrQuotations), true);
            if(isset($arrQuotations[$quotation['document_identity']]['products'][$quotation['product_id']])) {
                $arrQuotations[$quotation['document_identity']]['products'][$quotation['product_id']]['order_qty'] += $quotation['qty'];
            } else {
                $arrQuotations[$quotation['document_identity']]['products'][$quotation['product_id']] = array(
                    'sort_order' => $quotation['sort_order'],
                    'ref_document_type_id' => $quotation['document_type_id'],
                    'ref_document_identity' => $quotation['document_identity'],
                    'product_id' => $quotation['product_id'],
                    'product_code' => $quotation['product_code'],
                    'product_name' => $quotation['product_name'],
                    'description' => $quotation['description'],
                    'unit_id' => $quotation['unit_id'],
                    'unit' => $quotation['unit'],
                    'order_qty' => $quotation['qty'],
                    'utilized_qty' => 0,
                    'conversion_qty' => $quotation['conversion_qty'],
                    'base_unit_id' => $quotation['base_unit_id'],
                    'base_qty' => $quotation['base_qty'],
                    'document_currency_id' => $quotation['document_currency_id'],
                    'rate' => $quotation['rate'],
                    'wht_percent' => $quotation['wht_percent'],
                    'additional_rate' => $quotation['additional_rate'],
                    'net_rate' => $quotation['net_rate'],
                    'wht_amount' => $quotation['wht_amount'],
                    'amount' => $quotation['amount'],
                    'base_currency_id' => $quotation['base_currency_id'],
                    'conversion_rate' => $quotation['conversion_rate'],
                    'base_amount' => $quotation['base_amount'],
                );
            }

            //Goods Received against Purchase Order
            $sql = "SELECT *";
            $sql .= " FROM `vw_ins_sale_order_detail`";
            $sql .= " WHERE ref_document_type_id=38 AND ref_document_identity='" . $quotation['document_identity'] . "'";
            $sql .= " AND product_id = '".$quotation['product_id']."'";
            if($excl_document_id !='') {
                $sql .= " AND sale_order_id != '".$excl_document_id."'";
            }

            $query = $this->conn->query($sql);
            $sale_order = $query->rows;
            foreach($sale_order as $gr) {
                $arrQuotations[$quotation['document_identity']]['products'][$gr['product_id']]['utilized_qty'] += $gr['qty'];
            }

        }
//        d($arrQuotations, true);

        return $arrQuotations;
    }
}
?>