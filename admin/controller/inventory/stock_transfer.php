<?php

class ControllerInventoryStockTransfer extends HController {
    protected $document_type_id = 25;

    protected function getAlias() {
        return 'inventory/stock_transfer';
    }

    protected function getPrimaryKey() {
        return 'stock_transfer_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {
        $lang = $this->load->language('inventory/stock_transfer');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action', 'document_date', 'document_identity', 'warehouse', 'total_qty', 'created_at', 'check_box');

        /*
         * Paging
         */
        $sLimit = "";
        if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
            $data['criteria']['start'] = $_GET['iDisplayStart'];
            $data['criteria']['limit'] = $_GET['iDisplayLength'];
        }

        /*
         * Ordering
         */
        $sOrder = "";
        if (isset($_GET['iSortCol_0'])) {
            $sOrder = " ORDER BY  ";
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
                if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
                    $sOrder .= "`" . $aColumns[intval($_GET['iSortCol_' . $i])] . "` " .
                        ($_GET['sSortDir_' . $i] === 'asc' ? 'asc' : 'desc') . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == " ORDER BY") {
                $sOrder = "";
            }
            $data['criteria']['orderby'] = $sOrder;
        }


        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $arrWhere = array();
        $arrWhere[] = "`company_id` = '".$this->session->data['company_id']."'";
        $arrWhere[] = "`company_branch_id` = '".$this->session->data['company_branch_id']."'";
        $arrWhere[] = "`fiscal_year_id` = '".$this->session->data['fiscal_year_id']."'";
        if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
            $arrSSearch = array();
            for ($i = 0; $i < count($aColumns); $i++) {
                if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch'] != '') {
                    $arrSSearch[] = "LOWER(`" . $aColumns[$i] . "`) LIKE '%" . $this->db->escape(strtolower($_GET['sSearch'])) . "%'";
                }
            }
            if(!empty($arrSSearch)) {
                $arrWhere[] = '(' . implode(' OR ', $arrSSearch) . ')';
            }
        }

        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
                $arrWhere[] = "LOWER(`" . $aColumns[$i] . "`) LIKE '%" . $this->db->escape(strtolower($_GET['sSearch_' . $i])) . "%' ";
            }
        }

        if (!empty($arrWhere)) {
            //$data['filter']['RAW'] = substr($sWhere, 5, strlen($sWhere) - 5);
            $data['filter']['RAW'] = implode(' AND ', $arrWhere);
        }

        //d($data, true);
        $results = $this->model[$this->getAlias()]->getLists($data);
        $iFilteredTotal = $results['total'];
        $iTotal = $results['table_total'];


        /*
         * Output
         */
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $iTotal,
            "iTotalDisplayRecords" => $iFilteredTotal,
            "aaData" => array()
        );

        foreach ($results['lists'] as $aRow) {
            $row = array();
            $actions = array();

            $actions[] = array(
                'text' => $lang['edit'],
                'href' => $this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                'btn_class' => 'btn btn-primary btn-xs',
                'class' => 'fa fa-pencil'
            );

            $actions[] = array(
                'text' => $lang['print'],
                'target' => '_blank',
                'href' => $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                'btn_class' => 'btn btn-info btn-xs',
                'class' => 'fa fa-print'
            );

            if($aRow['is_post']==0) {
                $actions[] = array(
                    'text' => $lang['post'],
                    'href' => $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                    'btn_class' => 'btn btn-info btn-xs',
                    'class' => 'fa fa-thumbs-up'
                );

                $actions[] = array(
                    'text' => $lang['delete'],
                    'href' => 'javascript:void(0);',
                    'click' => "ConfirmDelete('" . $this->url->link($this->getAlias() . '/delete', 'token=' . $this->session->data['token'] . '&id=' . $aRow[$this->getPrimaryKey()], 'SSL') . "')",
                    'btn_class' => 'btn btn-danger btn-xs',
                    'class' => 'fa fa-times'
                );
            }

            $strAction = '';
            foreach ($actions as $action) {
                $strAction .= '<a '.(isset($action['btn_class'])?'class="'.$action['btn_class'].'"':'').' href="' . $action['href'] .'" '. (isset($action['target']) ? 'target="' . $action['target'] . '"' : '') . ' data-toggle="tooltip" title="' . $action['text'] . '" ' . (isset($action['click']) ? 'onClick="' . $action['click'] . '"' : '') . '>';
                if (isset($action['class'])) {
                    $strAction .= '<span class="' . $action['class'] . '"></span>';
                } else {
                    $strAction .= $action['text'];
                }
                $strAction .= '</a>&nbsp;';
            }

            for ($i = 0; $i < count($aColumns); $i++) {
                if ($aColumns[$i] == 'action') {
                    $row[] = $strAction;
                } elseif ($aColumns[$i] == 'document_date') {
                    $row[] = stdDate($aRow['document_date']);
                } elseif ($aColumns[$i] == 'created_at') {
                    $row[] = stdDateTime($aRow['created_at']);
                } elseif ($aColumns[$i] == 'check_box') {
                    $row[] = '<input type="checkbox" name="selected[]" value="' . $aRow[$this->getPrimaryKey()] . '" />';
                } else {
                    $row[] = $aRow[$aColumns[$i]];
                }

            }
            $output['aaData'][] = $row;
        }

        echo json_encode($output);
    }

    protected function getForm() {
        parent::getForm();

        $this->model['product'] = $this->load->model('inventory/product');
        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencys'] = $this->model['currency']->getRows();

        $this->model['unit'] = $this->load->model('inventory/unit');
        $this->data['units'] = $this->model['unit']->getRows(array('company_id' => $this->session->data['company_id']));
        $arrUnits = $this->model['unit']->getArrays('unit_id','name',array('company_id' => $this->session->data['company_id']));

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['FromWarehouses'] = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['warehouses'] = $this->model['warehouse']->getRows();

        $this->model['customer'] = $this->load->model('setup/customer');
        $this->data['partners'] = $this->model['customer']->getRows(array('company_id' => $this->session->data['company_id'], 'customer_id' => 'ce45eb2c-6cf1-46ac-93d2-8a050b0cf051'),array('name'));

        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->data['company_branchs'] = $this->model['company_branch']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";

        $this->data['document_date'] = stdDate();
        if (isset($this->request->get['stock_transfer_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                if ($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                } elseif($field=='billty_date') {
                    $this->data[$field] = stdDate($value);
                }else {
                    $this->data[$field] = $value;
                }
            }


            $this->model['stock_transfer_detail'] = $this->load->model('inventory/stock_transfer_detail');
            $rows = $this->model['stock_transfer_detail']->getRows(array('stock_transfer_id' => $this->request->get['stock_transfer_id']),array('sort_order'));

            foreach($rows as $row_no => $row) {
                $this->data['stock_transfer_details'][$row_no] = $row;
                // $warehouses = $this->model['warehouse']->getRows(array('company_branch_id' => $row['to_company_branch_id']));
//                $html = '';
//                $html .= '<option value="">&nbsp;</option>';
//                foreach($warehouses as $warehouse) {
//
//                    if($row['warehouse_id']!= "") {
//                        $html .= '<option value="'.$row['warehouse_id'].'" selected="true">'.$warehouse['name'].'</option>';
//                    } else {
//                        $html .= '<option value="'.$warehouse['warehouse_id'].'">'.$warehouse['name'].'</option>';
//                    }
//                }
//                $this->data['stock_transfer_details'][$row_no]['warehouse_id'] = $html;
            }
//d($this->data['stock_transfer_details'],true);


        }
        $this->data['href_get_product_json'] = $this->url->link($this->getAlias() . '/getProductJson', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_un_post'] = $this->url->link($this->getAlias() . '/Unpost', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['get_warehouse_by_branch'] = $this->url->link($this->getAlias() . '/getWarehouseByBranch', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_container_data'] = $this->url->link($this->getAlias() . '/getContainerData', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print'] = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['strValidation'] = "{
            'rules': {
                'document_date': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'partner_id': {'required': true},
                'warehouse_id': {'required': true},
                'total_qty': {'required': true, 'min':1},
            },
            'ignore': [],
        }";

        $this->response->setOutput($this->render());
    }

    public function getWarehouseByBranch() {
        $post = $this->request->post;
        $Company_branch_id = $post['company_branch_id'];
        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        
        // commented this because of client's requirements
        // $rows = $this->model['warehouse']->getRows(array('company_branch_id' => $Company_branch_id));
        $rows = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['warehouses'] = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id']));

        $html = '';
        $html .= '<option value="">&nbsp;</option>';
        foreach($rows as $row) {
            $html .= '<option value="'.$row['warehouse_id'].'">'.$row['name'].'</option>';
        }
        $json = array(
            'success' => true,
            'html' => $html
        );
//d($json,true);
        echo json_encode($json);
    }

    public function getContainerData() {
        $post = $this->request->post;
        $warehouse_id = $post['warehouse_id'];
        $container_no = $post['container_no'];
        $this->model['stock'] = $this->load->model('common/stock_ledger');
        $details = $this->model['stock']->getBalanceContainerStocks($container_no, $warehouse_id);
        $json = array(
            'success' => true,
            'details' => $details
        );

        echo json_encode($json);
    }

    public function getProductJson() {
        $search = $this->request->post['q'];
        $page = $this->request->post['page'];

        $this->model['product'] = $this->load->model('inventory/product');
        $rows = $this->model['product']->getProductJson($search, $page);
        echo json_encode($rows);
    }

    protected function insertData($data) {

        $this->model['document_type'] = $this->load->model('common/document_type');
        $document = $this->model['document_type']->getNextDocument($this->document_type_id);

        $data['document_type_id'] = $this->document_type_id;
        $data['document_prefix'] = $document['document_prefix'];
        $data['document_no'] = $document['document_no'];
        $data['document_identity'] = $document['document_identity'];

        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['document_date'] = MySqlDate($data['document_date']);

        if($data['billty_date'] != '') {
            $data['billty_date'] = MySqlDate($data['billty_date']);
        } else {
            $data['billty_date'] = NULL;
        }

        $data['base_amount'] = $data['total_amount'] * $data['conversion_rate'];
        $data['partner_type_id'] = 2;

        $stock_transfer_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $data['document_id'] = $stock_transfer_id;


        $this->model['stock_transfer_detail'] = $this->load->model('inventory/stock_transfer_detail');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['customer'] = $this->load->model('setup/customer');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['branch_rate']= $this->load->model('inventory/branch_rate');

        $Customers = $this->model['customer']->getRow(array('customer_id' => $data['partner_id']));

        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $data['document_id'],
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['total_amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['base_amount'],
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);
        //d(array($document_id, $document), true);
        $stock_ledger = array();

//        $this->model['setting']= $this->load->model('common/setting');
//        $setting = $this->model['setting']->getRow(array(
//            'company_id' => $this->session->data['company_id'],
//            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
//            'module' => 'inventory',
//            'field' => 'branch_payable_account_id',
//        ));


        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $Current_Company_Branch = $this->model['company_branch']->getRow(array('company_id' => $this->session->data['company_id'],'company_branch_id' => $this->session->data['company_branch_id']));
        $To_Company_Branch = $this->model['company_branch']->getRow(array('company_id' => $this->session->data['company_id'],'company_branch_id' => $data['to_branch_id']));
        $Current_branch_account_id = $Current_Company_Branch['branch_account_id'];
        $To_branch_account_id = $To_Company_Branch['branch_account_id'];

        // Form Compnay ledger entry //

        // Cogs Account Entry //
        // $gl_data[] = array(
        //     'document_detail_id' => '',
        //     'coa_id' => $To_branch_account_id,
        //     'document_debit' => $data['total_amount'],
        //     'document_credit' => 0,
        //     'debit' => $data['total_amount'],
        //     'credit' => 0,
        //     'document_amount' => $data['total_amount'],
        //     'amount' => ($data['total_amount'] * $data['conversion_rate']),
        //     'company_branch_id' => $this->session->data['company_branch_id'],

        // );

        // // Inventory Account Entry //
        // $gl_data[] = array(
        //     'document_detail_id' => '',
        //     'coa_id' => $Current_branch_account_id,
        //     'document_debit' => 0,
        //     'document_credit' => $data['total_amount'],
        //     'debit' => 0,
        //     'credit' => $data['total_amount'],
        //     'document_amount' => $data['total_amount'],
        //     'amount' => ($data['total_amount'] * $data['conversion_rate']),
        //     'company_branch_id' => $data['to_branch_id'],
        // );


        foreach ($data['stock_transfer_details'] as $sort_order => $detail) {
            $detail['stock_transfer_id'] = $stock_transfer_id;
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['sort_order'] = $sort_order;
            $detail['to_company_branch_id'] = $data['to_branch_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_cog_rate'] = ($detail['cog_rate'] * $data['conversion_rate']);
            $detail['base_cog_amount'] = ($detail['cog_amount'] * $data['conversion_rate']);
            $stock_transfer_detail_id=$this->model['stock_transfer_detail']->add($this->getAlias(), $detail);

            $customer_rate = array(
                'company_id' => $detail['company_id'],
                //'customer_id' => $data['partner_id'],
                'branch_id' => $data['to_branch_id'],
                'product_id' => $detail['product_id'],
                'rate' => $detail['rate'],
                'invoice_date' => $data['document_date'],
                'created_at' => date("Y-m-d H:i:s"),

            );

            // Add customer_rate //
            $rate_id = $this->model['branch_rate']->add($this->getAlias(), $customer_rate);

            $stock_ledger[] = array(
                'document_detail_id' => $stock_transfer_detail_id,
                'warehouse_id' => $data['warehouse_id'],
                'container_no' => $detail['container_no'],
                'batch_no' => $detail['batch_no'],
                'product_id' => $detail['product_id'],
                'document_unit_id' => $detail['unit_id'],
                'document_qty' => $detail['qty'],
                'unit_conversion' => 1,
                'base_unit_id' => $detail['unit_id'],
                'base_qty' => (-1 * $detail['qty']),
                'document_rate' => $detail['rate'],
                'document_amount' => (-1 * $detail['amount']),
                'base_rate' => ($detail['rate'] * $detail['conversion_rate']),
                'base_amount' => (-1 * $detail['amount'] * $detail['conversion_rate']),
                'remarks' => $data['remarks'],
                'company_branch_id' => $this->session->data['company_branch_id'],
            );

            // $this->model['warehouse'] = $this->load->model('inventory/warehouse');
            // $warehouse = $this->model['warehouse']->getRow(array('warehouse_id' => $detail['warehouse_id']));
            
            $stock_ledger[] = array(
                'document_detail_id' => $stock_transfer_detail_id,
                'warehouse_id' => $detail['warehouse_id'],
                'container_no' => $detail['container_no'],
                'batch_no' => $detail['batch_no'],
                'product_id' => $detail['product_id'],
                'document_unit_id' => $detail['unit_id'],
                'document_qty' => $detail['qty'],
                'unit_conversion' => 1,
                'base_unit_id' => $detail['unit_id'],
                'base_qty' => ($detail['qty']),
                'document_rate' => $detail['rate'],
                'document_amount' => ($detail['amount']),
                'base_rate' => ($detail['rate'] * $detail['conversion_rate']),
                'base_amount' => ($detail['amount'] * $detail['conversion_rate']),
                'remarks' => $data['remarks'],
                'company_branch_id' => $this->session->data['company_branch_id'],
            );

            // For Ledger //


        }

//        d(array($data,$detail,$stock_ledger,$gl_data,$ToBranch),true);
        //d($stock_ledger, true);
        foreach($stock_ledger as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $data['document_id'];
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['sort_order'] = $sort_order;
            $ledger['base_currency_id'] = $data['base_currency_id'];
            $ledger['document_currency_id'] = $data['document_currency_id'];
            $ledger['currency_conversion'] = $data['conversion_rate'];

            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $ledger);
        }

        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['ref_document_type_id'] = $this->document_type_id;
            $ledger['ref_document_identity'] = $data['document_identity'];
            $ledger['document_id'] = $stock_transfer_id;
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['sort_order'] = $sort_order;
            $ledger['base_currency_id'] = $data['base_currency_id'];
            $ledger['document_currency_id'] = $data['document_currency_id'];
            $ledger['conversion_rate'] = $data['conversion_rate'];
            $ledger['partner_type_id'] = $data['partner_type_id'];
            $ledger['partner_id'] = $data['partner_id'];
            $ledger['remarks'] = $data['remarks'];

            $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);
        }
        return $stock_transfer_id;
    }

    protected function insertRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias().'/update', 'token=' . $this->session->data['token'] . '&stock_transfer_id=' . $id, 'SSL'));
    }

    protected function updateData($primary_key, $data) {
        $stock_transfer_id = $primary_key;
        $data['document_date'] = MySqlDate($data['document_date']);
        $data['partner_type_id'] = 2;

        $this->model['stock_transfer'] = $this->load->model('inventory/stock_transfer');
        $this->model['stock_transfer_detail'] = $this->load->model('inventory/stock_transfer_detail');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['customer'] = $this->load->model('setup/customer');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['branch_rate']= $this->load->model('inventory/branch_rate');

        if($data['billty_date'] != '') {
            $data['billty_date'] = MySqlDate($data['billty_date']);
        } else {
            $data['billty_date'] = NULL;
        }


        $this->model['stock_transfer']->edit($this->getAlias(), $primary_key, $data);
        $this->model['stock_transfer_detail']->deleteBulk($this->getAlias(), array('stock_transfer_id' => $stock_transfer_id));
        $this->model['document']->deleteBulk($this->getAlias(), array('document_id' => $stock_transfer_id));
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_id' => $stock_transfer_id));
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('document_id' => $stock_transfer_id));


        $Customers = $this->model['customer']->getRow(array('customer_id' => $data['partner_id']));

        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $data['document_id'],
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['total_amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['base_amount'],
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);
        //d(array($document_id, $document), true);
        $stock_ledger = array();


//        $this->model['setting']= $this->load->model('common/setting');
//        $setting = $this->model['setting']->getRow(array(
//            'company_id' => $this->session->data['company_id'],
//            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
//            'module' => 'inventory',
//            'field' => 'branch_payable_account_id',
//        ));
//        $branch_payable_account_id = $setting['value'];


        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $Current_Company_Branch = $this->model['company_branch']->getRow(array('company_id' => $this->session->data['company_id'],'company_branch_id' => $this->session->data['company_branch_id']));
        $To_Company_Branch = $this->model['company_branch']->getRow(array('company_id' => $this->session->data['company_id'],'company_branch_id' => $data['to_branch_id']));
        $Current_branch_account_id = $Current_Company_Branch['branch_account_id'];
        $To_branch_account_id = $To_Company_Branch['branch_account_id'];


        // Form Compnay ledger entry //

        // Cogs Account Entry //
        // $gl_data[] = array(
        //     'document_detail_id' => '',
        //     'coa_id' => $To_branch_account_id,
        //     'document_debit' => $data['total_amount'],
        //     'document_credit' => 0,
        //     'debit' => $data['total_amount'],
        //     'credit' => 0,
        //     'document_amount' => $data['total_amount'],
        //     'amount' => ($data['total_amount'] * $data['conversion_rate']),
        //     'company_branch_id' => $this->session->data['company_branch_id'],

        // );

        // // Inventory Account Entry //
        // $gl_data[] = array(
        //     'document_detail_id' => '',
        //     'coa_id' => $Current_branch_account_id,
        //     'document_debit' => 0,
        //     'document_credit' => $data['total_amount'],
        //     'debit' => 0,
        //     'credit' => $data['total_amount'],
        //     'document_amount' => $data['total_amount'],
        //     'amount' => ($data['total_amount'] * $data['conversion_rate']),
        //     'company_branch_id' => $data['to_branch_id'],
        // );

//ddd        d(array($data,$Current_Company_Branch,$To_Company_Branch,$Current_branch_account_id,$To_branch_account_id),true);
        foreach ($data['stock_transfer_details'] as $sort_order => $detail) {
            $detail['stock_transfer_id'] = $stock_transfer_id;
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['sort_order'] = $sort_order;
            $detail['to_company_branch_id'] = $data['to_branch_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_cog_rate'] = ($detail['cog_rate'] * $data['conversion_rate']);
            $detail['base_cog_amount'] = ($detail['cog_amount'] * $data['conversion_rate']);
            $stock_transfer_detail_id=$this->model['stock_transfer_detail']->add($this->getAlias(), $detail);

            $customer_rate = array(
                'company_id' => $detail['company_id'],
                //customer_id' => $data['partner_id'],
                'branch_id' => $data['to_branch_id'],
                'product_id' => $detail['product_id'],
                'rate' => $detail['rate'],
                'invoice_date' => $data['document_date'],
                'created_at' => date("Y-m-d H:i:s"),

            );

            // Add customer_rate //
            $rate_id = $this->model['branch_rate']->add($this->getAlias(), $customer_rate);


            $stock_ledger[] = array(
                'document_detail_id' => $stock_transfer_detail_id,
                'warehouse_id' => $data['warehouse_id'],
                'container_no' => $detail['container_no'],
                'batch_no' => $detail['batch_no'],
                'product_id' => $detail['product_id'],
                'document_unit_id' => $detail['unit_id'],
                'document_qty' => $detail['qty'],
                'unit_conversion' => 1,
                'base_unit_id' => $detail['unit_id'],
                'base_qty' => (-1 * $detail['qty']),
                'document_rate' => $detail['rate'],
                'document_amount' => (-1 * $detail['amount']),
                'base_rate' => ($detail['rate'] * $detail['conversion_rate']),
                'base_amount' => (-1 * $detail['amount'] * $detail['conversion_rate']),
                'remarks' => $data['remarks'],
                'company_branch_id' => $this->session->data['company_branch_id'],
            );
            $stock_ledger[] = array(
                'document_detail_id' => $stock_transfer_detail_id,
                'warehouse_id' => $detail['warehouse_id'],
                'container_no' => $detail['container_no'],
                'batch_no' => $detail['batch_no'],
                'product_id' => $detail['product_id'],
                'document_unit_id' => $detail['unit_id'],
                'document_qty' => $detail['qty'],
                'unit_conversion' => 1,
                'base_unit_id' => $detail['unit_id'],
                'base_qty' => ($detail['qty']),
                'document_rate' => $detail['rate'],
                'document_amount' => ($detail['amount']),
                'base_rate' => ($detail['rate'] * $detail['conversion_rate']),
                'base_amount' => ($detail['amount'] * $detail['conversion_rate']),
                'remarks' => $data['remarks'],
                'company_branch_id' => $this->session->data['company_branch_id'],
            );

//            // For Ledger //
//
//            // Form Compnay ledger entry //
//
//            // Cogs Account Entry //
//            $gl_data[] = array(
//                'document_detail_id' => $stock_transfer_detail_id,
//                'coa_id' => $To_branch_account_id,
//                'document_debit' => $detail['amount'],
//                'document_credit' => 0,
//                'debit' => $detail['amount'],
//                'credit' => 0,
//                'product_id' => $detail['product_id'],
//                'qty' => $detail['qty'],
//                'document_amount' => $detail['amount'],
//                'amount' => ($detail['amount'] * $data['conversion_rate']),
//                'company_branch_id' => $this->session->data['company_branch_id'],
//
//            );
//            // Inventory Account Entry //
//            $gl_data[] = array(
//                'document_detail_id' => $stock_transfer_detail_id,
//                'coa_id' => $Current_branch_account_id,
//                'document_debit' => 0,
//                'document_credit' => $detail['amount'],
//                'debit' => 0,
//                'credit' => $detail['amount'],
//                'product_id' => $detail['product_id'],
//                'qty' => $detail['qty'],
//                'document_amount' => $detail['amount'],
//                'amount' => ($detail['amount'] * $data['conversion_rate']),
//                'company_branch_id' => $this->session->data['company_branch_id'],
//
//            );
//
        }

//        d(array($data,$detail,$stock_ledger,$gl_data,$ToBranch),true);
        //d($stock_ledger, true);
        foreach($stock_ledger as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $data['document_id'];
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['sort_order'] = $sort_order;
            $ledger['base_currency_id'] = $data['base_currency_id'];
            $ledger['document_currency_id'] = $data['document_currency_id'];
            $ledger['currency_conversion'] = $data['conversion_rate'];

            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $ledger);
        }

        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['ref_document_type_id'] = $this->document_type_id;
            $ledger['ref_document_identity'] = $data['document_identity'];
            $ledger['document_id'] = $stock_transfer_id;
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['sort_order'] = $sort_order;
            $ledger['base_currency_id'] = $data['base_currency_id'];
            $ledger['document_currency_id'] = $data['document_currency_id'];
            $ledger['conversion_rate'] = $data['conversion_rate'];
            $ledger['partner_type_id'] = $data['partner_type_id'];
            $ledger['partner_id'] = $data['partner_id'];
            $ledger['remarks'] = $data['remarks'];

            $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);
        }
        return $stock_transfer_id;
    }

    protected function updateRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias().'/update', 'token=' . $this->session->data['token'] . '&stock_transfer_id=' . $id, 'SSL'));
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('delete', $this->getAlias())) {
            $this->error['warning'] = $this->language->get('error_permission_delete');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function Unpost() {

        $lang = $this->load->language($this->getAlias());
//        if (!$this->user->hasPermission('post', $this->getAlias())) {
//            $this->session->data['error_warning'] = $lang['error_permission_post'];
//        } else {

        $data = array(
            'is_post' => 0,
            'post_date' => date('Y-m-d H:i:s'),
            'post_by_id' => $this->session->data['user_id']
        );
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $this->model[$this->getAlias()]->edit($this->getAlias(),$this->request->get[$this->getPrimaryKey()],$data);

        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->edit($this->getAlias(),$this->request->get[$this->getPrimaryKey()],$data);

        //    }

        $this->redirect($this->url->link($this->getAlias(), 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL'));
    }

    protected function deleteData($primary_key) {
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['stock_transfer_detail'] = $this->load->model('inventory/stock_transfer_detail');
        $this->model['stock_transfer_detail']->deleteBulk($this->getAlias(),array('stock_transfer_id' => $primary_key));

        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->deleteBulk($this->getAlias(), array('document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

    public function ajaxValidateForm() {
        $post  = $this->request->post;
        $lang = $this->load->language('inventory/stock_transfer');
        $error = array();

        if($post['voucher_date'] == '') {
            $error[] = $lang['error_voucher_date'];
        }

        if($post['supplier_id'] == '') {
            $error[] = $lang['error_supplier'];
        }

        $details = $post['stock_transfer_details'];
        if(empty($details)) {
            $error[] = $lang['error_input_detail'];
        } else {
            $row_no = 0;
            foreach($details as $detail) {
                $row_no++;
                if($detail['product_code'] == '') {
                    $error[] = $lang['error_product_code'] . ' for Row ' . $row_no;
                }
                if($detail['product_id'] == '') {
                    $error[] = $lang['error_product'] . ' for Row ' . $row_no;
                }
                if($detail['warehouse_id'] == '') {
                    $error[] = $lang['error_warehouse'] . ' for Row ' . $row_no;
                }
                if($detail['qty'] == '') {
                    $error[] = $lang['error_qty'] . ' for Row ' . $row_no;
                }
                if($detail['unit_id'] == '') {
                    $error[] = $lang['error_unit'] . ' for Row ' . $row_no;
                }
                if($detail['rate'] == '') {
                    $error[] = $lang['error_rate'] . ' for Row ' . $row_no;
                }
                if($detail['amount'] == '') {
                    $error[] = $lang['error_amount'] . ' for Row ' . $row_no;
                }
                if($detail['qty'] > ($detail['order_qty']-$detail['received_qty'])) {
                    $error[] = $lang['error_qty'] . ' for Row ' . $row_no;
                }
            }
        }

        if (!$error) {
            $json = array(
                'success' => true
            );
        } else {
            $json = array(
                'success' => false,
                'error' => implode("\r\n",$error),
                'errors' => $error,
                'post' => $post
            );
        }

        echo json_encode($json);
        exit;
    }


    public function printDocument() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        $stock_transfer_id = $this->request->get['stock_transfer_id'];

        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['stock_transfer'] = $this->load->model('inventory/stock_transfer');
        $this->model['stock_transfer_detail'] = $this->load->model('inventory/stock_transfer_detail');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->model['warehouse'] = $this->load->model('inventory/warehouse');

        $voucher = $this->model['stock_transfer']->getRow(array('stock_transfer_id' => $stock_transfer_id));
        $Warehouse = $this->model['warehouse']->getRow(array('warehouse_id' => $voucher['warehouse_id']));

        $details = $this->model['stock_transfer_detail']->getRows(array('stock_transfer_id' => $stock_transfer_id));



        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Transfer Note');
        $pdf->SetSubject('Transfer Note');

        if($voucher['billty_date'] != '') {
            $voucher['billty_date'] = MySqlDate($voucher['billty_date']);
        } else {
            $voucher['billty_date'] = NULL;
        }
        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            //'report_name' => $lang['heading_title'],
            'report_name' => 'Transfer Note',
            'company_logo' => $session['company_image'],
            'remarks' => $voucher['remarks'],
            'document_identity' => $voucher['document_identity'],
            'dc_no' => $voucher['dc_no'],
            'document_date' => $voucher['document_date'],
            'partner_name' => $voucher['partner_name'],
            'remarks' => $voucher['remarks'],
            'bilty_no' => $voucher['billty_no'],
            'bilty_remarks' => $voucher['billty_remarks'],
            'bilty_date' => $voucher['billty_date'],
            'warehouse_name' => $Warehouse['name'],

        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 46, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 40);

        // add a page
        $pdf->AddPage();
        // set font

        $pdf->SetFont('times', '', 7);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $total_amount = 0;
        $sr=0;
        foreach($details as $detail) {

            $ToWarehouse = $this->model['warehouse']->getRow(array('warehouse_id' => $detail['warehouse_id']));

            $sr++;
            $pdf->ln(8);
            $pdf->Cell(10, 8, $sr, 1, false, 'L', 0, '', 1);
            $pdf->Cell(85, 8, html_entity_decode($detail['product_name']), 1, false, 'C', 0, '', 1);
            $pdf->Cell(30, 8, $ToWarehouse['name'], 1, false, 'C', 0, '', 1);
            $pdf->Cell(15, 8, $detail['unit'], 1, false, 'C', 0, '', 1);
            $pdf->Cell(15, 8, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1);
            $pdf->Cell(15, 8, number_format($detail['rate'],4), 1, false, 'R', 0, '', 1);
            $pdf->Cell(25, 8, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1);

            $total_amount += $detail['amount'];
        }

        $pdf->ln(2);
        $pdf->ln(6);
        $pdf->Cell(140, 6, '', 0, false, 'L');
        $pdf->Cell(30, 6, 'Total Amount', 1, false, 'L');
        $pdf->Cell(25, 6, number_format($total_amount,2), 1, false, 'R');



        //Close and output PDF document
        $pdf->Output('Purchase Order - '.$voucher['document_identity'].'.pdf', 'I');

    }
}
class PDF extends TCPDF {
    public $data = array();

    //Page header
    public function Header() {
        // Logo
        /*
        if($this->data['company_logo'] != '') {
            $image_file = DIR_IMAGE.$this->data['company_logo'];
            //$this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
            //$this->Image($image_file, 10, 5, 150, '', '', '', 'C', false, 300, '', false, false, 1, false, false, false);
            $x = 15;
            $y = 0;
            $w = 180;
            $h = 40;
            //$this->Rect($x, $y, $w, $h, 'F', array(), array(128,255,128));
            $this->Image($image_file, $x, $y, $w, $h, '', '', '', false, 300, '', false, false, 0, 'LM', false, false);
        }
        */
        // Set font

        $this->SetFont('helvetica', 'B', 20);
        $this->Ln(2);
        // Title
        $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);

        $this->SetFont('helvetica', '', 16);
        $this->Cell(0, 8, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(15);


        $this->SetFont('times', 'B', 9);
        $this->Cell(20, 7, 'Voucher No: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // set font
        $this->SetFont('times', '', 9);
        $this->Cell(60, 7, $this->data['document_identity'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // set font
        $this->SetFont('times', 'B', 9);
        $this->Cell(25, 7, 'Voucher Date: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // set font
        $this->SetFont('times', '', 9);
        $this->Cell(30, 7, stdDate($this->data['document_date']), 0, false, 'L', 0, '', 0, false, 'M', 'M');

        $this->SetFont('times', 'B', 9);
        $this->Cell(30, 7, 'From Warehouse :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // set font
        $this->SetFont('times', '', 9);
        $this->Cell(40, 7, $this->data['warehouse_name'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
//            $this->ln(7);
//            $this->SetFont('times', 'B', 9);
//            $this->Cell(30, 7, 'Remarks :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Ln(8);


        $this->SetFont('times', 'B', 9);
        $this->Cell(20, 7, 'Cargo Name: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // set font
        $this->SetFont('times', '', 9);
        $this->Cell(60, 7, $this->data['bilty_remarks'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // set font
        $this->SetFont('times', 'B', 9);
        $this->Cell(25, 7, 'Bilty No: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // set font
        $this->SetFont('times', '', 9);
        $this->Cell(30, 7, $this->data['bilty_no'], 0, false, 'L', 0, '', 0, false, 'M', 'M');

        $this->SetFont('times', 'B', 9);
        $this->Cell(30, 7, 'Bilty Date :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // set font
        $this->SetFont('times', '', 9);
        $this->Cell(40, 7, stdDate($this->data['bilty_date']), 0, false, 'L', 0, '', 0, false, 'M', 'M');

//            $this->ln(7);
//            $this->SetFont('times', 'B', 9);
//            $this->Cell(30, 7, 'Remarks :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // set font
//            $this->SetFont('times', '', 9);
//            $this->SetFillColor(255,255,255);
//            $this->MultiCell(155, 8, $this->data['remarks'], 0, 'L', 1, 2, 30, 42, true);

        $this->ln(6);
        $this->SetFont('times', '', 7);
        $this->SetFillColor(215, 215, 215);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(10, 8, 'S.No.', 1, false, 'C', 1, '', 1);
        $this->Cell(85, 8, 'Item Description', 1, false, 'C', 1, '', 1);
        $this->Cell(30, 8, 'To Warehouse', 1, false, 'C', 1, '', 1);
        $this->Cell(15, 8, 'Unit', 1, false, 'C', 1, '', 1);
        $this->Cell(15, 8, 'Qty', 1, false, 'C', 1, '', 1);
        $this->Cell(15, 8, 'Rate', 1, false, 'C', 1, '', 1);
        $this->Cell(25, 8, 'Amount', 1, false, 'C', 1, '', 1);
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'B', 8);
        // Page number
        //      $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
//        $this->Cell(40, 7, 'Prepared By', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
//        $this->Cell(25, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//        $this->Cell(40, 7, 'Quality Assurance', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
//        $this->Cell(25, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//        $this->Cell(40, 7, 'Approved By', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
////        $this->Cell(10, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
////        $this->Cell(40, 7, 'Vice President Ind.Division', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(5);
        $this->Cell(0, 5, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
?>