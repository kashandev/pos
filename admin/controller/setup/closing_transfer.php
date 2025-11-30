<?php

class ControllerSetupClosingTransfer extends HController
{
    protected $document_type_id;

    protected function getAlias()
    {
        return 'setup/closing_transfer';
    }

    protected function getPrimaryKey()
    {
        return 'company_id';
    }

    protected function init()
    {
        $this->model[$this->getAlias()] = $this->load->model('common/setting');
        $this->data['lang'] = $this->load->language('setup/closing_transfer');
        $this->document->setTitle($this->data['lang']['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    public function index()
    {
        $this->redirect($this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . '&company_id=' . $this->session->data['company_id'], 'SSL'));
    }

    protected function getForm()
    {
        parent::getForm();
        $this->model['image'] = $this->load->model('tool/image');
        $this->data['no_image'] = $this->model['image']->resize('no_logo.jpg', 300, 100);

        $this->data['time_zones'] = getTimeZoneList();


        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencies'] = $this->model['currency']->getArrays('currency_id','name',array('company_id' => $this->session->data['company_id']));

        $this->model['coa'] = $this->load->model('gl/coa_level3');
        $this->data['coas'] = $this->model['coa']->getRows(array('company_id' => $this->session->data['company_id']));

        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
        );
        $results = $this->model[$this->getAlias()]->getRows($filter);
        foreach ($results as $result) {
            if($result['field']=='inventory_account_id') {
                $this->data[$result['field']][] = $result['value'];
            } elseif($result['field']=='revenue_account_id') {
                $this->data[$result['field']][] = $result['value'];
            } elseif($result['field']=='cogs_account_id') {
                $this->data[$result['field']][] = $result['value'];
            } elseif($result['field']=='adjustment_account_id') {
                $this->data[$result['field']][] = $result['value'];
            } else {
                $this->data[$result['field']] = $result['value'];
            }
        }

        if ($this->data['company_logo'] && file_exists(DIR_IMAGE . $this->data['company_logo']) && is_file(DIR_IMAGE . $this->data['company_logo'])) {
            $this->data['src_company_image'] = $this->model['image']->resize($this->data['company_logo'], 300, 100);
        } else {
            $this->data['src_company_image'] = $this->model['image']->resize('no_logo.jpg', 300, 100);
        }
        $this->data['action_update'] = $this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . 'SSL');

        $this->data['strValidation'] = "{
            'rules':{
		        'base_currency_id': {'required':true,},
		        'time_zone': {'required':true,},
		        'suspense_account_id': {'required':true,},
             },
            'ignore':[]
        }";

        $this->response->setOutput($this->render());
    }

    public function update() {
        $this->init();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateUpdate()) {
            //$this->db->beginTransaction();
            $id = $this->updateData($this->request->post);
            //$this->db->commit();

            $this->session->data['success'] = $this->language->get('success_update');

            $this->updateRedirect($id, $this->request->post);
        }
        $this->data['isEdit'] = 1;
        $this->getForm();
    }

    protected function updateData($data) {
        ini_set('max_execution_time', 0);
        $files = $this->request->files;
        $post = $this->request->post;

        $sql = "TRUNCATE TABLE `temporary_supplier`;";
        $this->db->query($sql);
        $sql = "TRUNCATE TABLE `temporary_customer`;";
        $this->db->query($sql);
        $sql = "TRUNCATE TABLE `temporary_warehouse`;";
        $this->db->query($sql);
        $sql = "TRUNCATE TABLE `temporary_product`;";
        $this->db->query($sql);
        $sql = "TRUNCATE TABLE `temporary_opening_stock`;";
        $this->db->query($sql);
        //$sql = "TRUNCATE TABLE `temporary_opening_account`;";
        //$this->db->query($sql);

        if(file_exists($files['supplier_file']['tmp_name'])) {
            $sql = "LOAD DATA LOCAL INFILE '".$this->db->escape($files['supplier_file']['tmp_name'])."' INTO TABLE `temporary_supplier`";
            $sql .= " FIELDS TERMINATED BY ','";
            $sql .= " ENCLOSED BY '\"'";
            $sql .= " LINES TERMINATED BY '\r\n'";
            $sql .= " IGNORE 1 LINES";
            $this->db->query($sql);
        }

        if(file_exists($files['customer_file']['tmp_name'])) {
            $sql = "LOAD DATA LOCAL INFILE '".$this->db->escape($files['customer_file']['tmp_name'])."' INTO TABLE `temporary_customer`";
            $sql .= " FIELDS TERMINATED BY ','";
            $sql .= " ENCLOSED BY '\"'";
            $sql .= " LINES TERMINATED BY '\r\n'";
            $sql .= " IGNORE 1 LINES";
            $this->db->query($sql);
        }

        if(file_exists($files['warehouse_file']['tmp_name'])) {
            $sql = "LOAD DATA LOCAL INFILE '".$this->db->escape($files['warehouse_file']['tmp_name'])."' INTO TABLE `temporary_warehouse`";
            $sql .= " FIELDS TERMINATED BY ','";
            $sql .= " ENCLOSED BY '\"'";
            $sql .= " LINES TERMINATED BY '\r\n'";
            $sql .= " IGNORE 1 LINES";
            $this->db->query($sql);
        }

        if(file_exists($files['product_file']['tmp_name'])) {
            $sql = "LOAD DATA LOCAL INFILE '".$this->db->escape($files['product_file']['tmp_name'])."' INTO TABLE `temporary_product`";
            $sql .= " FIELDS TERMINATED BY ','";
            $sql .= " ENCLOSED BY '\"'";
            $sql .= " LINES TERMINATED BY '\r\n'";
            $sql .= " IGNORE 1 LINES";
            $this->db->query($sql);
        }

        if(file_exists($files['opening_stock_file']['tmp_name'])) {
            $sql = "LOAD DATA LOCAL INFILE '".$this->db->escape($files['opening_stock_file']['tmp_name'])."' INTO TABLE `temporary_opening_stock`";
            $sql .= " FIELDS TERMINATED BY ','";
            $sql .= " ENCLOSED BY '\"'";
            $sql .= " LINES TERMINATED BY '\r\n'";
            $sql .= " IGNORE 1 LINES";
            $this->db->query($sql);
        }


        $this->model['partner'] = $this->load->model('common/partner');

        $sql = "SELECT * FROM `temporary_supplier`";
        $query = $this->db->query($sql);
        $suppliers = $query->rows;
        if(count($suppliers)>0) {
            $sql = "TRUNCATE TABLE `supplier`";
            $this->db->query($sql);

            $this->model['supplier'] = $this->load->model('setup/supplier');
            foreach($suppliers as $supplier) {
                $supplier['company_id'] = $this->session->data['company_id'];
                $supplier['company_branch_id'] = $this->session->data['company_branch_id'];

                $supplier_id = $this->model['supplier']->add($this->getAlias(), $supplier);

                $supplier['partner_type_id'] = 1;
                $supplier['partner_type'] = 'Supplier';
                $supplier['partner_id'] = $supplier_id;
                $supplier['ref_id'] = $supplier['id'];

                $this->model['partner']->add($this->getAlias(), $supplier);
            }
        }

        $sql = "SELECT * FROM `temporary_customer`";
        $query = $this->db->query($sql);
        $customers = $query->rows;
        if(count($customers)>0) {
            $sql = "TRUNCATE TABLE `customer`";
            $this->db->query($sql);

            $this->model['customer'] = $this->load->model('setup/customer');
            foreach($customers as $customer) {
                $customer['company_id'] = $this->session->data['company_id'];
                $customer['company_branch_id'] = $this->session->data['company_branch_id'];

                $customer_id = $this->model['customer']->add($this->getAlias(), $customer);

                $customer['partner_type_id'] = 2;
                $customer['partner_type'] = 'Customer';
                $customer['partner_id'] = $customer_id;
                $customer['ref_id'] = $customer['id'];

                $this->model['partner']->add($this->getAlias(), $customer);
            }
        }

        $sql = "SELECT * FROM `temporary_warehouse`";
        $query = $this->db->query($sql);
        $warehouses = $query->rows;
        if(count($warehouses)>0) {
            $sql = "TRUNCATE TABLE `warehouse`";
            $this->db->query($sql);

            $this->model['warehouse'] = $this->load->model('inventory/warehouse');
            foreach($warehouses as $warehouse) {
                $warehouse['company_id'] = $this->session->data['company_id'];
                $warehouse['company_branch_id'] = $this->session->data['company_branch_id'];

                $warehouse_id = $this->model['warehouse']->add($this->getAlias(), $warehouse);
            }
        }

        $sql = "SELECT * FROM `temporary_product`";
        $query = $this->db->query($sql);
        $products = $query->rows;
        if(count($products) > 0) {
            $sql = "TRUNCATE TABLE `inventory_product_category`";
            $query = $this->db->query($sql);
            $sql = "TRUNCATE TABLE `inventory_thickness`";
            $query = $this->db->query($sql);
            $sql = "TRUNCATE TABLE `inventory_width`";
            $query = $this->db->query($sql);
            $sql = "TRUNCATE TABLE `inventory_length`";
            $query = $this->db->query($sql);
            $sql = "TRUNCATE TABLE `inventory_grade`";
            $query = $this->db->query($sql);
            $sql = "TRUNCATE TABLE `inventory_sawmill`";
            $query = $this->db->query($sql);
            $sql = "TRUNCATE TABLE `inventory_unit`";
            $query = $this->db->query($sql);
            $sql = "TRUNCATE TABLE `inventory_product`";
            $query = $this->db->query($sql);

            $sql = "SELECT DISTINCT `product_category_id`, `product_category` as `name` FROM `temporary_product` WHERE `product_category_id` != 0 AND `product_category` != '' ORDER BY product_category_id";
            $query = $this->db->query($sql);
            $rows = $query->rows;
            $this->model['product_category'] = $this->load->model('inventory/product_category');
            foreach($rows as $data) {
                $data['company_id'] = $this->session->data['company_id'];
                $this->model['product_category']->add($this->getAlias(), $data);
            }

            $sql = "SELECT DISTINCT `thickness_id`, `thickness` as `name` FROM `temporary_product` WHERE `thickness_id` != 0 AND `thickness` != '' ORDER BY thickness_id";
            $query = $this->db->query($sql);
            $rows = $query->rows;
            $this->model['thickness'] = $this->load->model('inventory/thickness');
            foreach($rows as $data) {
                $data['company_id'] = $this->session->data['company_id'];
                $this->model['thickness']->add($this->getAlias(), $data);
            }

            $sql = "SELECT DISTINCT `width_id`, `width` as `name` FROM `temporary_product` WHERE `width_id` != 0 AND `width` != '' ORDER BY width_id";
            $query = $this->db->query($sql);
            $rows = $query->rows;
            $this->model['width'] = $this->load->model('inventory/width');
            foreach($rows as $data) {
                $data['company_id'] = $this->session->data['company_id'];
                $this->model['width']->add($this->getAlias(), $data);
            }

            $sql = "SELECT DISTINCT `length_id`, `length` as `name` FROM `temporary_product` WHERE `length_id` != 0 AND `length` != '' ORDER BY length_id";
            $query = $this->db->query($sql);
            $rows = $query->rows;
            $this->model['length'] = $this->load->model('inventory/length');
            foreach($rows as $data) {
                $data['company_id'] = $this->session->data['company_id'];
                $this->model['length']->add($this->getAlias(), $data);
            }

            $sql = "SELECT DISTINCT `grade_id`, `grade` as `name` FROM `temporary_product` WHERE `grade_id` != 0 AND `grade` != '' ORDER BY grade_id";
            $query = $this->db->query($sql);
            $rows = $query->rows;
            $this->model['grade'] = $this->load->model('inventory/grade');
            foreach($rows as $data) {
                $data['company_id'] = $this->session->data['company_id'];
                $this->model['grade']->add($this->getAlias(), $data);
            }

            $sql = "SELECT DISTINCT `sawmill_id`, `sawmill` as `name` FROM `temporary_product` WHERE `sawmill_id` != 0 AND `sawmill` != '' ORDER BY sawmill_id";
            $query = $this->db->query($sql);
            $rows = $query->rows;
            $this->model['sawmill'] = $this->load->model('inventory/sawmill');
            foreach($rows as $data) {
                $data['company_id'] = $this->session->data['company_id'];
                $this->model['sawmill']->add($this->getAlias(), $data);
            }

            $sql = "SELECT DISTINCT `unit_id`, `unit` as `name` FROM `temporary_product` WHERE `unit_id` != 0 AND `unit` != '' ORDER BY unit_id";
            $query = $this->db->query($sql);
            $rows = $query->rows;
            $this->model['unit'] = $this->load->model('inventory/unit');
            foreach($rows as $data) {
                $data['company_id'] = $this->session->data['company_id'];
                $this->model['unit']->add($this->getAlias(), $data);
            }

            $this->model['setting'] = $this->load->model('common/setting');
            $config = $this->model['setting']->getArrays('field','value',array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $this->session->data['company_branch_id'],
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'module' => 'Inventory',
            ));

            $sql = "SELECT * FROM `temporary_product`";
            $query = $this->db->query($sql);
            $rows = $query->rows;
            $this->model['product'] = $this->load->model('inventory/product');
            foreach($rows as $data) {
                $data['company_id'] = $this->session->data['company_id'];
                $data['cogs_account_id'] = $config['cogs_account_id'];
                $data['inventory_account_id'] = $config['inventory_account_id'];
                $data['revenue_account_id'] = $config['revenue_account_id'];
                $data['adjustment_account_id'] = $config['adjustment_account_id'];
                $this->model['product']->add($this->getAlias(), $data);
            }
        }

        $sql = "SELECT `warehouse_id`, `container_no`, SUM(`rate`*`qty`) AS `net_amount`";
        $sql .= " FROM `temporary_opening_stock`";
        $sql .= " GROUP BY `warehouse_id`, `container_no`";
        $query = $this->db->query($sql);
        $opening_stocks = $query->rows;
        if(count($opening_stocks)>0) {
            $this->document_type_id = 6;
            if(isset($post['truncate_opening_stock']) && $post['truncate_opening_stock']==1) {
                $sql = "TRUNCATE TABLE `inventory_opening_stock_detail`";
                $this->db->query($sql);
                $sql = "TRUNCATE TABLE `inventory_opening_stock`";
                $this->db->query($sql);
                $sql = "DELETE FROM `document` WHERE `document_type_id` = '".$this->document_type_id."'";
                $this->db->query($sql);
                $sql = "DELETE FROM `ledger` WHERE `document_type_id` = '".$this->document_type_id."'";
                $this->db->query($sql);
                $sql = "DELETE FROM `inventory_stock_ledger` WHERE `document_type_id` = '".$this->document_type_id."'";
                $this->db->query($sql);
            }

            $this->model['opening_stock'] = $this->load->model('inventory/opening_stock');
            $this->model['opening_stock_detail'] = $this->load->model('inventory/opening_stock_detail');
            $this->model['document'] = $this->load->model('common/document');
            $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
            $this->model['ledger'] = $this->load->model('gl/ledger');
            $this->model['product'] = $this->load->model('inventory/product');
            $this->model['setting'] = $this->load->model('common/setting');
            $this->model['document_type'] = $this->load->model('common/document_type');

            $row = $this->model['setting']->getRow(array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $this->session->data['company_branch_id'],
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'module' => 'general',
                'field' => 'suspense_account_id',
            ));
            $suspense_account_id = $row['value'];

            foreach($opening_stocks as $data) {
                $document = $this->model['document_type']->getNextDocument($this->document_type_id);
                $data['remarks'] = $data['container_no'];
                $data['base_currency_id'] = $this->session->data['base_currency_id'];
                $data['base_currency'] = $this->session->data['base_currency_name'];
                $data['document_currency_id'] = $this->session->data['base_currency_id'];
                $data['conversion_rate'] = "1.00";
                $data['document_date'] = MySqlDate();
                $data['company_id'] = $this->session->data['company_id'];
                $data['company_branch_id'] = $this->session->data['company_branch_id'];
                $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
                $data['document_type_id'] = $this->document_type_id;
                $data['document_prefix'] = $document['document_prefix'];
                $data['document_no'] = $document['document_no'];
                $data['document_identity'] = $document['document_identity'];
                $data['base_amount'] = $data['net_amount'] * $data['conversion_rate'];
                $opening_stock_id = $this->model['opening_stock']->add($this->getAlias(), $data);
                $data['document_id'] = $opening_stock_id;

                $insert_document = array(
                    'company_id' => $this->session->data['company_id'],
                    'company_branch_id' => $this->session->data['company_branch_id'],
                    'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                    'document_type_id' => $this->document_type_id,
                    'document_id' => $opening_stock_id,
                    'document_identity' => $data['document_identity'],
                    'document_date' => $data['document_date'],
                    'partner_type_id' => '',
                    'partner_id' => '',
                    'document_currency_id' => $data['document_currency_id'],
                    'document_amount' => $data['net_amount'],
                    'conversion_rate' => $data['conversion_rate'],
                    'base_currency_id' => $data['base_currency_id'],
                    'base_amount' => $data['base_amount'],
                );
                $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

                $gl = array();
                $gl[] = array(
                    'partner_type_id' => '',
                    'partner_id' => '',
                    'reference_document_type_id' => '',
                    'reference_document_identity' => '',
                    'coa_id' => $suspense_account_id,
                    'remarks' => '',
                    'document_currency_id' => $data['document_currency_id'],
                    'document_debit' => 0,
                    'document_credit' => $data['net_amount'],
                    'base_currency_id' => $data['base_currency_id'],
                    'conversion_rate' => $data['conversion_rate'],
                    'debit' => 0,
                    'credit' => $data['base_amount'],
                );

                $sql="SELECT tos.container_no, tos.batch_no, tos.product_id, p.product_code, p.unit_id, tos.qty, tos.rate, (tos.qty*tos.rate) AS amount";
                $sql .= " FROM `temporary_opening_stock` tos";
                $sql .= " LEFT JOIN `inventory_product` p ON p.product_id = tos.product_id";
                $sql .= " WHERE `warehouse_id` = '".$data['warehouse_id']."' AND `container_no` = '".$data['container_no']."'";
                $query = $this->db->query($sql);
                $details = $query->rows;

                $sort_order = 0;
                foreach ($details as $detail) {
                    $product = $this->model['product']->getRow(array('product_id' => $detail['product_id']));

                    $detail['opening_stock_id'] = $opening_stock_id;
                    $detail['sort_order'] = $sort_order+1;
                    $detail['company_id'] = $this->session->data['company_id'];
                    $detail['company_branch_id'] = $this->session->data['company_branch_id'];
                    $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
                    $detail['document_currency_id'] = $data['document_currency_id'];
                    $detail['base_currency_id'] = $data['base_currency_id'];
                    $detail['conversion_rate'] = $data['conversion_rate'];
                    $detail['base_amount'] = $data['conversion_rate'] * $detail['amount'];
                    $opening_stock_detail_id = $this->model['opening_stock_detail']->add($this->getAlias(), $detail);

                    $stock_ledger = array(
                        'company_id' => $detail['company_id'],
                        'company_branch_id' => $detail['company_branch_id'],
                        'fiscal_year_id' => $detail['fiscal_year_id'],
                        'document_type_id' => $data['document_type_id'],
                        'document_id' => $data['document_id'],
                        'document_identity' => $data['document_identity'],
                        'document_date' => $data['document_date'],
                        'document_detail_id' => $opening_stock_detail_id,
                        'warehouse_id' => $data['warehouse_id'],
                        'container_no' => $detail['container_no'],
                        'batch_no' => $detail['batch_no'],
                        'product_id' => $detail['product_id'],
                        'document_unit_id' => $detail['unit_id'],
                        'document_qty' => $detail['qty'],
                        'unit_conversion' => 1,
                        'base_unit_id' => $detail['unit_id'],
                        'base_qty' => $detail['qty'],
                        'document_currency_id' => $detail['document_currency_id'],
                        'document_rate' => $detail['rate'],
                        'document_amount' => $detail['amount'],
                        'currency_conversion' => $detail['conversion_rate'],
                        'base_currency_id' => $detail['base_currency_id'],
                        'base_rate' => ($detail['rate'] * $detail['conversion_rate']),
                        'base_amount' => ($detail['amount'] * $detail['conversion_rate']),
                    );
                    $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $stock_ledger);

                    $gl[] = array(
                        'document_detail_id' => $opening_stock_detail_id,
                        'partner_type_id' => '',
                        'partner_id' => '',
                        'reference_document_type_id' => $this->document_type_id,
                        'reference_document_identity' => $data['document_identity'],
                        'coa_id' => $product['inventory_account_id'],
                        'remarks' => '',
                        'document_currency_id' => $detail['document_currency_id'],
                        'document_debit' => $detail['amount'],
                        'document_credit' => 0,
                        'base_currency_id' => $detail['base_currency_id'],
                        'conversion_rate' => $detail['conversion_rate'],
                        'debit' => ($detail['amount'] * $detail['conversion_rate']),
                        'credit' => 0,
                    );
                    $sort_order++;
                }

                if($gl) {
                    foreach($gl as $sort_order => $ledger) {
                        $ledger['company_id'] = $this->session->data['company_id'];
                        $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
                        $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
                        $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
                        $ledger['document_type_id'] = $this->document_type_id;
                        $ledger['document_id'] = $data['document_id'];
                        $ledger['document_identity'] = $data['document_identity'];
                        $ledger['document_date'] = $data['document_date'];
                        $ledger['sort_order'] = $sort_order;

                        $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);
                    }
                }

            }

        }
    }
}

?>