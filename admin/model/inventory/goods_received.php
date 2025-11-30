<?php

class ModelInventoryGoodsReceived extends HModel {

    protected function getTable() {
        return 'inp_goods_received';
    }

    protected function getView() {
        return 'vw_inp_goods_received';
    }

    public function getGoodsReceiveds($filter, $excl_document_id='') {
        $sql = "SELECT *";
        $sql .= " FROM `vw_inp_goods_received_detail`";
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
        $sql.= " ORDER BY sort_order";
        $query = $this->conn->query($sql);
        $goods_receiveds = $query->rows;
       // d(array($sql,$goods_receiveds),true);
        $arrGoodsReceiveds = array();
        foreach($goods_receiveds as $goods_received) {
            $arrGoodsReceiveds[$goods_received['document_identity']]['goods_received_id'] = $goods_received['goods_received_id'];
            $arrGoodsReceiveds[$goods_received['document_identity']]['company_id'] = $goods_received['company_id'];
            $arrGoodsReceiveds[$goods_received['document_identity']]['company_branch_id'] = $goods_received['company_branch_id'];
            $arrGoodsReceiveds[$goods_received['document_identity']]['fiscal_year_id'] = $goods_received['fiscal_year_id'];
            $arrGoodsReceiveds[$goods_received['document_identity']]['document_id'] = $goods_received['goods_received_id'];
            $arrGoodsReceiveds[$goods_received['document_identity']]['document_date'] = $goods_received['document_date'];
            $arrGoodsReceiveds[$goods_received['document_identity']]['document_identity'] = $goods_received['document_identity'];
            $arrGoodsReceiveds[$goods_received['document_identity']]['manual_ref_no'] = $goods_received['manual_ref_no'];
            $arrGoodsReceiveds[$goods_received['document_identity']]['partner_type_id'] = $goods_received['partner_type_id'];
            $arrGoodsReceiveds[$goods_received['document_identity']]['partner_id'] = $goods_received['partner_id'];
            $arrGoodsReceiveds[$goods_received['document_identity']]['document_currency_id'] = $goods_received['document_currency_id'];
            $arrGoodsReceiveds[$goods_received['document_identity']]['conversion_rate'] = $goods_received['conversion_rate'];
            $arrGoodsReceiveds[$goods_received['document_identity']]['base_currency_id'] = $goods_received['base_currency_id'];
            $arrGoodsReceiveds[$goods_received['document_identity']]['discount'] = $goods_received['discount'];
            //d(array($goods_received, $arrGoodsReceiveds), true);
            if(isset($arrGoodsReceiveds[$goods_received['document_identity']]['products'][$goods_received['product_id']])) {
                $arrGoodsReceiveds[$goods_received['document_identity']]['products'][$goods_received['product_id']]['order_qty'] += $goods_received['qty'];
            } else {
                $arrGoodsReceiveds[$goods_received['document_identity']]['products'][$goods_received['product_id']] = array(
                    'sort_order' => $goods_received['sort_order'],
                    'ref_document_type_id' => $goods_received['document_type_id'],
                    'ref_document_identity' => $goods_received['document_identity'],
                    'product_id' => $goods_received['product_id'],
                    'warehouse_id' => $goods_received['warehouse_id'],
                    'warehouse_name' => $goods_received['warehouse'],
                    'product_code' => $goods_received['product_code'],
                    'product_name' => $goods_received['product_name'],
                    'unit_id' => $goods_received['unit_id'],
                    'unit' => $goods_received['unit'],
                    'order_qty' => $goods_received['qty'],
                    'utilized_qty' => 0,
                    'conversion_qty' => $goods_received['conversion_qty'],
                    'base_unit_id' => $goods_received['base_unit_id'],
                    'base_qty' => $goods_received['base_qty'],
                    'document_currency_id' => $goods_received['document_currency_id'],
                    'rate' => $goods_received['rate'],
                    'amount' => $goods_received['amount'],
                    'inc_tax_percent' => $goods_received['inc_tax_percent'],
                    'inc_tax_amount' => $goods_received['inc_tax_amount'],
                    'wht_percent' => $goods_received['wht_percent'],
                    'wht_amount' => $goods_received['wht_amount'],
                    'sales_tax_percent' => $goods_received['sales_tax_percent'],
                    'sales_tax_amount' => $goods_received['sales_tax_amount'],
                    'discount_amount' => $goods_received['discount_amount'],
                    'discount_percent' => $goods_received['discount_percent'],
                    'gross_amount' => $goods_received['gross_amount'],
                    'net_rate' => $goods_received['net_rate'],
                    'net_amount' => $goods_received['net_amount'],
                    'total_amount' => $goods_received['total_amount'],
                    'base_currency_id' => $goods_received['base_currency_id'],
                    'conversion_rate' => $goods_received['conversion_rate'],
                    'base_amount' => $goods_received['base_amount'],
                );
            }

            //Purchase Invoice against Purchase Order
            $sql = "SELECT *";
            $sql .= " FROM `vw_inp_purchase_invoice_detail`";
            $sql .= " WHERE ref_document_type_id=17 AND ref_document_identity='" . $goods_received['document_identity'] . "'";
            $sql .= " AND product_id = '".$goods_received['product_id']."'";
            if($excl_document_id !='') {
                $sql .= " AND purchase_invoice_id != '".$excl_document_id."'";
            }

            $query = $this->conn->query($sql);
            $rows = $query->rows;
            // d($rows, true);
            foreach($rows as $row) {
                $arrGoodsReceiveds[$goods_received['document_identity']]['products'][$row['product_id']]['utilized_qty'] += $row['qty'];
            }
        }
        return $arrGoodsReceiveds;
    }
}

?>