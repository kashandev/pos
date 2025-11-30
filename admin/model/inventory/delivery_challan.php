<?php
class ModelInventoryDeliveryChallan extends HModel {

    protected function getTable() {
        return 'ins_delivery_challan';
    }

    protected function getView() {
        return 'vw_ins_delivery_challan';
    }

        protected function getListRecord() {

            return 'vw_ins_delivery_challan_against_sales';
        }

    public function getLists($data) {
        if(!isset($data['filter'])) {
            $data['filter'] = array();
        }
        if(!isset($data['criteria'])) {
            $data['criteria'] = array();
        }
        $filterSQL = $this->getFilterString($data['filter']);
        $criteriaSQL = $this->getCriteriaString($data['criteria']);

        $sql = "SELECT count(*) as total";
        $sql .= " FROM " . DB_PREFIX . $this->getListRecord();
        if($filterSQL) {
            $sql .= " WHERE " . $filterSQL;
        }
        $query = $this->conn->query($sql);
        $table_total = $query->row['total'];

        $sql = "SELECT count(*) as total";
        $sql .= " FROM " . DB_PREFIX . $this->getListRecord();
        if($filterSQL) {
            $sql .= " WHERE " . $filterSQL;
        }
        $query = $this->conn->query($sql);
        $total = $query->row['total'];

        $sql = "SELECT *";
        $sql .= " FROM " . DB_PREFIX . $this->getListRecord();
        if($filterSQL) {
            $sql .= " WHERE " . $filterSQL;
        }
        if($criteriaSQL) {
            $sql .= $criteriaSQL;
        }

    //    d($sql,true);
        $query = $this->conn->query($sql);
        $lists = $query->rows;

        return array('table_total' => $table_total, 'total' => $total, 'lists' => $lists);
    }

    public function validateEdit($delivery_challan_id)
    {
        $sql = " SELECT * FROM vw_ins_delivery_challan_against_sales WHERE delivery_challan_id = '".$delivery_challan_id."' ";
        // d($sql, true);
        $query = $this->conn->query($sql);
        return $query->row;
    }

    public function getRefDocumentJson($search, $page,$challan_type,$type,$limit=25, $filter=array()) {
        if($page=='') {
            $page = 0;
        }

        $offset = $page*$limit;

        $arrWhere = array();
        // $arrWhere[] = "`challan_type` = '".$challan_type."' ";
        $arrWhere[] = "(`document_no` LIKE '".$search."%' OR `document_identity` LIKE '%".$search."%')";
        $where = implode(' AND ', $arrWhere);
        $rows = $this->getDeliveryChallanDocuments($where,$type);
        $rows = array_values($rows);

//        $sql = "SELECT count(*) as total_records";
//        $sql .= " FROM `ins_delivery_challan`";
//        $sql .= " WHERE " . implode(' AND ', $arrWhere);
//        $sql .= "  AND `challan_type` = '".$challan_type."'";
//        $query = $this->conn->query($sql);
//        $row = $query->row;
//        $total_records = $row['total_records'];
        $total_records = count($rows);

//        $sql = "SELECT *, delivery_challan_id as id";
//        $sql .= " FROM `ins_delivery_challan`";
//        $sql .= " WHERE " . implode(' AND ', $arrWhere);
//        $sql .= "  AND `challan_type` = '".$challan_type."'";
//        $sql .= " LIMIT " . $offset . "," . $limit;
//        $query = $this->conn->query($sql);
//        $rows = $query->rows;
        $rows = array_slice($rows, $offset, $limit);
//d($rows,true);

        return array(
            'total_count' => $total_records,
//            'sql' => $sql,
            'items' => $rows
        );
    }




    public function getDeliveryChallanDocuments($filter,$type, $excl_document_id='') {
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
        $sql.= " order by sort_order";
        $query = $this->conn->query($sql);
        $delivery_challans = $query->rows;
        // d(array($sql,$delivery_challans),true);
        $arrDeliveryChallan = array();
        foreach($delivery_challans as $delivery_challan) {
            $arrDeliveryChallan[$delivery_challan['document_identity']]['id'] = $delivery_challan['delivery_challan_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['delivery_challan_id'] = $delivery_challan['delivery_challan_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['company_id'] = $delivery_challan['company_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['company_branch_id'] = $delivery_challan['company_branch_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['fiscal_year_id'] = $delivery_challan['fiscal_year_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['document_id'] = $delivery_challan['delivery_challan_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['document_date'] = $delivery_challan['document_date'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['document_identity'] = $delivery_challan['document_identity'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['manual_ref_no'] = $delivery_challan['manual_ref_no'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['partner_type_id'] = $delivery_challan['partner_type_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['partner_id'] = $delivery_challan['partner_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['document_currency_id'] = $delivery_challan['document_currency_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['conversion_rate'] = $delivery_challan['conversion_rate'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['base_currency_id'] = $delivery_challan['base_currency_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['discount'] = $delivery_challan['discount'];
            //d(array($delivery_challan, $arrDeliveryChallan), true);
            if(isset($arrDeliveryChallan[$delivery_challan['document_identity']]['products'][$delivery_challan['product_id']])) {
                $arrDeliveryChallan[$delivery_challan['document_identity']]['products'][$delivery_challan['product_id']]['order_qty'] += $delivery_challan['qty'];
            } else {
                $arrDeliveryChallan[$delivery_challan['document_identity']]['products'][$delivery_challan['product_id']] = array(
                    'sort_order' => $delivery_challan['sort_order'],
                    'ref_document_type_id' => $delivery_challan['document_type_id'],
                    'ref_document_identity' => $delivery_challan['document_identity'],
                    'product_id' => $delivery_challan['product_id'],
                    'product_code' => $delivery_challan['product_code'],
                    'product_name' => $delivery_challan['product_name'],
                    'description' => $delivery_challan['description'],
                    'warehouse_id' => $delivery_challan['warehouse_id'],
                    'unit_id' => $delivery_challan['unit_id'],
                    'unit' => $delivery_challan['unit'],
                    'order_qty' => $delivery_challan['qty'],
                    'utilized_qty' => 0,
                    'document_currency_id' => $delivery_challan['document_currency_id'],
                    'rate' => $delivery_challan['rate'],
                    'amount' => $delivery_challan['amount'],
                    'tax_percent' => $delivery_challan['tax_percent'],
                    'tax_amount' => $delivery_challan['tax_amount'],
                    'base_currency_id' => $delivery_challan['base_currency_id'],
                    'conversion_rate' => $delivery_challan['conversion_rate'],
                    'base_amount' => $delivery_challan['base_amount'],
                    'net_amount' => $delivery_challan['net_amount'],
                );
            }

// d($type, true);
            if($type == 'sale_invoice')
            {
                //Goods Received against sale Order
                $sql = "SELECT *";
                $sql .= " FROM `vw_ins_sale_invoice_detail`";
                $sql .= " WHERE ref_document_type_id=16 AND ref_document_identity='" . $delivery_challan['document_identity'] . "'";
                $sql .= " AND product_id = '".$delivery_challan['product_id']."'";
                if($excl_document_id !='') {
                    $sql .= " AND delivery_challan_id != '".$excl_document_id."'";
                }

                $query = $this->conn->query($sql);

//            d($query,true);
                $sale_invoice = $query->rows;
                foreach($sale_invoice as $si) {
                    $arrDeliveryChallan[$delivery_challan['document_identity']]['products'][$si['product_id']]['utilized_qty'] += $si['qty'];
                }


            }
            else{

                //Goods Received against sale Order
                $sql = "SELECT *";
                $sql .= " FROM `vw_ins_sale_tax_invoice_detail`";
                $sql .= " WHERE ref_document_type_id=16 AND ref_document_identity='" . $delivery_challan['document_identity'] . "'";
                $sql .= " AND product_id = '".$delivery_challan['product_id']."'";
                if($excl_document_id !='') {
                    $sql .= " AND delivery_challan_id != '".$excl_document_id."'";
                }

                $query = $this->conn->query($sql);

        //    d($query);
                $sale_invoice = $query->rows;
                foreach($sale_invoice as $si) {
                    $arrDeliveryChallan[$delivery_challan['document_identity']]['products'][$si['product_id']]['utilized_qty'] += $si['qty'];
                }
            }

            // d($arrDeliveryChallan, true);


        }

// d($arrDeliveryChallan,true);
        foreach($arrDeliveryChallan as $delivery_challan_id => $delivery_challan) {
            foreach($delivery_challan['products'] as $product_id => $product) {
                if($product['order_qty'] <= $product['utilized_qty']) {
                    unset($delivery_challan['products'][$product_id]);
                }
            }
            if(empty($delivery_challan['products'])) {
                unset($arrDeliveryChallan[$delivery_challan_id]);
            }
        }
        return $arrDeliveryChallan;

    }



    public function getDeliveryChallans($filter,$type, $excl_document_id='') {
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
        $sql.= " order by sort_order";
        $query = $this->conn->query($sql);
        $delivery_challans = $query->rows;
    //    d(array($sql,$delivery_challans),true);
        $arrDeliveryChallan = array();
        foreach($delivery_challans as $delivery_challan) {
            $arrDeliveryChallan[$delivery_challan['document_identity']]['delivery_challan_id'] = $delivery_challan['delivery_challan_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['company_id'] = $delivery_challan['company_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['company_branch_id'] = $delivery_challan['company_branch_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['fiscal_year_id'] = $delivery_challan['fiscal_year_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['document_id'] = $delivery_challan['delivery_challan_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['document_date'] = $delivery_challan['document_date'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['document_identity'] = $delivery_challan['document_identity'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['manual_ref_no'] = $delivery_challan['manual_ref_no'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['partner_type_id'] = $delivery_challan['partner_type_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['partner_id'] = $delivery_challan['partner_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['document_currency_id'] = $delivery_challan['document_currency_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['conversion_rate'] = $delivery_challan['conversion_rate'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['base_currency_id'] = $delivery_challan['base_currency_id'];
            $arrDeliveryChallan[$delivery_challan['document_identity']]['discount'] = $delivery_challan['discount'];
            //d(array($delivery_challan, $arrDeliveryChallan), true);
            if(isset($arrDeliveryChallan[$delivery_challan['document_identity']]['products'][$delivery_challan['product_id']])) {
                $arrDeliveryChallan[$delivery_challan['document_identity']]['products'][$delivery_challan['product_id']]['order_qty'] += $delivery_challan['qty'];
            } else {
                $arrDeliveryChallan[$delivery_challan['document_identity']]['products'][$delivery_challan['product_id']] = array(
                    'delivery_challan_detail_id' => $delivery_challan['delivery_challan_detail_id'],
                    'sort_order' => $delivery_challan['sort_order'],
                    'ref_document_type_id' => $delivery_challan['document_type_id'],
                    'ref_document_identity' => $delivery_challan['document_identity'],
                    'product_id' => $delivery_challan['product_id'],
                    'product_code' => $delivery_challan['product_code'],
                    'product_name' => $delivery_challan['product_name'],
                    'description' => $delivery_challan['description'],
                    'warehouse_id' => $delivery_challan['warehouse_id'],
                    'unit_id' => $delivery_challan['unit_id'],
                    'unit' => $delivery_challan['unit'],
                    'order_qty' => $delivery_challan['qty'],
                    'utilized_qty' => 0,
                    'document_currency_id' => $delivery_challan['document_currency_id'],
                    'rate' => $delivery_challan['rate'],
                    'amount' => $delivery_challan['amount'],
                    'cog_rate' => $delivery_challan['cog_rate'],
                    'cog_amount' => $delivery_challan['cog_amount'],
                    'tax_percent' => $delivery_challan['tax_percent'],
                    'tax_amount' => $delivery_challan['tax_amount'],
                    'base_currency_id' => $delivery_challan['base_currency_id'],
                    'conversion_rate' => $delivery_challan['conversion_rate'],
                    'base_amount' => $delivery_challan['base_amount'],
                    'net_amount' => $delivery_challan['net_amount'],
                );
            }

            if($type == 'sale_invoice')
            {
                //Goods Received against sale Order
                $sql = "SELECT *";
                $sql .= " FROM `vw_ins_sale_invoice_detail`";
                $sql .= " WHERE ref_document_type_id=16 AND ref_document_identity='" . $delivery_challan['document_identity'] . "'";
                $sql .= " AND product_id = '".$delivery_challan['product_id']."'";
                if($excl_document_id !='') {
                    $sql .= " AND delivery_challan_id != '".$excl_document_id."'";
                }

//            d($sql,true);
                $query = $this->conn->query($sql);

//            d($query,true);
                $sale_invoice = $query->rows;
                foreach($sale_invoice as $si) {
                    $arrDeliveryChallan[$delivery_challan['document_identity']]['products'][$si['product_id']]['utilized_qty'] += $si['qty'];
                }

            }
            else{
                //Goods Received against sale Order
                $sql = "SELECT *";
                $sql .= " FROM `vw_ins_sale_tax_invoice_detail`";
                $sql .= " WHERE ref_document_type_id=16 AND ref_document_identity='" . $delivery_challan['document_identity'] . "'";
                $sql .= " AND product_id = '".$delivery_challan['product_id']."'";
                if($excl_document_id !='') {
                    $sql .= " AND delivery_challan_id != '".$excl_document_id."'";
                }

//            d($sql,true);
                $query = $this->conn->query($sql);

//            d($query,true);
                $sale_invoice = $query->rows;
                foreach($sale_invoice as $si) {
                    $arrDeliveryChallan[$delivery_challan['document_identity']]['products'][$si['product_id']]['utilized_qty'] += $si['qty'];
                }

            }

        }
//d($arrDeliveryChallan,true);
        return $arrDeliveryChallan;
    }
}
?>