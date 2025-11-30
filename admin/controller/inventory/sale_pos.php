<?php

class ControllerInventoryPOSInvoice extends HController {

    protected $document_type_id = 35;

    protected function getAlias() {
        return 'inventory/pos_invoice';
    }

    protected function getPrimaryKey() {
        return 'pos_invoice_id';
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

            if(in_array($this->session->data['company_branch_id'], array(58))) {
                $url_print = $this->url->link($this->getAlias() . '/printInvoice', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL');
            } else {
                $url_print = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL');
            }
            $actions[] = array(
                'text' => $lang['print'],
                'target' => '_blank',
                'href' => $url_print,
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
        $this->data['company_branch_id'] = $this->session->data['company_branch_id'];

        $this->model['product'] = $this->load->model('setup/product');
        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencys'] = $this->model['currency']->getRows();

        $this->model['salesman'] = $this->load->model('setup/salesman');
        $this->data['salesmans'] = $this->model['salesman']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['unit'] = $this->load->model('setup/unit');
        $this->data['units'] = $this->model['unit']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['warehouse'] = $this->load->model('setup/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));
        $this->data['arrWarehouses'] = json_encode($this->data['warehouses']);

        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";

        $this->data['partner_types'] = $this->session->data['partner_types'];
        $this->data['partner_type_id'] = 2;

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partners'] = $this->model['partner']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id'], 'partner_type_id'=>2), array('name'));

        $this->data['document_date'] = stdDate();
        if (isset($this->request->get['pos_invoice_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $result = $this->model[$this->getAlias()]->getRow(array('pos_invoice_id' => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                if ($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }
            $this->model['pos_invoice_detail'] = $this->load->model('inventory/pos_invoice_detail');
            $details = $this->model['pos_invoice_detail']->getRows(array('pos_invoice_id' => $this->request->get['pos_invoice_id']), array('sort_order DESC'));

            $this->data['pos_invoice_details'] = $details;
        }

        $this->data['href_discount_policy'] = $this->url->link($this->getAlias() . '/getDiscountPolicy', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_ref_document_no'] = $this->url->link($this->getAlias() . '/getReferenceDocumentNos', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_ref_document'] = $this->url->link($this->getAlias() . '/getReferenceDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        if(in_array($this->session->data['company_branch_id'], array(58))) {
            if($this->request->get['printInvoice']) {
                $this->data['action_print'] = $this->url->link($this->getAlias() . '/printInvoice', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get['printInvoice'], 'SSL');
            } else {
                $this->data['action_print'] = $this->url->link($this->getAlias() . '/printInvoice', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
            }
        } else {
            if($this->request->get['printInvoice']) {
                $this->data['action_print'] = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get['printInvoice'], 'SSL');
            } else {
                $this->data['action_print'] = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
            }
        }

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['strValidation'] = "{
            'rules': {
                'document_date': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'net_amount': {'required': true},
            },
            messages: {
            document_date:{
                remote: 'Invalid Date'
            }}
        }";

        $this->response->setOutput($this->render());
    }

    public function getDiscountPolicy() {
        $post = $this->request->post;
        $post['company_id'] = $this->session->data['company_id'];
        $post['company_branch_id'] = $this->session->data['company_branch_id'];
        if($post['document_date'] != '') {
            $post['document_date'] = MySqlDate($post['document_date']);
            $this->model['discount_policy'] = $this->load->model('inventory/sale_discount_policy');
            $rows = $this->model['discount_policy']->getDiscountPolicy($post);
            if(empty($rows) && $post['partner_id'] != '') {
                $post['partner_id'] = '';
                $rows = $this->model['discount_policy']->getDiscountPolicy($post);
            }
            $discounts = array();
            foreach($rows as $row) {
                if($row['type']=='Product') {
                    $discounts['Product'][$row['id']]=$row['discount_percent'];
                }elseif($row['type']=='Category') {
                    $discounts['Category'][$row['id']]=$row['discount_percent'];
                } else {
                    $discounts['General'] = $row['discount_percent'];
                }
            }
        } else {
            $discounts = array();
        }

        $json = array(
            'success' => true,
            'policy' => $discounts,
            'rows' => $rows,
            'post' => $post
        );

        echo json_encode($json);
    }

    public function ajaxValidateForm() {
        $post  = $this->request->post;
        $ID = $this->request->get;
//        d($ID,true);
//       d($post,true);
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


        $details = $post['pos_invoice_details'];
        $this->model['company'] = $this->load->model('setup/company');
        $company =  $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
//        d($company,true);
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
                    $filter ['document_id'] = $ID['pos_invoice_id'];

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
        $pos_invoice_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $data['document_id'] = $pos_invoice_id;

        $this->model['document'] = $this->load->model('common/document');
        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $pos_invoice_id,
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
        $this->model['product']= $this->load->model('setup/product');

        $partner = $this->model['partner']->getRow(array('partner_type_id' => $data['partner_type_id'], 'partner_id' => $data['partner_id']));
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        //d(array($data, $partner, $company), true);

        $cash_account_id = $partner['cash_account_id'];
        $outstanding_account_id = $partner['outstanding_account_id'];
        $sale_tax_account_id = $company['sale_tax_account_id'];
        $sale_discount_account_id = $company['sale_discount_account_id'];

        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $outstanding_account_id,
            'document_credit' => 0,
            'document_debit' => $data['net_amount'],
            'credit' => 0,
            'debit' => ($data['net_amount'] * $data['conversion_rate']),
            'comments' => 'Net Amount',
        );

        if(isset($data['discount']) && $data['discount'] != 0) {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $sale_discount_account_id,
                'document_credit' => 0,
                'document_debit' => $data['discount'],
                'credit' => 0,
                'debit' => ($data['discount'] * $data['conversion_rate']),
                'comments' => 'Discount',
            );
        }

        $this->model['pos_invoice_detail'] = $this->load->model('inventory/pos_invoice_detail');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');

        $this->model['mapping_account'] = $this->load->model('gl/mapping_coa');
        $account = $this->model['mapping_account']->getRow(array('company_id' => $this->session->data['company_id'], 'mapping_type_code' => 'GRIR'));
        $gr_ir_account_id = $account['coa_account_id'];

        foreach ($data['pos_invoice_details'] as $sort_order => $detail) {
            //d($detail, true);
            $detail['warehouse_id'] = $data['warehouse_id'];
            $detail['pos_invoice_id'] = $pos_invoice_id;
            $detail['sort_order'] = $sort_order;
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_total'] = $detail['total_amount'] * $data['conversion_rate'];
            $pos_invoice_detail_id = $this->model['pos_invoice_detail']->add($this->getAlias(), $detail);

            $stock_ledger = array(
                'company_id' => $detail['company_id'],
                'company_branch_id' => $detail['company_branch_id'],
                'fiscal_year_id' => $detail['fiscal_year_id'],
                'document_type_id' => $data['document_type_id'],
                'document_id' => $data['document_id'],
                'document_identity' => $data['document_identity'],
                'document_date' => $data['document_date'],
                'document_detail_id' => $pos_invoice_detail_id,
                'warehouse_id' => $data['warehouse_id'],
                'product_id' => $detail['product_id'],
                'document_unit_id' => $detail['unit_id'],
                'document_qty' => (-1 * $detail['qty']),
                'unit_conversion' => 1,
                'base_unit_id' => $detail['unit_id'],
                'base_qty' => (-1 * $detail['qty']),
                'document_currency_id' => $detail['document_currency_id'],
                'document_rate' => $detail['cog_rate'],
                'document_amount' => (-1 * $detail['cog_amount']),
                'currency_conversion' => $detail['conversion_rate'],
                'base_currency_id' => $detail['base_currency_id'],
                'base_rate' => ($detail['cog_rate'] * $detail['conversion_rate']),
                'base_amount' => (-1 * $detail['cog_amount'] * $detail['conversion_rate']),
            );
            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $stock_ledger);

            $product = $this->model['product']->getRow(array('product_id' => $detail['product_id']));
            if($detail['ref_document_type_id'] == 16) {
                if(isset($detail['cog_amount']) && $detail['cog_amount'] != 0) {
                    $gl_data[] = array(
                        'document_detail_id' => $pos_invoice_detail_id,
                        'ref_document_type_id' => $detail['ref_document_type_id'],
                        'ref_document_id' => $detail['ref_document_id'],
                        'ref_document_identity' => $detail['ref_document_identity'],
                        //'coa_id' => $gr_ir_account_id,
                        'coa_id' => $product['inventory_account_id'],
                        'document_credit' => $detail['cog_amount'],
                        'document_debit' => 0,
                        'credit' => ($detail['cog_amount'] * $data['conversion_rate']),
                        'debit' => 0,
                        'product_id' => $detail['product_id'],
                        'qty' => $detail['qty'],
                        'document_amount' => $detail['cog_amount'],
                        'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                        'comments' => 'Inventory Account',
                    );

                    $gl_data[] = array(
                        'document_detail_id' => $pos_invoice_detail_id,
                        'ref_document_type_id' => $detail['ref_document_type_id'],
                        'ref_document_id' => $detail['ref_document_id'],
                        'ref_document_identity' => $detail['ref_document_identity'],
                        'coa_id' => $product['cogs_account_id'],
                        'document_debit' => $detail['cog_amount'],
                        'document_credit' => 0,
                        'debit' => ($detail['cog_amount'] * $data['conversion_rate']),
                        'credit' => 0,
                        'product_id' => $detail['product_id'],
                        'qty' => $detail['qty'],
                        'document_amount' => $detail['cog_amount'],
                        'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                        'comments' => 'COG Account',
                    );
                }
            } else {
                $gl_data[] = array(
                    'document_detail_id' => $pos_invoice_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $product['inventory_account_id'],
                    'document_credit' => $detail['cog_amount'],
                    'document_debit' => 0,
                    'credit' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'debit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['cog_amount'],
                    'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'comments' => 'Inventory Account',
                );
                $gl_data[] = array(
                    'document_detail_id' => $pos_invoice_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $product['cogs_account_id'],
                    'document_debit' => $detail['cog_amount'],
                    'document_credit' => 0,
                    'debit' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'credit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['cog_amount'],
                    'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'comments' => 'COG Account',
                );
            }

            $gl_data[] = array(
                'document_detail_id' => $pos_invoice_detail_id,
                'ref_document_type_id' => $detail['ref_document_type_id'],
                'ref_document_id' => $detail['ref_document_id'],
                'ref_document_identity' => $detail['ref_document_identity'],
                //'coa_id' => $gr_ir_account_id,
                'coa_id' => $product['revenue_account_id'],
                'document_credit' => $detail['amount'],
                'document_debit' => 0,
                'credit' => ($detail['amount'] * $data['conversion_rate']),
                'debit' => 0,
                'product_id' => $detail['product_id'],
                'qty' => $detail['qty'],
                'document_amount' => $detail['amount'],
                'amount' => ($detail['amount'] * $data['conversion_rate']),
                'comments' => 'Revenue Account',
            );

            if(isset($detail['discount_amount']) && $detail['discount_amount']) {
                $gl_data[] = array(
                    'document_detail_id' => $pos_invoice_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $sale_discount_account_id,
                    'document_credit' => 0,
                    'document_debit' => $detail['discount_amount'],
                    'credit' => 0,
                    'debit' => ($detail['discount_amount'] * $data['conversion_rate']),
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['discount_amount'],
                    'amount' => ($detail['discount_amount'] * $data['conversion_rate']),
                    'comments' => 'Product Discount Account',
                );
            }

            if(isset($detail['tax_amount']) && $detail['tax_amount']) {
                $gl_data[] = array(
                    'document_detail_id' => $pos_invoice_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $sale_tax_account_id,
                    'document_credit' => $detail['tax_amount'],
                    'document_debit' => 0,
                    'credit' => ($detail['tax_amount'] * $data['conversion_rate']),
                    'debit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['tax_amount'],
                    'amount' => ($detail['tax_amount'] * $data['conversion_rate']),
                    'comments' => 'Product Tax Account',
                );
            }
        }

        if(isset($data['cash_received']) && $data['cash_received']) {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $outstanding_account_id,
                'document_debit' => 0,
                'document_credit' => $data['cash_received'],
                'debit' => 0,
                'credit' => ($data['cash_received'] * $data['conversion_rate']),
                'comments' => 'Outstanding Account',
            );

            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $cash_account_id,
                'document_credit' => 0,
                'document_debit' => $data['cash_received'],
                'credit' => 0,
                'debit' => ($data['cash_received'] * $data['conversion_rate']),
                'comments' => 'Cash Account',
            );
        }
        $this->model['ledger'] = $this->load->model('gl/ledger');
//        d(array($data, $gl_data));
        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $pos_invoice_id;
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['sort_order'] = $sort_order;
            $ledger['base_currency_id'] = $data['base_currency_id'];
            $ledger['document_currency_id'] = $data['document_currency_id'];
            $ledger['conversion_rate'] = $data['conversion_rate'];
            $ledger['partner_type_id'] = $data['partner_type_id'];
            $ledger['partner_id'] = $data['partner_id'];
            $ledger['remarks'] = isset($ledger['remarks'])?$ledger['remarks']:$data['remarks'];

            $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);
//            d(array($data,$detail,$ledger_id,$ledger),true);
        }
        return $pos_invoice_id;
    }

    protected function insertRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias().'/insert', 'token=' . $this->session->data['token'] . '&printInvoice=' . $id . $url, 'SSL'));
    }

    protected function updateData($primary_key, $data) {
        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['document_date'] = MySqlDate($data['document_date']);
        $data['base_net_amount'] = $data['net_amount'] * $data['conversion_rate'];
        $pos_invoice_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        $data['document_id'] = $pos_invoice_id;

        $this->model['pos_invoice_detail'] = $this->load->model('inventory/pos_invoice_detail');
        $this->model['pos_invoice_detail']->deleteBulk($this->getAlias(), array('pos_invoice_id' => $pos_invoice_id));

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
            'document_id' => $pos_invoice_id,
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
        $this->model['product']= $this->load->model('setup/product');

        $partner = $this->model['partner']->getRow(array('partner_type_id' => $data['partner_type_id'], 'partner_id' => $data['partner_id']));
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        //d(array($data, $partner, $company), true);

        $cash_account_id = $partner['cash_account_id'];
        $outstanding_account_id = $partner['outstanding_account_id'];
        $sale_tax_account_id = $company['sale_tax_account_id'];
        $sale_discount_account_id = $company['sale_discount_account_id'];

        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $outstanding_account_id,
            'document_credit' => 0,
            'document_debit' => $data['net_amount'],
            'credit' => 0,
            'debit' => ($data['net_amount'] * $data['conversion_rate']),
            'comments' => 'Outstanding Account',
        );

        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $sale_discount_account_id,
            'document_credit' => 0,
            'document_debit' => $data['discount'],
            'credit' => 0,
            'debit' => ($data['discount'] * $data['conversion_rate']),
            'comments' => 'Sale Discount Account',
        );

        $this->model['pos_invoice_detail'] = $this->load->model('inventory/pos_invoice_detail');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');

        $this->model['mapping_account'] = $this->load->model('gl/mapping_coa');
        $account = $this->model['mapping_account']->getRow(array('company_id' => $this->session->data['company_id'], 'mapping_type_code' => 'GRIR'));
        $gr_ir_account_id = $account['coa_account_id'];

        foreach ($data['pos_invoice_details'] as $sort_order => $detail) {
            //d($detail, true);
            $detail['warehouse_id'] = $data['warehouse_id'];
            $detail['pos_invoice_id'] = $pos_invoice_id;
            $detail['sort_order'] = $sort_order;
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_total'] = $detail['total_amount'] * $data['conversion_rate'];
            $pos_invoice_detail_id = $this->model['pos_invoice_detail']->add($this->getAlias(), $detail);

            $stock_ledger = array(
                'company_id' => $detail['company_id'],
                'company_branch_id' => $detail['company_branch_id'],
                'fiscal_year_id' => $detail['fiscal_year_id'],
                'document_type_id' => $data['document_type_id'],
                'document_id' => $data['document_id'],
                'document_identity' => $data['document_identity'],
                'document_date' => $data['document_date'],
                'document_detail_id' => $pos_invoice_detail_id,
                'warehouse_id' => $data['warehouse_id'],
                'product_id' => $detail['product_id'],
                'document_unit_id' => $detail['unit_id'],
                'document_qty' => (-1 * $detail['qty']),
                'unit_conversion' => 1,
                'base_unit_id' => $detail['unit_id'],
                'base_qty' => (-1 * $detail['qty']),
                'document_currency_id' => $detail['document_currency_id'],
                'document_rate' => $detail['cog_rate'],
                'document_amount' => (-1 * $detail['cog_amount']),
                'currency_conversion' => $detail['conversion_rate'],
                'base_currency_id' => $detail['base_currency_id'],
                'base_rate' => ($detail['cog_rate'] * $detail['conversion_rate']),
                'base_amount' => (-1 * $detail['cog_amount'] * $detail['conversion_rate']),
            );
            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $stock_ledger);

            $product = $this->model['product']->getRow(array('product_id' => $detail['product_id']));
            if($detail['ref_document_type_id'] == 16) {
                if(isset($detail['cog_amount']) && $detail['cog_amount'] != 0) {
                    $gl_data[] = array(
                        'document_detail_id' => $pos_invoice_detail_id,
                        'ref_document_type_id' => $detail['ref_document_type_id'],
                        'ref_document_id' => $detail['ref_document_id'],
                        'ref_document_identity' => $detail['ref_document_identity'],
                        //'coa_id' => $gr_ir_account_id,
                        'coa_id' => $product['inventory_account_id'],
                        'document_credit' => $detail['cog_amount'],
                        'document_debit' => 0,
                        'credit' => ($detail['cog_amount'] * $data['conversion_rate']),
                        'debit' => 0,
                        'product_id' => $detail['product_id'],
                        'qty' => $detail['qty'],
                        'document_amount' => $detail['cog_amount'],
                        'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                        'comments' => 'Inventory Account',
                    );

                    $gl_data[] = array(
                        'document_detail_id' => $pos_invoice_detail_id,
                        'ref_document_type_id' => $detail['ref_document_type_id'],
                        'ref_document_id' => $detail['ref_document_id'],
                        'ref_document_identity' => $detail['ref_document_identity'],
                        'coa_id' => $product['cogs_account_id'],
                        'document_debit' => $detail['cog_amount'],
                        'document_credit' => 0,
                        'debit' => ($detail['cog_amount'] * $data['conversion_rate']),
                        'credit' => 0,
                        'product_id' => $detail['product_id'],
                        'qty' => $detail['qty'],
                        'document_amount' => $detail['cog_amount'],
                        'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                        'comments' => 'COG Account',
                    );
                }
            } else {
                $gl_data[] = array(
                    'document_detail_id' => $pos_invoice_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $product['inventory_account_id'],
                    'document_credit' => $detail['cog_amount'],
                    'document_debit' => 0,
                    'credit' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'debit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['cog_amount'],
                    'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'comments' => 'Inventory Account',
                );
                $gl_data[] = array(
                    'document_detail_id' => $pos_invoice_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $product['cogs_account_id'],
                    'document_debit' => $detail['cog_amount'],
                    'document_credit' => 0,
                    'debit' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'credit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['cog_amount'],
                    'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'comments' => 'COG Account',
                );
            }

            $gl_data[] = array(
                'document_detail_id' => $pos_invoice_detail_id,
                'ref_document_type_id' => $detail['ref_document_type_id'],
                'ref_document_id' => $detail['ref_document_id'],
                'ref_document_identity' => $detail['ref_document_identity'],
                //'coa_id' => $gr_ir_account_id,
                'coa_id' => $product['revenue_account_id'],
                'document_credit' => $detail['amount'],
                'document_debit' => 0,
                'credit' => ($detail['amount'] * $data['conversion_rate']),
                'debit' => 0,
                'product_id' => $detail['product_id'],
                'qty' => $detail['qty'],
                'document_amount' => $detail['amount'],
                'amount' => ($detail['amount'] * $data['conversion_rate']),
                'comments' => 'Revenue Account',
            );

            if(isset($detail['discount_amount']) && $detail['discount_amount']) {
                $gl_data[] = array(
                    'document_detail_id' => $pos_invoice_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $sale_discount_account_id,
                    'document_credit' => 0,
                    'document_debit' => $detail['discount_amount'],
                    'credit' => 0,
                    'debit' => ($detail['discount_amount'] * $data['conversion_rate']),
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['discount_amount'],
                    'amount' => ($detail['discount_amount'] * $data['conversion_rate']),
                    'comments' => 'Product Discount Account',
                );
            }

            if(isset($detail['tax_amount']) && $detail['tax_amount']) {
                $gl_data[] = array(
                    'document_detail_id' => $pos_invoice_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $sale_tax_account_id,
                    'document_credit' => $detail['tax_amount'],
                    'document_debit' => 0,
                    'credit' => ($detail['tax_amount'] * $data['conversion_rate']),
                    'debit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['tax_amount'],
                    'amount' => ($detail['tax_amount'] * $data['conversion_rate']),
                    'comments' => 'Product Tax Account',
                );
            }
        }

        if(isset($data['cash_received']) && $data['cash_received']) {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $outstanding_account_id,
                'document_debit' => 0,
                'document_credit' => $data['cash_received'],
                'debit' => 0,
                'credit' => ($data['cash_received'] * $data['conversion_rate']),
                'comments' => 'Outstanding Account',
            );

            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $cash_account_id,
                'document_credit' => 0,
                'document_debit' => $data['cash_received'],
                'credit' => 0,
                'debit' => ($data['cash_received'] * $data['conversion_rate']),
                'comments' => 'Cash Account',
            );
        }
        $this->model['ledger'] = $this->load->model('gl/ledger');
        //d(array($data, $gl_data), true);
        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $pos_invoice_id;
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
        return $pos_invoice_id;
    }

    protected function deleteData($primary_key) {
        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);

        $this->model['pos_invoice_detail'] = $this->load->model('inventory/pos_invoice_detail');
        $this->model['pos_invoice_detail']->deleteBulk($this->getAlias(), array('pos_invoice_id' => $primary_key));

        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

    }

    public function getReferenceDocumentNos() {
        $pos_invoice_id = $this->request->get['pos_invoice_id'];
        $post = $this->request->post;

        $this->model['document'] = $this->load->model('common/document');
        $where = "company_id=" . $this->session->data['company_id'];
        $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
        $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
        $where .= " AND partner_type_id='" . $post['partner_type_id'] . "'";
        $where .= " AND partner_id='" . $post['partner_id'] . "'";
        $where .= " AND document_type_id='" . $post['ref_document_type_id'] . "'";
        //$where .= " AND document_currency_id='" . $post['document_currency_id'] . "'";
        //$where .= " AND is_post=1";

        $orders = $this->model['document']->getRows($where);

        $html = "";
        $html .= '<option value="">&nbsp;</option>';
        foreach($orders as $goods_received) {
            $html .= '<option value="'.$goods_received['document_identity'].'">'.$goods_received['document_identity']. '</option>';
        }

        //d($goods_received,true);
        $json = array(
            'success' => true,
            'pos_invoice_id' => $pos_invoice_id,
            'post' => $post,
            'where' => $where,
            'orders' => $orders,
            'html' => $html
        );

        echo json_encode($json);
    }

    public function getReferenceDocument() {
        $pos_invoice_id = $this->request->get['pos_invoice_id'];
        $post = $this->request->post;

        if($post['ref_document_type_id'] == 26) {
            //Sale Inquiry
            $this->model['sale_inquiry_detail'] = $this->load->model('inventory/sale_inquiry_detail');
            $where = "company_id=" . $this->session->data['company_id'];
            $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
            $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
            $where .= " AND partner_type_id='" . $post['partner_type_id'] . "'";
            $where .= " AND partner_id='" . $post['partner_id'] . "'";
            $where .= " AND document_identity='" . $post['ref_document_identity'] . "'";
//            $where .= " AND document_currency_id='" . $post['document_currency_id'] . "'";
//            $where .= " AND is_post=1";

            $rows = $this->model['sale_inquiry']->getRows($where);
            $html = '';
            $details = array();
            $this->model['product'] = $this->load->model('setup/product');
            $this->model['stock'] = $this->load->model('common/stock_ledger');
            foreach($rows as $row_no => $row) {
                $product = $this->model['product']->getRow(array('product_id' => $row['product_id']));
                $filter = array(
                    'company_id' => $this->session->data['company_id'],
                    'company_branch_id' => $this->session->data['company_branch_id'],
                    'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                    'product_id' => $row['product_id'],
                );
                $stock = $this->model['stock']->getStock($filter);
//                d($stock,true);
                $href = $this->url->link('inventory/sale_inquiry/update', 'token=' . $this->session->data['token'] . '&sale_inquiry_id=' . $row['sale_inquiry_id']);
                $details[$row_no] = $row;
                $details[$row_no]['href'] = $href;
                $details[$row_no]['rate'] = $product['sale_price'];
                $details[$row_no]['amount'] = ($row['qty'] * $product['sale_price']);
                $details[$row_no]['cog_rate'] = $stock['avg_stock_rate'];
                $details[$row_no]['cog_amount'] = ($row['qty'] * $stock['avg_stock_rate']);
                $details[$row_no]['stock_qty'] = $stock['stock_qty'];
            }
        } elseif($post['ref_document_type_id'] == 16) {
            //Delivery Challan
            $this->model['delivery_challan_detail'] = $this->load->model('inventory/delivery_challan_detail');
            $where = "company_id=" . $this->session->data['company_id'];
            $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
            $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
            $where .= " AND partner_type_id='" . $post['partner_type_id'] . "'";
            $where .= " AND partner_id='" . $post['partner_id'] . "'";
            $where .= " AND document_identity='" . $post['ref_document_identity'] . "'";
//            $where .= " AND document_currency_id='" . $post['document_currency_id'] . "'";
//            $where .= " AND is_post=1";

            $rows = $this->model['delivery_challan_detail']->getRows($where);
            $html = '';
            $details = array();
            $this->model['product'] = $this->load->model('setup/product');
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
                $href = $this->url->link('inventory/delivery_challan/update', 'token=' . $this->session->data['token'] . '&delivery_challan_id=' . $row['delivery_challan_id']);
                $details[$row_no] = $row;
                $details[$row_no]['href'] = $href;
                $details[$row_no]['rate'] = $product['sale_price'];
                $details[$row_no]['amount'] = ($row['qty'] * $product['sale_price']);
                $details[$row_no]['stock_qty'] = $stock['stock_qty'];
            }
        }

        $json = array(
            'success' => true,
            'pos_invoice_id' => $pos_invoice_id,
            'post' => $post,
            'where' => $where,
            'details' => $details);
        echo json_encode($json);
    }

    public function printDocument() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        //d(array('session' => $this->session->data, 'post' => $this->request->post, 'get' => $this->request->get), true);
        $lang = $this->load->language($this->getAlias());
        $pos_invoice_id = $this->request->get['pos_invoice_id'];
        $post = $this->request->post;
        $session = $this->session->data;
        $time = date('H:i', time()+18000);

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->model['pos_invoice'] = $this->load->model('inventory/pos_invoice');
        $this->model['pos_invoice_detail'] = $this->load->model('inventory/pos_invoice_detail');
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $invoice = $this->model['pos_invoice']->getRow(array('pos_invoice_id' => $pos_invoice_id));
        $details = $this->model['pos_invoice_detail']->getRows(array('pos_invoice_id' => $pos_invoice_id));
        //d($company, true);
        $pdf = new PDF('P', PDF_UNIT,  array(74,160), true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('POS INVOICE');
        $pdf->SetSubject('POS INVOICE');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'company_address' => $branch['address'],
            'company_phone' => $branch['phone_no'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $session['company_image'],
            'company_branch'=>$branch['name'],
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(1, 2, 2);
        $pdf->SetHeaderMargin(2);
        $pdf->SetFooterMargin(2);

        // set auto page breaks
        //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font
        $pdf->SetFont('times', 'B', 8);

        // add a page
        $pdf->AddPage();

        if($pdf->data['company_logo'] != '') {
            $image_file = DIR_IMAGE.$pdf->data['company_logo'];
            //$pdf->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
            $pdf->Image($image_file, 0, 0, 80, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $pdf->Ln(20);
        } else {
            // Set font
            $pdf->SetTextColor(255,255,255);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Ln(0);
            // Title
            $pdf->Cell(0, 6, $pdf->data['company_name'], 0, false, 'C', 1, '', 0, false, 'M', 'M');
        }
        $pdf->Ln(7);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(0, 4, $pdf->data['company_branch'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(0, 6, $pdf->data['company_branch'], 0, false, 'C', 1, '', 0, false, 'M', 'M');
        $pdf->Ln(6);

        if($pdf->data['company_address']) {
            $pdf->Cell(0, 6, $pdf->data['company_address'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Ln(6);
        }
        if($pdf->data['company_phone']) {
            $pdf->Cell(0, 6, 'Phone: '.$pdf->data['company_phone'], 0, false, 'C', 1, '', 0, false, 'M', 'M');
            $pdf->Ln(6);
        }
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(0, 4, $pdf->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('times', '', 10);
        $pdf->Ln(5);
        $pdf->Cell(30, 4, 'Transaction No.', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 4, $invoice['document_identity'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(4);
        $pdf->Cell(30, 4, 'Transaction Date', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 4, stdDate($invoice['document_date']), 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(4);
        $pdf->Cell(30, 4, 'Customer', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 4, $invoice['partner_name'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(4);
        $pdf->Cell(30, 4, 'Print Time', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 4, $time, 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(4);
        $pdf->SetFont('times', '', 12);
        $pdf->Cell(0, 8, 'Original Receipt', 'TB', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(8);
        $pdf->SetFont('times', 'B', 8);
        $pdf->Cell(5, 4, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(28, 4, 'Product Description', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(8, 4, 'Qty', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(9, 4, 'Price', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(9, 4, 'Disc', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(12, 4, 'Total', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('times', '', 6);
        $sr = 0;
        $total_amount = 0;
        $total_discount = $invoice['discount'];
        $total_qty = 0;
        foreach($details as $detail) {
            $sr++;
            $pdf->Ln(4);
            $pdf->Cell(5, 4, $sr, 0,  false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(28, 4, $detail['product_code'].' - '.$detail['product_name'], 0, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(8, 4, number_format($detail['qty'],2), 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(9, 4, number_format($detail['rate'],2), 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(9, 4, number_format($detail['discount_amount'],2), 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(12, 4, number_format($detail['gross_amount'],2), 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $total_amount += $detail['gross_amount'];
            $total_discount += $detail['discount_amount'];
            $total_qty += $detail['qty'];
        }
        $pdf->Ln(4);
        $pdf->SetFont('times', '', 8);
        $pdf->Cell(50, 4, 'Total Items / Quantity', 'T', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 4, count($details).' / '.number_format($total_qty,2), 'T', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(4);
        $pdf->SetFont('times', '', 10);
        $pdf->Cell(50, 4, 'Discount', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 4, number_format($total_discount,2), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        if($invoice['return_amount'] > 0){
            $pdf->Ln(4);
            $pdf->SetFont('times', '', 10);
            $pdf->Cell(50, 4, 'Return', 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 4, number_format($invoice['return_amount'],2), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        }
        $pdf->Ln(4);
        $pdf->Cell(50, 4, 'Invoice Value', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 4, number_format($invoice['net_amount'],2), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(6);
        $pdf->Cell(0, 14, '', 'TB', false, 'L', 0, '', 0, false, 'M', 'M');
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);

        $pdf->write1DBarcode($invoice['document_identity'], 'C128A',$x-68,$y+2,66,10,0.4,array(
            'position' => 'S',
            'align' => 'C',
            'stretch' => true,
            'fitwidth' => false,
            'cellfitalign' => 'C',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255),
            'text' => false,
            'font' => 'helvetica',
            'fontsize' => 5,
            //'module_width' => 13,
        ),'M');

        $pdf->Ln(7);
        $pdf->Cell(0, 6, 'No exchange without barcode and invoice.', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(6);
        $pdf->Cell(0, 6, 'No cash refund.', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(6);
        $pdf->Cell(0, 6, 'Exchange within 7days.', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(6);
        $pdf->Cell(0, 6, 'All products are dryclean only.', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(6);
        $pdf->Cell(0, 6, 'WE ARE PLEASED TO SERVE YOU', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(6);
        $pdf->Cell(0, 6, 'Thankyou for Shopping', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(6);
        $pdf->Cell(0, 4, 'Developed By: Bharmal System Designers', 'RTL', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(4);
        $pdf->Cell(0, 4, 'www.bharmalsystems.com', 'RBL', false, 'C', 0, '', 0, false, 'M', 'M');
        //$pdf->Ln(4);
        //$pdf->Cell(0, 4, 'Tel: 02136674168, Cell: 03333003758', 'RBL', false, 'C', 0, '', 0, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('POS Invoice:'.date('YmdHis').'.pdf', 'I');
    }

    public function printInvoice() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        //d(array('session' => $this->session->data, 'post' => $this->request->post, 'get' => $this->request->get), true);
        $lang = $this->load->language($this->getAlias());
        $pos_invoice_id = $this->request->get['pos_invoice_id'];
        $post = $this->request->post;
        $session = $this->session->data;

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->model['pos_invoice'] = $this->load->model('inventory/pos_invoice');
        $this->model['pos_invoice_detail'] = $this->load->model('inventory/pos_invoice_detail');
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $invoice = $this->model['pos_invoice']->getRow(array('pos_invoice_id' => $pos_invoice_id));
        $details = $this->model['pos_invoice_detail']->getRows(array('pos_invoice_id' => $pos_invoice_id));

        //d($company, true);
        $pdf = new PDFInvoice('P', PDF_UNIT, 'A5', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('POS INVOICE');
        $pdf->SetSubject('POS INVOICE');

        //Set Header
        $pdf->data = array(
            'company_name' => 'MONA EMBROIDERY',
            'company_address' => $branch['address'],
            'company_phone' => $branch['phone_no'],
            'report_name' => $lang['heading_title'],
            'company_logo' => ''
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 30, 8);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font
        $pdf->SetFont('times', 'B', 8);

        // add a page
        $pdf->AddPage();
        //$pdf->Cell(0, 7, '', 1, 1, 'L', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(130, 7, '', 1, 1, 'L', 0, '', 0, false, 'M', 'M');
        // set font
        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell(20, 7, 'M/S: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // set font
        $pdf->SetFont('times', '', 9);
        $pdf->Cell(110, 7, $invoice['partner_name'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->ln(7);
        // set font
        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell(20, 7, 'Invoice No: ', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        // set font
        $pdf->SetFont('times', '', 9);
        $pdf->Cell(40, 7, $invoice['document_identity'], 'B', false, 'C', 0, '', 0, false, 'M', 'M');
        // set font
        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell(20, 7, 'Date: ', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        // set font
        $pdf->SetFont('times', '', 9);
        $pdf->Cell(40, 7, stdDate($invoice['document_date']), 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->ln(10);

        // set font
        $pdf->SetFont('times', '', 8);
        $pdf->SetFillColor(215, 215, 215);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(15, 8, 'Product Code', 1, false, 'C', 1, '', 1);
        $pdf->Cell(30, 8, 'Particulars', 1, false, 'C', 1, '', 1);
        $pdf->Cell(10, 8, 'Qty', 1, false, 'C', 1, '', 1);
        $pdf->Cell(15, 8, 'Rate', 1, false, 'C', 1, '', 1);
        $pdf->Cell(15, 8, 'Amount', 1, false, 'C', 1, '', 1);
        $pdf->Cell(15, 8, 'Discount', 1, false, 'C', 1, '', 1);
        $pdf->Cell(15, 8, 'Tax', 1, false, 'C', 1, '', 1);
        $pdf->Cell(15, 8, 'Total', 1, false, 'C', 1, '', 1);
        $pdf->ln(8);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $total_qty = 0;
        $total_amount = 0;
        $total_discount = 0;
        $total_tax = 0;
        $net_amount = 0;
        foreach($details as $detail) {
            $pdf->Cell(15, 8, $detail['product_code'], 1, false, 'L', 1, '', 1);
            $pdf->Cell(30, 8, $detail['product_name'], 1, false, 'L', 1, '', 1);
            $pdf->Cell(10, 8, $detail['qty'], 1, false, 'R', 1, '', 1);
            $pdf->Cell(15, 8, number_format($detail['rate'],2), 1, false, 'R', 1, '', 1);
            $pdf->Cell(15, 8, number_format($detail['amount']), 1, false, 'R', 1, '', 1);
            $pdf->Cell(15, 8, number_format($detail['discount_amount']), 1, false, 'R', 1, '', 1);
            $pdf->Cell(15, 8, number_format($detail['tax_amount']), 1, false, 'R', 1, '', 1);
            $pdf->Cell(15, 8, number_format($detail['total_amount']), 1, false, 'R', 1, '', 1);
            $pdf->ln(8);

            $total_qty += $detail['qty'];
            $total_amount += $detail['amount'];
            $total_discount += $detail['discount_amount'];
            $total_tax += $detail['tax_amount'];
            $net_amount += $detail['total_amount'];
        }

        $pdf->SetFont('times', 'B', 8);
        $pdf->ln(5);
        $pdf->Cell(115, 5, 'Total Qty:', 0, false, 'R', 1, '', 1);
        $pdf->Cell(15, 5, $total_qty, 0, false, 'R', 1, '', 1);
        $pdf->ln(5);
        $pdf->Cell(115, 5, 'Total Amount:', 0, false, 'R', 1, '', 1);
        $pdf->Cell(15, 5, number_format($total_amount), 0, false, 'R', 1, '', 1);
        $pdf->ln(5);
        $pdf->Cell(115, 5, 'Total Discount:', 0, false, 'R', 1, '', 1);
        $pdf->Cell(15, 5, number_format($total_discount), 0, false, 'R', 1, '', 1);
        $pdf->ln(5);
        $pdf->Cell(115, 5, 'Total Tax:', 0, false, 'R', 1, '', 1);
        $pdf->Cell(15, 5, number_format($total_tax), 0, false, 'R', 1, '', 1);
        $pdf->ln(5);
        $pdf->Cell(100, 5, '', '', false, 'R', 1, '', 1);
        $pdf->Cell(15, 5, 'Net Amount:', 'TB', false, 'R', 1, '', 1);
        $pdf->Cell(15, 5, number_format($net_amount), 'TB', false, 'R', 1, '', 1);

        //Close and output PDF document
        $pdf->Output('POS Invoice:'.date('YmdHis').'.pdf', 'I');
    }
}

class PDF extends TCPDF {
    public $data = array();

    //Page header
    public function Header() {
        // Logo
//        if($this->data['company_logo'] != '') {
//            $image_file = DIR_IMAGE.$this->data['company_logo'];
//            //$this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
//            $this->Image($image_file, 10, 10, 30, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
//        }
//        // Set font
//        $this->SetTextColor(255,255,255);
//        $this->SetFont('helvetica', 'B', 10);
//        $this->Ln(2);
//        // Title
//        $this->Cell(0, 4, $this->data['company_name'], 0, false, 'C', 1, '', 0, false, 'M', 'M');
//        $this->Ln(4);
//        if($this->data['company_address']) {
//            $this->Cell(0, 4, $this->data['company_address'], 0, false, 'C', 0, '', 1, false, 'M', 'M');
//            $this->Ln(4);
//        }
//        if($this->data['company_phone']) {
//            $this->Cell(0, 4, 'Phone: '.$this->data['company_phone'], 0, false, 'C', 1, '', 0, false, 'M', 'M');
//            $this->Ln(4);
//        }
//        $this->SetTextColor(0,0,0);
//        $this->Cell(0, 4, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
//        // Position at 15 mm from bottom
//        $this->SetY(-15);
//        // Set font
//        $this->SetFont('helvetica', 'I', 8);
//        // Page number
//        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

class PDFInvoice extends TCPDF {
    public $data = array();

    //Page header
    public function Header() {
        // Logo
        if($this->data['company_logo'] != '') {
            $image_file = DIR_IMAGE.$this->data['company_logo'];
            //$this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
            $this->Image($image_file, 48, 5, 50, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $this->Ln(20);
        } else {
            // Set font
            //$this->SetTextColor(255,255,255);
            $this->SetFont('times', '', 14);
            $this->Ln(2);
            // Title
            $this->Cell(0, 4, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Ln(5);
            $this->SetFont('times', '', 11);
            if($this->data['company_address']) {
                $this->Cell(0, 4, $this->data['company_address'], 0, false, 'C', 0, '',1, false, 'M', 'M');
                $this->Ln(4);
            }
            if($this->data['company_phone']) {
                $this->Cell(0, 4, $this->data['company_phone'], 0, false, 'C', 0, '', 1, false, 'M', 'M');
                $this->Ln(4);
            }
            $this->Ln(5);
        }
        $this->SetFont('helvetica', 'B', 10);
        $this->SetTextColor(0,0,0);
        $this->Cell(0, 4, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

?>