<?php

class ControllerInventoryPurchaseReturn extends HController {

    protected $document_type_id = 23;

    protected function getAlias() {
        return 'inventory/purchase_return';
    }

    protected function getPrimaryKey() {
        return 'purchase_return_id';
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
        $aColumns = array('action', 'document_date', 'document_identity','partner_name','remarks', 'net_amount', 'created_at', 'check_box');

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
                $strAction .= '<a '.(isset($action['btn_class'])?'class="'.$action['btn_class'].'"':'').' '.(isset($action['target'])?'target="'.$action['target'].'"':'').' '.' href="' . $action['href'] . '" data-toggle="tooltip" title="' . $action['text'] . '" ' . (isset($action['click']) ? 'onClick="' . $action['click'] . '"' : '') . '>';
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

                }elseif ($aColumns[$i] == 'created_at') {
                    $row[] = stdDateTime($aRow['created_at']);
                } elseif ($aColumns[$i] == 'check_box') {
                    if($aRow['is_post']==0) {
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

        // $this->model['product'] = $this->load->model('inventory/product');
        // $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));

        // $this->model['supplier'] = $this->load->model('setup/supplier');
        // $this->data['suppliers'] = $this->model['supplier']->getRows(array('company_id' => $this->session->data['company_id']),array('name'));

        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencys'] = $this->model['currency']->getRows();

        $this->model['unit'] = $this->load->model('inventory/unit');
        $this->data['units'] = $this->model['unit']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));
        $this->data['arrWarehouses'] = json_encode($this->data['warehouses']);

        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";

        $this->data['partner_types'] = $this->session->data['partner_types'];
        $this->data['partner_type_id'] = 1;

        $this->data['document_date'] = stdDate();
        if (isset($this->request->get['purchase_return_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $result = $this->model[$this->getAlias()]->getRow(array('purchase_return_id' => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                if ($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }
            $this->model['purchase_return_detail'] = $this->load->model('inventory/purchase_return_detail');
            $details = $this->model['purchase_return_detail']->getRows(array('purchase_return_id' => $this->request->get['purchase_return_id']), array('sort_order DESC'));

            // $this->data['purchase_return_details'] = $details;
            $this->model['purchase_invoice'] = $this->load->model('inventory/purchase_invoice');
            foreach ($details as $key => $value) {
                $purchase_invoice = $this->model['purchase_invoice']->getRow(array('document_identity' => $value['ref_document_identity']));
                // d($purchase_invoice);
                $this->data['purchase_return_details'][$key] = $value;
                $this->data['purchase_return_details'][$key]['href'] = $this->url->link('inventory/purchase_invoice/update', 'token=' . $this->session->data['token'] . '&purchase_invoice_id=' .$purchase_invoice['purchase_invoice_id'], 'SSL');
            }
            // d($this->data['purchase_return_details'],true);

        }

        $this->data['href_get_partner_json'] = $this->url->link($this->getAlias() . '/getPartnerJson', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_partner'] = $this->url->link($this->getAlias() . '/getPartner', 'token=' . $this->session->data['token']);

        $this->data['href_get_product_json'] = $this->url->link($this->getAlias() . '/getProductJson', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

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

        //       d($post,true);
        $lang = $this->load->language($this->getAlias());
        $error = array();
        if($post['document_date'] == '') {
            $error[] = $lang['error_invoice_date'];
        }
        if($post['supplier_id'] == '') {
            $error[] = $lang['error_supplier'];
        }
        if($post['document_currency_id'] == '') {
            $error[] = $lang['error_document_currency'];
        }
        if($post['conversion_rate'] == '') {
            $error[] = $lang['error_conversion_rate'];
        }
        $details = $post['purchase_return_details'];
        //        d($details);
        if(empty($details)) {
            $error[] = $lang['error_input_detail'];
        } else {
            $row_no = 0;
            foreach($details as $detail) {
                // d($detail,true);
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

    protected function validateForm() {

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    protected  function insertData($data) {
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
        $data['base_amount'] = $data['net_amount'] * $data['conversion_rate'];
        $purchase_return_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $this->model['document'] = $this->load->model('common/document');
        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $purchase_return_id,
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
        $data['document_id'] = $document_id;
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['product']= $this->load->model('inventory/product');
        $partner = $this->model['partner']->getRow(array('partner_type_id' => $data['partner_type_id'], 'partner_id' => $data['partner_id']));
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        // d(array($partner,$company));

        $this->model['setting']= $this->load->model('common/setting');

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
            'module' => 'inventory',
            'field' => 'purchase_discount_account_id',
        ));
       
        $purchase_discount_account_id = $setting['value'];
        $outstanding_account_id = $partner['outstanding_account_id'];
        // ye wala
        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $outstanding_account_id,
            'document_credit' => 0,
            'document_debit' => $data['net_amount'],
            'credit' => 0,
            'debit' => ($data['net_amount'] * $data['conversion_rate']),
        );
        // ye wala
        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $purchase_discount_account_id,
            'document_credit' => 0,
            'document_debit' => $data['deduction_amount'],
            'credit' => 0,
            'debit' => ($data['deduction_amount'] * $data['conversion_rate'])
        );

        $this->model['purchase_return_detail'] = $this->load->model('inventory/purchase_return_detail');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        foreach ($data['purchase_return_details'] as $sort_order => $detail) {
            $detail['purchase_return_id'] = $purchase_return_id;
            $detail['sort_order'] = $sort_order;
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_total'] = $data['total_amount'] * $data['conversion_rate'];
            //  d($sort_order,true);
            $purchase_return_detail_id = $this->model['purchase_return_detail']->add($this->getAlias(), $detail);

            $stock_ledger = array(
                'company_id' => $detail['company_id'],
                'company_branch_id' => $detail['company_branch_id'],
                'fiscal_year_id' => $detail['fiscal_year_id'],
                'document_type_id' => $data['document_type_id'],
                'document_id' => $data['document_id'],
                'document_identity' => $data['document_identity'],
                'document_date' => $data['document_date'],
                'document_detail_id' => $purchase_return_detail_id,
                'warehouse_id' => $detail['warehouse_id'],
                'product_id' => $detail['product_id'],
                'document_unit_id' => $detail['unit_id'],
                'document_qty' => -1 * $detail['qty'],
                'unit_conversion' => 1,
                'base_unit_id' => $detail['unit_id'],
                'base_qty' => -1 * $detail['qty'],
                'document_currency_id' => $detail['document_currency_id'],
                'document_rate' => $detail['rate'],
                'document_amount' => -1 * $detail['amount'],
                'currency_conversion' => $detail['conversion_rate'],
                'base_currency_id' => $detail['base_currency_id'],
                'base_rate' => ($detail['rate'] * $detail['conversion_rate']),
                'base_amount' => -1 * ($detail['amount'] * $detail['conversion_rate']),
            );
            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $stock_ledger);
            $product = $this->model['product']->getRow(array('product_id' => $detail['product_id']));
            // d($product,true);

            if( floatval($detail['amount']) > 0 ){

                $gl_data[] = array(
                    'document_detail_id' => $purchase_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $product['inventory_account_id'],
                    'document_credit' => $detail['amount'],
                    'document_debit' => 0,
                    'credit' => ($detail['amount'] * $data['conversion_rate']),
                    'debit' => 0,
                    'remarks' => $detail['remarks'],
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['amount'],
                    'amount' => ($detail['amount'] * $data['conversion_rate']),
                );

            }

            if( floatval($detail['discount_amount']) > 0 ){

                $gl_data[] = array(
                    'document_detail_id' => $purchase_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $purchase_discount_account_id,
                    'document_credit' => 0,
                    'document_debit' => $detail['discount_amount'],
                    'credit' => 0,
                    'debit' => ($detail['discount_amount'] * $data['conversion_rate']),
                    'remarks' => $detail['remarks'],
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['discount_amount'],
                    'amount' => ($detail['discount_amount'] * $data['conversion_rate']),
                );

            }

            if( floatval($detail['tax_amount']) > 0 ){

                $gl_data[] = array(
                    'document_detail_id' => $purchase_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $sale_tax_account_id,
                    'document_credit' => $detail['tax_amount'],
                    'document_debit' => 0,
                    'credit' => ($detail['tax_amount'] * $data['conversion_rate']),
                    'debit' => 0,
                    'remarks' => $detail['remarks'],
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['tax_amount'],
                    'amount' => ($detail['tax_amount'] * $data['conversion_rate']),
                );
                
            }
        }
        // d($data);
        // d($gl_data,true);
            $this->model['ledger'] = $this->load->model('gl/ledger');
            foreach($gl_data as $sort_order => $ledger) {
                $ledger['company_id'] = $this->session->data['company_id'];
                $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
                $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
                $ledger['document_type_id'] = $this->document_type_id;
                $ledger['document_id'] = $purchase_return_id;
                $ledger['document_identity'] = $data['document_identity'];
                $ledger['document_date'] = $data['document_date'];
                $ledger['sort_order'] = $sort_order;
                $ledger['base_currency_id'] = $data['base_currency_id'];
                $ledger['document_currency_id'] = $data['document_currency_id'];
                $ledger['conversion_rate'] = $data['conversion_rate'];
                $ledger['partner_type_id'] = $data['partner_type_id'];
                $ledger['partner_id'] = $data['partner_id'];
                $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);
            }
        
        return $purchase_return_id;
    }

    protected function updateData($primary_key, $data) {
        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['document_date'] = MySqlDate($data['document_date']);
        $data['base_amount'] = $data['net_amount'] * $data['conversion_rate'];

        $purchase_return_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);

        $this->model['purchase_return_detail'] = $this->load->model('inventory/purchase_return_detail');
        $this->model['purchase_return_detail']->deleteBulk($this->getAlias(), array('purchase_return_id' => $purchase_return_id));

        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

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
            'document_id' => $purchase_return_id,
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
        // d(array($partner,$company));

        $this->model['setting']= $this->load->model('common/setting');

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
            'module' => 'inventory',
            'field' => 'purchase_discount_account_id',
        ));
       
        $purchase_discount_account_id = $setting['value'];
        $outstanding_account_id = $partner['outstanding_account_id'];
        // ye wala
        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $outstanding_account_id,
            'document_credit' => 0,
            'document_debit' => $data['net_amount'],
            'credit' => 0,
            'debit' => ($data['net_amount'] * $data['conversion_rate']),
        );
        // ye wala
        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $purchase_discount_account_id,
            'document_credit' => 0,
            'document_debit' => $data['deduction_amount'],
            'credit' => 0,
            'debit' => ($data['deduction_amount'] * $data['conversion_rate'])
        );
        
        foreach ($data['purchase_return_details'] as $sort_order => $detail) {
            $detail['purchase_return_id'] = $purchase_return_id;
            $detail['sort_order'] = $sort_order;
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_total'] = $data['total_amount'] * $data['conversion_rate'];
//            d($sort_order,true);
            $purchase_return_detail_id = $this->model['purchase_return_detail']->add($this->getAlias(), $detail);

            $stock_ledger = array(
                'company_id' => $detail['company_id'],
                'company_branch_id' => $detail['company_branch_id'],
                'fiscal_year_id' => $detail['fiscal_year_id'],
                'document_type_id' => $data['document_type_id'],
                'document_id' => $data['document_id'],
                'document_identity' => $data['document_identity'],
                'document_date' => $data['document_date'],
                'document_detail_id' => $purchase_return_detail_id,
                'warehouse_id' => $detail['warehouse_id'],
                'product_id' => $detail['product_id'],
                'document_unit_id' => $detail['unit_id'],
                'document_qty' => -1 * $detail['qty'],
                'unit_conversion' => 1,
                'base_unit_id' => $detail['unit_id'],
                'base_qty' => -1 * $detail['qty'],
                'document_currency_id' => $detail['document_currency_id'],
                'document_rate' => $detail['rate'],
                'document_amount' => -1 * $detail['amount'],
                'currency_conversion' => $detail['conversion_rate'],
                'base_currency_id' => $detail['base_currency_id'],
                'base_rate' => ($detail['rate'] * $detail['conversion_rate']),
                'base_amount' => -1 * ($detail['amount'] * $detail['conversion_rate']),
            );
            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $stock_ledger);
            $product = $this->model['product']->getRow(array('product_id' => $detail['product_id']));
            // d($product,true);

            if( floatval($detail['amount']) > 0 ){

                    $gl_data[] = array(
                        'document_detail_id' => $purchase_return_detail_id,
                        'ref_document_type_id' => $detail['ref_document_type_id'],
                        'ref_document_identity' => $detail['ref_document_identity'],
                        'coa_id' => $product['inventory_account_id'],
                        'document_credit' => $detail['amount'],
                        'document_debit' => 0,
                        'credit' => ($detail['amount'] * $data['conversion_rate']),
                        'debit' => 0,
                        'remarks' => $detail['remarks'],
                        'product_id' => $detail['product_id'],
                        'qty' => $detail['qty'],
                        'document_amount' => $detail['amount'],
                        'amount' => ($detail['amount'] * $data['conversion_rate']),
                    );

                }

            if( floatval($detail['discount_amount']) > 0 ){

                    $gl_data[] = array(
                        'document_detail_id' => $purchase_return_detail_id,
                        'ref_document_type_id' => $detail['ref_document_type_id'],
                        'ref_document_identity' => $detail['ref_document_identity'],
                        'coa_id' => $purchase_discount_account_id,
                        'document_credit' => 0,
                        'document_debit' => $detail['discount_amount'],
                        'credit' => 0,
                        'debit' => ($detail['discount_amount'] * $data['conversion_rate']),
                        'remarks' => $detail['remarks'],
                        'product_id' => $detail['product_id'],
                        'qty' => $detail['qty'],
                        'document_amount' => $detail['discount_amount'],
                        'amount' => ($detail['discount_amount'] * $data['conversion_rate']),
                    );

                }

            if( floatval($detail['tax_amount']) > 0 ){

                $gl_data[] = array(
                    'document_detail_id' => $purchase_return_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $sale_tax_account_id,
                    'document_credit' => $detail['tax_amount'],
                    'document_debit' => 0,
                    'credit' => ($detail['tax_amount'] * $data['conversion_rate']),
                    'debit' => 0,
                    'remarks' => $detail['remarks'],
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['tax_amount'],
                    'amount' => ($detail['tax_amount'] * $data['conversion_rate']),
                );

            }

        }
            foreach($gl_data as $sort_order => $ledger) {
                $ledger['company_id'] = $this->session->data['company_id'];
                $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
                $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
                $ledger['document_type_id'] = $this->document_type_id;
                $ledger['document_id'] = $purchase_return_id;
                $ledger['document_identity'] = $data['document_identity'];
                $ledger['document_date'] = $data['document_date'];
                $ledger['sort_order'] = $sort_order;
                $ledger['base_currency_id'] = $data['base_currency_id'];
                $ledger['document_currency_id'] = $data['document_currency_id'];
                $ledger['conversion_rate'] = $data['conversion_rate'];
                $ledger['partner_type_id'] = $data['partner_type_id'];
                $ledger['partner_id'] = $data['partner_id'];
                $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);
            }

        return $purchase_return_id;
    }

    protected function deleteData($primary_key) {
        
        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));

        $this->model['purchase_return_detail'] = $this->load->model('inventory/purchase_return_detail');
        $this->model['purchase_return_detail']->deleteBulk($this->getAlias(), array('purchase_return_id' => $primary_key));


        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);        
    }

    public function getProductJson() {
        $search = $this->request->post['q'];
        $page = $this->request->post['page'];

        $this->model['product'] = $this->load->model('inventory/product');
        $rows = $this->model['product']->getProductJson($search, $page);

        echo json_encode($rows);
    }

    

    public function getReferenceDocumentNos() {
        $purchase_return_id = $this->request->get['purchase_return_id'];
        $post = $this->request->post;

        // d($post,true);

        $this->model['document'] = $this->load->model('common/document');
        $where = "company_id=" . $this->session->data['company_id'];
        $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
        $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
        $where .= " AND partner_type_id='" . $post['partner_type_id'] . "'";
        $where .= " AND partner_id='" . $post['partner_id'] . "'";
        $where .= " AND document_type_id='" . $post['ref_document_type_id'] . "'";
        //$where .= " AND document_currency_id='" . $post['document_currency_id'] . "'";
        // $where .= " AND is_post=1";

        $orders = $this->model['document']->getRows($where);

        $html = "";
        $html .= '<option value="">&nbsp;</option>';
        foreach($orders as $goods_received) {
            $html .= '<option value="'.$goods_received['document_identity'].'">'.$goods_received['document_identity']. '</option>';
        }

        //d($goods_received,true);
        $json = array(
            'success' => true,
            'purchase_return_id' => $purchase_return_id,
            'post' => $post,
            'where' => $where,
            'orders' => $orders,
            'html' => $html
        );

        echo json_encode($json);
    }

    public function getReferenceDocument() {
        $purchase_return_id = $this->request->get['purchase_return_id'];
        $post = $this->request->post;
        if($post['ref_document_type_id'] == 1) {
            //Purchase Invoice
            $this->model['purchase_invoice_detail'] = $this->load->model('inventory/purchase_invoice_detail');
            $where = " WHERE company_id=" . $this->session->data['company_id'];
            $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
            $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
            $where .= " AND partner_type_id='" . $post['partner_type_id'] . "'";
            $where .= " AND partner_id='" . $post['partner_id'] . "'";
            $where .= " AND document_identity='" . $post['ref_document_identity'] . "'";
            $where .= " AND document_currency_id='" . $post['document_currency_id'] . "'";
            // $where .= " AND is_post=1";

            $rows = $this->model['purchase_invoice_detail']->getDocumentForPurchaseReturn($where);
            // d($rows,true);
            $html = '';
            $details = array();
            foreach($rows as $row_no => $row) {
                $href = $this->url->link('inventory/purchase_invoice/update', 'token=' . $this->session->data['token'] . '&purchase_invoice_id=' . $row['purchase_invoice_id']);
                $details[$row_no] = $row;
                $details[$row_no]['href'] = $href;
                // $details[$row_no][$row['product_id']]['href'] = $href;
            }

            
        } elseif($post['ref_document_type_id'] == 17) {
            //Goods Received
            $this->model['goods_received_detail'] = $this->load->model('inventory/goods_received_detail');
            $where = "company_id=" . $this->session->data['company_id'];
            $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
            $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
            $where .= " AND partner_type_id='" . $post['partner_type_id'] . "'";
            $where .= " AND partner_id='" . $post['partner_id'] . "'";
            $where .= " AND document_identity='" . $post['ref_document_identity'] . "'";
//            $where .= " AND document_currency_id='" . $post['document_currency_id'] . "'";
//            $where .= " AND is_post=1";

            $rows = $this->model['goods_received_detail']->getRows($where);
            $html = '';
            $details = array();
            foreach($rows as $row_no => $row) {
                $href = $this->url->link('inventory/goods_received/update', 'token=' . $this->session->data['token'] . '&goods_received_id=' . $row['goods_received_id']);
                $details[$row_no] = $row;
                $details[$row_no]['href'] = $href;
            }
        }
        // d($details,true);
        $json = array(
            'success' => true,
            'purchase_invoice_id' => $purchase_return_id,
            'post' => $post,
            'where' => $where,
            'details' => $details);
        echo json_encode($json);
    }

    public function post() {
        $lang = $this->load->language($this->getAlias());
        if (!$this->user->hasPermission('post', $this->getAlias())) {
            $this->session->data['error_warning'] = $lang['error_permission_post'];
        } else {

            // d($this->request->get[$this->getPrimaryKey()],true);

            $data = array(
                'is_post' => 1,
                'post_date' => date('Y-m-d H:i:s'),
                'post_by_id' => $this->session->data['user_id']
            );
            $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
            $row = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));

            // d($row,true);
            if($row['ref_document_identity'] == '')
            {
                $this->model[$this->getAlias()]->edit($this->getAlias(),$this->request->get[$this->getPrimaryKey()],$data);
                $this->model['document'] = $this->load->model('common/document');
                $this->model['document']->edit($this->getAlias(),$this->request->get[$this->getPrimaryKey()],$data);    
            }

            else
            {
                // check if ref document is posted
                // purchase inv
                $this->model['purchase_invoice'] = $this->load->model('inventory/purchase_invoice');
                $PI = $this->model['purchase_invoice']->getRow(array('document_identity' => $row['ref_document_identity'], 'is_post' => 1));
                if(!empty($PI))
                {
                    $this->model[$this->getAlias()]->edit($this->getAlias(),$this->request->get[$this->getPrimaryKey()],$data);
                    $this->model['document'] = $this->load->model('common/document');
                    $this->model['document']->edit($this->getAlias(),$this->request->get[$this->getPrimaryKey()],$data);
                }
                else
                {
                    $this->session->data['error_warning'] = 'Reference Document is not posted!';
                }

            }
        }

        $this->redirect($this->url->link($this->getAlias(), 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL'));
    }

    public function printDocument() {
        $company_id = $this->session->data['company_id'];
        $company_branch_id = $this->session->data['company_branch_id'];
        $fiscal_year_id = $this->session->data['fiscal_year_id'];

        $purchase_return_id = $this->request->get['purchase_return_id'];
        $this->data['lang'] = $this->load->language($this->getAlias());

        $this->model['image'] = $this->load->model('tool/image');
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');

        $company = $this->model['company']->getRow(array('company_id' => $company_id));
        $this->data['company'] = $company;
        if ($company['company_logo'] && file_exists(DIR_IMAGE . $company['company_logo'])) {
            $company_logo = $this->model['image']->resize($company['company_logo'], 75, 75);
        } else {
            $company_logo = "";
        }
        $this->data['company_logo'] = $company_logo;
        $this->data['company_branch'] = $this->model['company_branch']->getRow(array('company_id' => $company_id, 'company_branch_id' => $company_branch_id));

        $this->model['purchase_return'] = $this->load->model('inventory/purchase_return');
        $row = $this->model['purchase_return']->getRow(array('purchase_return_id' => $purchase_return_id));

        $this->model['partner'] = $this->load->model('common/partner');
        $partner = $this->model['partner']->getRow(array('company_id' => $company_id, 'company_branch_id' => $company_branch_id, 'partner_type_id' => $row['partner_type_id'], 'partner_id' => $row['partner_id']));
        //d($partner, true);

        $data['document_date'] = $row['document_date'];
        $data['document_no'] = $row['document_identity'];
        $data['partner_name'] = $partner['name'];

        $this->model['purchase_return_detail'] = $this->load->model('inventory/purchase_return_detail');
        $details = $this->model['purchase_return_detail']->getRows(array('purchase_return_id' => $purchase_return_id));
        foreach($details as $row_no => $detail) {
            $data['details'][$row_no] = $detail;
        }
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

        // PDF to print goods received notes
        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Hira Anwer');
        $pdf->SetTitle('Purchase Return');
        $pdf->SetSubject('Purchase Return');

        //Set Header
        $pdf->data = array(
            'company_name' => $this->data['company_branch']['name'],
            'report_name' => 'Purchase Return',
            'company_logo' => $company_logo,
            'document_date' => $data['document_date'],
            'document_no' => $data['document_no'],
            'partner_name' => $data['partner_name'],
            'company_header_print' => $company_header_print,
            'company_footer_print' => $company_footer_print,
        );
        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(5, 50, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 60);

        // add a page
        $pdf->AddPage();

        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(20, 7, 'Product Code', 1, false, 'C', 1, '', 1);
        $pdf->Cell(50, 7, 'Product Name', 1, false, 'C', 1, '', 1);
        $pdf->Cell(25, 7, 'Warehouse', 1, false, 'C', 1, '', 1);
        $pdf->Cell(12, 7, 'Unit', 1, false, 'C', 1, '', 1);
        $pdf->Cell(15, 7, 'Quantity', 1, false, 'C', 1, '', 1);
        $pdf->Cell(15, 7, 'Rate', 1, false, 'C', 1, '', 1);
        $pdf->Cell(20, 7, 'Dicount Amount', 1, false, 'C', 1, '', 1);
        $pdf->Cell(20, 7, 'Tax Amount', 1, false, 'C', 1, '', 1);
        $pdf->Cell(20, 7, 'Amount', 1, false, 'C', 1, '', 1);
        $pdf->ln(7);




        // set font
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $details = $data['details'];


        $total_amount = 0;
        foreach($details as $detail) {

            $productName = $detail['product_name'];

            if(multili_var_length_check(array($productName), 40)) {
                
                $pdf->Cell(20, 7, $detail['product_code'], 'L', false, 'C', 0, '', 1);
                $pdf->Cell(50, 7, html_entity_decode($detail['product_name']), 'L', false, 'C', 0, '', 1);
                $pdf->Cell(25, 7, html_entity_decode($detail['warehouse']), 'L', false, 'C', 0, '', 1);
                $pdf->Cell(12, 7, $detail['unit'], 'L', false, 'C', 0, '', 1);
                $pdf->Cell(15, 7, number_format($detail['qty'],2), 'L', false, 'C', 0, '', 1);
                $pdf->Cell(15, 7, number_format($detail['rate'],2), 'L', false, 'C', 0, '', 1);
                $pdf->Cell(20, 7, number_format($detail['discount_amount'],2), 'L', false, 'C', 0, '', 1);
                $pdf->Cell(20, 7, number_format($detail['tax_amount'],2), 'L', false, 'C', 0, '', 1);
                $pdf->Cell(20, 7, number_format($detail['amount'],2), 'L,R', false, 'C', 0, '', 1);
                $pdf->ln(7);
                
            } else {

                $arrProduct = splitString($productName, 40);
                $length = max_array_index_count($arrProduct);
                for($index=0; $index <= ($length-1); $index++){
                 
                    if($index==0){
                 
                        $pdf->Cell(20, 7, $detail['product_code'], 'TLR', false, 'C', 0, '', 1);
                        $pdf->Cell(50, 7, html_entity_decode($arrProduct[$index]), 'TLR', false, 'L', 0, '', 1);
                        $pdf->Cell(25, 7, html_entity_decode($detail['warehouse']), 'TLR', false, 'C', 0, '', 1);
                        $pdf->Cell(12, 7, $detail['unit'], 'TLR', false, 'C', 0, '', 1);
                        $pdf->Cell(15, 7, number_format($detail['qty'],2), 'TLR', false, 'C', 0, '', 1);
                        $pdf->Cell(15, 7, number_format($detail['rate'],2), 'TLR', false, 'C', 0, '', 1);
                        $pdf->Cell(20, 7, number_format($detail['discount_amount'],2), 'TLR', false, 'C', 0, '', 1);
                        $pdf->Cell(20, 7, number_format($detail['tax_amount'],2), 'TLR', false, 'C', 0, '', 1);
                        $pdf->Cell(20, 7, number_format($detail['amount'],2), 'TLR', false, 'C', 0, '', 1);

                    } else if($index<($length-1)){

                        $pdf->Cell(20, 7, '', 'LR', false, 'C', 0, '', 1);
                        $pdf->Cell(50, 7, html_entity_decode($arrProduct[$index]), 'LR', false, 'L', 0, '', 1);
                        $pdf->Cell(25, 7, '', 'LR', false, 'C', 0, '', 1);
                        $pdf->Cell(12, 7, '', 'LR', false, 'C', 0, '', 1);
                        $pdf->Cell(15, 7, '', 'LR', false, 'C', 0, '', 1);
                        $pdf->Cell(15, 7, '', 'LR', false, 'C', 0, '', 1);
                        $pdf->Cell(20, 7, '', 'LR', false, 'C', 0, '', 1);
                        $pdf->Cell(20, 7, '', 'LR', false, 'C', 0, '', 1);
                        $pdf->Cell(20, 7, '', 'LR', false, 'C', 0, '', 1);
                 
                    } else {
                 
                        $pdf->Cell(20, 7, '', 'BLR', false, 'C', 0, '', 1);
                        $pdf->Cell(50, 7, html_entity_decode($arrProduct[$index]), 'BLR', false, 'L', 0, '', 1);
                        $pdf->Cell(25, 7, '', 'BLR', false, 'C', 0, '', 1);
                        $pdf->Cell(12, 7, '', 'BLR', false, 'C', 0, '', 1);
                        $pdf->Cell(15, 7, '', 'BLR', false, 'C', 0, '', 1);
                        $pdf->Cell(15, 7, '', 'BLR', false, 'C', 0, '', 1);
                        $pdf->Cell(20, 7, '', 'BLR', false, 'C', 0, '', 1);
                        $pdf->Cell(20, 7, '', 'BLR', false, 'C', 0, '', 1);
                        $pdf->Cell(20, 7, '', 'BLR', false, 'C', 0, '', 1);

                    }
                 
                    $pdf->ln(4);
                
                }

            }

            $total_amount += $detail['amount'];
        }

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);

        $pdf->Ln(-1);
        for ($i = $y; $i <= 200; $i++) {

            $pdf->Ln(1);
            $pdf->Cell(20, 8,'', 'L', false, 'C', 0, '', 1);
            $pdf->Cell(50, 8, '', 'L', false, 'L', 0, '', 1);
            $pdf->Cell(25, 8, '', 'L', false, 'R', 0, '', 1);
            $pdf->Cell(12, 8, '', 'L', false, 'C', 0, '', 1);
            $pdf->Cell(15, 8, '', 'L', false, 'R', 0, '', 1);
            $pdf->Cell(15, 8, '', 'L', false, 'R', 0, '', 1);
            $pdf->Cell(20, 8, '', 'L', false, 'C', 0, '', 1);
            $pdf->Cell(20, 8, '', 'L', false, 'R', 0, '', 1);
            $pdf->Cell(20, 8, '', 'L,R', false, 'R', 0, '', 1);
            $y =$i;
        }
        $pdf->Ln(-1);
        $pdf->Ln(5);
        
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);
        $pdf->Cell(190, 8, '', 'B', false, 'C', 0, '', 0, false, 'M', 'M');
         $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);
        
        $pdf->ln(4);
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->Cell(177, 8, 'Total Amount ' , 1, false, 'R');
        $pdf->Cell(20, 8, number_format($total_amount,2), 1, false, 'R');
        $pdf->ln(6);

        //Close and output PDF document
        $pdf->Output('Purchase Return - '.$data['document_no'].'.pdf', 'I');


    }

}

class PDF extends TCPDF {
    public $data = array();

    //Page header
    public function Header() {
        // Logo
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
        // Set font
        $this->Ln(20);
        $this->SetFont('helvetica', 'B', 15);
        
        // Title
        // $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        // $this->Ln(10);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(15);

        $this->SetFont('helvetica', 'B', 7);
        $this->Cell(25, 5, 'Document No.: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(25, 5, $this->data['document_no'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        
        $this->Cell(110, 5, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        
        $this->Cell(25, 5, 'Document Date: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(20, 5, stdDate($this->data['document_date']), 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->ln(5);
        $this->Cell(25, 5, 'Partner Name : ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(0, 5, html_entity_decode($this->data['partner_name']), 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // $this->Cell(5, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');

        // $this->ln(6);

    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        
        $this->SetY(-25);
        $y = $this->GetY();

        if($this->data['company_footer_print'] != '') {
            $image_file = DIR_IMAGE.$this->data['company_footer_print'];
            // $this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
            $this->Image($image_file, 5, ($y-10), 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        // Set font
        $this->SetFont('helvetica', '', 8);
        // Page number
        $this->Ln(-8);
        $this->Cell(10, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 7, 'Prepared By', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(40, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 7, 'Production Manager',  'T', false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(50, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 7, 'Finance Manager',  'T', false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->SetFont('helvetica', '', 7);

      //  $this->Cell(0, 5, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

    }

}

?>