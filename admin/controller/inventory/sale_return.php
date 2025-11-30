<?php

class ControllerInventorySaleReturn extends HController {

    protected $document_type_id = 3;

    protected function getAlias() {
        return 'inventory/sale_return';
    }

    protected function getPrimaryKey() {
        return 'sale_return_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {
        $lang = $this->load->language($this->getAlias());
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action', 'document_date', 'document_identity', 'partner_type','partner_name','remarks', 'net_amount', 'created_at', 'check_box');

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
                    'class' => 'fa fa-thumbs-up',
                    'click'=> 'return confirm(\'Are you sure you want to post this item?\');'
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
                $strAction .= '<a '.(isset($action['btn_class'])?'class="'.$action['btn_class'].'"':'').' '.(isset($action['target'])?'target="'.$action['target'].'"':'').' '.' href="' . $action['href'] .'" '. (isset($action['target']) ? 'target="' . $action['target'] . '"' : '') . ' data-toggle="tooltip" title="' . $action['text'] . '" ' . (isset($action['click']) ? 'onClick="' . $action['click'] . '"' : '') . '>';
                if (isset($action['class'])) {
                    $strAction .= '<span class="' . $action['class'] . '"></span>';
                } else {
                    $strAction .= $action['text'];
                }
                $strAction .= '</a>&nbsp;';
            }
            $action_update = $this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL');
            for ($i = 0; $i < count($aColumns); $i++) {
                if ($aColumns[$i] == 'action') {
                     $row[] = $strAction;
                } elseif ($aColumns[$i] == 'document_identity') {
                    $row[] = '<a href="'.$action_update.'">'.$aRow['document_identity'].'</a>';

                }elseif ($aColumns[$i] == 'document_date') {
                    $row[] = stdDate($aRow['document_date']);
                } elseif ($aColumns[$i] == 'created_at') {
                    $row[] = stdDateTime($aRow['created_at']);
                } elseif ($aColumns[$i] == 'check_box') {
                    if($aRow['is_post']==0){
                    $row[] = '<input type="checkbox" name="selected[]" value="' . $aRow[$this->getPrimaryKey()] . '" />';
                    }else{
                        $row[] = '';
                    }
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
        $this->model['stock'] = $this->load->model('common/stock_ledger');
        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));
        $this->data['arrWarehouses'] = json_encode($this->data['warehouses']);

        $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');
        $this->data['customer_units'] = $this->model['customer_unit']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";

        $this->data['partner_types'] = $this->session->data['partner_types'];

        $this->data['document_date'] = stdDate();
        if (isset($this->request->get['sale_return_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $result = $this->model[$this->getAlias()]->getRow(array('sale_return_id' => $this->request->get[$this->getPrimaryKey()]));
           // d($result,true);
            foreach ($result as $field => $value) {
                if ($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }
            $this->model['sale_return_detail'] = $this->load->model('inventory/sale_return_detail');
            $details = $this->model['sale_return_detail']->getRows(array('sale_return_id' => $this->request->get['sale_return_id']), array('sort_order DESC'));
            $this->data['sale_return_details'] = $details;
            foreach($details as $row_no => $row) {
                $filter = array(
                    'company_id' => $this->session->data['company_id'],
                    'company_branch_id' => $this->session->data['company_branch_id'],
                    'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                    'product_id' => $row['product_id'],
                );
                $stock = $this->model['stock']->getStock($filter);
                $this->data['sale_return_details'][$row_no]['stock_qty'] = $stock['stock_qty'];
            }
        //    d($details,true);


        }

        $this->data['partner_type_id'] = 2;

        $this->data['href_get_partner_json'] = $this->url->link($this->getAlias() . '/getPartnerJson', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_partner'] = $this->url->link($this->getAlias() . '/getPartner', 'token=' . $this->session->data['token']);
        $this->data['url_validate_stock'] = $this->url->link('common/function/getWarehouseStock', 'token=' . $this->session->data['token']);

        $this->data['href_get_ref_document_no'] = $this->url->link($this->getAlias() . '/getReferenceDocumentNos', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_ref_document'] = $this->url->link($this->getAlias() . '/getReferenceDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print'] = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['strValidation'] = "{
            'rules': {
                'document_date': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'partner_id': {'required': true},
                'net_amount': {'required': true},
            },
            messages: {
            document_date:{
                remote: 'Invalid Date'
            }}
        }";

        $this->response->setOutput($this->render());
    }

    public function getPartnerJson() {
        $search = $this->request->post['q'];
        $page = $this->request->post['page'];

        $partner_type_id = '';
        if( isset($this->request->get['partner_type_id']) )
        {
            $partner_type_id = $this->request->get['partner_type_id'];
        }

        $this->model['partner'] = $this->load->model('common/partner');
        $rows = $this->model['partner']->getPartnerJson($search, $page, 25, ['partner_type_id' => $partner_type_id]);

        echo json_encode($rows);
    }

    public function getPartner() {
        $partner_type_id = $this->request->post['partner_type_id'];
        $partner_id = $this->request->post['partner_id'];

        $this->model['partner'] = $this->load->model('common/partner');
        $partners = $this->model['partner']->getRows(array('company_id' => $this->session->data['company_id'], 'partner_type_id' => $partner_type_id));


         $html = '<option value="">&nbsp;</option>';
        foreach($partners as $partner) {
            $html .= '<option value="'.$partner['partner_id'].'">'.$partner['name'].'</option>';
        }

        $json = array(
            'success' => true,
            'html' => $html
        );

        echo json_encode($json);
    }

    public function ajaxValidateForm() {
        $post  = $this->request->post;
        $ID = $this->request->get;
        $lang = $this->load->language($this->getAlias());
        $error = array();

        if($post['invoice_date'] == '') {
            $error[] = $lang['error_invoice_date'];
        }

        if($post['customer_id'] == '') {
            $error[] = $lang['error_customer'];
        }

        if($post['document_currency_id'] == '') {
            $error[] = $lang['error_document_currency'];
        }
        if($post['conversion_rate'] == '' || $post['conversion_rate'] <= 0 ) {
            $error[] = $lang['error_conversion_rate'];
        }


        $details = $post['sale_return_details'];
        $this->model['company'] = $this->load->model('setup/company');
        $company =  $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        if($company['out_of_stock'] == 1)
        {

            $this->model['product'] = $this->load->model('setup/product');
            $arrProducts = $this->model['product']->getArrays('product_id','name', array('company_id' => $this->session->data['company_id']));
            $filter = array(
                'company_id' => $this->session->data['company_id'],
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'company_branch_id' => $this->session->data['company_branch_id'],
            );


            $stocks =array();
            foreach($details as $stock){
                if(empty($stock['ref_document_type_id']) || $stock['ref_document_type_id'] != 16) {
                    if(isset($stocks[$stock['warehouse_id']][$stock['product_id']])) {
                        $stocks[$stock['warehouse_id']][$stock['product_id']] += $stock['qty'];
                    } else {
                        $stocks[$stock['warehouse_id']][$stock['product_id']] = $stock['qty'];
                    }
                }
            }
            //$error[] =  $lang['error_stock'] ;
            foreach($stocks as  $warehouse_id => $rows)
            {
                foreach($rows as $product_id => $qty) {
                    $filter ['product_id'] = $product_id;
                    $filter ['warehouse_id'] = $warehouse_id;
                    $filter ['document_id'] = $ID['sale_return_id'];

                    $product_stock = $this->model['product']->getProductStock($filter);

                    if($product_stock['qty'] < $qty)
                    {
                        $product =  $arrProducts[$product_id];
                        $error[] =   ' Product ' . $product .' , Stock Qty= ' . $product_stock['qty'] . ' , Qty= '. $qty;
                    }
                }
            }
        }
//        d(array($filter,$product_stock),true);

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
                'errors' => $error
            );
        }

        echo json_encode($json);
        exit;
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
        $data['base_net_amount'] = $data['net_amount'] * $data['conversion_rate'];
        $sale_return_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $data['document_id'] = $sale_return_id;

        $this->model['document'] = $this->load->model('common/document');
        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $sale_return_id,
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['net_amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['base_net_amount'],
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['product']= $this->load->model('inventory/product');
        $this->model['setting']= $this->load->model('common/setting');

        $partner = $this->model['partner']->getRow(array('partner_type_id' => $data['partner_type_id'], 'partner_id' => $data['partner_id']));
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        // d(array($data, $partner, $company), true);

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'cash_account_id',
        ));
        $cash_account_id = $setting['value'];

        // $cash_account_id = $partner['cash_account_id'];
        $outstanding_account_id = $partner['outstanding_account_id'];


        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'sale_discount_account_id',
        ));
        $sale_discount_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'sale_tax_account_id',
        ));
        $sale_tax_account_id = $setting['value'];
        // d($sale_tax_account_id,true);
        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $outstanding_account_id,
            'document_debit' => 0,
            'document_credit' => $data['balance_amount'],
            'debit' => 0,
            'credit' => ($data['balance_amount'] * $data['conversion_rate']),
        );

        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $sale_discount_account_id,
            'document_debit' => 0,
            'document_credit' => $data['deduction_amount'],
            'debit' => 0,
            'credit' => ($data['deduction_amount'] * $data['conversion_rate'])
        );

        $this->model['sale_return_detail'] = $this->load->model('inventory/sale_return_detail');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');

//        $this->model['mapping_account'] = $this->load->model('gl/mapping_coa');
//        $account = $this->model['mapping_account']->getRow(array('company_id' => $this->session->data['company_id'], 'mapping_type_code' => 'GRIR'));
//        $gr_ir_account_id = $account['coa_account_id'];
//d($data,true);
        foreach ($data['sale_return_details'] as $sort_order => $detail) {
            $detail['sale_return_id'] = $sale_return_id;
            $detail['sort_order'] = $sort_order;
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_total'] = $detail['total_amount'] * $data['conversion_rate'];
            $sale_return_detail_id = $this->model['sale_return_detail']->add($this->getAlias(), $detail);

            $stock_ledger = array(
                'company_id' => $detail['company_id'],
                'company_branch_id' => $detail['company_branch_id'],
                'fiscal_year_id' => $detail['fiscal_year_id'],
                'document_type_id' => $data['document_type_id'],
                'document_id' => $data['document_id'],
                'document_identity' => $data['document_identity'],
                'document_date' => $data['document_date'],
                'document_detail_id' => $sale_return_detail_id,
                'warehouse_id' => $detail['warehouse_id'],
                'product_id' => $detail['product_id'],
                'document_unit_id' => $detail['unit_id'],
                'document_qty' => ($detail['qty']),
                'unit_conversion' => 1,
                'base_unit_id' => $detail['unit_id'],
                'base_qty' => ($detail['qty']),
                'document_currency_id' => $detail['document_currency_id'],
                'document_rate' => $detail['cog_rate'],
                'document_amount' => ($detail['cog_amount']),
                'currency_conversion' => $detail['conversion_rate'],
                'base_currency_id' => $detail['base_currency_id'],
                'base_rate' => ($detail['cog_rate'] * $detail['conversion_rate']),
                'base_amount' => ($detail['cog_amount'] * $detail['conversion_rate']),
            );
            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $stock_ledger);

            $product = $this->model['product']->getRow(array('product_id' => $detail['product_id']));
            if($detail['ref_document_type_id'] == 16) {
                $gl_data[] = array(
                    'document_detail_id' => $sale_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    //'coa_id' => $gr_ir_account_id,
                    'coa_id' => $product['inventory_account_id'],
                    'document_debit' => $detail['cog_amount'],
                    'document_credit' => 0,
                    'debit' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'credit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['cog_amount'],
                    'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                );
                $gl_data[] = array(
                    'document_detail_id' => $sale_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $product['cogs_account_id'],
                    'document_debit' => $detail['cog_amount'],
                    'document_credit' => 0,
                    'credit' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'debit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['cog_amount'],
                    'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                );
            } else {
                $gl_data[] = array(
                    'document_detail_id' => $sale_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $product['inventory_account_id'],
                    'document_debit' => $detail['cog_amount'],
                    'document_credit' => 0,
                    'debit' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'credit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['cog_amount'],
                    'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                );
                $gl_data[] = array(
                    'document_detail_id' => $sale_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $product['cogs_account_id'],
                    'document_credit' => $detail['cog_amount'],
                    'document_debit' => 0,
                    'credit' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'debit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['cog_amount'],
                    'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                );
            }

            if( floatval($detail['amount']) > 0 ){

                $gl_data[] = array(
                    'document_detail_id' => $sale_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    //'coa_id' => $gr_ir_account_id,
                    'coa_id' => $product['revenue_account_id'],
                    'document_debit' => $detail['amount'],
                    'document_credit' => 0,
                    'debit' => ($detail['amount'] * $data['conversion_rate']),
                    'credit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['amount'],
                    'amount' => ($detail['amount'] * $data['conversion_rate']),
                );
                
            }

            if( floatval($detail['discount_amount']) > 0 ){

                $gl_data[] = array(
                    'document_detail_id' => $sale_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $sale_discount_account_id,
                    'document_debit' => 0,
                    'document_credit' => $detail['discount_amount'],
                    'debit' => 0,
                    'credit' => ($detail['discount_amount'] * $data['conversion_rate']),
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['discount_amount'],
                    'amount' => ($detail['discount_amount'] * $data['conversion_rate']),
                );

            }

            if( floatval($detail['tax_amount']) > 0 ){

                $gl_data[] = array(
                    'document_detail_id' => $sale_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $sale_tax_account_id,
                    'document_debit' => $detail['tax_amount'],
                    'document_credit' => 0,
                    'debit' => ($detail['tax_amount'] * $data['conversion_rate']),
                    'credit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['tax_amount'],
                    'amount' => ($detail['tax_amount'] * $data['conversion_rate']),
                );

            }
        }

        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $outstanding_account_id,
            'document_credit' => 0,
            'document_debit' => $data['cash_returned'],
            'debit' => 0,
            'credit' => ($data['cash_received'] * $data['conversion_rate']),
        );

        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $cash_account_id,
            'document_debit' => 0,
            'document_credit' => $data['cash_returned'],
            'debit' => 0,
            'credit' => ($data['cash_returned'] * $data['conversion_rate']),
        );
        $this->model['ledger'] = $this->load->model('gl/ledger');
        // d($gl_data,true);
        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $sale_return_id;
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
        return $sale_return_id;
    }

    protected function updateData($primary_key, $data) {
        //d($data,true);
        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['document_date'] = MySqlDate($data['document_date']);
        $data['base_amount'] = $data['net_amount'] * $data['conversion_rate'];
        $sale_return_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        $data['document_id'] = $sale_return_id;

        $this->model['sale_return_detail'] = $this->load->model('inventory/sale_return_detail');
        $this->model['sale_return_detail']->deleteBulk($this->getAlias(), array('sale_return_id' => $sale_return_id));

        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $sale_return_id,
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['net_amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['base_amount'],
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['product']= $this->load->model('inventory/product');

        $partner = $this->model['partner']->getRow(array('partner_type_id' => $data['partner_type_id'], 'partner_id' => $data['partner_id']));
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $this->model['setting']= $this->load->model('common/setting');
        //d(array($data, $partner, $company), true);



        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'sale_discount_account_id',
        ));
        $sale_discount_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'sale_tax_account_id',
        ));
        $sale_tax_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'cash_account_id',
        ));
        $cash_account_id = $setting['value'];


        // $cash_account_id = $partner['cash_account_id'];
        $outstanding_account_id = $partner['outstanding_account_id'];
//        $sale_tax_account_id = $company['sale_tax_account_id'];
//        $sale_discount_account_id = $company['sale_discount_account_id'];

        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $outstanding_account_id,
            'document_debit' => 0,
            'document_credit' => $data['balance_amount'],
            'debit' => 0,
            'credit' => ($data['balance_amount'] * $data['conversion_rate']),
        );

        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $sale_discount_account_id,
            'document_debit' => 0,
            'document_credit' => $data['deduction_amount'],
            'debit' => 0,
            'credit' => ($data['deduction_amount'] * $data['conversion_rate'])
        );

        $this->model['sale_return_detail'] = $this->load->model('inventory/sale_return_detail');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');

//        $this->model['mapping_account'] = $this->load->model('gl/mapping_coa');
//        $account = $this->model['mapping_account']->getRow(array('company_id' => $this->session->data['company_id'], 'mapping_type_code' => 'GRIR'));
//        $gr_ir_account_id = $account['coa_account_id'];

        foreach ($data['sale_return_details'] as $sort_order => $detail) {
            $detail['sale_return_id'] = $sale_return_id;
            $detail['sort_order'] = $sort_order;
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_total'] = $detail['total_amount'] * $data['conversion_rate'];
            $sale_return_detail_id = $this->model['sale_return_detail']->add($this->getAlias(), $detail);

            $stock_ledger = array(
                'company_id' => $detail['company_id'],
                'company_branch_id' => $detail['company_branch_id'],
                'fiscal_year_id' => $detail['fiscal_year_id'],
                'document_type_id' => $data['document_type_id'],
                'document_id' => $data['document_id'],
                'document_identity' => $data['document_identity'],
                'document_date' => $data['document_date'],
                'document_detail_id' => $sale_return_detail_id,
                'warehouse_id' => $detail['warehouse_id'],
                'product_id' => $detail['product_id'],
                'document_unit_id' => $detail['unit_id'],
                'document_qty' => ($detail['qty']),
                'unit_conversion' => 1,
                'base_unit_id' => $detail['unit_id'],
                'base_qty' => ($detail['qty']),
                'document_currency_id' => $detail['document_currency_id'],
                'document_rate' => $detail['cog_rate'],
                'document_amount' => ($detail['cog_amount']),
                'currency_conversion' => $detail['conversion_rate'],
                'base_currency_id' => $detail['base_currency_id'],
                'base_rate' => ($detail['cog_rate'] * $detail['conversion_rate']),
                'base_amount' => ($detail['cog_amount'] * $detail['conversion_rate']),
            );
//            d($stock_ledger,true);
            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $stock_ledger);

            $product = $this->model['product']->getRow(array('product_id' => $detail['product_id']));
            if($detail['ref_document_type_id'] == 16) {
                $gl_data[] = array(
                    'document_detail_id' => $sale_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    //'coa_id' => $gr_ir_account_id,
                    'coa_id' => $product['inventory_account_id'],
                    'document_debit' => $detail['cog_amount'],
                    'document_credit' => 0,
                    'debit' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'credit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['cog_amount'],
                    'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                );
                $gl_data[] = array(
                    'document_detail_id' => $sale_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $product['cogs_account_id'],
                    'document_debit' => $detail['cog_amount'],
                    'document_credit' => 0,
                    'credit' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'debit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['cog_amount'],
                    'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                );
            } else {
                $gl_data[] = array(
                    'document_detail_id' => $sale_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $product['inventory_account_id'],
                    'document_debit' => $detail['cog_amount'],
                    'document_credit' => 0,
                    'debit' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'credit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['cog_amount'],
                    'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                );
                $gl_data[] = array(
                    'document_detail_id' => $sale_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $product['cogs_account_id'],
                    'document_credit' => $detail['cog_amount'],
                    'document_debit' => 0,
                    'credit' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'debit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['cog_amount'],
                    'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                );
            }

            if( floatval($detail['amount']) > 0 ){

                $gl_data[] = array(
                    'document_detail_id' => $sale_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    //'coa_id' => $gr_ir_account_id,
                    'coa_id' => $product['revenue_account_id'],
                    'document_debit' => $detail['amount'],
                    'document_credit' => 0,
                    'debit' => ($detail['amount'] * $data['conversion_rate']),
                    'credit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['amount'],
                    'amount' => ($detail['amount'] * $data['conversion_rate']),
                );

            }

            if( floatval($detail['discount_amount']) > 0 ){

                $gl_data[] = array(
                    'document_detail_id' => $sale_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $sale_discount_account_id,
                    'document_debit' => 0,
                    'document_credit' => $detail['discount_amount'],
                    'debit' => 0,
                    'credit' => ($detail['discount_amount'] * $data['conversion_rate']),
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['discount_amount'],
                    'amount' => ($detail['discount_amount'] * $data['conversion_rate']),
                );

            }

            if( floatval($detail['tax_amount']) > 0 ){

                $gl_data[] = array(
                    'document_detail_id' => $sale_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $sale_tax_account_id,
                    'document_debit' => $detail['tax_amount'],
                    'document_credit' => 0,
                    'debit' => ($detail['tax_amount'] * $data['conversion_rate']),
                    'credit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['tax_amount'],
                    'amount' => ($detail['tax_amount'] * $data['conversion_rate']),
                );

            }
        }

        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $outstanding_account_id,
            'document_credit' => 0,
            'document_debit' => $data['cash_returned'],
            'debit' => 0,
            'credit' => ($data['cash_received'] * $data['conversion_rate']),
        );

        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $cash_account_id,
            'document_debit' => 0,
            'document_credit' => $data['cash_returned'],
            'debit' => 0,
            'credit' => ($data['cash_returned'] * $data['conversion_rate']),
        );
        $this->model['ledger'] = $this->load->model('gl/ledger');
        // d($gl_data,true);
        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $sale_return_id;
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
        return $sale_return_id;
    }

    protected function deleteData($primary_key) {
        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);

        $this->model['sale_return_detail'] = $this->load->model('inventory/sale_return_detail');
        $this->model['sale_return_detail']->deleteBulk($this->getAlias(), array('sale_return_id' => $primary_key));

        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

    }

    public function getReferenceDocumentNos() {
        $sale_return_id = $this->request->get['sale_return_id'];
        $post = $this->request->post;

//        d($post,true);
        $this->model['sale_invoice'] = $this->load->model('inventory/sale_invoice');
        $this->model['sale_tax_invoice'] = $this->load->model('inventory/sale_tax_invoice');

//        $this->model['document'] = $this->load->model('common/document');

        $where = "company_id=" . $this->session->data['company_id'];
        $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
        $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
        $where .= " AND partner_type_id='" . $post['partner_type_id'] . "'";
        $where .= " AND partner_id='" . $post['partner_id'] . "'";
//        $where .= " AND document_type_id='" . $post['ref_document_type_id'] . "'";
        //$where .= " AND document_currency_id='" . $post['document_currency_id'] . "'";
        //$where .= " AND is_post=1";

        if($post['ref_document_type_id'] == "2")
        {
            $invoices = $this->model['sale_invoice']->getRows($where,array('document_identity asc'));
        }
        else{
            $invoices = $this->model['sale_tax_invoice']->getRows($where,array('document_identity asc'));
        }

        $html = "";
        $html .= '<option value="">&nbsp;</option>';
        foreach($invoices as $invoice) {
            $html .= '<option value="'.$invoice['document_identity'].'">'.$invoice['document_identity']. '</option>';
        }

        //d($goods_received,true);
        $json = array(
            'success' => true,
            'sale_return_id' => $sale_return_id,
            'post' => $post,
            'where' => $where,
            'orders' => $invoices,
            'html' => $html
        );

        echo json_encode($json);
    }

    public function getReferenceDocument() {
        $sale_return_id = $this->request->get['sale_return_id'];
        $post = $this->request->post;

//        if($post['ref_document_type_id'] == 2) {
            //Sale Invoice

            $this->model['sale_invoice_detail'] = $this->load->model('inventory/sale_invoice_detail');
            $this->model['sale_tax_invoice_detail'] = $this->load->model('inventory/sale_tax_invoice_detail');

            $where = "company_id=" . $this->session->data['company_id'];
            $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
            $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
            $where .= " AND partner_type_id='" . $post['partner_type_id'] . "'";
            $where .= " AND partner_id='" . $post['partner_id'] . "'";
            $where .= " AND document_identity='" . $post['ref_document_identity'] . "'";
//            $where .= " AND document_currency_id='" . $post['document_currency_id'] . "'";
//            $where .= " AND is_post=1";


            if($post['ref_document_type_id'] == "2")
            {
                $rows = $this->model['sale_invoice_detail']->getRows($where);
            }
            else{
                $rows = $this->model['sale_tax_invoice_detail']->getRows($where);
            }


            $html = '';
            $details = array();
            $this->model['product'] = $this->load->model('inventory/product');
            $this->model['stock'] = $this->load->model('common/stock_ledger');
            foreach($rows as $row_no => $row) {
                $filter = array(
                    'company_id' => $this->session->data['company_id'],
                    'company_branch_id' => $this->session->data['company_branch_id'],
                    'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                    'product_id' => $row['product_id'],
                );
                $stock = $this->model['stock']->getStock($filter);
                $product = $this->model['product']->getRow(array('product_id' => $row['product_id']));
                $href = $this->url->link('inventory/sale_invoice/update', 'token=' . $this->session->data['token'] . '&sale_invoice_id=' . $row['sale_invoice_id']);
                $details[$row_no] = $row;
                $details[$row_no]['href'] = $href;
                $details[$row_no]['description'] = $product['name'];
                $details[$row_no]['rate'] = $row['rate'];
                $details[$row_no]['amount'] = ($row['qty'] * $row['rate']);
                $details[$row_no]['stock_qty'] = $stock['stock_qty'];
            }
//        }
//        elseif($post['ref_document_type_id'] == 16) {
//            //Delivery Challan
//            $this->model['delivery_challan_detail'] = $this->load->model('inventory/delivery_challan_detail');
//            $where = "company_id=" . $this->session->data['company_id'];
//            $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
//            $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
//            $where .= " AND partner_type_id='" . $post['partner_type_id'] . "'";
//            $where .= " AND partner_id='" . $post['partner_id'] . "'";
//            $where .= " AND document_identity='" . $post['ref_document_identity'] . "'";
////            $where .= " AND document_currency_id='" . $post['document_currency_id'] . "'";
////            $where .= " AND is_post=1";
//
//            $rows = $this->model['delivery_challan_detail']->getRows($where);
//            $html = '';
//            $details = array();
//            $this->model['product'] = $this->load->model('setup/product');
//            $this->model['stock'] = $this->load->model('common/stock_ledger');
//            foreach($rows as $row_no => $row) {
//                $filter = array(
//                    'company_id' => $this->session->data['company_id'],
//                    'company_branch_id' => $this->session->data['company_branch_id'],
//                    'fiscal_year_id' => $this->session->data['fiscal_year_id'],
//                    'product_id' => $row['product_id'],
//                );
//                $stock = $this->model['stock']->getStock($filter);
//                $product = $this->model['product']->getRow(array('product_id' => $row['product_id']));
//                $href = $this->url->link('inventory/delivery_challan/update', 'token=' . $this->session->data['token'] . '&delivery_challan_id=' . $row['delivery_challan_id']);
//                $details[$row_no] = $row;
//                $details[$row_no]['href'] = $href;
//                $details[$row_no]['rate'] = $product['sale_price'];
//                $details[$row_no]['amount'] = ($row['qty'] * $product['sale_price']);
//                $details[$row_no]['stock_qty'] = $stock['stock_qty'];
//            }
//        }

        $json = array(
            'success' => true,
            'sale_return_id' => $sale_return_id,
            'post' => $post,
            'where' => $where,
            'details' => $details);
        echo json_encode($json);
    }

    public function printDocument() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        $sale_return_id = $this->request->get['sale_return_id'];
        $with_previous_balance = isset($this->request->get['with_previous_balance'])?1:0;

        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['sale_return'] = $this->load->model('inventory/sale_return');
        $this->model['sale_return_detail'] = $this->load->model('inventory/sale_return_detail');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');

        $invoice = $this->model['sale_return']->getRow(array('sale_return_id' => $sale_return_id));
        $details = $this->model['sale_return_detail']->getRows(array('sale_return_id' => $sale_return_id),array('sort_order asc'));
        $partner = $this->model['partner']->getRow(array('partner_id' => $invoice['partner_id']));
        // $outstanding = $this->model['partner']->getOutstanding("l.`partner_id` = '".$invoice['partner_id']."' AND l.`created_at` < '".$invoice['created_at']."'");
        //d(array($sale_invoice_id, $invoice, $details), true);
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');
        $CustomerUnit = $this->model['customer_unit']->getRow(array('customer_unit_id' => $invoice['customer_unit_id']));


        $this->model['party_ledger'] = $this->load->model('report/party_ledger');
    //            $where = "l.company_id = '".$this->session->data['company_id']."'";
    //            $where .= " AND l.company_branch_id = '".$this->session->data['company_branch_id']."'";
    //            $where .= " AND l.fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
    //            $where .= " AND l.document_date <= '".MySqlDate($invoice['document'])."'";
    //            $where .= " AND l.partner_id = '".$invoice['partner_id']."'";
    //
    //            $outstanding = $this->model['party_ledger']->getOutstanding($where);
    //
    //        $next_date= date('d-m-Y', strtotime(MySqlDate($invoice['document']). ' - 90 days'));
    //
    //        d($next_date,true);

    $this->model['setting'] = $this->load->model('common/setting');
    $setting = $this->model['setting']->getRow(array(
        'company_id' => $this->session->data['company_id'],
        'company_branch_id' => $this->session->data['company_branch_id'],
        'fiscal_year_id' => $this->session->data['fiscal_year_id'],
        'module' => 'general',
        'field' => 'company_logo',
    ));
    $company_logo = $setting['value'];

    $setting = $this->model['setting']->getRow(array(
        'company_id' => $this->session->data['company_id'],
        'company_branch_id' => $this->session->data['company_branch_id'],
        'fiscal_year_id' => $this->session->data['fiscal_year_id'],
        'module' => 'general',
        'field' => 'company_header_print',
    ));
    $company_header_print = $setting['value'];

    $setting = $this->model['setting']->getRow(array(
        'company_id' => $this->session->data['company_id'],
        'company_branch_id' => $this->session->data['company_branch_id'],
        'fiscal_year_id' => $this->session->data['fiscal_year_id'],
        'module' => 'general',
        'field' => 'company_footer_print',
    ));
    $company_footer_print = $setting['value'];

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Sale Return');
        $pdf->SetSubject('Sale Return');

        //$pdf->footer = HTTP_IMAGE.'footer.jpeg';
        //Set Header

        $pdf->InvoiceCheck = "Bill";
        $pdf->data = array(
            'company_name' => $branch['name'],
            //'report_name' => $lang['heading_title'],
            'report_name' => 'Sale Return',
            'company_logo' => $session['company_image'],
           // 'header_image' => HTTP_IMAGE.'header.jpg',
           // 'footer_image' => HTTP_IMAGE.'footer.jpg'
           'company_header_print' => $company_header_print,
           'company_footer_print' => $company_footer_print,

        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(7, 40, 7);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
//        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetAutoPageBreak(TRUE, 65);


        // add a page
        $pdf->AddPage();
        // set font
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(10, 7, 'M/S : ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // set font
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(70, 7, html_entity_decode($invoice['partner_name']), 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(30, 7, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7, 'Document No : ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(60, 7, $invoice['document_identity'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(8);
        $pdf->Cell(10, 7, 'Date: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(70, 7, stdDate($invoice['document_date']), 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(30, 7, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7, 'Unit : ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(60, 7, $CustomerUnit['customer_unit'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->ln(10);
        $pdf->Cell(20, 7, 'Address: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(165, 7, $partner['address'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->ln(10);

        // set font
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(20, 7, 'Qty', 1, false, 'C', 1, '', 1);
        $pdf->Cell(110, 7, 'Particulars', 1, false, 'C', 1, '', 1);
        $pdf->Cell(30, 7, 'Rate', 1, false, 'C', 1, '', 1);
        $pdf->Cell(30, 7, 'Amount', 1, false, 'C', 1, '', 1);
        
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $total_amount = 0;
        $pdf->ln(7);
        
        $pdf->SetFont('helvetica', '', 7);
        foreach($details as $detail) {

            $productName = $detail['product_name'];

            if(multili_var_length_check(array($productName), 95)) {   
                $pdf->Cell(20, 7, number_format($detail['qty'],2), 1, false, 'C', 0, '', 1);
                $pdf->Cell(110, 7, html_entity_decode($detail['product_name']), 1, false, 'L', 0, '', 1);
                $pdf->Cell(30, 7, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1);
                $pdf->Cell(30, 7, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1);
                $pdf->ln(7);
            } else {

                $arrProduct = splitString($productName, 95);
                $length = max_array_index_count($arrProduct);
                
                for($index=0; $index <= ($length-1); $index++){
                 
                    if($index==0){

                        $pdf->Cell(20, 4, number_format($detail['qty'],2), 'TLR', false, 'C', 0, '', 1);
                        $pdf->Cell(110, 4, html_entity_decode($arrProduct[$index]), 'TLR', false, 'L', 0, '', 1);
                        $pdf->Cell(30, 4, number_format($detail['rate'],2), 'TLR', false, 'R', 0, '', 1);
                        $pdf->Cell(30, 4, number_format($detail['amount'],2), 'TLR', false, 'R', 0, '', 1);
                    
                    } else if($index<($length-1)){

                        $pdf->Cell(20, 4, '', 'LR', false, 'C', 0, '', 1);
                        $pdf->Cell(110, 4, html_entity_decode($arrProduct[$index]), 'LR', false, 'L', 0, '', 1);
                        $pdf->Cell(30, 4, '', 'LR', false, 'R', 0, '', 1);
                        $pdf->Cell(30, 4, '', 'LR', false, 'R', 0, '', 1);
                    
                    } else {
                    
                        $pdf->Cell(20, 4, '', 'BLR', false, 'C', 0, '', 1);
                        $pdf->Cell(110, 4, html_entity_decode($arrProduct[$index]), 'BLR', false, 'L', 0, '', 1);
                        $pdf->Cell(30, 4, '', 'BLR', false, 'R', 0, '', 1);
                        $pdf->Cell(30, 4, '', 'BLR', false, 'R', 0, '', 1);

                    }
                 
                    $pdf->ln(4);
                
                }

            }

            $total_amount += $detail['amount'];
        }

        $pdf->Ln(-2);
        $pdf->Ln(1);

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetXY($x, $y);

        for ($i = $y; $i <= 210; $i++) {

            $pdf->Ln(1);
            $pdf->Cell(20, 8, '',  'LR', false, 'R', 0, '', 1);
            $pdf->Cell(110, 8, '', 'LR', false, 'L', 0, '', 1);
            $pdf->Cell(30, 8, '',  'LR', false, 'R', 0, '', 1);
            $pdf->Cell(30, 8, '',  'LR', false, 'R', 0, '', 1);
            $y =$i;
        }
        $pdf->Ln(-4);
        $pdf->Ln(8);
        $pdf->Cell(190, 8, '', 'B', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setXY($x,$y);
        $pdf->Ln(9);


        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(130, 7, 'In words: ' . Number2Words(round($total_amount,2)). ' only', 0, false, 'L');
        $pdf->Cell(30, 7, $lang['total_amount'].': ', 1, false, 'L');
        $pdf->Cell(30, 7, number_format($total_amount,2), 1, false, 'R');


        //Close and output PDF document
        $pdf->Output('Sale Return - '.$invoice['document_identity'].'.pdf', 'I');

    }
}

class PDF extends TCPDF {
    public $data = array();
    Public $InvoiceCheck;
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

        if($this->data['company_header_print'] != '') {
            $image_file = DIR_IMAGE.$this->data['company_header_print'];
            // d($image_file, true);
            // $this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
            $this->Image($image_file, 5, 5, 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }else
        {
            // Set font
            $this->SetFont('helvetica', 'B', 20);
            $this->Ln(2);
            //Title
            $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        }

        if($this->InvoiceCheck == "Bill")
        {
//            $txt="TEL: 92-21-32401236 92-21-32415063
//        FAX: 92-21-32428040";
//
//            $this->Ln(6);
//            $this->SetFont('helvetica', 'B', 30);
//            $this->Ln(2);
//            // Title
//            $this->Cell(190, 12, html_entity_decode($this->data['company_name']), 0, false, 'C', 0, '', 0, false, 'M', 'M');
//
//            $this->SetFont('helvetica', 'B', 10);
//            $this->SetFillColor(255,255,255);
//            $this->MultiCell(55, 5, $txt, 0, 'R', 1, 2, 150, 7, true);
//
//            $this->Ln(5);
//            $this->SetFont('helvetica', 'B', 10);
//            $this->Cell(0, 5, "IMOPERTERS, EXPORTERS, AGENTS, MANUFACTURER'S & REPRESNTATIVE", 0, false, 'C', 0, '', 0, false, 'M', 'M');
//            $this->Ln(5);
//            $this->Cell(0, 5, "Deals in : Hardware Tools, Safety Equipments & General Order Suppliers.", 0, false, 'C', 0, '', 0, false, 'M', 'M');
//            $this->Ln(5);
//            $this->Cell(0, 5, "21,Adnan Centre Jeswani Street, off. Aiwan-E-Tijarat Road, Karachi - 74000, Pakistan", 0, false, 'C', 0, '', 0, false, 'M', 'M');
//            $this->Ln(5);
//            $this->Cell(0, 5, "Email : info@hacsons.com , Web: www.hacsons.com", 0, false, 'C', 0, '', 0, false, 'M', 'M');
//            $this->Ln(10);

            //$this->Image($this->data['header_image'], 0, 5, 205, "", "JPG", "", "T", false, 300, "", false, false, 0, false, false, false);
            // $this->Ln(10);
            // $this->SetFont('times', 'B,I', 26);
            // $this->Cell(0, 10, html_entity_decode($this->data['company_name']), 0, false, 'C', 0, '', 0, false, 'M', 'M');

            $this->Ln(18);
            $this->SetFont('helvetica', 'B', 12);
            $this->Cell(0, 10, "Sale Return", 0, false, 'C', 0, '', 0, false, 'M', 'M');
//            $this->Ln(5);


        }
        if($this->InvoiceCheck == "LocalBill")
        {
            $txt="TEL: 92-21-32401236/92-21-32415063 FAX: 92-21-32428040
Email : info@hacsons.com";

            $address="Shop # 21,Adnan Centre Jeswani Street, off. Aiwan-E-Tijarat Road,
Karachi - 74000";

            $this->Ln(6);
            $this->SetFont('helvetica', 'B', 30);
            $this->Ln(2);
            // Title
            $this->Cell(190, 12, html_entity_decode($this->data['company_name']), 0, false, 'C', 0, '', 0, false, 'M', 'M');

            $this->SetFont('helvetica', 'B', 8);
            $this->SetFillColor(255,255,255);
            $this->MultiCell(51, 5, $txt, 0, 'L', 1, 2, 155, 20, true);
            $this->MultiCell(50, 5, $address, 0, 'L', 1, 2, 15, 20, true);

            $this->Ln(10);

        }


    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-40);
        // Set font
        $this->SetFont('times', 'B,I', 10);
        $this->Cell(100, 5, "Goods once sold can not be taken back of exchanged", 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->SetFont('times', 'B', 12);
        $this->Cell(80, 5, 'For '.html_entity_decode($this->data['company_name']), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $this->Ln(5);

        $this->SetY(-25);
        $y = $this->GetY();

        if($this->data['company_footer_print'] != '') {
            $image_file = DIR_IMAGE.$this->data['company_footer_print'];
            // $this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
            $this->Image($image_file, 5, ($y-10), 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        /*if($this->InvoiceCheck == "Bill")
        {
            $this->Image($this->data['footer_image'], 0, 250, 205, "", "JPG", "", "T", false, 300, "", false, false, 0, false, false, false);
        }*/
//        $this->SetFont('helvetica', 'I', 8);
//        // Page number
//        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
?>