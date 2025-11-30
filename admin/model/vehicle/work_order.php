<?php

class ModelVehicleWorkOrder extends HModel {

    protected function getTable() {
        return 'work_order';
    }

    public function getPOrder($data) {
        $filterSQL = $this->getFilterString($data['filter']);
        $criteriaSQL = $this->getCriteriaString($data['criteria']);

        $sql = "SELECT po.*, s.name as supplier";
        $sql .= " FROM `" . DB_PREFIX . "work_order` po";
        $sql .= " inner join `" . DB_PREFIX . "work_order_detail` pod";
        $sql .= " ON pod.work_order_id = po.work_order_id";
        $sql .= " inner join `" . DB_PREFIX . "supplier` s";
        $sql .= " ON s.supplier_id = pod.supplier_id";
        if($filterSQL) {
            $sql .= " AND " . $filterSQL;
        }
        if($criteriaSQL) {
            $sql .= $criteriaSQL;
        }

        $query = $this->db->query($sql);
        //d(array($data,$sql,$query));
        return $query->rows;
    }

    public function getUtilizedProduct($work_order_id, $product_id, $ref_document_id=''){
        $sql = "select SUM(qty) as qty FROM (";
        $sql .= " SELECT gr.document_type_id, gr.goods_received_id AS document_id, gr.voucher_no AS document_identity, grd.product_id, grd.qty";
        $sql .= " FROM `goods_received` gr";
        $sql .= " INNER JOIN `goods_received_detail` grd ON gr.goods_received_id = grd.goods_received_id";
        $sql .= " WHERE ref_document_type_id = 4 AND ref_document_id = '".$work_order_id."'";
        $sql .= " AND product_id = '".$product_id."'";
        if($ref_document_id) {
            $sql .= " AND gr.goods_received_id != '".$ref_document_id."'";
        }
        $sql .= " ) as x";

        $query = $this->db->query($sql);
        $row = $query->row;

        return $row['qty'];
    }

    public function getWorkOrderWithID($work_order_id){
        $sql ="SELECT wo.work_order_id,wo.document_identity,wo.manual_ref_no,wo.commodity,woc.work_order_commodity_id,woc.field,woc.value ";
        $sql .= " FROM `work_order` wo";
        $sql .= " INNER JOIN `work_order_commodity` woc ON wo.work_order_id = woc.work_order_id";
        $sql .= " WHERE wo.work_order_id = '".$work_order_id."' ";

        $query = $this->db->query($sql);
//        d(array($sql,$query),true);

        return $query->rows;
    }

    public function getworkOrders($filter, $excl_document_id='') {
        $sql = "SELECT *";
        $sql .= " FROM `vw_work_order`";
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
        $query = $this->db->query($sql);
        $work_orders = $query->rows;
//        d(array($sql,$work_orders),true);
        $arrworkOrders = array();
        foreach($work_orders as $work_order) {
            $arrworkOrders[$work_order['work_order_id']]['work_order_id'] = $work_order['work_order_id'];
            $arrworkOrders[$work_order['work_order_id']]['company_id'] = $work_order['company_id'];
            $arrworkOrders[$work_order['work_order_id']]['company_branch_id'] = $work_order['company_branch_id'];
            $arrworkOrders[$work_order['work_order_id']]['fiscal_year_id'] = $work_order['fiscal_year_id'];
            $arrworkOrders[$work_order['work_order_id']]['invoice_date'] = $work_order['invoice_date'];
            $arrworkOrders[$work_order['work_order_id']]['invoice_no'] = $work_order['invoice_no'];
            $arrworkOrders[$work_order['work_order_id']]['document_id'] = $work_order['work_order_id'];
            $arrworkOrders[$work_order['work_order_id']]['document_identity'] = $work_order['invoice_no'];
            $arrworkOrders[$work_order['work_order_id']]['manual_ref_no'] = $work_order['manual_ref_no'];
            if(isset($arrworkOrders[$work_order['work_order_id']]['products'][$work_order['product_id']])) {
                $arrworkOrders[$work_order['work_order_id']]['products'][$work_order['product_id']]['order_qty'] += $work_order['qty'];
            } else {
                $arrworkOrders[$work_order['work_order_id']]['products'][$work_order['product_id']] = array(
                    'product_id' => $work_order['product_id'],
                    'product_code' => $work_order['product_code'],
                    'sort_order' => $work_order['sort_order'],
                    'product_service' => $work_order['product_service'],
                    'unit_id' => $work_order['unit_id'],
                    'order_qty' => $work_order['qty'],
                    'utilized_qty' => 0,
                    'rate' => $work_order['rate'],
                    'document_currency_id' => $work_order['document_currency_id'],
                    'conversion_rate' => $work_order['conversion_rate'],
                    'amount' => $work_order['amount'],
                );
            }

            //Goods Received against work Order
            $sql = "SELECT *";
            $sql .= " FROM `vw_goods_received`";
            $sql .= " WHERE ref_document_type_id=4 AND ref_document_id='" . $work_order['work_order_id'] . "'";
            $sql .= " AND product_id = '".$work_order['product_id']."'";
            if($excl_document_id !='') {
                $sql .= " AND goods_received_id != '".$excl_document_id."'";
            }

            $query = $this->db->query($sql);
            $goods_received = $query->rows;
//            d(array($excl_document_id, $arrworkOrders,$sql,$goods_received),true);
            foreach($goods_received as $gr) {
                $arrworkOrders[$work_order['work_order_id']]['products'][$gr['product_id']]['utilized_qty'] += $gr['qty'];
            }

            //work Invoice against work Order
            $sql = "SELECT *";
            $sql .= " FROM `vw_work_invoice`";
            $sql .= " WHERE ref_document_type_id=4 AND ref_document_id='" . $work_order['work_order_id'] . "'";
            $sql .= " AND product_id = '".$work_order['product_id']."'";
            if($excl_document_id !='') {
                $sql .= " AND work_invoice_id != '".$excl_document_id."'";
            }

            $query = $this->db->query($sql);
            $rows = $query->rows;
//            d(array($arrworkOrders,$sql,$goods_received),true);
            foreach($rows as $row) {
                $arrworkOrders[$work_order['work_order_id']]['products'][$gr['product_id']]['utilized_qty'] += $row['qty'];
            }

        }

        return $arrworkOrders;
    }

}

?>