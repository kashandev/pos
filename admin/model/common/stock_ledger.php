<?php

class ModelCommonStockLedger extends HModel {

    protected function getTable() {
        return 'core_stock_ledger';
    }

    protected function getView() {
        return 'vw_core_stock_ledger';
    }

    public function getStocks($filter = array()) {
        $sql = "";
        $sql .= "SELECT product_id, SUM(base_qty) AS stock_qty, SUM(base_amount) AS stock_amount, ROUND(SUM(base_amount)/SUM(base_qty),4) AS avg_cogs_rate";
        $sql .= " FROM `core_stock_ledger`";
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
        $sql .= " GROUP BY company_id, company_branch_id, fiscal_year_id, product_id";

        $query = $this->conn->query($sql);
        return $query->rows;
    }

    public function getStock($filter = array()) {
        $sql = "";
        $sql .= "SELECT SUM(base_qty) AS stock_qty, SUM(base_amount) AS stock_amount, base_rate as cost_price , ROUND(SUM(base_amount)/SUM(base_qty),4) AS avg_stock_rate";
        $sql .= " FROM `core_stock_ledger`";
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

        // d($sql);
        $query = $this->conn->query($sql);
        return $query->row;
    }

    public function getWarehouseStock($product_id, $warehouse_id='', $document_identity='', $document_date='') {
        
        $sql2 = "";
        $sql2 .= " SELECT ROUND(IFNULL(SUM(base_amount)/SUM(base_qty),0),4) AS avg_stock_rate";
        $sql2 .= " FROM `core_stock_ledger`";
        $sql2 .= " WHERE company_id = '{$this->session->data['company_id']}'";
        $sql2 .= " AND company_branch_id = '".$this->session->data['company_branch_id']."'";
        $sql2 .= " AND fiscal_year_id = '{$this->session->data['fiscal_year_id']}'";
        $sql2 .= " AND product_id  = '{$product_id}'";
        if( !empty($document_identity) ) {
            $sql2 .= " AND document_identity != '{$document_identity}'";
        }

        if( !empty($document_date) ) {
            $sql2 .= " AND document_date <= '{$document_date}'";
        }
        $query = $this->conn->query($sql2);
        $base_amount = $query->row;
        // d($base_amount, true);

        $sql = "";
        $sql .= " SELECT IFNULL(SUM(base_qty),0) AS stock_qty, ".$base_amount['avg_stock_rate']." AS avg_stock_rate";
        $sql .= " FROM `core_stock_ledger`";
        $sql .= " WHERE company_id = '{$this->session->data['company_id']}'";
        $sql .= " AND company_branch_id = '".$this->session->data['company_branch_id']."'";
        $sql .= " AND fiscal_year_id = '{$this->session->data['fiscal_year_id']}'";
        $sql .= " AND product_id  = '{$product_id}'";
        if(!empty($warehouse_id))
        {
            $sql .= " AND warehouse_id= '{$warehouse_id}'";
        }

        if( !empty($document_identity) ) {
            $sql .= " AND document_identity != '{$document_identity}'";
        }

        if( !empty($document_date) ) {
            $sql .= " AND document_date <= '{$document_date}'";
        }

        // d($sql, true);

        $query = $this->conn->query($sql);
        $stock = $query->row;

        return [
            'stock_qty'         => $stock['stock_qty'],
            'avg_stock_rate'    => (($stock['avg_stock_rate']<0)?0:$stock['avg_stock_rate'])
        ];
    }
    

    public function getBalanceContainers($filter=array()) {
        $sql = "";
        $sql .= "SELECT `container_no`, COUNT(*) as records, SUM(`base_qty`) AS base_qty";
        $sql .= " FROM `core_stock_ledger`";
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
        $sql .= " GROUP BY `container_no`";
        $sql .= " HAVING `base_qty` > 0";

        $query = $this->conn->query($sql);
        return $query->rows;
    }

    public function getBalanceContainerStocks($container_no, $warehouse_id='') {
        $sql = "";
        $sql .= "SELECT sl.warehouse_id, w.name as `warehouse_name`, sl.container_no, sl.batch_no, sl.product_id, p.product_code, p.name as product_name, sl.base_unit_id";
        $sql .= ", p.cubic_meter, p.cubic_feet, p.cost_price, p.sale_price";
        $sql .= ", (sl.base_qty * p.cubic_meter) AS total_cubic_meter, (sl.base_qty * p.cubic_feet) AS total_cubic_feet";
        $sql .= ", SUM(sl.base_qty) AS balance_qty, SUM(sl.base_amount) AS balance_amount, Round(SUM(sl.base_amount)/SUM(sl.base_qty * p.cubic_feet),2) AS avg_cog_rate";
        $sql .= " FROM `core_stock_ledger` sl";
        $sql .= " INNER JOIN `in0_product` p ON p.product_id = sl.product_id";
        $sql .= " LEFT JOIN `in0_warehouse` w ON w.warehouse_id = sl.warehouse_id";
        $sql .= " WHERE `container_no` = '".$container_no."'";
        if($warehouse_id != '') {
            $sql .= " AND `warehouse_id` = '".$warehouse_id."'";
        }
        $sql .= " GROUP BY warehouse_id, container_no, batch_no, product_id, base_unit_id";

        $query = $this->conn->query($sql);
        return $query->rows;
    }

    public function getProductStock($product_category_id) {
        $sql = "";
        $sql .= " SELECT sl.`product_id`,p.name,`warehouse_id`,p.`product_code`,p.`unit_id`,p.`unit`,p.`product_category_id`,SUM(base_qty) AS stock_qty,ROUND(SUM(base_amount)/SUM(base_qty),2) AS avg_stock_rate";
        $sql .= " FROM `core_stock_ledger` sl";
        $sql .= " INNER JOIN `vw_in0_product` p ON p.`product_id` = sl.`product_id` ";
        $sql .= " WHERE sl.company_id = '".$this->session->data['company_id']."'";
        $sql .= " AND sl.company_branch_id = '".$this->session->data['company_branch_id']."'";
        $sql .= " AND sl.fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
        $sql .= " AND p.`product_category_id` = '".$product_category_id."'";
        $sql .= " GROUP BY sl.product_id,warehouse_id ORDER BY p.`name`";

        $query = $this->conn->query($sql);
        return $query->rows;
    }


    public function getWarehouseStockAdjustment($product_id, $warehouse_id='', $document_identity='', $document_date='') {
        $sql = "";
        $sql .= " SELECT IFNULL(SUM(base_qty),0) AS stock_qty, ROUND(IFNULL(SUM(base_amount)/SUM(base_qty),0),2) AS avg_stock_rate, ROUND(SUM(base_amount),4) AS stock_amount";
        $sql .= " FROM `core_stock_ledger`";
        $sql .= " WHERE company_id = '{$this->session->data['company_id']}'";
        $sql .= " AND company_branch_id = '".$this->session->data['company_branch_id']."'";
        $sql .= " AND fiscal_year_id = '{$this->session->data['fiscal_year_id']}'";
        $sql .= " AND product_id  = '{$product_id}'";
        $sql .= " AND warehouse_id= '{$warehouse_id}'";

        if( !empty($document_identity) ) {
            $sql .= " AND document_identity != '{$document_identity}'";
        }

        if( !empty($document_date) ) {
            $sql .= " AND document_date <= '{$document_date}'";
        }

        // d($sql, true);

        $query = $this->conn->query($sql);
        $stock = $query->row;

        return [
            'stock_qty'         => $stock['stock_qty'],
            'avg_stock_rate'    => (($stock['avg_stock_rate'] == 0 )?0:$stock['avg_stock_rate']),
            'stock_amount'  => $stock['stock_amount']
        ];
    }


}

?>