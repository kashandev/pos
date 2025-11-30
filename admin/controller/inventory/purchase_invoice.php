<?php

class ControllerInventoryPurchaseInvoice extends HController {

    protected $document_type_id = 1;

    protected function getAlias() {
        return 'inventory/purchase_invoice';
    }

    protected function getPrimaryKey() {
        return 'purchase_invoice_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }


    
 
     public function borderSet()
    {
        $pdf->SetLineStyle(array('cap' => 'butt', 'join' => 'miter', 'dash' => 2));
        $pdf->Cell(3 ,6, '', 'R', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->SetLineStyle(array('cap' => 'butt', 'join' => 'miter', 'dash' => 0));
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

            $actions[] = array(
                'text' => $lang['print_barcode'],
                'target' => '_blank',
                'href' => $this->url->link($this->getAlias() . '/printLabels', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),    
                'btn_class' => 'btn btn-info btn-xs',
                'class' => 'fa fa-print'
            );

            $actions[] = array(
                'text' => $lang['print_purchase_invoice'],
                'target' => '_blank',
                'href' => $this->url->link($this->getAlias() . '/printPurchaseInvoice', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()] . '&type=purchase_invoice' , 'SSL'),
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

            for ($i = 0; $i < count($aColumns); $i++) {
                if ($aColumns[$i] == 'action') {
                    $row[] = $strAction;
                } elseif ($aColumns[$i] == 'created_at') {
                    $row[] = stdDateTime($aRow['created_at']);
                } elseif ($aColumns[$i] == 'net_amount') {
                    $row[] = (int)($aRow['net_amount']);
                } elseif ($aColumns[$i] == 'document_date') {
                    $row[] = stdDate($aRow['document_date']);
                } elseif ($aColumns[$i] == 'check_box') {
                    if($aRow['is_post']==0) {
                        $row[] = '<input type="checkbox" name="selected[]" value="' . $aRow[$this->getPrimaryKey()] . '" />';
                    } else {
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

        $this->model['product_category'] = $this->load->model('inventory/product_category');
        $this->data['product_categories'] = $this->model['product_category']->getRows(array('company_id' => $this->session->data['company_id']),array('name'));

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
        //d($this->data, true);

        $this->data['partner_types'] = $this->session->data['partner_types'];
        

        $this->data['document_date'] = stdDate();
        if (isset($this->request->get['purchase_invoice_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $result = $this->model[$this->getAlias()]->getRow(array('purchase_invoice_id' => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                if ($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }
            $this->model['purchase_invoice_detail'] = $this->load->model('inventory/purchase_invoice_detail');
            $this->data['purchase_invoice_details'] = $this->model['purchase_invoice_detail']->getRows(array('purchase_invoice_id' => $this->request->get['purchase_invoice_id']), array('sort_order'));
        }

        $this->data['partner_type_id'] = 1;
        $this->data['href_get_partner_json'] = $this->url->link($this->getAlias() . '/getPartnerJson', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_partner'] = $this->url->link($this->getAlias() . '/getPartner', 'token=' . $this->session->data['token']);

        $this->data['href_get_product_json'] = $this->url->link($this->getAlias() . '/getProductJson', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['href_check_exists_product'] = $this->url->link('common/function/checkExistProduct', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['href_get_ref_document_no'] = $this->url->link($this->getAlias() . '/getReferenceDocumentNos', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_ref_document'] = $this->url->link($this->getAlias() . '/getReferenceDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print'] = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print_purchase_invoice'] = $this->url->link($this->getAlias() . '/printPurchaseInvoice', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()] . '&type=purchase_invoice', 'SSL');

        
        $this->data['action_print_barcode'] = $this->url->link($this->getAlias() . '/printLabels', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['href_product_form'] = $this->url->link('inventory/product/insert', 'token=' . $this->session->data['token'].'&popup=popup', 'SSL');

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

    protected function insertData($data) {
        $this->model['purchase_invoice_detail'] = $this->load->model('inventory/purchase_invoice_detail');
        foreach ($data['purchase_invoice_details'] as $key => $value) {
            // d($value);
            $temp = $this->model['purchase_invoice_detail']->getRow(array('company_id' => $this->session->data['company_id'], 'ref_document_type_id' => $value['ref_document_type_id'], 'ref_document_identity' => $value['ref_document_identity']));
            if(!empty($temp) && $value['ref_document_type_id'] != 0 && !empty($value['ref_document_identity']))
            {
                $previous_invoice[] = $temp;
            }
        }

        if($previous_invoice)
        {
            return $this->session->data['error_warning'] = 'Purchase Invoice already created for this Good Receive Note';
        }
        // d($previous_invoice,true);
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['document_type'] = $this->load->model('common/document_type');
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['product']= $this->load->model('inventory/product');
        $this->model['setting']= $this->load->model('common/setting');

        //d($data, true);
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
        $purchase_invoice_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $data['document_id'] = $purchase_invoice_id;

        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $purchase_invoice_id,
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

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'cash_account_id',
        ));
        $cash_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'purchase_discount_account_id',
        ));
        $purchase_discount_account_id = $setting['value'];

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
            'field' => 'gr_ir_account_id',
        ));
        $gr_ir_account_id = $setting['value'];


        // Freight Account
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'freight_account_id',
        ));
        $freight_account_id = $setting['value'];


        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'inventory_account_id',
        ));

        $inventory_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'inventory_account_id',
        ));

        $cogs_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'revenue_account_id',
        ));

        $revenue_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'adjustment_account_id',
        ));

        $adjustment_account_id = $setting['value'];


        $partner = $this->model['partner']->getRow(array('partner_type_id' => $data['partner_type_id'], 'partner_id' => $data['partner_id']));
        $outstanding_account_id = $partner['outstanding_account_id'];

        foreach ($data['purchase_invoice_details'] as $sort_order => $detail) {
            $product = $this->model['product']->getRow(['product_code' => $detail['product_code']]);
            if(empty($product)){
                $new_product = [];
                $new_product['company_id'] = $this->session->data['company_id'];     
                $new_product['product_category_id'] = $detail['product_category_id'];         
                $new_product['product_code'] = $detail['product_code'];
                $new_product['name'] = $detail['product_name'];
                $new_product['reorder_quantity'] = $detail['qty'];
                $new_product['cost_price'] = $detail['rate'];
                $new_product['sale_price'] = $detail['sale_rate'];
                $new_product['inventory_account_id'] = $inventory_account_id;
                $new_product['cogs_account_id'] = $cogs_account_id;
                $new_product['revenue_account_id'] = $revenue_account_id;
                $new_product['adjustment_account_id'] = $adjustment_account_id;
          
                $product_id = $this->model['product']->add('inventory/product', $new_product);
                $detail['purchase_invoice_id'] = $purchase_invoice_id;
                $detail['product_id'] = $product_id;
                $detail['product_category_id'] = $detail['product_category_id'];
                $detail['product_code'] = $detail['product_code'];             
                $detail['sort_order'] = $sort_order;
                $detail['company_id'] = $this->session->data['company_id'];
                $detail['company_branch_id'] = $this->session->data['company_branch_id'];
                $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
                $detail['document_currency_id'] = $data['document_currency_id'];
                $detail['base_currency_id'] = $data['base_currency_id'];
                $detail['conversion_rate'] = $data['conversion_rate'];
                $detail['base_total'] = $detail['total_amount'] * $data['conversion_rate'];
               }
              else {

                $detail['purchase_invoice_id'] = $purchase_invoice_id;            
                $detail['sort_order'] = $sort_order;
                $detail['company_id'] = $this->session->data['company_id'];
                $detail['company_branch_id'] = $this->session->data['company_branch_id'];
                $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
                $detail['document_currency_id'] = $data['document_currency_id'];
                $detail['base_currency_id'] = $data['base_currency_id'];
                $detail['conversion_rate'] = $data['conversion_rate'];
                $detail['base_total'] = $detail['total_amount'] * $data['conversion_rate'];


               $document_datetime = date('Y-m-d H:i:s',strtotime($data['document_date'].date('H:i:s')));
               $product = $this->model['product']->getRow(['product_id' => $detail['product_id']]);
               $update_sale_rate = [];
               $update_sale_rate['sale_price'] = $detail['sale_rate'];
               $product = $this->model['product']->edit('inventory/product', $detail['product_id'], $update_sale_rate);

            }

            $purchase_invoice_detail_id = $this->model['purchase_invoice_detail']->add($this->getAlias(), $detail);
            if( ($document_datetime >=  $product['document_datetime']) || empty($product['document_datetime'])){
                $product_update_date = [];
                $product_update_date['cost_price'] = $detail['rate'];
                $product_update_date['document_datetime'] = $document_datetime;
                $product = $this->model['product']->edit('inventory/product', $detail['product_id'], $product_update_date);
            }

            if($detail['ref_document_type_id'] == 17) {

                if(floatval($detail['amount']) > 0) {
                    $gl_data[] = array(
                        'document_detail_id' => $purchase_invoice_detail_id,
                        'ref_document_type_id' => $detail['ref_document_type_id'],
                        'ref_document_id' => $detail['ref_document_id'],
                        'ref_document_identity' => $detail['ref_document_identity'],
                        'coa_id' => $gr_ir_account_id,
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
            } else {
                
                $stock_ledger = array(
                    'company_id' => $detail['company_id'],
                    'company_branch_id' => $detail['company_branch_id'],
                    'fiscal_year_id' => $detail['fiscal_year_id'],
                    'document_type_id' => $data['document_type_id'],
                    'document_id' => $data['document_id'],
                    'document_identity' => $data['document_identity'],
                    'document_date' => $data['document_date'],
                    'document_detail_id' => $purchase_invoice_detail_id,
                    'warehouse_id' => $detail['warehouse_id'],
                    'product_id' => $detail['product_id'],
                    'document_unit_id' => $detail['unit_id'],
                    'document_qty' => $detail['qty'],
                    'unit_conversion' => 1,
                    'base_unit_id' => $detail['unit_id'],
                    'base_qty' => $detail['qty'],
                    'container_no' => $detail['container_no'],
                    'batch_no' => $detail['batch_no'],
                    'document_currency_id' => $detail['document_currency_id'],
                    'document_rate' => $detail['rate'],
                    'document_amount' => $detail['amount'],
                    'currency_conversion' => $detail['conversion_rate'],
                    'base_currency_id' => $detail['base_currency_id'],
                    'base_rate' => ($detail['rate'] * $detail['conversion_rate']),
                    'base_amount' => ($detail['amount'] * $detail['conversion_rate']),
                );
            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $stock_ledger);

            if(empty($product)){
                $product = $this->model['product']->getRow(array('product_id' => $product_id));
            }
            else {
                $product = $this->model['product']->getRow(array('product_id' => $detail['product_id'])); 
            }
                
                if(floatval($detail['amount']) > 0) {
                    $gl_data[] = array(
                        'document_detail_id' => $purchase_invoice_detail_id,
                        'ref_document_type_id' => $detail['ref_document_type_id'],
                        'ref_document_identity' => $detail['ref_document_identity'],
                        'coa_id' => $product['inventory_account_id'],
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
            }

            if(floatval($detail['discount_amount']) > 0) {
                $gl_data[] = array(
                    'document_detail_id' => $purchase_invoice_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $purchase_discount_account_id,
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

            if(floatval($detail['tax_amount']) > 0) {
                $gl_data[] = array(
                    'document_detail_id' => $purchase_invoice_detail_id,
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

        if(floatval($data['discount']) > 0) {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $purchase_discount_account_id,
                'document_debit' => 0,
                'document_credit' => $data['discount'],
                'debit' => 0,
                'credit' => ($data['discount'] * $data['conversion_rate'])
            );
        }

         if( floatval($data['freight_master']) > 0 )
            {
                // Freight DR
                $gl_data[] = array(
                    'ref_document_type_id' => $this->document_type_id,
                    'ref_document_identity' => $data['document_identity'],
                    'coa_id' => $freight_account_id,
                    'document_debit' => $data['freight_master'],
                    'document_credit' => 0,
                    'debit' => $data['freight_master'],
                    'credit' => 0,
                );
            }

        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $outstanding_account_id,
            'document_debit' => 0,
            'document_credit' => $data['net_amount'],
            'debit' => 0,
            'credit' => ($data['net_amount'] * $data['conversion_rate']),
        );

        if($data['invoice_type']=='Cash') {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $outstanding_account_id,
                'document_credit' => 0,
                'document_debit' => $data['net_amount'],
                'credit' => 0,
                'debit' => ($data['net_amount'] * $data['conversion_rate']),
            );

            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $cash_account_id,
                'document_debit' => 0,
                'document_credit' => $data['net_amount'],
                'debit' => 0,
                'credit' => ($data['net_amount'] * $data['conversion_rate']),
            );
        }

        //d($gl_data, true);
        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $purchase_invoice_id;
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
          return $purchase_invoice_id;
    }

    protected function updateData($primary_key, $data) {
        $this->model['purchase_invoice_detail'] = $this->load->model('inventory/purchase_invoice_detail');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['product']= $this->load->model('inventory/product');
        $this->model['setting']= $this->load->model('common/setting');
        $this->model['purchase_invoice_detail'] = $this->load->model('inventory/purchase_invoice_detail');

        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['document_date'] = MySqlDate($data['document_date']);
        $data['base_amount'] = $data['net_amount'] * $data['conversion_rate'];
        $purchase_invoice_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        $data['document_id'] = $purchase_invoice_id;

        $this->model['purchase_invoice_detail']->deleteBulk($this->getAlias(), array('purchase_invoice_id' => $purchase_invoice_id));
        $this->model['document']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));
        $this->model['ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $purchase_invoice_id,
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

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'cash_account_id',
        ));
        $cash_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'purchase_discount_account_id',
        ));
        $purchase_discount_account_id = $setting['value'];

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
            'field' => 'gr_ir_account_id',
        ));
        $gr_ir_account_id = $setting['value'];

        // Freight Account
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'freight_account_id',
        ));
        $freight_account_id = $setting['value'];



        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'inventory_account_id',
        ));

        $inventory_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'inventory_account_id',
        ));

        $cogs_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'revenue_account_id',
        ));

        $revenue_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'adjustment_account_id',
        ));

        $adjustment_account_id = $setting['value'];



        $partner = $this->model['partner']->getRow(array('partner_type_id' => $data['partner_type_id'], 'partner_id' => $data['partner_id']));
        $outstanding_account_id = $partner['outstanding_account_id'];

        foreach ($data['purchase_invoice_details'] as $sort_order => $detail) {

            $product = $this->model['product']->getRow(['product_code' => $detail['product_code']]);
            if(empty($product)){
                $new_product = [];
                $new_product['company_id'] = $this->session->data['company_id'];     
                $new_product['product_category_id'] = $detail['product_category_id'];         
                $new_product['product_code'] = $detail['product_code'];
                $new_product['name'] = $detail['product_name'];
                $new_product['reorder_quantity'] = $detail['qty'];
                $new_product['cost_price'] = $detail['rate'];
                $new_product['sale_price'] = $detail['sale_rate'];
                $new_product['inventory_account_id'] = $inventory_account_id;
                $new_product['cogs_account_id'] = $cogs_account_id;
                $new_product['revenue_account_id'] = $revenue_account_id;
                $new_product['adjustment_account_id'] = $adjustment_account_id;
          
                $product_id = $this->model['product']->add('inventory/product', $new_product);
                $detail['purchase_invoice_id'] = $purchase_invoice_id;
                $detail['product_id'] = $product_id;
                $detail['product_category_id'] = $detail['product_category_id'];
                $detail['product_code'] = $detail['product_code'];             
                $detail['sort_order'] = $sort_order;
                $detail['company_id'] = $this->session->data['company_id'];
                $detail['company_branch_id'] = $this->session->data['company_branch_id'];
                $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
                $detail['document_currency_id'] = $data['document_currency_id'];
                $detail['base_currency_id'] = $data['base_currency_id'];
                $detail['conversion_rate'] = $data['conversion_rate'];
                $detail['base_total'] = $detail['total_amount'] * $data['conversion_rate'];
               }
               else {

                $detail['purchase_invoice_id'] = $purchase_invoice_id;            
                $detail['sort_order'] = $sort_order;
                $detail['company_id'] = $this->session->data['company_id'];
                $detail['company_branch_id'] = $this->session->data['company_branch_id'];
                $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
                $detail['document_currency_id'] = $data['document_currency_id'];
                $detail['base_currency_id'] = $data['base_currency_id'];
                $detail['conversion_rate'] = $data['conversion_rate'];
                $detail['base_total'] = $detail['total_amount'] * $data['conversion_rate'];


               $document_datetime = date('Y-m-d H:i:s',strtotime($data['document_date'].date('H:i:s')));
               $product = $this->model['product']->getRow(['product_id' => $detail['product_id']]);
               $update_sale_rate = [];
               $update_sale_rate['sale_price'] = $detail['sale_rate'];
               $product = $this->model['product']->edit('inventory/product', $detail['product_id'], $update_sale_rate);

            }

            $purchase_invoice_detail_id = $this->model['purchase_invoice_detail']->add($this->getAlias(), $detail);
            if( ($document_datetime >=  $product['document_datetime']) || empty($product['document_datetime'])){
                $product_update_date = [];
                $product_update_date['cost_price'] = $detail['rate'];
                $product_update_date['document_datetime'] = $document_datetime;
                $product = $this->model['product']->edit('inventory/product', $detail['product_id'], $product_update_date);
            }


            // $detail['purchase_invoice_id'] = $purchase_invoice_id;
            // $detail['sort_order'] = $sort_order;
            // $detail['company_id'] = $this->session->data['company_id'];
            // $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            // $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            // $detail['document_currency_id'] = $data['document_currency_id'];
            // $detail['base_currency_id'] = $data['base_currency_id'];
            // $detail['conversion_rate'] = $data['conversion_rate'];
            // $detail['base_total'] = $detail['total_amount'] * $data['conversion_rate'];
            // $purchase_invoice_detail_id = $this->model['purchase_invoice_detail']->add($this->getAlias(), $detail);

            // $document_datetime = date('Y-m-d H:i:s',strtotime($data['document_date'].date('H:i:s')));
            // $product = $this->model['product']->getRow(['product_id' => $detail['product_id']]);
            //     $update_sale_rate = [];
            //     $update_sale_rate['sale_price'] = $detail['sale_rate'];
            //     $product = $this->model['product']->edit('inventory/product', $detail['product_id'], $update_sale_rate);
        

            // if( ($document_datetime >=  $product['document_datetime']) || empty($product['document_datetime'])){
            //     $product_update_date = [];
            //     $product_update_date['cost_price'] = $detail['rate'];
            //     $product_update_date['document_datetime'] = $document_datetime;
            //     $product = $this->model['product']->edit('inventory/product', $detail['product_id'], $product_update_date);
            // }

            if($detail['ref_document_type_id'] == 17) {
                if(floatval($detail['amount']) > 0) {

                    $gl_data[] = array(
                        'document_detail_id' => $purchase_invoice_detail_id,
                        'ref_document_type_id' => $detail['ref_document_type_id'],
                        'ref_document_id' => $detail['ref_document_id'],
                        'ref_document_identity' => $detail['ref_document_identity'],
                        'coa_id' => $gr_ir_account_id,
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
            } else {

                $stock_ledger = array(
                    'company_id' => $detail['company_id'],
                    'company_branch_id' => $detail['company_branch_id'],
                    'fiscal_year_id' => $detail['fiscal_year_id'],
                    'document_type_id' => $data['document_type_id'],
                    'document_id' => $data['document_id'],
                    'document_identity' => $data['document_identity'],
                    'document_date' => $data['document_date'],
                    'document_detail_id' => $purchase_invoice_detail_id,
                    'warehouse_id' => $detail['warehouse_id'],
                    'product_id' => $detail['product_id'],
                    'document_unit_id' => $detail['unit_id'],
                    'document_qty' => $detail['qty'],
                    'unit_conversion' => 1,
                    'base_unit_id' => $detail['unit_id'],
                    'base_qty' => $detail['qty'],
                    'container_no' => $detail['container_no'],
                    'batch_no' => $detail['batch_no'],
                    'document_currency_id' => $detail['document_currency_id'],
                    'document_rate' => $detail['rate'],
                    'document_amount' => $detail['amount'],
                    'currency_conversion' => $detail['conversion_rate'],
                    'base_currency_id' => $detail['base_currency_id'],
                    'base_rate' => ($detail['rate'] * $detail['conversion_rate']),
                    'base_amount' => ($detail['amount'] * $detail['conversion_rate']),
                );
                $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $stock_ledger);

          
              if(empty($product)){
                $product = $this->model['product']->getRow(array('product_id' => $product_id));
              }
              else {
                $product = $this->model['product']->getRow(array('product_id' => $detail['product_id'])); 
              }
                if(floatval($detail['amount']) > 0) {
                
                    $gl_data[] = array(
                        'document_detail_id' => $purchase_invoice_detail_id,
                        'ref_document_type_id' => $detail['ref_document_type_id'],
                        'ref_document_identity' => $detail['ref_document_identity'],
                        'coa_id' => $product['inventory_account_id'],
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
            }

            if(floatval($detail['discount_amount']) > 0) {
                $gl_data[] = array(
                    'document_detail_id' => $purchase_invoice_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $purchase_discount_account_id,
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

            if(floatval($detail['tax_amount']) > 0) {
                $gl_data[] = array(
                    'document_detail_id' => $purchase_invoice_detail_id,
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

        if(floatval($data['discount']) > 0) {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $purchase_discount_account_id,
                'document_debit' => 0,
                'document_credit' => $data['discount'],
                'debit' => 0,
                'credit' => ($data['discount'] * $data['conversion_rate'])
            );
        }


         if( floatval($data['freight_master']) > 0 )
            {
                // Freight DR
                $gl_data[] = array(
                    'ref_document_type_id' => $this->document_type_id,
                    'ref_document_identity' => $data['document_identity'],
                    'coa_id' => $freight_account_id,
                    'document_debit' => $data['freight_master'],
                    'document_credit' => 0,
                    'debit' => $data['freight_master'],
                    'credit' => 0,
                );
            }

        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $outstanding_account_id,
            'document_debit' => 0,
            'document_credit' => $data['net_amount'],
            'debit' => 0,
            'credit' => ($data['net_amount'] * $data['conversion_rate']),
        );

        if($data['invoice_type']=='Cash') {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $outstanding_account_id,
                'document_credit' => 0,
                'document_debit' => $data['net_amount'],
                'credit' => 0,
                'debit' => ($data['net_amount'] * $data['conversion_rate']),
            );

            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $cash_account_id,
                'document_debit' => 0,
                'document_credit' => $data['net_amount'],
                'debit' => 0,
                'credit' => ($data['net_amount'] * $data['conversion_rate']),
            );
        }

        //d($gl_data, true);
        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $purchase_invoice_id;
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
        return $purchase_invoice_id;
    }

    protected function deleteData($primary_key) {

        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->deleteBulk($this->getAlias(), array('document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['purchase_invoice_detail'] = $this->load->model('inventory/purchase_invoice_detail');
        $this->model['purchase_invoice_detail']->deleteBulk($this->getAlias(), array('purchase_invoice_id' => $primary_key));
        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }


    public function getProductJson() {
        $search = $this->request->post['q'];
        $page = $this->request->post['page'];

        $this->model['product'] = $this->load->model('inventory/product');
        $rows = $this->model['product']->getProductJson($search, $page);

        echo json_encode($rows);
    }

    
     public function checkExistProduct() {
        $product_code = $this->request->post['product_code'];
        $this->model['function'] = $this->load->model('common/function');
        $check_exist_product = $this->model['function']->checkExistProduct($product_code);
        echo json_encode($rows);
    }



    public function getReferenceDocumentNos() {
        $purchase_invoice_id = $this->request->get['purchase_invoice_id'];
        $post = $this->request->post;
        //d(array($goods_received_id, $post), true);

        //Purchase Order
        $this->model['goods_received'] = $this->load->model('inventory/goods_received');
        $where = "company_id=" . $this->session->data['company_id'];
        $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
        $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
        $where .= " AND partner_type_id='" . $post['partner_type_id'] . "'";
        $where .= " AND partner_id='" . $post['partner_id'] . "'";
//        $where .= " AND is_post=1";
        // d($where, true);
        $purchase_orders = $this->model['goods_received']->getGoodsReceiveds($where,$purchase_invoice_id);
        foreach($purchase_orders as $purchase_order_id => $purchase_order) {
            foreach($purchase_order['products'] as $product_id => $product) {
                if($product['order_qty'] <= $product['utilized_qty']) {
                    unset($purchase_order['products'][$product_id]);
                }
            }
            if(empty($purchase_order['products'])) {
                unset($purchase_orders[$purchase_order_id]);
            }
        }

//        $html = "";
//        if(count($purchase_orders) != 1) {
//            $html .= '<option value="">&nbsp;</option>';
//        }
        $html = "";
        $html .= '<option value="">&nbsp;</option>';
        foreach($purchase_orders as $purchase_order_id => $purchase_order) {

                if($purchase_order['purchase_order_id']==$post['ref_document_id']) {
                    $html .= '<option value="'.$purchase_order_id.'" selected="true">'.$purchase_order['document_identity'].' '.'('.$purchase_order['manual_ref_no'].')'. '</option>';
                } else {
                    $html .= '<option value="'.$purchase_order_id.'">'.$purchase_order['document_identity'].' '.'('.$purchase_order['manual_ref_no'].')'. '</option>';
                }

        }

       // d($html,true);
        $json = array(
            'success' => true,
            'purchase_invoice_id' => $purchase_invoice_id,
            'post' => $post,
            'where' => $where,
            'html' => $html
        );

        echo json_encode($json);
    }

    public function getReferenceDocument() {
        $purchase_invoice_id = $this->request->get['purchase_invoice_id'];
        $post = $this->request->post;

        //Purchase Order
        $this->model['goods_received'] = $this->load->model('inventory/goods_received');
        $where = "company_id=" . $this->session->data['company_id'];
        $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
        $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
        $where .= " AND partner_id='" . $post['partner_id'] . "'";
        $where .= " AND document_identity='" . $post['ref_document_identity'] . "'";

        $PO = $this->model['goods_received']->getRow($where);

        $purchase_orders = $this->model['goods_received']->getGoodsReceiveds($where,$purchase_invoice_id);
        $purchase_order = $purchase_orders[$post['ref_document_identity']];
        //        d($purchase_order,true);


        $details = array();
        $row_no = 0;
        foreach($purchase_order['products'] as $product) {
//d($product);
            if($product['order_qty'] - $product['utilized_qty'] > 0)
            {

                $href = $this->url->link('inventory/goods_received/update', 'token=' . $this->session->data['token'] . '&goods_received_id=' . $purchase_order['goods_received_id']);
                $details[$row_no] = $product;
                $details[$row_no]['ref_document_identity'] = $purchase_order['document_identity'];
                $details[$row_no]['row_identity'] = $purchase_order['document_identity'].'-'.$product['product_code'];
                $details[$row_no]['href'] = $href;
                $details[$row_no]['balanced_qty'] = ($product['order_qty'] - $product['utilized_qty']);
                $details[$row_no]['utilized_qty'] = ($product['order_qty'] - $product['utilized_qty']);

                $row_no++;
//d($details,true);
            }
        }

        $purchase_order['products'] = $details;
//d($purchase_order['products'],true);
        $json = array(
            'success' => true,
            'purchase_invoice_id' => $purchase_invoice_id,
            'post' => $post,
            'data' => $purchase_order,
            'PO' => $PO,
        );
//        d($json,true);

        echo json_encode($json);
    }


  /*  public function printDocument() {
        $company_id = $this->session->data['company_id'];
        $company_branch_id = $this->session->data['company_branch_id'];
        $fiscal_year_id = $this->session->data['fiscal_year_id'];

        $purchase_invoice_id = $this->request->get['purchase_invoice_id'];
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

        $this->model['purchase_invoice'] = $this->load->model('inventory/purchase_invoice');
        $row = $this->model['purchase_invoice']->getRow(array('purchase_invoice_id' => $purchase_invoice_id));

        $this->model['partner'] = $this->load->model('common/partner');
        $partner = $this->model['partner']->getRow(array('company_id' => $company_id, 'company_branch_id' => $company_branch_id, 'partner_type_id' => $row['partner_type_id'], 'partner_id' => $row['partner_id']));
        //d($partner, true);

        $this->data['document_date'] = $row['document_date'];
        $this->data['document_no'] = $row['document_identity'];
        $this->data['partner_name'] = $partner['name'];
        $this->data['phone_no'] = $partner['phone'];
        $this->data['address'] = $partner['address'];

        $this->model['purchase_invoice_detail'] = $this->load->model('inventory/purchase_invoice_detail');
        $details = $this->model['purchase_invoice_detail']->getRows(array('purchase_invoice_id' => $purchase_invoice_id));
        foreach($details as $row_no => $detail) {
            $this->data['details'][$row_no] = $detail;
        }
        //d($row,$detail,true);
        $this->template = 'inventory/purchase_invoice_print.tpl';
        $contents = $this->render();

        try
        {
            // init HTML2PDF
            $html2pdf = new HTML2PDF('L', 'A5', 'en', true, 'UTF-8', array(0, 0, 0, 0));

            // display the full page
            $html2pdf->pdf->SetDisplayMode('fullpage');

            // convert
            $html2pdf->writeHTML($contents);

            // send the PDF
            $html2pdf->Output('Purchase Invoice.pdf');
        } catch(HTML2PDF_exception $e) {
            echo $e;
            exit;
        }
    }

  */

//     public function printDocument() {

//         $post = $this->request->post;
//         $lang = $this->load->language($this->getAlias());

//         $purchase_invoice_id = $this->request->get['purchase_invoice_id'];
//        // d($purchase_invoice_id,true);
//         $this->model['company'] = $this->load->model('setup/company');
//         //$this->model['company_branch'] = $this->load->model('setup/company_branch');
//         $this->model['purchase_invoice'] = $this->load->model('inventory/purchase_invoice');
//         $this->model['purchase_invoice_detail'] = $this->load->model('inventory/purchase_invoice_detail');
//         $this->model['partner'] = $this->load->model('common/partner');

//         $company_id = $this->session->data['company_id'];

//         $purchase_invoice =  $this->model['purchase_invoice']->getRow(array('purchase_invoice_id'=>$purchase_invoice_id));
//         $purchase_invoice_details = $this->model['purchase_invoice_detail']->getRows(array('purchase_invoice_id'=>$purchase_invoice_id));
//         $Partner = $this->model['partner']->getRow(array('partner_type_id' => $purchase_invoice['partner_type_id'],'partner_id' => $purchase_invoice['partner_id']));



//         $fiscal_year_id = $this->session->data['fiscal_year_id'];



//       $company = $this->model['company']->getRow(array('company_id' => $company_id));
//       $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

//         // set document information
//         $pdf->SetCreator(PDF_CREATOR);
//         $pdf->SetAuthor('Aamir Shakil');
//         $pdf->SetTitle('Purchase Invoice');
//         $pdf->SetSubject('Purchase Invoice');

//         //Set Header

//         $pdf->data = array(
//             'company_name' => $company['name'],
//             //'company_address' => $company_branch,
//              'report_name' => 'Purchase Invoice',

//         );



//         // set margins
//         //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//         $pdf->SetMargins(10, 2, 2);
//         $pdf->SetHeaderMargin(10);
//         $pdf->SetFooterMargin(2);

//         // set auto page breaks
//         //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

//         // set font
//         $pdf->SetFont('helvetica', '', 10);

//         // add a page
//         $pdf->AddPage();

//         $pdf->SetTextColor(0,0,0);

//         $pdf->Ln(40);
//         $pdf->Cell(20,10,'Invoice No : ',0,false,'L',0,'',0,false,'M','M');
//         $pdf->Cell(20,10,$purchase_invoice['document_identity'],0,false,'L',0,'',0,false,'M','M');

//         $pdf->Cell(120,10,'Invoice Date : ',0,false,'R',0,'',0,false,'M','M');
//         $pdf->Cell(20,10,$purchase_invoice['document_date'],0,false,'L',0,'',0,false,'M','M');

//         $pdf->Ln(5);
//         $pdf->Cell(30,10,'Supplier Name : ',0,false,'L',0,'',0,false,'M','M');
//         $pdf->Cell(20,10,$Partner['name'],0,false,'L',0,'',0,false,'M','M');

//         $pdf->Ln(5);
//         $pdf->Cell(20,10,'Remarks : ',0,false,'L',0,'',0,false,'M','M');
//         $pdf->Cell(20,10,$purchase_invoice['remarks'],0,false,'L',0,'',0,false,'M','M');

//         $pdf->Ln(12);

//         $pdf->SetFont('helvetica', '', 8);
//         $pdf->SetFillColor(255, 255, 255);
//         $pdf->SetTextColor(0, 0, 0);
//         $pdf->Cell(60, 8, 'Product', 1, false, 'C', 1, '', 1);
//         $pdf->Cell(30, 8, 'Warehouse', 1, false, 'C', 1, '', 1);
//         $pdf->Cell(30, 8, 'Qty', 1, false, 'C', 1, '', 1);
//         $pdf->Cell(20, 8, 'Unit', 1, false, 'C', 1, '', 1);
//         $pdf->Cell(20, 8, 'Rate', 1, false, 'C', 1, '', 1);
//         $pdf->Cell(30, 8, 'Amount', 1, false, 'C', 1, '', 1);

//         $pdf->SetFillColor(255, 255, 255);
//         $pdf->SetTextColor(0, 0, 0);

//         $total_amount = 0;
//         $total_discount = 0;
//         $total_tax = 0;
//         $total_net_amount = 0;

//         foreach($purchase_invoice_details as $details){
//             $pdf->Ln(8);
//             $pdf->Cell(60, 8, $details['product_name'], 'L', false, 'L', 1, '', 1);
//             $pdf->Cell(30, 8, $details['warehouse'], 'L', false, 'C', 1, '', 1);
//             $pdf->Cell(30, 8, $details['qty'], 'L', false, 'C', 1, '', 1);
//             $pdf->Cell(20, 8, $details['unit'], 'L', false, 'C', 1, '', 1);
//             $pdf->Cell(20, 8, $details['rate'], 'L', false, 'C', 1, '', 1);
//             $pdf->Cell(30, 8, $details['amount'], 'L,R', false, 'C', 1, '', 1);

//             $total_amount += $details['amount'];
//             $total_discount += $details['discount_amount'];
//             $total_tax += $details['tax_amount'];
//             $total_net_amount += $details['gross_amount'];
//         }

//         $x = $pdf->GetX();
//         $y = $pdf->GetY();
//         for ($i = $y; $i <= 200; $i++) {

//             $pdf->Ln(1);
//             $pdf->Cell(60, 8,'', 'L', false, 'C', 0, '', 1);
//             $pdf->Cell(30, 8, '', 'L', false, 'L', 0, '', 1);
//             $pdf->Cell(30, 8, '', 'L', false, 'R', 0, '', 1);
//             $pdf->Cell(20, 8, '', 'L', false, 'C', 0, '', 1);
//             $pdf->Cell(20, 8, '', 'L', false, 'R', 0, '', 1);
//             $pdf->Cell(30, 8, '', 'L,R', false, 'R', 0, '', 1);
//             $y =$i;
//         }
//         $pdf->Ln(-1);
//         $pdf->Ln(5);
//         $pdf->Cell(190, 8, '', 'B', false, 'C', 0, '', 0, false, 'M', 'M');
//         $pdf->setXY($x,$y);


//         $pdf->SetFillColor(255, 255, 255);
//         $pdf->SetTextColor(0, 0, 0);

//         $pdf->SetFont('helvetica', '', 9);
//         $pdf->ln(9);
//         // $pdf->Cell(130, 7, '', 0, false, 'L');
//         $pdf->Cell(160, 7, $lang['total_amount'].': ', 'L,R,B', false, 'R');
//         $pdf->Cell(30, 7, number_format($total_amount,2), 'L,R,B', false, 'C');

//         $pdf->ln(7);

//         // $pdf->Cell(130, 7, '', 0, false, 'L');
//         $pdf->Cell(160, 7, $lang['discount_amount'].': ', 'L,R,B', false, 'R');
//         $pdf->Cell(30, 7, number_format($total_discount,2), 'L,R,B', false, 'C');

//         $pdf->ln(7);


//         // $pdf->Cell(130, 7, '', 0, false, 'L');
//         $pdf->Cell(160, 7, $lang['tax_amount'].': ', 'L,R,B', false, 'R');
//         $pdf->Cell(30, 7, number_format($total_tax,2), 'L,R,B', false, 'C');

//         $pdf->ln(7);


//         // $pdf->Cell(130, 7, '', 0, false, 'L');
//         $pdf->Cell(160, 7, $lang['net_amount'].': ', 'L,R,B', false, 'R');
//         $pdf->Cell(30, 7, number_format($total_net_amount,2), 'L,R,B', false, 'C');

//         $pdf->ln(7);
//         $pdf->Cell(190, 7, 'Amount In words: ' . Number2Words(round($total_net_amount,2)). ' only', 1, false, 'R');

//        // $data= $Quotation;//Close and output PDF document
//         $pdf->Output('Purchase Invoice :'.date('YmdHis').'.pdf', 'I');


// }


 public function printDocument() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);
        $height = 0;

        //d(array('session' => $this->session->data, 'post' => $this->request->post, 'get' => $this->request->get), true);
        $lang = $this->load->language($this->getAlias());
        $purchase_invoice_id = $this->request->get['purchase_invoice_id'];
        $post = $this->request->post;
        $session = $this->session->data;

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->model['purchase_invoice'] = $this->load->model('inventory/purchase_invoice');
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['purchase_invoice_detail'] = $this->load->model('inventory/purchase_invoice_detail');
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $invoice = $this->model['purchase_invoice']->getRow(array('purchase_invoice_id' => $purchase_invoice_id));
        $details = $this->model['purchase_invoice_detail']->getRows(array('purchase_invoice_id' => $purchase_invoice_id));
        $partner = $this->model['partner']->getRow(array('partner_id' => $invoice['partner_id']));
        $where = 'AND l.partner_id = "'.$partner['partner_id'].'"';
        $where.= 'AND l.coa_id = "'.$partner['outstanding_account_id'].'"';
        $where.= 'AND l.document_identity < "'.$invoice['document_identity'].'"';

        $previous_balance =  $this->model['purchase_invoice']->getPreviousBalance($where);

        $invoice_date = date_create($invoice['document_date']);
        $invoice_date = date_format($invoice_date,'d/m/Y');
        //d([$company, $branch], true);
        $pdf = new PDF('P', PDF_UNIT, array(74,240), true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('PURCHASE RECEIPT');
        $pdf->SetSubject('PURCHASE RECEIPT');

        //Set Header
//        $pdf->data = array(
//            'company_name' => $session['company_name'],
//            'company_address' => $branch['address'],
//            'company_phone' => $branch['phone_no'],
//            'report_name' => $lang['heading_title'],
//            'company_logo' => $session['company_image']
//        );
        $pdf->data = array(
            'company_name' => $branch['print_name'],
            'company_address' => $branch['address'],
            'company_phone' => $branch['phone_no'],
            'report_name' => 'PURCHASE RECEIPT',
            'company_logo' => ''
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(0, 2, 2);
        $pdf->SetHeaderMargin(2);
        $pdf->SetFooterMargin(0);

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
            // $pdf->Ln(2);
            // // Title
            // $pdf->Cell(0, 6, $pdf->data['company_name'], 0, false, 'C', 1, '', 0, false, 'M', 'M');
        }
        $pdf->Ln(2);
        // if($pdf->data['company_address']) {
        //     $pdf->Cell(0, 6, $pdf->data['company_address'], 0, false, 'C', 1, '', 0, false, 'M', 'M');
        //     $pdf->Ln(6);
        // }
       
        // if($pdf->data['company_phone']) {
        //     $pdf->Cell(0, 6, 'Phone: '.$pdf->data['company_phone'], 0, false, 'C', 1, '', 0, false, 'M', 'M');
        //     $pdf->Ln(6);
        // }
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(0, 4, $pdf->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('times', 'B', 9);
        $pdf->Ln(7);
        // if($company['ntn_no']) {
        //     $pdf->Cell(30, 4, 'NTN No.', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        //     $pdf->Cell(20, 4, $company['ntn_no'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        //     $pdf->Ln(4);
        // }
        // if($company['ntn_no']!='' && $branch['address'] =='' && $branch['phone_no'] == '')
        // {
        //     $height = 30;   // set margin with ntn & address
        // }   
        // else if($company['ntn_no']!='' && $branch['address']!='' && $branch['phone_no'] == '')
        // {
        //     $height = 36;   // set margin with ntn & address
        // }
        // else if($company['ntn_no']!='' && $branch['address']!='' && $branch['phone_no']!='')
        // {
        //     $height = 42;   // set margin with ntn & address & phone no
        // }
        // else
        // {
        //     $height = 26; // set margin without ntn & address & phone no
        // }
       
        // $pdf->Cell(30, 4, 'Transaction No.', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // $pdf->Cell(20, 4, $invoice['document_identity'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // $pdf->Ln(4);
        // $pdf->Cell(30, 4, 'Transaction Date', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // $pdf->Cell(20, 4, stdDate($invoice['document_date']), 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // $pdf->Ln(4);
        // $pdf->Cell(30, 4, 'Customer', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(24, 4, $invoice['partner_name'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(30, 4, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 4, $invoice_date, 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(4);
        $pdf->Cell(50, 4, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(24, 4, $invoice['document_identity'], 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(7);


          if(strlen($invoice['remarks']) < 38){
            $pdf->Cell(40,5,$invoice['remarks'],0,false,'L',0,'',0,false,'M','M');
          }
          else{

           $remarks = splitString($invoice['remarks'],38);
            foreach ($remarks as $key => $value) {
                if($key == 0){
                    $pdf->Cell(40, 5, trim($value) , 0, false, 'L', 0, '', 0, false, 'M', 'M');
                }
                else {
                     $pdf->Cell(40, 5, trim($value) , 0, false, 'L', 0, '', 0, false, 'M', 'M');
                }
      
                $pdf->ln(5);
            }
        }

        if(strlen($invoice['remarks']) < 38 ){
            $pdf->Ln(6);
        }
        else {
            $pdf->Ln(1); 
        }


        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell(80, 4, '', 'T', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(1);
        $pdf->setX(2);
        $pdf->Cell(5, 4, 'S.No', '', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(28, 4, 'PARTICULARS', '', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(8, 4, 'QTY', '', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setX(45);
        $pdf->Cell(9, 4, 'RATE', '', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setX(59);
        $pdf->Cell(12, 4, 'AMOUNT', '', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(5);
        $pdf->Cell(80, 4, '', 'T', false, 'L', 0, '', 0, false, 'M', 'M');


        $pdf->SetFont('times', '', 8);
        $sr = 0;
        $total_amount = 0;
        $total_qty = 0;
        $total_balance = 0;

        if(strlen($invoice['remarks']) < 38 ){
            $pdf->setY(31);
        }
        else {
            $pdf->setY(35);
        }


        foreach($details as $detail) {
            $pdf->SetFont('times', 'B', 8);
            $sr++;
            $pdf->Ln(4);
            $pdf->Cell(5, 4, $sr, 0,  false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(28, 4, $detail['product_name'], 0, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(8, 4, number_format($detail['qty'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->setX(45);
            $pdf->Cell(9, 4, number_format($detail['rate'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->setX(59);
            $pdf->Cell(12, 4, number_format($detail['amount'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $total_amount += $detail['amount'];
            $total_qty += $detail['qty'];
        }


        $amount_received = $invoice['amount_received']; 
        $total_balance = ($invoice['net_amount'] + $previous_balance - $amount_received);
        $pdf->Ln(4);
        $pdf->Cell(80, 4, '', 'T', false, 'L',0, '', 0, false, 'M', 'M');
        $pdf->Ln(2);
        $pdf->setX(32);
        $pdf->Cell(12, 5, number_format($total_qty,0), 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setX(56);
        $pdf->Cell(17, 4, number_format($total_amount,0), 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(2);
        $pdf->SetFont('times', 'B', 9);
        $pdf->Ln(4);
        $pdf->setX(37);
        $pdf->Cell(50, 4, 'Freight :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->setX(57);
        $pdf->Cell(15, 4, number_format($invoice['freight_master'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(4);
        $pdf->setX(37);
        $pdf->Cell(50, 4, 'Net Bill :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->setX(57);
        $pdf->Cell(15, 4, number_format($invoice['net_amount'],0), 'T', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(10);
        $pdf->Cell(80, 14, '', 'T', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(-3);
        $pdf->setX(15);
        $pdf->Cell(58, 5, 'Previous Balance :', 1, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->setX(42);
        $pdf->Cell(30, 5, number_format($previous_balance,0), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(5);
        $pdf->setX(15);
        $pdf->Cell(58, 5, 'Total Amount :', 1, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->setX(42);
        $pdf->Cell(30, 5, number_format($invoice['net_amount'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(5);
        $pdf->setX(15);
        $pdf->Cell(58, 5, 'Amount Received :', 1, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->setX(42);
        $pdf->Cell(30, 5, number_format($amount_received,0), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(5);
        $pdf->setX(15);
        $pdf->Cell(58, 5, 'Balance Amount :', 1, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->setX(42);
        $pdf->Cell(30, 5,  number_format($total_balance,0), 0, false, 'R', 0, '', 0, false, 'M', 'M');

        $pdf->ln(6);
        $pdf->setX(7);
        $pdf->SetFont('times', 'B', 13);
        $pdf->Cell(30, 5, '*', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->setX(10);
        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell(30, 5, 'No Claim', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->setX(25);
        $pdf->SetFont('times', 'B', 13);
        $pdf->Cell(30, 5, '*', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->setX(28);
        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell(30, 5, 'No Return', 0, false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->setX(44);
        $pdf->SetFont('times', 'B', 13);
        $pdf->Cell(30, 5, '*', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->setX(47);
        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell(30, 5, 'No Exchange', 0, false, 'L', 0, '', 0, false, 'M', 'M');


        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);


        //Close and output PDF document
        $pdf->Output('Purchase Receipt:'.date('YmdHis').'.pdf', 'I');
    }

    public function printPurchaseInvoice() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        $purchase_invoice_id = $this->request->get['purchase_invoice_id'];
        $with_previous_balance = isset($this->request->get['with_previous_balance'])?1:0;
        $type = $this->request->get['type'];

        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['purchase_invoice'] = $this->load->model('inventory/purchase_invoice');
        $this->model['purchase_invoice_detail'] = $this->load->model('inventory/purchase_invoice_detail');
        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
        $this->model['company'] = $this->load->model('setup/company_branch');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $company_address = $this->model['company']->getRow(array('company_id' => $session['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $invoice = $this->model['purchase_invoice']->getRow(array('purchase_invoice_id' => $purchase_invoice_id));
        // d($invoice,true);
        $details = $this->model['purchase_invoice_detail']->getRows(array('purchase_invoice_id' => $purchase_invoice_id), array('sort_order asc'));
        $partner = $this->model['partner']->getRow(array('partner_id' => $invoice['partner_id']));
        $delivery_challan_id = $invoice['ref_document_id'];

        $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');
        $CustomerUnit = $this->model['customer_unit']->getRow(array('customer_unit_id' => $invoice['customer_unit_id']));
        $where = 'AND l.partner_id = "'.$partner['partner_id'].'"';
        $where.= 'AND l.coa_id = "'.$partner['outstanding_account_id'].'"';
        $where.= 'AND l.document_identity < "'.$invoice['document_identity'].'"';
        $previous_balance =  $this->model['purchase_invoice']->getPreviousBalance($where);
        
        // $outstanding = $this->model['partner']->getOutstanding("l.`partner_id` = '".$invoice['partner_id']."' AND l.`created_at` < '".$invoice['created_at']."'");
        //d(array($purchase_invoice_id, $invoice, $details), true);
        $result_str = array();

        $dcnos = json_decode($invoice['ref_document_id'],true);

        foreach($dcnos as $item){

            $delivery_challan = $this->model['delivery_challan']->getRow(array('delivery_challan_id' => $item));
            //d($delivery_challan,true);

            $result_str[] = $delivery_challan['document_identity'];
        }
        $abc = implode(", ",$result_str);

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

        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        if($invoice['po_date'] == "" || $invoice['po_date'] == '0000-00-00'){
            $this->data['po_date'] = null;
        }
        else{
            $this->data[$invoice['po_date']] = stdDate($invoice['po_date']);
        }

        /*if($invoice['billty_date'] == "" || $invoice['billty_date'] == '0000-00-00'){
            $this->data['billty_date'] = null;
        }
        else{
            $this->data[$invoice['billty_date']] = stdDate($invoice['billty_date']);
        }
*/

        $this->model['user'] = $this->load->model('user/user');
        $user = $this->model['user']->getRow(array('user_id' => $session['user_id']));

        $user_name=$user['user_name'];

        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $company_branch = $this->model['company_branch']->getRow(array('company_branch_id' => $this->session->data['company_branch_id']));

        $company_branch_name=$company_branch['name'];

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Fahad Siddiqui');
        $pdf->SetTitle('Purchase Invoice');
        $pdf->SetSubject('Purchase Invoice');

        //Set Header
        $pdf->InvoiceCheck = $type;
        $pdf->data = array(
            'company_name' => $branch['name'],
            //'report_name' => $lang['heading_title'],
            'report_name' => 'Bill',
            'company_logo' => $session['company_image'],
            'company_header_print' => $company_header_print,
            'company_footer_print' => $company_footer_print,
            'user_name' => $user_name,
            'company_branch_id' => $this->session->data['company_branch_id'],
            'company_branch_name' => $company_branch_name,
        );

        // set margins
        // $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(7, 30, 7);
        
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        // $pdf->SetAutoPageBreak(TRUE, 0);

        // add a page
        $pdf->AddPage();
        // set font

        
        // $txt="21,Adnan Centre Jeswani Street, off. Aiwan-E-Tijarat Road,
        // Karachi - 74000, Pakistan
        // TEL: 92-21-32401236,92-21-32415063
        // FAX: 92-21-32428040
        // Email : info@hacsons.com Web: www.hacsons.com.
        // 82/4 Railway Road Chowk Dalgarah Lahore.
        // TEL: 042-37662521,042-37662527";

        $txt = $company_address['address'].' , 
        '.$company_address['name'].' 
        TEL: '.$company_address['phone_no'];

//        $txt = "";
        if($invoice['po_date'] != '')
        {
            $invoice['po_date'] = stdDate($invoice['po_date']);
        }
        $pdf->SetFont('freesans', 'B', 9);
        $pdf->SetFillColor(255,255,255);
        $arrAddress = splitString($partner['address'], 40);

        $pdf->setXY(8,17);
        $pdf->SetFont('helvetica', 'B', 11);    

        $pdf->Cell(26, 9, 'M/s.  ', '', false, 'L', 1, '', 1);
        $pdf->setXY(18,16.4);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(60, 9, $invoice['partner_name'], '', false, 'L', 1, '', 1);

        $invoice['document_date'] = date_create($invoice['document_date']);
        $invoice['document_date'] = date_format($invoice['document_date'],'d/m/Y');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->setXY(117,18.1);
        $pdf->Cell(30, 5, $invoice['document_identity'], 0, false, 'L', 1, '', 1);
        $pdf->setXY(117,25.4);
        $pdf->Cell(30, 5,  $invoice['document_date'], '', false, 'L', 1, '', 1);


        $pdf->setXY(156,16);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(24, 9, 'M/s.  ', '', false, 'L', 1, '', 1);
        $pdf->setXY(166,15.4);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(60, 9, $invoice['partner_name'], '', false, 'L', 1, '', 1);

        $pdf->SetFont('helvetica', '', 9);
        $pdf->setXY(263,18.2);
        $pdf->Cell(30, 5, $invoice['document_identity'], 0, false, 'L', 1, '', 1);
        $pdf->setXY(263,25.4);
        $pdf->Cell(30, 5,  $invoice['document_date'],'0', false, 'L', 1, '', 1);



         $pdf->setXY(7,24);
         $pdf->Cell(1,24, '', '', false, 'L', 1, '', 1);
         $pdf->SetFont('helvetica', 'B', 10);
         $pdf->Cell(20, 8, 'Address. ', '', false, 'L', 1, '', 1);


         foreach ($arrAddress as $index => $add) {

            if($index == 0) {       
              $pdf->SetFont('helvetica', '', 9);
              $pdf->Cell(86, 8, $arrAddress[$index], '', false, 'L', 1, '', 1);
            }
             else {
              $pdf->setXY(28,31);  
              $pdf->SetFont('helvetica', '', 9);     
              $pdf->Cell(86, 8, $arrAddress[$index], '', false, 'L', 1, '', 1);
            }
             $pdf->ln(6);
        }


          $pdf->setXY(155,24);

          $pdf->Cell(1,24, '', '', false, 'L', 1, '', 1);
          $pdf->SetFont('helvetica', 'B', 10);
          $pdf->Cell(20, 8, 'Address. ', '', false, 'L', 1, '', 1);

         foreach ($arrAddress as $index => $add) {

            if($index == 0) {       
              $pdf->SetFont('helvetica', '', 9);
              $pdf->Cell(86, 8, $arrAddress[$index], '', false, 'L', 1, '', 1);
            }
             else {
              $pdf->setXY(176,31);  
              $pdf->SetFont('helvetica', '', 9);     
              $pdf->Cell(86, 8, $arrAddress[$index], '', false, 'L', 1, '', 1);
            }
             $pdf->ln(6);
        }
               
        $x= $pdf->GetX();
        $y= $pdf->GetY();
        $pdf->SetXY($x, $y);

        $y = $y + 10;

        $pdf->ln(4.9);
        // set font
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->Cell(2, 6, '', '', false, 'C', 1, '', 1);
        $pdf->Cell(11, 7.4, 'S.no', 1, false, 'C', 1, '', 1);
        $pdf->Cell(34, 7.4, 'Description', 1, false, 'L', 1, '', 1);
        $pdf->Cell(28, 7.4, 'Category', 1, false, 'C', 1, '', 1);
        $pdf->Cell(13, 7.4, 'Qty', 1, false, 'C', 1, '', 1);
        $pdf->Cell(20, 7.4, 'Rate', 1, false, 'C', 1, '', 1);
        $pdf->Cell(28, 7.4, 'Amount', 1, false, 'C', 1, '', 1);


        $pdf->Cell(14, 6, '', 'L', false, 'C', 1, '', 1);
        $pdf->Cell(11, 7.4, 'S.no', 1, false, 'C', 1, '', 1);
        $pdf->Cell(34, 7.4, 'Description', 1, false, 'L', 1, '', 1);
        $pdf->Cell(28, 7.4, 'Category', 1, false, 'C', 1, '', 1);
        $pdf->Cell(13, 7.4, 'Qty', 1, false, 'C', 1, '', 1);
        $pdf->Cell(20, 7.4, 'Rate', 1, false, 'C', 1, '', 1);
        $pdf->Cell(28, 7.4, 'Amount', 1, false, 'C', 1, '', 1);


        // $pdf->Ln(-1);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        
        $total_qty = 0;
        $total_qty2 = 0;
        $total_amount = 0;
        $total_amount2 = 0;
        $balance_amount = 0;
        $sr = 0;
        $sr2 = 0;
        $pdf->SetFont('helvetica', '', 8);
        $pdf->ln(7);

        foreach($details as $record_no => $detail) {
            $sr++;

             $pdf->Cell(2, 6, '', '', false, 'C', 1, '', 1);
            if(strlen($detail['product_name'])<=20){
                    $pdf->Cell(11, 7, $sr, 'BLR', false, 'C', 0, '', 1);
                    $pdf->Cell(34, 7, html_entity_decode($detail['product_name']), 'BLR', false, 'L', 0, '', 1);
                    $pdf->Cell(28, 7, html_entity_decode($detail['product_category']), 'BLR', false, 'C', 0, '', 1);
                    $pdf->Cell(13, 7, (int) ($detail['qty']), 'BLR', false, 'C', 0, '', 1);
                    $pdf->Cell(20, 7, (int) ($detail['rate']), 'BLR', false, 'C', 0, '', 1);
                    $pdf->Cell(28, 7, number_format($detail['amount'],0), 'BLR', false, 'R', 0, '', 1);
                    $pdf->Cell(14, 6, '', 'L', false, 'C', 1, '', 1);
                    $pdf->Cell(11, 7, $sr, 'BLR', false, 'C', 0, '', 1);
                    $pdf->Cell(34, 7, html_entity_decode($detail['product_name']), 'BLR', false, 'L', 0, '', 1);
                    $pdf->Cell(28, 7, html_entity_decode($detail['product_category']), 'BLR', false, 'C', 0, '', 1);
                    $pdf->Cell(13, 7, (int) ($detail['qty']), 'BLR', false, 'C', 0, '', 1);
                    $pdf->Cell(20, 7, (int) ($detail['rate']), 'BLR', false, 'C', 0, '', 1);
                    $pdf->Cell(28, 7, number_format($detail['amount'],0), 'BLR', false, 'R', 0, '', 1);

                    $pdf->ln(7);
    
            } else {
                $arrDesc = splitString($detail['product_name'], 20);
                
                   foreach($arrDesc as $index => $remark){
                    
                     if($index==0){

                      $pdf->Cell(11, 5, $sr, 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(34, 5, $remark, 'LR', false, 'L', 0, '', 1);
                      $pdf->Cell(28, 5, html_entity_decode($detail['product_category']), 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(13, 5, (int) ($detail['qty']), 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(20, 5, (int) ($detail['rate']), 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(28, 5,  number_format($detail['amount'],0), 'LR', false, 'R', 0, '', 1);
                      $pdf->Cell(1, 5, '', 'L', false, 'R', 0, '', 1);

                      $pdf->Cell(13, 6, '', '', false, 'C', 1, '', 1);
                      $pdf->Cell(11, 5, $sr, 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(34, 5, $remark, 'LR', false, 'L', 0, '', 1);
                      $pdf->Cell(28, 5, html_entity_decode($detail['product_category']), 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(13, 5, (int) ($detail['qty']), 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(20, 5, (int) ($detail['rate']), 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(28, 5,  number_format($detail['amount'],0), 'LR', false, 'R', 0, '', 1);


                     } else if($index<=count($arrDesc)-1){
                      $pdf->Cell(2, 5, '', '', false, 'C', 1, '', 1);
                      $pdf->Cell(11, 5, '', 'LRB', false, 'C', 0, '', 1);
                      $pdf->Cell(34, 5, $remark, 'LRB', false, 'L', 0, '', 1);
                      $pdf->Cell(28, 5, '', 'LRB', false, 'C', 0, '', 1);
                      $pdf->Cell(13, 5, '', 'LRB', false, 'C', 0, '', 1);
                      $pdf->Cell(20, 5, '', 'LRB', false, 'C', 0, '', 1);
                      $pdf->Cell(28, 5, '', 'LRB', false, 'R', 0, '', 1);
                      $pdf->Cell(1, 5, '', 'L', false, 'R', 0, '', 1);


                      $pdf->Cell(11, 6, '', '', false, 'C', 1, '', 1);
                      $pdf->Cell(2, 5, '', '', false, 'C', 1, '', 1);
                      $pdf->Cell(11, 5, '', 'LRB', false, 'C', 0, '', 1);
                      $pdf->Cell(34, 5, $remark, 'LRB', false, 'L', 0, '', 1);
                      $pdf->Cell(28, 5, '', 'LRB', false, 'C', 0, '', 1);
                      $pdf->Cell(13, 5, '', 'LRB', false, 'C', 0, '', 1);
                      $pdf->Cell(20, 5, '', 'LRB', false, 'C', 0, '', 1);
                      $pdf->Cell(28, 5, '', 'LRB', false, 'R', 0, '', 1);
                     } else {

                    $pdf->Cell(2, 5, '', '', false, 'C', 1, '', 1);   
                      $pdf->Cell(11, 5, '', 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(34, 5, $remark, 'LR', false, 'L', 0, '', 1);
                      $pdf->Cell(28, 5, '', 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(13, 5, '', 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(20, 5, '', 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(28, 5, '', 'LR', false, 'R', 0, '', 1);

                      $pdf->Cell(12, 6, '', '', false, 'C', 1, '', 1);
                      $pdf->Cell(2, 5, '', '', false, 'C', 1, '', 1);   
                      $pdf->Cell(11, 5, '', 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(34, 5, $remark, 'LR', false, 'L', 0, '', 1);
                      $pdf->Cell(28, 5, '', 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(13, 5, '', 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(20, 5, '', 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(28, 5, '', 'LR', false, 'R', 0, '', 1);
                     }

                    $pdf->Ln(5);
                }
            }

       $total_qty += $detail['qty'];
       $total_amount += $detail['amount'];
       $styless = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 2, 'color' => array(128, 128, 128  ));
       $styles = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
       $pdf->Line(150, 7, 150, 204, $styless);

       $pdf->Line(0, 0, 0, 0,$styles);
       $pdf->Line(0, 0, 0, 0,$styles);
            $y = $pdf->GetY();
            if($y>=135 && $record_no < (count($details)-1)) {
              $pdf->Cell(1, 1, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
              $pdf->AddPage();
              $pdf->SetFont('helvetica', 'B', 9);
              $pdf->ln(-12);
              $pdf->Cell(2, 6, '', '', false, 'C', 1, '', 1);
              $pdf->Cell(11, 7.4, 'S.no', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(34, 7.4, 'Description', 'TBLR', false, 'L', 1, '', 1);
              $pdf->Cell(28, 7.4, 'Category', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(13, 7.4, 'Qty', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(20, 7.4, 'Rate', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(28, 7.4, 'Amount', 'TBLR', false, 'C', 1, '', 1);

              $pdf->setX(154);
              $pdf->Cell(3, 6, '', '', false, 'C', 1, '', 1);
              $pdf->Cell(11, 7.4, 'S.no', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(34, 7.4, 'Description', 'TBLR', false, 'L', 1, '', 1);
              $pdf->Cell(28, 7.4, 'Category', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(13, 7.4, 'Qty', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(20, 7.4, 'Rate', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(28, 7.4, 'Amount', 'TBLR', false, 'C', 1, '', 1);
              $pdf->ln(7);
              $pdf->SetFont('helvetica', '', 8);
            }
        }

        $balance_amount = ($invoice['net_amount'] + $previous_balance);

        $pdf->ln(4);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(3, 5, ' ', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 5, 'Total Pairs: '. number_format($total_qty,0), 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(52, 5, ' ', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(13, 5,number_format($total_qty,0), 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setX(115);
        $pdf->Cell(28, 5,number_format($total_amount,0), 1, false, 'R', 0, '', 0, false, 'M', 'M');


        $pdf->Cell(12, 6, '', '', false, 'C', 1, '', 1);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(3, 5, ' ', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 5, ' Total Pairs: '. number_format($total_qty,0), 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(52, 5, ' ', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(13, 5,number_format($total_qty,0), 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setX(263);
        $pdf->Cell(28, 5,number_format($total_amount,0), 1, false, 'R', 0, '', 0, false, 'M', 'M');

// 

   
        $pdf->ln(6.5);
        $pdf->Cell(2, 5, ' ', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(134, 4, '', 'T', false, 'L',0, '', 0, false, 'M', 'M');
        $pdf->Cell(10, 6, '', '', false, 'C', 1, '', 1);
        $pdf->Cell(4, 5, ' ', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(134, 4, '', 'T', false, 'L',0, '', 0, false, 'M', 'M');
        $pdf->Ln(0.2);


        $pdf->setX(76);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(35, 6, 'Previous outstanding    : ', 0, false, 'L', false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(30, 6, number_format($previous_balance,0), 0, false, 'R', false, 'M', 'M');


        $pdf->Cell(2 ,6, '', '', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setX(224);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(35, 6, 'Previous outstanding    : ', 0, false, 'L', false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(30, 6, number_format($previous_balance,0), 0, false, 'R', false, 'M', 'M');
        $pdf->Ln(7.5);

        
        $pdf->setX(77);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(35, 6, 'Bill Amount                     : ', 0, false, 'L', false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(30, 6, number_format($total_amount,0), 0, false, 'R', false, 'M', 'M');

        $pdf->Cell(2, 6, '', '', false, 'C', 1, '', 1);
        $pdf->setX(224);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(35, 6, 'Bill Amount                     : ', 0, false, 'L', false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(30, 6, number_format($total_amount,0), 0, false, 'R', false, 'M', 'M');


        $pdf->Ln(7.5);


        $pdf->setX(77);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(35, 6, 'Freight Charges              : ', 0, false, 'L', false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(30, 6, number_format($invoice['freight_master'],0), 0, false, 'R', false, 'M', 'M');
       
        $pdf->Cell(2, 6, '', '', false, 'C', 1, '', 1);
        $pdf->setX(224);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(35, 6, 'Freight Charges              : ', 0, false, 'L', false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(30, 6, number_format($invoice['freight_master'],0), 0, false, 'R', false, 'M', 'M');

        $pdf->Ln(7.5);


        $pdf->setX(77);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(35, 6, 'Amount Paid                    : ', 0, false, 'L', false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(30, 6, 0, 0, false, 'R', false, 'M', 'M');
         
        $pdf->Cell(10, 6, '', '', false, 'C', 1, '', 1);

        $pdf->setX(224);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(35, 6, 'Amount Paid                    : ', 0, false, 'L', false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(30, 6, 0, 0, false, 'R', false, 'M', 'M');
        $pdf->Ln(10.5);




        $pdf->setX(77);
        $pdf->Cell(64, 4, '', 'T', false, 'L',0, '', 0, false, 'M', 'M');
         $pdf->Cell(10, 6, '', '', false, 'C', 1, '', 1);
        $pdf->setX(224);
        $pdf->Cell(64, 4, '', 'T', false, 'L',0, '', 0, false, 'M', 'M');
        $pdf->Ln(-1);




        $pdf->setX(77);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(35, 6, 'Balance Amount              : ', 0, false, 'L', false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(30, 6, number_format($balance_amount,0), 0, false, 'R', false, 'M', 'M');

        $pdf->Cell(10, 6, '', '', false, 'C', 1, '', 1);
        $pdf->setX(224);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(35, 6, 'Balance Amount              : ', 0, false, 'L', false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(30, 6, number_format($balance_amount,0), 0, false, 'R', false, 'M', 'M');
        $pdf->Ln(-30);
 

       $pdf->Cell(3, 6, '', '', false, 'C', 1, '', 1);
       $pdf->SetFont('helvetica', 'B', 9);
       $pdf->Cell(130, 6, 'Amount in Words: ', 0, false, 'L', false, 'M', 'M');

       $pdf->Cell(10, 6, '', '', false, 'C', 1, '', 1);

       
       $pdf->Cell(7, 6, '', '', false, 'C', 1, '', 1);
       $pdf->SetFont('helvetica', 'B', 9);
       $pdf->Cell(130, 6, 'Amount in Words: ', 0, false, 'L', false, 'M', 'M');

  
       $pdf->Ln(6.8);
       $pdf->SetFont('helvetica', '', 9);
       $pdf->Cell(3, 6, '', '', false, 'C', 1, '', 1);
   

       $amountStr = Number2Words($invoice['net_amount']);

          if(strlen($amountStr) < 35){

           $pdf->Cell(50, 6, Number2Words($invoice['net_amount']) . ' Only ', 0, false, 'L');
            // $pdf->Cell(10, 6, '', '', false, 'C', 1, '', 1);
           $pdf->setX(157);
           $pdf->Cell(50, 6, Number2Words($invoice['net_amount']) . ' Only ', 0, false, 'L');
          }
          else{
           $arrAmount = splitString($amountStr,35);
          foreach ($arrAmount as $key => $amount) {

           if($key == 0){
               $pdf->Cell(50, 6, $amount, 0, false, 'L');
               $pdf->setX(157);
               $pdf->Cell(50, 6, $amount, 0, false, 'L');
           }
           else {
               $pdf->Cell(3, 6, '', '', false, 'C', 1, '', 1);
               $pdf->Cell(50, 6, $amount . 'Only', 0, false, 'L');
               $pdf->setX(157);
               $pdf->Cell(50, 6, $amount . 'Only', 0, false, 'L');
           }
           $pdf->Ln(6);
          }
       }

       $styless = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 2, 'color' => array(128, 128, 128  ));
       $styles = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
       $pdf->Line(150, 7, 150, 204, $styless);
       $pdf->Line(0, 0, 0, 0,$styles);
        //Close and output PDF document
        $pdf->Output('Purchase Invoice - '.$invoice['document_identity'].'.pdf', 'I');

    }

    public function printLabels(){

        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $lang = $this->load->language($this->getAlias());
        $get = $this->request->get;
        $session = $this->session->data;
        
        $this->model['purchase_invoice'] = $this->load->model('inventory/purchase_invoice');
        $this->model['purchase_invoice_detail'] = $this->load->model('inventory/purchase_invoice_detail');
        $this->model['company'] = $this->load->model('setup/company');
        $company_address = $this->model['company']->getRow(array('company_id' => $session['company_id']));
        $invoice = $this->model['purchase_invoice']->getRow(array('purchase_invoice_id' => $get['purchase_invoice_id']));

        $this->model['partner_category'] = $this->load->model('setup/partner_category');
        $partner_category_id = $this->model['partner_category']->getRow(array('partner_category_id' => $invoice['partner_category_id']));
        
        $details = $this->model['purchase_invoice_detail']->getRows(array('purchase_invoice_id' => $get['purchase_invoice_id']), array('sort_order asc'));

        $pdf = new PDF('P', 'mm', array(47,27.94), true, 'UTF-8', false);
        $pdf->setPageOrientation('L',false,'');
        $pdf->SetAutoPageBreak(TRUE, 0);

        

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Muhammad Salman');
        $pdf->SetTitle('Print Barcode');
        $pdf->SetSubject('Print Barcode');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $session['company_image']
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(1, 1, 1);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 0);

        // set font
        // $pdf->SetFont('times', '', 8);

        // add a page
        $pdf->AddPage();

        $column = 1;
        $pdf->SetFont('helvetica', '', 8);

        // define barcode style
        $style = array(
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
        );

        $product = array();
        foreach ($details as $detail) {
            for($i=1; $i<=$detail['qty']; $i++ ){
                $product[] = $detail;
            }
        }
       
       for( $i=0; $i< count($product); $i+=1 ){

          $this_code =  (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $product['product_code'])) ? 'C128' : 'C39';

                $pdf->ln(3);
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->Cell(45, 0, $session['company_name'], 0, 0,'C');
                $pdf->Cell(5, 0, '', 0, 0,'C');

                // if(($i<(count($product)-1))) {
                //     $pdf->Cell(45, 0, $session['company_name'], 0, 0,'C');
                // }

                $pdf->Cell(45, 0, '', 0, 1,'C');
                $pdf->SetFont('helvetica', '', 8);
                $pdf->ln(2);
                
                $pdf->Cell(45,0,'',0);
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->setXY($x,$y);
                $pdf->write1DBarcode(strtoupper($product[$i]['product_code']),$this_code, ($x-45), ($y-2), 40, 8, 0.4, $style, 'M');
                // d($product[$i]['product_code']);

                // if(($i<(count($product)-1))) {
                //     $pdf->Cell(45,0,'',0);
                //     $x = $pdf->GetX();
                //     $y = $pdf->GetY();
                //     $pdf->setXY($x,$y);
                //     $pdf->write1DBarcode(strtoupper($product[($i+1)]['product_code']), $this_code, ($x-35), ($y-4), 40, 8, 0.4, $style, 'M');
                //     // d($product[($i+1)]['product_code']);
                // }
                
                $pdf->ln(3);
                
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->Cell(45, 0, $product[$i]['product_name'], 0, 0,'C');

                // if(($i<(count($product)-1))) {  
                //     $pdf->Cell(5, 0, '', 0, 0,'C');
                //     $pdf->Cell(45, 0, $product[($i+1)]['product_name'], 0, 0,'C');
                // }
                

                $pdf->ln(5);
                $pdf->Cell(22, 0, strtoupper($product[$i]['product_code']), 0, 0,'L');
                $pdf->Cell(22, 0, 'Price: '.number_format($product[$i]['amount'],0,".",""), 0, 0,'R');
                
                $pdf->Cell(5, 0, '', 0, 0,'C');
                // if(($i<(count($product)-1))) {    
                //     $pdf->Cell(22, 0, strtoupper($product[($i+1)]['product_code']), 0, 0,'L');
                //     $pdf->Cell(22, 0, 'Price: '.number_format($product[($i+1)]['amount'],0,".",""), 0, 0,'R');  
                // }

            if(($i<(count($product)-2))) {
                $pdf->AddPage();
            }

       }

        //Close and output PDF document
        $pdf->Output('Barcode:'.date('YmdHis').'.pdf', 'I');
    }


}

class PDF extends TCPDF {
    public $data = array();
    public $InvoiceCheck;

//    //Page header
    public function Header() { 

        if($this->InvoiceCheck == "purchase_invoice")
        {

            $this->SetFont('helvetica', 'B', 11);
            $this->Ln(5);
            // Title
            $this->Cell(135, 12, 'Purcahse Invoice', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->setX(150);
            $this->Cell(135, 12, 'Purcahse Invoice', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        }



    }
//    // Page footer
   public function Footer() { }
}




// class PDF extends TCPDF {
//     public $data = array();
//     public $term = array();

//     //Page header
//         //Page header
//         public function Header() {
//             // Logo

//             if($this->data['company_logo'] != '') {
//                 $image_file = DIR_IMAGE.$this->data['company_logo'];
//                 //$this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
//                 $this->Image($image_file, 10, 10, 30, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
//             }
//             // Set font
//             $this->SetFont('helvetica', 'B', 20);
//             $this->Ln(5);
//             // Title
//             $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
//             $this->Ln(10);
//             $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');

//             $this->SetFont('helvetica', 'B', 10);
//             //$this->Ln(10);
//             //$this->Cell(0, 10, 'From Date : '.$this->data['date_from'].'     To Date  :  '.$this->data['date_to'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
//             $this->Ln(20);


//             //$this->Cell(20,10,'Invoice No : ',0,false,'L',0,'',0,false,'M','M');

//             //$this->Cell(140,10,'Invoice Date : ',0,false,'R',0,'',0,false,'M','M');

//             //$this->Ln(8);
//             //$this->Cell(20,10,'Supplier Name : ',0,false,'L',0,'',0,false,'M','M');


//            /* $this->Ln(8);
//             $this->Cell(20,10,'Remarks : ',0,false,'L',0,'',0,false,'M','M');

//             $this->Ln(12);

//             $this->SetFont('times', '', 8);
//             $this->SetFillColor(215, 215, 215);
//             $this->SetTextColor(0, 0, 0);
//             $this->Cell(60, 8, 'Product', 1, false, 'C', 1, '', 1);
//             $this->Cell(30, 8, 'Warehouse', 1, false, 'C', 1, '', 1);
//             $this->Cell(30, 8, 'Qty', 1, false, 'C', 1, '', 1);
//             $this->Cell(20, 8, 'Unit', 1, false, 'C', 1, '', 1);
//             $this->Cell(20, 8, 'Rate', 1, false, 'C', 1, '', 1);
//             $this->Cell(30, 8, 'Amount', 1, false, 'C', 1, '', 1);


//             $this->SetFillColor(255, 255, 255);
//             $this->SetTextColor(0, 0, 0);
//            */

//            /* $this->SetFont('times', 'B', 7);
//             $this->Cell(20, 5, 'Document Date', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
//             $this->Cell(30, 5, 'Document Identity', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
//             $this->Cell(30, 5, 'Warehouse Name', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
//             $this->Cell(50, 5, 'Partner Name', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
//             $this->Cell(50, 5, 'Product', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
//             $this->Cell(20, 5, 'Unit', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
//             $this->Cell(20, 5, 'Quantity', 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');
//             $this->Cell(20, 5, 'Rate', 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');
//             $this->Cell(25, 5, 'Amount', 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');
//             */
//             // set font


//         }



//     // Page footer
//     public function Footer() {
// //        // Position at 15 mm from bottom
//         //$this->SetY(-75);
// //        // Set font
//         //$this->SetFont('times', 'B', 12);

//         //$this->Cell(0, 5, 'Terms & Condition', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//         //$this->SetFont('times', 'B', 10);
// //        // Page number

//         //$rows = $this->term;
//         //$this->Ln(6);

//         /*foreach($rows as $r => $row) {
//             foreach($row as $term ) {
//                 $this->Cell(0, 5, '* '.$term['term'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
//                 $this->Ln(5);
//             }
//         }
//         $this->SetFont('times', 'B', 11);
//         $this->Ln(5);
//         $this->Cell(0, 5, 'In case of any clarification or query , please feel free to contact us.', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//         $this->Ln(5);
//         */

//     }
// }

?>