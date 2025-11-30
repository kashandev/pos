<?php

class ModelInventorySaleOrder extends HModel {

    protected function getTable() {
        return 'ins_sale_order';
    }

    protected function getView() {
        return 'vw_ins_sale_order';
    }


    public function getLatestSaleOrders() {
        $sql =  " SELECT  sale_order_id,`document_identity`,`partner_name`,`item_total`  ";
        $sql .= " FROM vw_ins_sale_order ORDER BY `created_at` DESC ";
        $sql .= " LIMIT 10 ";

        $query = $this->conn->query($sql);
        return $query->rows;
    }


    public function getSaleOrders($filter, $excl_document_id='') {
        $sql = "SELECT *";
        $sql .= " FROM `vw_ins_sale_order_detail`";
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
        $sale_orders = $query->rows;
        //d(array($sql,$purchase_orders),true);
        $arrSaleOrders = array();
        foreach($sale_orders as $sale_order) {
            $arrSaleOrders[$sale_order['document_identity']]['sale_order_id'] = $sale_order['sale_order_id'];
            $arrSaleOrders[$sale_order['document_identity']]['company_id'] = $sale_order['company_id'];
            $arrSaleOrders[$sale_order['document_identity']]['company_branch_id'] = $sale_order['company_branch_id'];
            $arrSaleOrders[$sale_order['document_identity']]['fiscal_year_id'] = $sale_order['fiscal_year_id'];
            $arrSaleOrders[$sale_order['document_identity']]['document_id'] = $sale_order['sale_order_id'];
            $arrSaleOrders[$sale_order['document_identity']]['document_date'] = $sale_order['document_date'];
            $arrSaleOrders[$sale_order['document_identity']]['document_identity'] = $sale_order['document_identity'];
            $arrSaleOrders[$sale_order['document_identity']]['manual_ref_no'] = $sale_order['manual_ref_no'];
            $arrSaleOrders[$sale_order['document_identity']]['partner_type_id'] = $sale_order['partner_type_id'];
            $arrSaleOrders[$sale_order['document_identity']]['partner_id'] = $sale_order['partner_id'];
            $arrSaleOrders[$sale_order['document_identity']]['document_currency_id'] = $sale_order['document_currency_id'];
            $arrSaleOrders[$sale_order['document_identity']]['conversion_rate'] = $sale_order['conversion_rate'];
            $arrSaleOrders[$sale_order['document_identity']]['base_currency_id'] = $sale_order['base_currency_id'];
            $arrSaleOrders[$sale_order['document_identity']]['discount'] = $sale_order['discount'];
            //d(array($sale_order, $arrSaleOrders), true);
            if(isset($arrSaleOrders[$sale_order['document_identity']]['products'][$sale_order['product_id']])) {
                $arrSaleOrders[$sale_order['document_identity']]['products'][$sale_order['product_id']]['order_qty'] += $sale_order['qty'];
            } else {
                $arrSaleOrders[$sale_order['document_identity']]['products'][$sale_order['product_id']] = array(
                    'sort_order' => $sale_order['sort_order'],
                    'ref_document_type_id' => $sale_order['document_type_id'],
                    'ref_document_identity' => $sale_order['document_identity'],
                    'product_id' => $sale_order['product_id'],
                    'product_code' => $sale_order['product_code'],
                    'product_name' => $sale_order['product_name'],
                    'description' => $sale_order['description'],
                    'unit_id' => $sale_order['unit_id'],
                    'unit' => $sale_order['unit'],
                    'order_qty' => $sale_order['qty'],
                    'utilized_qty' => 0,
                    'document_currency_id' => $sale_order['document_currency_id'],
                    'rate' => $sale_order['rate'],
                    'wht_percent' => $sale_order['wht_percent'],
                    'additional_rate' => $sale_order['additional_rate'],
                    'net_rate' => $sale_order['net_rate'],
                    'wht_amount' => $sale_order['wht_amount'],
                    'amount' => $sale_order['amount'],
                    'base_currency_id' => $sale_order['base_currency_id'],
                    'conversion_rate' => $sale_order['conversion_rate'],
                    'base_amount' => $sale_order['base_amount'],
                    'tax_percent' => $sale_order['tax_percent'],
                    'tax_amount' => $sale_order['tax_amount'],
                    'net_amount' => $sale_order['net_amount'],
                );
            }

            //Goods Received against sale Order
            $sql = "SELECT *";
            $sql .= " FROM `vw_ins_delivery_challan_detail`";
            $sql .= " WHERE ref_document_type_id=5 AND ref_document_identity='" . $sale_order['document_identity'] . "'";
            $sql .= " AND product_id = '".$sale_order['product_id']."'";
            if($excl_document_id !='') {
                $sql .= " AND delivery_challan_id != '".$excl_document_id."'";
            }

            $query = $this->conn->query($sql);

//            d($query,true);
            $goods_received = $query->rows;
            foreach($goods_received as $gr) {
                $arrSaleOrders[$sale_order['document_identity']]['products'][$gr['product_id']]['utilized_qty'] += $gr['qty'];
            }
//d($goods_received,true);
//            //sale Invoice against Purchase Order
//            $sql = "SELECT *";
//            $sql .= " FROM `vw_ins_sale_invoice_detail`";
//            $sql .= " WHERE ref_document_type_id=4 AND ref_document_identity='" . $sale_order['document_identity'] . "'";
//            $sql .= " AND product_id = '".$sale_order['product_id']."'";
//            if($excl_document_id !='') {
//                $sql .= " AND sale_invoice_id != '".$excl_document_id."'";
//            }

//            $query = $this->conn->query($sql);
//            $rows = $query->rows;
//            foreach($rows as $row) {
//                $arrSaleOrders[$sale_order['document_identity']]['products'][$gr['product_id']]['utilized_qty'] += $row['qty'];
//            }
        }
        //d($arrSaleOrders, true);

        return $arrSaleOrders;
    }
}
?>