<?php
class ControllerInventorySaleTaxInvoice extends HController {

    protected $document_type_id = 39;

    protected function getAlias() {
        return 'inventory/sale_tax_invoice';
    }

    protected function getPrimaryKey() {
        return 'sale_tax_invoice_id';
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
        $aColumns = array('action', 'document_date', 'document_identity','partner_name', 'net_amount', 'created_at');

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
                'text' => $lang['print_sales_bill'],
                'target' => '_blank',
                'href' => $this->url->link($this->getAlias() . '/printSalesTaxInvoice', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()].'&with_previous_balance=1', 'SSL'),
                'btn_class' => 'btn btn-info btn-xs',
                'class' => 'fa fa-print'
            );

            $actions[] = array(
                'text' => $lang['print_sale_receipt'],
                'target' => '_blank',
                'href' => $this->url->link($this->getAlias() . '/printSaleReceipt', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                'btn_class' => 'btn btn-info btn-xs',
                'class' => 'fa fa-print'
            );

            $actions[] = array(
                'text' => $lang['print_sale_invoice'],
                'target' => '_blank',
                'href' => $this->url->link($this->getAlias() . '/printSalesNewInvoice', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()] . '&type=sale_invoice' , 'SSL'),
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
                } elseif ($aColumns[$i] == 'net_amount') {
                    $row[] = (int)($aRow['net_amount']);
                }
                 elseif ($aColumns[$i] == 'invoice_status') {
                    $html = '';
                    //$balance = $aRow['net_amount'] - ($aRow['bank_receipt_amount'] + $aRow['cash_receipt_amount']);
                    // if($aRow['invoice_status'] == "Cleared")
                    // {
                    //     $html .= '<label class="btn btn-success btn-xs" style="margin:1px;">Cleared</label>';
                    // }else{
                    //     $html .= '<label class="btn btn-danger btn-xs" style="margin:1px;">Pending</label>';
                    // }
                    // $row[] = $html;
                }
                elseif ($aColumns[$i] == 'check_box') {
                    // if($aRow['is_post']==0) {
                    //     $row[] = '<input type="checkbox" name="selected[]" value="' . $aRow[$this->getPrimaryKey()] . '" />';
                    // } else {
                    //     $row[] = '';
                    // }
                } else {
                    $row[] = $aRow[$aColumns[$i]];
                }

            }
            $output['aaData'][] = $row;
        }

        echo json_encode($output);
    }

    protected function getForm() {
        // d($_SESSION,true);
        parent::getForm();

        // Allow out of Stock Check
        $this->model['setting'] = $this->load->model('common/setting');
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field'  => 'allow_out_of_stock'
        );
        $status = $this->model['setting']->getRow($filter);
        $this->data['allow_out_of_stock'] = $status['value'] ?? 0;
        
//        $this->model['product'] = $this->load->model('inventory/product');
//        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']),array('name'));

        // $this->model['product'] = $this->load->model('inventory/product');
        // $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']),array('name'));

//        $this->data['products'] = $products;

        $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');
        $this->data['customer_units'] = $this->model['customer_unit']->getRows(array('company_id' => $this->session->data['company_id']),array('customer_unit'));
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

        $this->data['partner_type_id'] = 2;
        // $this->model['customer'] = $this->load->model('setup/customer');
        // $where = "company_id=" . $this->session->data['company_id'];
        // removining this where clause to add all the GST + NONGST customers into the system
        // $where .= " AND partner_category_id=" .'1';
        // $this->data['partners'] = $this->model['customer']->getRows($where,array('name'));

        // $this->model['partner'] = $this->load->model('common/partner');
        // $this->data['partners'] = $this->model['partner']->getRows(array('company_id' => $this->session->data['company_id'], 'partner_type_id' => 2),array('name'));


       
        $this->data['partner_types'] = $this->session->data['partner_types'];

        $this->data['document_date'] = stdDate();
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        if (isset($this->request->get['sale_tax_invoice_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $result = $this->model[$this->getAlias()]->getRow(array('sale_tax_invoice_id' => $this->request->get[$this->getPrimaryKey()]));
            $this->model['quotation'] = $this->load->model('inventory/quotation');
            $this->data['quotations'] = $this->model['quotation']->getRows(array('partner_id' => $result['partner_id']));
            // d($result,true);
            $this->model['stock'] = $this->load->model('common/stock_ledger');
            foreach ($result as $field => $value) {
                if ($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                } elseif($field=='po_date') {
                    if($value == "" || $value == '0000-00-00'){
                        $this->data[$field] = null;
                    }
                    else{
                        $this->data[$field] = stdDate($value);
                    }

                }elseif($field=='bilty_date') {
                    $this->data[$field] = stdDate($value);
                }
//                elseif($field=='ref_document_id')
//                {
//                    $this->data[$field] = json_decode($value);
//                }
                else
                {
                    $this->data[$field] = $value;
                }
            }

            $where = " company_id=".$this->session->data['company_id'];
            $where .= " AND delivery_challan_id IN('" . implode("','", json_decode($result['ref_document_id'])) . "')";

            $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
            $this->data['ref_documents'] = $this->model['delivery_challan']->getRows($where);
            $this->data['ref_document_id'] = json_decode($result['ref_document_id']);

            $this->model['sale_tax_invoice_detail'] = $this->load->model('inventory/sale_tax_invoice_detail');
            $details = $this->model['sale_tax_invoice_detail']->getRows(array('sale_tax_invoice_id' => $this->request->get['sale_tax_invoice_id']), array('sort_order asc'));
            // $this->model['partner'] = $this->load->model('common/partner');
            // $this->data['partners'] = $this->model['partner']->getRows(array('partner_type_id' => $result['partner_type_id']));

            $where = " company_id=".$this->session->data['company_id'];
            $where .= " AND document_identity='".$result['dc_no']."'";
            

            $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
            $this->data['dc_detail'] = $this->model['delivery_challan']->getRow($where);

            $this->data['manual_ref_no']=$this->data['dc_detail']['manual_ref_no'];

            $this->data['sale_tax_invoice_details'] = $details;
            foreach($details as $row_no => $row) {
                $filter = array(
                    'company_id' => $this->session->data['company_id'],
                    'company_branch_id' => $this->session->data['company_branch_id'],
                    'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                    'product_id' => $row['product_id'],
                );
                $stock = $this->model['stock']->getStock($filter);
                $this->data['sale_tax_invoice_details'][$row_no]['stock_qty'] = $stock['stock_qty'];
            }
            // d($this->data['sale_tax_invoice_details'],true);

            // $this->data['action_cash_receipt'] = $this->url->link('gl/cash_receipt/insert', 'token=' . $this->session->data['token'] . '&sale_tax_invoice_id='.$this->request->get[$this->getPrimaryKey()] , 'SSL');
            $this->data['action_receipt'] = $this->url->link('gl/receipts/insert', 'token=' . $this->session->data['token'] . '&sale_tax_invoice_id='.$this->request->get[$this->getPrimaryKey()] , 'SSL');
        }


        $this->data['href_get_partner_json'] = $this->url->link($this->getAlias() . '/getPartnerJson', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_partner'] = $this->url->link($this->getAlias() . '/getPartner', 'token=' . $this->session->data['token']);

         $this->data['href_get_product_json'] = $this->url->link($this->getAlias() . '/getProductJson', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['href_get_ref_document_json'] = $this->url->link($this->getAlias() . '/getRefDocumentJson', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        
        $this->data['href_get_document_detail'] = $this->url->link($this->getAlias() . '/getDocumentDetails', 'token=' . $this->session->data['token'] . '&announcement_id=' . $this->request->get['announcement_id']);
        
        $this->data['href_get_container_products'] = $this->url->link($this->getAlias() . '/getContainerProducts', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        
        $this->data['get_ref_document'] = $this->url->link($this->getAlias() . '/getRefDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        
        $this->data['get_ref_document_record'] = $this->url->link($this->getAlias() . '/getRefDocumentRecord', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        
        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        
        $this->data['action_print_sale_receipt'] = $this->url->link($this->getAlias() . '/printSaleReceipt', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        
        $this->data['action_print_sales_tax_invoice'] = $this->url->link($this->getAlias() . '/printSalesTaxInvoice', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()].'&with_previous_balance=1', 'SSL');

        $this->data['action_print_sales_tax_new_invoice'] = $this->url->link($this->getAlias() . '/printSalesNewInvoice', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()] . '&type=sale_invoice', 'SSL');

        $this->data['action_print_sales_tax_commercial_invoice'] = $this->url->link($this->getAlias() . '/printCommercialInvoice', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()].'&with_previous_balance=1', 'SSL');

        $this->data['action_print_exempted_invoice'] = $this->url->link($this->getAlias() . '/printExemptedInvoice', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()].'&with_previous_balance=1', 'SSL');
        
        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);

        $this->data['url_validate_stock'] = $this->url->link('common/function/getWarehouseStock', 'token=' . $this->session->data['token']);
        
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
    protected function insertRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias().'/update', 'token=' . $this->session->data['token'] . '&sale_tax_invoice_id=' . $id, 'SSL'));
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

    // public function validateWarehouseStock()
    // {
    //     $post = $this->request->post;
    //     d($post,true);

    // }



    public function getProductJson() {
        $search = $this->request->post['q'];
        $page = $this->request->post['page'];

        $this->model['product'] = $this->load->model('inventory/product');
        $rows = $this->model['product']->getProductJson($search, $page);

        echo json_encode($rows);
    }


    public function getRefDocumentJson() {
        $search = $this->request->post['q'];
        $page = $this->request->post['page'];
        // $challan_type = 'GST';
        $type = 'sale_tax_invoice';
        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
        $rows = $this->model['delivery_challan']->getRefDocumentJson($search, $page,NULL,$type);
        echo json_encode($rows);
    }


    public function getRefDocumentRecord() {
        $post = $this->request->post;
        $ref_document_id = $post['ref_document_id'];


        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'delivery_challan_id' => $ref_document_id
        );

        $delivery_challans = $this->model['delivery_challan']->getRow($filter);

        $json = array(
            'success' => true,
            'data' => $delivery_challans
        );
//d($json,true);
        $this->response->setOutput(json_encode($json));
    }


    public function getRefDocument() {

        $delivery_challan_id = $this->request->get['delivery_challan_id'];
        $post = $this->request->post;

        //Purchase Order
        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
        $where = "company_id=" . $this->session->data['company_id'];
        $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
        $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
        //  because we have added all partners in sale invoice
        // $where .= " AND partner_type_id='" . 2 . "'";
        $where .= " AND partner_id='" . $post['partner_id'] . "'";

//        $where .= " AND is_post=1";

        $delivery_challans = $this->model['delivery_challan']->getDeliveryChallans($where,$delivery_challan_id);

        // d($delivery_challans,true);
        foreach($delivery_challans as $delivery_challan_id => $delivery_challan) {
            foreach($delivery_challan['products'] as $product_id => $product) {
                if($product['order_qty'] <= $product['utilized_qty']) {
                    unset($delivery_challan['products'][$product_id]);
                }
            }
            if(empty($delivery_challan['products'])) {
                unset($delivery_challan[$delivery_challan_id]);
            }
        }

        $html = "";
        if(count($delivery_challans) != 1) {
//            $html .= '<option value="">&nbsp;</option>';
        }
        $html .= '<option value="">&nbsp;</option>';
        foreach($delivery_challans as $sale_order_id => $delivery_challan) {
            if($delivery_challan['sale_order_id']==$post['ref_document_id']) {
                $html .= '<option value="'.$sale_order_id.'" selected="true">'.$delivery_challan['document_identity'].'</option>';
            } else {
                $html .= '<option value="'.$sale_order_id.'">'.$delivery_challan['document_identity'].'</option>';
            }
        }

        $json = array(
            'success' => true,
            'delivery_challan_id' => $delivery_challan_id,
            'post' => $post,
            'where' => $where,
            'html' => $html,
            'partners' => $delivery_challans
        );

        echo json_encode($json);

    }


//    public function getDocumentDetails() {
//
//        $post = $this->request->post;
////d($post,true);
//
//        $PoNo = '';
//        $PoDate = '';
//
//
//        $arrPartner = array();
//        $Partner="";
//        $PoNo = "";
//        $PoDate = "";
//        $CustomerUnitId = "";
//
//
//        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
//        if($post['ref_document_id'] != '')
//        {
//            $where = " delivery_challan_id IN('" . implode("','", array_values($post['ref_document_id'])) . "')";
//        }
//        $DeliveryChallans = $this->model['delivery_challan']->getRows($where);
//
//        $resultstr = array();
//        foreach($DeliveryChallans as $DeliveryChallan)
//        {
//            $resultstr[] = $DeliveryChallan['document_identity'];
//            $PoNo = $DeliveryChallan['po_no'];
//        }
//
//
//        $this->model['delivery_challan_detail'] = $this->load->model('inventory/delivery_challan_detail');
//
//        if($post['ref_document_id'] != '')
//        {
//            $where = " delivery_challan_id IN('" . implode("','", array_values($post['ref_document_id'])) . "')";
//        }
//        $rows = $this->model['delivery_challan_detail']->getRows($where);
//
////        d(array($post,$where,$rows),true);
//
//        $html = '';
//
//        $details = array();
//
//
//        foreach($rows as $row_no => $row) {
//
//            $arrPartner[$row['partner_id']] = $row['document_identity'];
//            $Partner = $row['partner_id'];
//            $PoDate = stdDate($row['po_date']);
//            $PoNo = $row['po_no'];
//            $CustomerUnitId = $row['customer_unit_id'];
//
//            $href = $this->url->link('inventory/delivery_challan/update', 'token=' . $this->session->data['token'] . '&delivery_challan_id=' . $row['delivery_challan_id']);
//            $details[$row_no] = $row;
//            $details[$row_no]['ref_document_identity'] = $row['document_identity'];
//            $details[$row_no]['row_identity'] = $row['document_identity'].'-'.$row['product_code'];
//            $details[$row_no]['href'] = $href;
//            $details[$row_no]['amount'] = ($row['qty']) * ($row['cog_rate']);
//
//        }
//        $DcNo = implode(",",$resultstr);
//
//        if(count($arrPartner) == 1)
//        {
//            $json = array(
//                'success' => true,
//                'post' => $post,
//                'where' => $where,
//                'po_no' => $PoNo,
//                'dc_no' => $DcNo,
//                'po_date'=> $PoDate,
//                'partner_id' => $Partner,
//                'customer_unit_id' => $CustomerUnitId,
//                'details' => $details
//            );
//
//        }
//        else{
//            $json = array(
//                'success' => false,
//                'error' => "Delivery challan with multiple customer.",
//            );
//
//        }
//
////        d($json,true);
//        echo json_encode($json);
//
//    }



    public function getDocumentDetails() {

        $post = $this->request->post;

        $sale_invoice_id = $this->request->get['sale_tax_invoice_id'];
        $post = $this->request->post;

        $arrPartner = array();
        $Partner="";
        $PoNo = "";
        $PoDate = "";
        $CustomerUnitId = "";
        //Purchase Order
        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');

        $where = "company_id=" . $this->session->data['company_id'];
        $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
        $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
//        $where .= " AND partner_id='" . $post['partner_id'] . "'";
        $where .= " AND delivery_challan_id IN('" . implode("','", array_values($post['ref_document_id'])) . "')";

        $Delivery_Challans = $this->model['delivery_challan']->getDeliveryChallans($where,'sale_tax_invoice',$sale_invoice_id);
        // d($Delivery_Challans,true);

        $details = array();
        $row_no = 0;


        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
        if($post['ref_document_id'] != '')
        {
            $where = " delivery_challan_id IN('" . implode("','", array_values($post['ref_document_id'])) . "')";
            // d($where,true);
        }
        $DeliveryChallans = $this->model['delivery_challan']->getRows($where);

        $resultstr = array();
        foreach($DeliveryChallans as $DeliveryChallan)
        {
            $resultstr[] = $DeliveryChallan['document_identity'];
            $PoNo = $DeliveryChallan['po_no'];
            $ManRefNo = $DeliveryChallan['manual_ref_no'];

            $PoDate = stdDate($DeliveryChallan['po_date']);
//            $PoNo = $row['po_no'];
            $CustomerUnitId = $DeliveryChallan['customer_unit_id'];

            $delivery_challan = $Delivery_Challans[$DeliveryChallan['document_identity']];

            foreach($delivery_challan['products'] as $product) {
                if($product['order_qty'] - $product['utilized_qty'] > 0)
                {

                    $balance = ($product['order_qty'] - $product['utilized_qty']);
                    $href = $this->url->link('inventory/delivery_challan/update', 'token=' . $this->session->data['token'] . '&delivery_challan_id=' . $delivery_challan['delivery_challan_id']);
                    $details[$row_no] = $product;
                    $details[$row_no]['ref_document_identity'] = $delivery_challan['document_identity'];
                    $details[$row_no]['row_identity'] = $delivery_challan['document_identity'].'-'.$product['product_code'];
                    $details[$row_no]['href'] = $href;
                    $details[$row_no]['balanced_qty'] = ($product['order_qty'] - $product['utilized_qty']);
                    $details[$row_no]['utilized_qty'] = ($product['order_qty'] - $product['utilized_qty']);
                    $details[$row_no]['amount'] = ($balance * $product['rate']);

                    if($product['tax_percent'] == 0)
                    {
                        $details[$row_no]['net_amount'] = ($balance * $product['rate']);
                    }
                    else{
                        $details[$row_no]['net_amount'] = $product['net_amount'];

                    }
                    $arrPartner[$delivery_challan['partner_id']] = $delivery_challan['document_identity']['document_identity'];
                    $Partner = $delivery_challan['partner_id'];

                    $row_no++;

                }
            }
        }

        $delivery_challan['products'] = $details;
        $delivery_challan['manual_ref_no'] = $ManRefNo;

        $DcNo = implode(",",$resultstr);

    //    d($ManRefNo,true);
        if(count($arrPartner) == 1)
        {
            $json = array(
                'success' => true,
                'post' => $post,
                'where' => $where,
                'po_no' => $PoNo,
                'dc_no' => $DcNo,
                'po_date'=> $PoDate,
                'partner_id' => $Partner,
                'sale_invoice_id' =>$sale_invoice_id,
                'customer_unit_id' => $CustomerUnitId,
                'data' => $delivery_challan,
                'details' => $delivery_challan
            );
        }
        else{
            $json = array(
                'success' => false,
                'error' => "Delivery challan with multiple customer.",
            );
        }
        echo json_encode($json);


    }


    public function getContainerProducts() {
        $post = $this->request->post;
        $container_no = $post['container_no'];

        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $stocks = $this->model['stock_ledger']->getBalanceContainerStocks($container_no);

        $json = array(
            'success' => true,
            'details' => $stocks
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

        $details = $post['sale_tax_invoice_details'];
        $this->model['company'] = $this->load->model('setup/company');
        $company =  $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
//        d($company,true);
        if($company['out_of_stock'] == 1)
        {

            $this->model['product'] = $this->load->model('inventory/product');
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
                    $filter ['document_id'] = $ID['sale_tax_invoice_id'];

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
        // d($data,true);
        $data['partner_type_id'] = 2;
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['product']= $this->load->model('inventory/product');
        $this->model['setting']= $this->load->model('common/setting');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['sale_tax_invoice_detail'] = $this->load->model('inventory/sale_tax_invoice_detail');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['document_type'] = $this->load->model('common/document_type');
        $this->model['customer_rate']= $this->load->model('inventory/customer_rate');


        // get Customer //
        $this->model['customer'] = $this->load->model('setup/customer');
        $customer = $this->model['customer']->getRow(array('customer_id' => $data['partner_id']));

        // get Document //
        // $document = $this->model[$this->getAlias()]->getNextDocument($this->document_type_id,$customer['customer_code']);

        // now getting document number without customer code
        if($data['sale_type'] == 'sale_tax_invoice')
        {
            // get sale tax no.
            $document = $this->model[$this->getAlias()]->getNextDocument($this->document_type_id);
            // d('sale tax inv');
            // d($document,true);
        }
        else
        {
            // get sale invoice no.
            // by using sale invoice document type id
            $document = $this->model[$this->getAlias()]->getSaleInvNextDocument(2);
            // d('sale inv');
            // d($document,true);
        }        

        $data['document_type_id'] = $this->document_type_id;
        $data['document_prefix'] = $document['document_prefix'];
        $data['document_no'] = $document['document_no'];
        $data['document_identity'] = $document['document_identity'];

        $data['ref_document_id'] = json_encode($data['ref_document_id']);

        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];

        $data['document_date'] = MySqlDate($data['document_date']);
        $data['base_net_amount'] = $data['net_amount'] * $data['conversion_rate'];

        if($data['po_date'] != '') {
            $data['po_date'] = MySqlDate($data['po_date']);
        } else {
            $data['po_date'] = NULL;
        }
        if($data['bilty_date'] != '') {
            $data['bilty_date'] = MySqlDate($data['bilty_date']);
        } else {
            $data['bilty_date'] = NULL;
        }

        if($data['sale_type'] == 'sale_tax_invoice')
        {
            // get sale tax no.
            $this->model['sale_tax_no'] = $this->load->model('inventory/sale_tax_invoice');
            $sale_tax_no = $this->model['sale_tax_no']->getSaleTaxNo();
            // d($sale_tax_no,true);
            $sale_tax_no = $sale_tax_no['sale_tax_no'] + 1;
            $sale_tax_no = '000'.$sale_tax_no;
            $data['sale_tax_no'] = $sale_tax_no;
            // d($manual_ref_no,true);
        }

        // insert Invoice
        $sale_tax_invoice_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $data['document_id'] = $sale_tax_invoice_id;

        // document add
        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $sale_tax_invoice_id,
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['net_amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['net_amount'],
            'exempted' => $data['exempted'],
        );
        // insert document
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
            'module' => 'inventory',
            'field' => 'gr_ir_account_id',
        ));
        $gr_ir_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'labour_charges_account_id',
        ));
        $labour_charges_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'misc_charges_account_id',
        ));
        $misc_charges_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'rent_charges_account_id',
        ));
        $rent_charges_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'cartage_account_id',
        ));
        $cartage_account_id = $setting['value'];


        $this->model['stock'] = $this->load->model('common/stock_ledger');
        //  Add Invoice Detail
        foreach ($data['sale_tax_invoice_details'] as $sort_order => $detail) {

            $stock = $this->model['stock']->getWarehouseStock($detail['product_id'], $detail['warehouse_id'], $data['document_identity'], $data['document_date']);
            if(!empty($detail['ref_document_detail_id'])){
                $this->model['delivery_challan_detail'] = $this->load->model('inventory/delivery_challan_detail');    
                $delivery_challan_detail = $this->model['delivery_challan_detail']->getRow([
                    'delivery_challan_detail_id' => $detail['ref_document_detail_id'],
                    'company_id' => $this->session->data['company_id']
                ]);
                $detail['cog_rate'] = $delivery_challan_detail['cog_rate'];
                $detail['cog_amount'] = $delivery_challan_detail['cog_amount'];
            } 
            else {
                    $detail['cog_rate'] = $stock['avg_stock_rate'];                    
                    $detail['cog_amount'] = ($detail['qty']*$stock['avg_stock_rate']);
            }

            // d($detail,true);
            $detail['sale_tax_invoice_id'] = $sale_tax_invoice_id;
            $detail['sort_order'] = $sort_order;
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_total'] = $detail['total_amount'] * $data['conversion_rate'];
            $sale_tax_invoice_detail_id = $this->model['sale_tax_invoice_detail']->add($this->getAlias(), $detail);

            $customer_rate = array(
                'company_id' => $detail['company_id'],
                'customer_id' => $data['partner_id'],
                'product_id' => $detail['product_id'],
                'rate' => $detail['rate'],
                'invoice_date' => $data['document_date'],
                'created_at' => date("Y-m-d H:i:s"),
            );

            // Add customer_rate //
            $rate_id = $this->model['customer_rate']->add($this->getAlias(), $customer_rate);
            $product = $this->model['product']->getRow(array('product_id' => $detail['product_id']));
            // d($product);
            // d($detail['ref_document_type_id'],true);
            if($detail['ref_document_type_id'] == 16) {
                // If we are making Invoice through Delivery Challan.
                // $gl_data[] = array(
                //     'document_detail_id' => $sale_tax_invoice_detail_id,
                //     'ref_document_type_id' => $detail['ref_document_type_id'],
                //     'ref_document_id' => $detail['ref_document_id'],
                //     'ref_document_identity' => $detail['ref_document_identity'],
                //     'coa_id' => $gr_ir_account_id,
                //     'document_credit' => $detail['cog_amount'],
                //     'document_debit' => 0,
                //     'credit' => ($detail['cog_amount'] * $data['conversion_rate']),
                //     'debit' => 0,
                //     'product_id' => $detail['product_id'],
                //     'qty' => $detail['qty'],
                //     'document_amount' => $detail['cog_amount'],
                //     'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                //     'comment' => 'GRIR Account Debit',
                // );
                // $gl_data[] = array(
                //     'document_detail_id' => $sale_tax_invoice_detail_id,
                //     'ref_document_type_id' => $detail['ref_document_type_id'],
                //     'ref_document_id' => $detail['ref_document_id'],
                //     'ref_document_identity' => $detail['ref_document_identity'],
                //     'coa_id' => $product['cogs_account_id'],
                //     'document_debit' => $detail['cog_amount'],
                //     'document_credit' => 0,
                //     'debit' => ($detail['cog_amount'] * $data['conversion_rate']),
                //     'credit' => 0,
                //     'product_id' => $detail['product_id'],
                //     'qty' => $detail['qty'],
                //     'document_amount' => $detail['cog_amount'],
                //     'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                //     'comment' => 'COG Account',
                // );                
            
            } else {

                $stock_ledger = array(
                    'company_id' => $detail['company_id'],
                    'company_branch_id' => $detail['company_branch_id'],
                    'fiscal_year_id' => $detail['fiscal_year_id'],
                    'document_type_id' => $data['document_type_id'],
                    'document_id' => $data['document_id'],
                    'document_identity' => $data['document_identity'],
                    'document_date' => $data['document_date'],
                    'document_detail_id' => $sale_tax_invoice_detail_id,
                    'warehouse_id' => $detail['warehouse_id'],
                    'container_no' => $detail['container_no'],
                    'batch_no' => $detail['batch_no'],
                    'product_id' => $detail['product_id'],
                    'document_unit_id' => $detail['unit_id'],
                    'document_qty' => (-1 * $detail['qty']),
                    'unit_conversion' => 1,
                    'base_unit_id' => $detail['unit_id'],
                    'base_qty' => (-1 * $detail['qty']),
                    'document_currency_id' => $detail['document_currency_id'],
                    'document_rate' => $detail['cog_rate'],
                    'document_amount' => (-1 * $detail['cog_amount']),
                    // 'document_rate' => $stock['avg_stock_rate'],
                    // 'document_amount' => (-1 * ($detail['qty'] * $stock['avg_stock_rate'])),
                    'currency_conversion' => $detail['conversion_rate'],
                    'base_currency_id' => $detail['base_currency_id'],
                    'base_rate' => ($detail['cog_rate'] * $detail['conversion_rate']),
                    'base_amount' => (-1 * ($detail['cog_amount']*$detail['conversion_rate'])),
                    // 'base_rate' => ($stock['avg_stock_rate'] * $detail['conversion_rate']),
                    // 'base_amount' => (-1 * ($detail['qty'] * $stock['avg_stock_rate'] * $detail['conversion_rate'])),
                );

                // Add Stock Ledger //
                $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $stock_ledger);
                    
                // If we are making direct invoice.
                $gl_data[] = array(
                    'document_detail_id' => $sale_tax_invoice_detail_id,
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
                    'comment' => 'Inventory Account',
                );
                $gl_data[] = array(
                    'document_detail_id' => $sale_tax_invoice_detail_id,
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
                    'comment' => 'COGS Account',
                );
            }

            if( floatval($detail['amount']) > 0 ){

                $gl_data[] = array(
                    'document_detail_id' => $sale_tax_invoice_detail_id,
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
                    'comment' => 'Revenue Account',
                );
            }

            // if(floatval($detail['discount_amount']) > 0) {
            //     $gl_data[] = array(
            //         'document_detail_id' => $sale_tax_invoice_detail_id,
            //         'ref_document_type_id' => $detail['ref_document_type_id'],
            //         'ref_document_identity' => $detail['ref_document_identity'],
            //         'coa_id' => $sale_discount_account_id,
            //         'document_credit' => 0,
            //         'document_debit' => $detail['discount_amount'],
            //         'credit' => 0,
            //         'debit' => ($detail['discount_amount'] * $data['conversion_rate']),
            //         'product_id' => $detail['product_id'],
            //         'qty' => $detail['qty'],
            //         'document_amount' => $detail['discount_amount'],
            //         'amount' => ($detail['discount_amount'] * $data['conversion_rate']),
            //         'comment' => 'Item Discount Account',
            //     );
            // }

            // if(floatval($detail['tax_amount']) > 0) {
            //     $gl_data[] = array(
            //         'document_detail_id' => $sale_tax_invoice_detail_id,
            //         'ref_document_type_id' => $detail['ref_document_type_id'],
            //         'ref_document_id' => $detail['ref_document_id'],
            //         'ref_document_identity' => $detail['ref_document_identity'],
            //         'coa_id' => $sale_tax_account_id,
            //         'document_credit' => $detail['tax_amount'],
            //         'document_debit' => 0,
            //         'credit' => ($detail['tax_amount'] * $data['conversion_rate']),
            //         'debit' => 0,
            //         'product_id' => $detail['product_id'],
            //         'qty' => $detail['qty'],
            //         'document_amount' => $detail['tax_amount'],
            //         'amount' => ($detail['tax_amount'] * $data['conversion_rate']),
            //         'comment' => 'Sales Tax Account',
            //     );
            // }
        }

        $partner = $this->model['partner']->getRow(array('company_branch_id' => $this->session->data['company_branch_id'], 'partner_type_id' => 2, 'partner_id' => $data['partner_id']));
        $outstanding_account_id = $partner['outstanding_account_id'];
        // d($outstanding_account_id);
        //d(array($data, $partner, $outstanding_account_id), true);

        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $outstanding_account_id,
            'document_credit' => 0,
            'document_debit' => $data['net_amount'],
            'credit' => 0,
            'debit' => ($data['net_amount'] * $data['conversion_rate']),
            'comment' => 'Outstanding Account Debit',
        );

        if(floatval($data['discount_amount']) > 0) {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $sale_discount_account_id,
                'document_credit' => 0,
                'document_debit' => $data['discount_amount'],
                'credit' => 0,
                'debit' => ($data['discount_amount'] * $data['conversion_rate']),
                'comment' => 'Additional Discount Account',
            );
        }

        if(floatval($data['labour_charges']) > 0) {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $labour_charges_account_id,
                'document_debit' => 0,
                'document_credit' => $data['labour_charges'],
                'debit' => 0,
                'credit' => ($data['labour_charges'] * $data['conversion_rate']),
                'comment' => 'Labour Charges Account',
            );
        }

        if(floatval($data['misc_charges']) > 0) {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $misc_charges_account_id,
                'document_debit' => 0,
                'document_credit' => $data['misc_charges'],
                'debit' => 0,
                'credit' => ($data['misc_charges'] * $data['conversion_rate']),
                'comment' => 'Misc Charges Account',
            );
        }

        if(floatval($data['rent_charges']) > 0) {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $rent_charges_account_id,
                'document_debit' => 0,
                'document_credit' => $data['rent_charges'],
                'debit' => 0,
                'credit' => ($data['rent_charges'] * $data['conversion_rate']),
                'comment' => 'Rent Charges Account',
            );
        }

        if(floatval($data['cartage']) > 0) {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $cartage_account_id,
                'document_debit' => 0,
                'document_credit' => $data['cartage'],
                'debit' => 0,
                'credit' => ($data['cartage'] * $data['conversion_rate']),
                'comment' => 'Cartage Account',
            );
        }

        if(floatval($data['cash_received']) > 0) {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $outstanding_account_id,
                'document_debit' => 0,
                'document_credit' => $data['cash_received'],
                'debit' => 0,
                'credit' => ($data['cash_received'] * $data['conversion_rate']),
                'comment' => 'Outstanding Account Cash Received',
            );

            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $cash_account_id,
                'document_credit' => 0,
                'document_debit' => $data['cash_received'],
                'credit' => 0,
                'debit' => ($data['cash_received'] * $data['conversion_rate']),
                'comment' => 'Cash Account Cash Received',
            );
        }

        if($data['invoice_type'] == 'Cash') {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $outstanding_account_id,
                'document_debit' => 0,
                'document_credit' => $data['net_amount'],
                'debit' => 0,
                'credit' => ($data['net_amount'] * $data['conversion_rate']),
                'comment' => 'Outstanding Account Cash Received',
            );

            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $cash_account_id,
                'document_credit' => 0,
                'document_debit' => $data['net_amount'],
                'credit' => 0,
                'debit' => ($data['net_amount'] * $data['conversion_rate']),
                'comment' => 'Cash Account Cash Received',
            );
        }
        $this->model['ledger'] = $this->load->model('gl/ledger');
        // d($gl_data, true);
        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $sale_tax_invoice_id;
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['sort_order'] = $sort_order;
            $ledger['base_currency_id'] = $data['base_currency_id'];
            $ledger['document_currency_id'] = $data['document_currency_id'];
            $ledger['conversion_rate'] = $data['conversion_rate'];
            $ledger['partner_type_id'] = $data['partner_type_id'];
            $ledger['partner_id'] = $data['partner_id'];
            $ledger['remarks'] = $data['remarks'];
            $ledger['po_no'] = $data['po_no'];
            $ledger['dc_no'] = $data['dc_no'];
            $ledger['customer_unit_id'] = $data['customer_unit_id'];
            $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);
        }

        return $sale_tax_invoice_id;
    }

    protected function updateData($primary_key, $data) {
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['product']= $this->load->model('inventory/product');
        $this->model['setting']= $this->load->model('common/setting');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['sale_tax_invoice_detail'] = $this->load->model('inventory/sale_tax_invoice_detail');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['customer_rate']= $this->load->model('inventory/customer_rate');

        $data['partner_type_id'] = 2;

        //d(array($primary_key, $data), true);
        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['document_date'] = MySqlDate($data['document_date']);

        if($data['po_date'] != '') {
            $data['po_date'] = MySqlDate($data['po_date']);
        }
        else
        {
            $data['po_date'] = NULL;
        }
        if($data['bilty_date'] != '')
        {
            $data['bilty_date'] = MySqlDate($data['bilty_date']);
        }
        else
        {
            $data['bilty_date'] = NULL;
        }

        if($data['exempted'] == 1)
        {
            $data['exempted'] = 1;

        }else   {
            $data['exempted'] =0;
        }

        $data['base_amount'] = $data['net_amount'] * $data['conversion_rate'];
        $sale_tax_invoice_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        $data['document_id'] = $sale_tax_invoice_id;

        $this->model['sale_tax_invoice_detail']->deleteBulk($this->getAlias(), array('sale_tax_invoice_id' => $sale_tax_invoice_id));
        $this->model['document']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));
        $this->model['ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $sale_tax_invoice_id,
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['net_amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['net_amount'],
            'exempted' => $data['exempted'],
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
            'module' => 'inventory',
            'field' => 'gr_ir_account_id',
        ));
        $gr_ir_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'labour_charges_account_id',
        ));
        $labour_charges_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'misc_charges_account_id',
        ));
        $misc_charges_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'rent_charges_account_id',
        ));
        $rent_charges_account_id = $setting['value'];

        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'inventory',
            'field' => 'cartage_account_id',
        ));
        $cartage_account_id = $setting['value'];

        //d(array($misc_charges_account_id, $labour_charges_account_id, $gr_ir_account_id, $sale_tax_account_id, $sale_discount_account_id, $cash_account_id), true);
        foreach ($data['sale_tax_invoice_details'] as $sort_order => $detail) {

            $this->model['stock'] = $this->load->model('common/stock_ledger');
            $stock = $this->model['stock']->getWarehouseStock($detail['product_id'], $detail['warehouse_id'], $data['document_identity'], $data['document_date']);

            if(!empty($detail['ref_document_detail_id'])){
                $this->model['delivery_challan_detail'] = $this->load->model('inventory/delivery_challan_detail');    
                $delivery_challan_detail = $this->model['delivery_challan_detail']->getRow([
                    'delivery_challan_detail_id' => $detail['ref_document_detail_id'],
                    'company_id' => $this->session->data['company_id']
                ]);
                $detail['cog_rate'] = $delivery_challan_detail['cog_rate'];
                $detail['cog_amount'] = $delivery_challan_detail['cog_amount'];
            } 
            else {
                    $detail['cog_rate'] = $stock['avg_stock_rate'];                    
                    $detail['cog_amount'] = ($detail['qty']*$stock['avg_stock_rate']);
            }

            $detail['sale_tax_invoice_id'] = $sale_tax_invoice_id;
            $detail['sort_order'] = $sort_order;
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_total'] = $detail['total_amount'] * $data['conversion_rate'];
            $sale_tax_invoice_detail_id = $this->model['sale_tax_invoice_detail']->add($this->getAlias(), $detail);
            
            //d(array($stock_ledger_id, $stock_ledger), true);

            $customer_rate = array(
                'company_id' => $detail['company_id'],
                'customer_id' => $data['partner_id'],
                'product_id' => $detail['product_id'],
                'rate' => $detail['rate'],
                'invoice_date' => $data['document_date'],
                'created_at' => date("Y-m-d H:i:s"),

            );

            // Add customer_rate //
            $rate_id = $this->model['customer_rate']->add($this->getAlias(), $customer_rate);
            $product = $this->model['product']->getRow(array('product_id' => $detail['product_id']));
            if($detail['ref_document_type_id'] == 16) {
                // If we are making Invoice through Delivery Challan.
                // $gl_data[] = array(
                //     'document_detail_id' => $sale_tax_invoice_detail_id,
                //     'ref_document_type_id' => $detail['ref_document_type_id'],
                //     'ref_document_id' => $detail['ref_document_id'],
                //     'ref_document_identity' => $detail['ref_document_identity'],
                //     'coa_id' => $gr_ir_account_id,
                //     'document_credit' => $detail['cog_amount'],
                //     'document_debit' => 0,
                //     'credit' => ($detail['cog_amount'] * $data['conversion_rate']),
                //     'debit' => 0,
                //     'product_id' => $detail['product_id'],
                //     'qty' => $detail['qty'],
                //     'document_amount' => $detail['cog_amount'],
                //     'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                //     'comment' => 'GRIR Account Debit',
                // );
                // $gl_data[] = array(
                //     'document_detail_id' => $sale_tax_invoice_detail_id,
                //     'ref_document_type_id' => $detail['ref_document_type_id'],
                //     'ref_document_id' => $detail['ref_document_id'],
                //     'ref_document_identity' => $detail['ref_document_identity'],
                //     'coa_id' => $product['cogs_account_id'],
                //     'document_debit' => $detail['cog_amount'],
                //     'document_credit' => 0,
                //     'debit' => ($detail['cog_amount'] * $data['conversion_rate']),
                //     'credit' => 0,
                //     'product_id' => $detail['product_id'],
                //     'qty' => $detail['qty'],
                //     'document_amount' => $detail['cog_amount'],
                //     'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                //     'comment' => 'COG Account',
                // );
            } else {

                $stock_ledger = array(
                'company_id' => $detail['company_id'],
                'company_branch_id' => $detail['company_branch_id'],
                'fiscal_year_id' => $detail['fiscal_year_id'],
                'document_type_id' => $data['document_type_id'],
                'document_id' => $data['document_id'],
                'document_identity' => $data['document_identity'],
                'document_date' => $data['document_date'],
                'document_detail_id' => $sale_tax_invoice_detail_id,
                'warehouse_id' => $detail['warehouse_id'],
                'container_no' => $detail['container_no'],
                'batch_no' => $detail['batch_no'],
                'product_id' => $detail['product_id'],
                'document_unit_id' => $detail['unit_id'],
                'document_qty' => (-1 * $detail['qty']),
                'unit_conversion' => 1,
                'base_unit_id' => $detail['unit_id'],
                'base_qty' => (-1 * $detail['qty']),
                'document_currency_id' => $detail['document_currency_id'],
                'document_rate' => $detail['cog_rate'],
                'document_amount' => (-1 * $detail['cog_amount']),
                // 'document_rate' => $stock['avg_stock_rate'],
                // 'document_amount' => (-1 * ($detail['qty'] * $stock['avg_stock_rate'])),
                'currency_conversion' => $detail['conversion_rate'],
                'base_currency_id' => $detail['base_currency_id'],
                'base_rate' => ($detail['cog_rate'] * $detail['conversion_rate']),
                'base_amount' => (-1 * ($detail['cog_amount']*$detail['conversion_rate'])),
                // 'base_rate' => ($stock['avg_stock_rate'] * $detail['conversion_rate']),
                // 'base_amount' => (-1 * ($detail['qty'] * $stock['avg_stock_rate'] * $detail['conversion_rate'])),
            );
            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $stock_ledger);


                // If we are making direct invoice.
                $gl_data[] = array(
                    'document_detail_id' => $sale_tax_invoice_detail_id,
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
                    'comment' => 'Inventory Account',
                );
                $gl_data[] = array(
                    'document_detail_id' => $sale_tax_invoice_detail_id,
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
                    'comment' => 'COGS Account',
                );
            }

            if(floatval($detail['amount']) > 0) {
                
                $gl_data[] = array(
                    'document_detail_id' => $sale_tax_invoice_detail_id,
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
                    'comment' => 'Revenue Account',
                );
            }

            // if(floatval($detail['discount_amount']) > 0) {
            //     $gl_data[] = array(
            //         'document_detail_id' => $sale_tax_invoice_detail_id,
            //         'ref_document_type_id' => $detail['ref_document_type_id'],
            //         'ref_document_identity' => $detail['ref_document_identity'],
            //         'coa_id' => $sale_discount_account_id,
            //         'document_credit' => 0,
            //         'document_debit' => $detail['discount_amount'],
            //         'credit' => 0,
            //         'debit' => ($detail['discount_amount'] * $data['conversion_rate']),
            //         'product_id' => $detail['product_id'],
            //         'qty' => $detail['qty'],
            //         'document_amount' => $detail['discount_amount'],
            //         'amount' => ($detail['discount_amount'] * $data['conversion_rate']),
            //         'comment' => 'Item Discount Account',
            //     );
            // }

            // if(floatval($detail['tax_amount']) > 0) {
            //     $gl_data[] = array(
            //         'document_detail_id' => $sale_tax_invoice_detail_id,
            //         'ref_document_type_id' => $detail['ref_document_type_id'],
            //         'ref_document_id' => $detail['ref_document_id'],
            //         'ref_document_identity' => $detail['ref_document_identity'],
            //         'coa_id' => $sale_tax_account_id,
            //         'document_credit' => $detail['tax_amount'],
            //         'document_debit' => 0,
            //         'credit' => ($detail['tax_amount'] * $data['conversion_rate']),
            //         'debit' => 0,
            //         'product_id' => $detail['product_id'],
            //         'qty' => $detail['qty'],
            //         'document_amount' => $detail['tax_amount'],
            //         'amount' => ($detail['tax_amount'] * $data['conversion_rate']),
            //         'comment' => 'Sales Tax Account',
            //     );
            // }
        }

        $partner = $this->model['partner']->getRow(array('company_branch_id' => $this->session->data['company_branch_id'], 'partner_type_id' => 2, 'partner_id' => $data['partner_id']));
        $outstanding_account_id = $partner['outstanding_account_id'];

        $gl_data[] = array(
            'ref_document_type_id' => $this->document_type_id,
            'ref_document_identity' => $data['document_identity'],
            'coa_id' => $outstanding_account_id,
            'document_credit' => 0,
            'document_debit' => $data['net_amount'],
            'credit' => 0,
            'debit' => ($data['net_amount'] * $data['conversion_rate']),
            'comment' => 'Outstanding Account Debit',
        );

        if(floatval($data['discount_amount']) > 0) {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $sale_discount_account_id,
                'document_credit' => 0,
                'document_debit' => $data['discount_amount'],
                'credit' => 0,
                'debit' => ($data['discount_amount'] * $data['conversion_rate']),
                'comment' => 'Additional Discount Account',
            );
        }

        if(floatval($data['labour_charges']) > 0) {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $labour_charges_account_id,
                'document_debit' => 0,
                'document_credit' => $data['labour_charges'],
                'debit' => 0,
                'credit' => ($data['labour_charges'] * $data['conversion_rate']),
                'comment' => 'Labour Charges Account',
            );
        }

        if(floatval($data['misc_charges']) > 0) {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $misc_charges_account_id,
                'document_debit' => 0,
                'document_credit' => $data['misc_charges'],
                'debit' => 0,
                'credit' => ($data['misc_charges'] * $data['conversion_rate']),
                'comment' => 'Misc Charges Account',
            );
        }

        if(floatval($data['rent_charges']) > 0) {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $rent_charges_account_id,
                'document_debit' => 0,
                'document_credit' => $data['rent_charges'],
                'debit' => 0,
                'credit' => ($data['rent_charges'] * $data['conversion_rate']),
                'comment' => 'Rent Charges Account',
            );
        }

        if(floatval($data['cartage']) > 0) {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $cartage_account_id,
                'document_debit' => 0,
                'document_credit' => $data['cartage'],
                'debit' => 0,
                'credit' => ($data['cartage'] * $data['conversion_rate']),
                'comment' => 'Cartage Account',
            );
        }

        if(floatval($data['cash_received']) > 0) {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $outstanding_account_id,
                'document_debit' => 0,
                'document_credit' => $data['cash_received'],
                'debit' => 0,
                'credit' => ($data['cash_received'] * $data['conversion_rate']),
                'comment' => 'Outstanding Account Cash Received',
            );

            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $cash_account_id,
                'document_credit' => 0,
                'document_debit' => $data['cash_received'],
                'credit' => 0,
                'debit' => ($data['cash_received'] * $data['conversion_rate']),
                'comment' => 'Cash Account Cash Received',
            );
        }

        if($data['invoice_type'] == 'Cash') {
            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $outstanding_account_id,
                'document_debit' => 0,
                'document_credit' => $data['net_amount'],
                'debit' => 0,
                'credit' => ($data['net_amount'] * $data['conversion_rate']),
                'comment' => 'Outstanding Account Cash Received',
            );

            $gl_data[] = array(
                'ref_document_type_id' => $this->document_type_id,
                'ref_document_identity' => $data['document_identity'],
                'coa_id' => $cash_account_id,
                'document_credit' => 0,
                'document_debit' => $data['net_amount'],
                'credit' => 0,
                'debit' => ($data['net_amount'] * $data['conversion_rate']),
                'comment' => 'Cash Account Cash Received',
            );
        }
        $this->model['ledger'] = $this->load->model('gl/ledger');
        //d($gl_data, true);
        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $sale_tax_invoice_id;
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['sort_order'] = $sort_order;
            $ledger['base_currency_id'] = $data['base_currency_id'];
            $ledger['document_currency_id'] = $data['document_currency_id'];
            $ledger['conversion_rate'] = $data['conversion_rate'];
            $ledger['partner_type_id'] = $data['partner_type_id'];
            $ledger['partner_id'] = $data['partner_id'];
            $ledger['remarks'] = $data['remarks'];
            $ledger['po_no'] = $data['po_no'];
            $ledger['dc_no'] = $data['dc_no'];
            $ledger['customer_unit_id'] = $data['customer_unit_id'];

            $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);
        }

        return $sale_tax_invoice_id;
    }

    protected function deleteData($primary_key) {

       
        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->deleteBulk($this->getAlias(), array('document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['sale_tax_invoice_detail'] = $this->load->model('inventory/sale_tax_invoice_detail');
        $this->model['sale_tax_invoice_detail']->deleteBulk($this->getAlias(), array('sale_tax_invoice_id' => $primary_key));
        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

    public function getReferenceDocumentNos() {
        $sale_tax_invoice_id = $this->request->get['sale_tax_invoice_id'];
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
            'sale_tax_invoice_id' => $sale_tax_invoice_id,
            'post' => $post,
            'where' => $where,
            'orders' => $orders,
            'html' => $html
        );

        echo json_encode($json);
    }

    public function getReferenceDocument() {
        $sale_tax_invoice_id = $this->request->get['sale_tax_invoice_id'];
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
            $this->model['product'] = $this->load->model('inventory/product');
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
            'sale_tax_invoice_id' => $sale_tax_invoice_id,
            'post' => $post,
            'where' => $where,
            'details' => $details);
        echo json_encode($json);
    }

    public function printExemptedInvoice() {

        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);


        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        $sale_tax_invoice_id = $this->request->get['sale_tax_invoice_id'];
        $with_previous_balance = isset($this->request->get['with_previous_balance'])?1:0;

        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['sale_tax_invoice'] = $this->load->model('inventory/sale_tax_invoice');
        $this->model['sale_tax_invoice_detail'] = $this->load->model('inventory/sale_tax_invoice_detail');
        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');

        $invoice = $this->model['sale_tax_invoice']->getRow(array('sale_tax_invoice_id' => $sale_tax_invoice_id));
        $partner = $this->model['partner']->getRow(array('partner_id' => $invoice['partner_id']));
        $Company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
//        d($Company,true);


        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('kashan');
        $pdf->SetTitle('Exempted Invoice');
        $pdf->SetSubject('Exempted Invoice');

        //Set Header

        $pdf->InvoiceCheck = "Exempted Bill";
        $pdf->data = array(
            'company_name' => $session['company_name'],
            //'report_name' => $lang['heading_title'],
            'report_name' => 'Exempted Invoice',
            'header_image' => HTTP_IMAGE.'header.jpg',
            'footer_image' => HTTP_IMAGE.'footer.jpg',
            'company_logo' => $session['company_image']
        );


        $pdf->SetMargins(25, 55, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 55);

        // add a page
        $pdf->AddPage();
        // set font

        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(160, 7, 'Date: '.stdDate($invoice['document_date']), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->ln(4);
        $pdf->Cell(80, 7, 'M/S: '.html_entity_decode($invoice['partner_name']), 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->ln(5);
        $pdf->Cell(80, 7, 'NTN No: '.$partner['ntn_no'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->ln(5);
        $pdf->Cell(80, 7, 'STR No: '.$partner['gst_no'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->ln(5);
        $pdf->Cell(80, 7, 'INVOICE # '.$invoice['document_identity'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->ln(5);
        $pdf->Cell(80, 7, 'Excluding Value: '.number_format($invoice['item_amount'],2), 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->ln(5);
        $pdf->Cell(80, 7, 'Sales Tax Value: '.number_format($invoice['item_tax'],2), 0, false, 'L', 0, '', 0, false, 'M', 'M');


        $pdf->ln(10);
        $html = $Company['description'];
        $pdf->writeHTML($html, true, false, true, false, '');

//        $pdf->SetFont('helvetica', 'B,U', 14);
//        $pdf->Cell(20, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(120, 7, 'Undertaking of Non Deduction of Tax on Supplies', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(20, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->ln(12);
//        $pdf->SetFont('helvetica', 'B', 14);
//        $pdf->Cell(20, 7, 'Dear Sir,', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->ln(8);
//        $pdf->SetFont('helvetica', 'B,U', 14);
//        $pdf->Cell(20, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(120, 7, 'Sales Tax:', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(20, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//
//        $pdf->ln(15);
//        $pdf->SetFont('helvetica', '', 14);
//        $pdf->Cell(180, 7, "We hereby confirm that we are not liable for the Withhold of Sales Tax Under The", 0, false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->ln(6);
//        $pdf->Cell(180, 7, "Sales Tax Special Procedure (Withholding) Rules, 2007 vide SRO 660(1)/2007", 0, false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->ln(6);
//        $pdf->Cell(180, 7, "Dated 30-06-2014 as supplies made by Commercial Importers on which VAT has", 0, false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->ln(6);
//        $pdf->Cell(180, 7, "been paid at the time of import re excluded under Rule 5 (Exclusion) of The Sales", 0, false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->ln(6);
//        $pdf->Cell(180, 7, "Tax Special Procedure (Withholding) Rules, 2007 vide SRO 897(1)/2013 Dated", 0, false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->ln(6);
//        $pdf->Cell(180, 7, "04-10-2013.", 0, false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->ln(15);
//
//
//        $pdf->SetFont('helvetica', 'B,U', 14);
//        $pdf->Cell(20, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(120, 7, 'Income Tax:', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(20, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//
//        $pdf->ln(15);
//        $pdf->SetFont('helvetica', '', 14);
//        $pdf->Cell(180, 7, "The supplied material is imported by us and Income Tax U/s 148 of the", 0, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->ln(6);
//        $pdf->Cell(180, 7, "Income Tax Ordinance, 2001 is already paid by us, so further tax deducted U/s 153", 0, false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->ln(6);
//        $pdf->Cell(180, 7, "of the Income Tax is not applicable on above mentioned supplies.", 0, false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->ln(10);
//        $pdf->Cell(180, 7, "We do hereby undertake to indemnify against any tax liability to be borne by", 0, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->ln(6);
//        $pdf->Cell(180, 7, "you in respect of the above transaction. And we also assure that we will be", 0, false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->ln(6);
//        $pdf->Cell(180, 7, "responsible for any misstatement in this regard.", 0, false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->ln(12);
//        $pdf->Cell(180, 7, "Thank you.", 0, false, 'L', 0, '', 0, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Exempted Invoice - '.$invoice['document_identity'].'.pdf', 'I');

    }

    public function printCommercialInvoice() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        $sale_tax_invoice_id = $this->request->get['sale_tax_invoice_id'];
        $with_previous_balance = isset($this->request->get['with_previous_balance'])?1:0;

        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['sale_tax_invoice'] = $this->load->model('inventory/sale_tax_invoice');
        $this->model['sale_tax_invoice_detail'] = $this->load->model('inventory/sale_tax_invoice_detail');
        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
        $this->model['company'] = $this->load->model('setup/company_branch');
        $company_address = $this->model['company']->getRow(array('company_id' => $session['company_id']));

        $invoice = $this->model['sale_tax_invoice']->getRow(array('sale_tax_invoice_id' => $sale_tax_invoice_id));
        // d($invoice,true);
        $details = $this->model['sale_tax_invoice_detail']->getRows(array('sale_tax_invoice_id' => $sale_tax_invoice_id), array('sort_order asc'));
        $partner = $this->model['partner']->getRow(array('partner_id' => $invoice['partner_id']));
        $delivery_challan_id = $invoice['ref_document_id'];

        $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');
        $CustomerUnit = $this->model['customer_unit']->getRow(array('customer_unit_id' => $invoice['customer_unit_id']));

        $outstanding = $this->model['partner']->getOutstanding("l.`partner_id` = '".$invoice['partner_id']."' AND l.`created_at` < '".$invoice['created_at']."'");
        //d(array($sale_tax_invoice_id, $invoice, $details), true);
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

        $this->model['user'] = $this->load->model('user/user');
        $user = $this->model['user']->getRow(array('user_id' => $session['user_id']));

        $user_name=$user['user_name'];

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

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
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $company_branch = $this->model['company_branch']->getRow(array('company_branch_id' => $this->session->data['company_branch_id']));

        $company_branch_name=$company_branch['name'];

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('kashan');
        $pdf->SetTitle('Commercial Invoice');
        $pdf->SetSubject('Commercial Invoice');

        //Set Header
        $pdf->InvoiceCheck = 'CommercialInvoice';
        $pdf->data = array(
            'company_name' => $session['company_name'],
            //'report_name' => $lang['heading_title'],
            'report_name' => 'Bill',
            'company_logo' => $session['company_image'],
            'company_header_print' => $company_header_print,
            'company_footer_print' => $company_footer_print,
            'user_name' => $user_name,
            'company_branch_name' =>$company_branch_name,
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(7, 30, 7);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // add a page
        $pdf->AddPage();
        // set font

        $txt = $company_address['address'].' , 
        '.$company_address['name'].' 
        TEL: '.$company_address['phone_no'];


        if($invoice['po_date'] != '')
        {
            $invoice['po_date'] = stdDate($invoice['po_date']);
        }

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(255,255,255);

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(25, 5, 'Invoice No. : ', 0, false, 'L', 1, '', 1);
        $pdf->Cell(35, 5, $invoice['document_identity'], 0, false, 'L', 1, '', 1);

        $pdf->Cell(75, 5, '', 0, false, 'L', 1, '', 1);
        $pdf->Cell(25, 5, 'Date: ', 0, false, 'L', 1, '', 1);
        $pdf->Cell(35, 5, stdDate($invoice['document_date']), 0, false, 'L', 1, '', 1);
        
        $pdf->ln(5);
        $pdf->Cell(25, 5, 'M/s. : ', 0, false, 'L', 1, '', 1);
        $pdf->Cell(110, 5, $partner['name'], 0, false, 'L', 1, '', 1);
        
        $pdf->Cell(25, 5, 'PO No : ', 0, false, 'L', 1, '', 1);
        $pdf->Cell(35, 5, $invoice['po_no'], 0, false, 'L', 1, '', 1);

        $pdf->ln(5);
        
        $arrAddress = splitString($partner['address'], 60);

        if($this->session->data['company_branch_id']!=24){

            $pdf->Cell(25, 5, 'Address : ', 0, false, 'L', 1, '', 1);
            $pdf->Cell(110, 5, $arrAddress[0], 0, false, 'L', 1, '', 1);

            $pdf->Cell(25, 5, 'NTN No. : ', 0, false, 'L', 1, '', 1);
            $pdf->Cell(35, 5, '4550927-1', 0, false, 'L', 1, '', 1);
            $pdf->ln(5);

            $pdf->Cell(25, 5, '', 0, false, 'L', 1, '', 1);
            $pdf->Cell(110, 5, $arrAddress[1], 0, false, 'L', 1, '', 1);

        
            $pdf->ln(5);
            $pdf->Cell(25, 5, '', 0, false, 'L', 1, '', 1);
            $pdf->Cell(110, 5, $arrAddress[2], 0, false, 'L', 1, '', 1);
        
            $pdf->Cell(25, 5, 'HS Code : ', 0, false, 'L', 1, '', 1);
            $pdf->Cell(35, 5, $invoice['hs_code'], 0, false, 'L', 1, '', 1);

            $pdf->ln(5);
            $pdf->Cell(25, 5, 'NTN No. : ', 0, false, 'L', 1, '', 1);
            $pdf->Cell(110, 5, $partner['ntn_no'], 0, false, 'L', 1, '', 1);
        
            $pdf->Cell(25, 5, 'Manual Ref. No : ', 0, false, 'L', 1, '', 1);
            $pdf->Cell(35, 5, $invoice['manual_ref_no'], 0, false, 'L', 1, '', 1);

           

            $pdf->ln(5);
            $pdf->Cell(25, 5, 'GST No. : ', 0, false, 'L', 1, '', 1);
            $pdf->Cell(110, 5, $partner['gst_no'], 0, false, 'L', 1, '', 1);

    
            $pdf->Cell(25, 5, 'Reference : ', 0, false, 'L', 1, '', 1);
            $pdf->Cell(35, 5, $invoice['dc_no'], 0, false, 'L', 1, '', 1);

        }
        else{
            $pdf->Cell(25, 5, 'Address : ', 0, false, 'L', 1, '', 1);
            $pdf->Cell(110, 5, $arrAddress[0], 0, false, 'L', 1, '', 1);

            $pdf->Cell(25, 5, 'Manual Ref. No : ', 0, false, 'L', 1, '', 1);
            $pdf->Cell(35, 5, $invoice['manual_ref_no'], 0, false, 'L', 1, '', 1);

            $pdf->ln(5);

            $pdf->Cell(25, 5, '', 0, false, 'L', 1, '', 1);
            $pdf->Cell(110, 5, $arrAddress[1], 0, false, 'L', 1, '', 1);

            $pdf->Cell(25, 5, 'Reference : ', 0, false, 'L', 1, '', 1);
            $pdf->Cell(35, 5, $invoice['dc_no'], 0, false, 'L', 1, '', 1);
        }

        $x= $pdf->GetX();
        $y= $pdf->GetY();
        $pdf->SetXY($x, $y);

        $y = $y + 10;

        $pdf->ln(6);
        // set font
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);


        $pdf->MultiCell(10, 7, 'SNo.', 1, 'C', false, 0, 7, $y, true, 0, false, true, 0, 'M', true);
        $pdf->MultiCell(95, 7, 'Particulars', 1, 'C', false, 0, (7+10), $y, true, 0, false, true, 0, 'M', true);
        $pdf->MultiCell(20, 7, 'Qty', 1, 'C', false, 0, (7+10+95), $y, true, 0, false, true, 0, 'M', true);
        $pdf->MultiCell(20, 7, 'Unit', 1, 'C', false, 0, (7+10+95+20), $y, true, 0, false, true, 0, 'M', true);
        $pdf->MultiCell(25, 7, 'Rate', 1, 'C', false, 0, (7+10+95+20+20), $y, true, 0, false, true, 0, 'M', true);
        $pdf->MultiCell(25, 7, 'Amount', 1, 'C', false, 0, (7+10+95+20+20+25), $y, true, 0, false, true, 0, 'M', true);

        $x= $pdf->GetX();
        $y= $pdf->GetY();
        $pdf->SetXY($x, $y);

        $pdf->Ln(-1);
        $pdf->Ln(1);


        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $total_amount = 0;
        $total_discount = 0;
        $total_tax = 0;
        $total_net = 0;
        $sr = 0;
        $pdf->SetFont('helvetica', '', 9);

        $SalesTax='';
        foreach($details as $detail) {
            $SalesTax = $detail['tax_percent'];
            $sr++;
                        
            $pdf->ln(7);
            if(strlen($detail['description'])<=45){

                    $pdf->Cell(10, 7, $sr, 1, false, 'C', 0, '', 1);
                    $pdf->Cell(95, 7, html_entity_decode($detail['product_name']), 1, false, 'L', 0, '', 1);
                    $pdf->Cell(20, 7, number_format($detail['qty'],2), 1, false, 'R', 0, '', 1);
                    $pdf->Cell(20, 7, $detail['unit'], 1, false, 'C', 0, '', 1);
                    $pdf->Cell(25, 7, number_format($detail['rate'],2), 1, false, 'R', 0, '', 1);
                    $pdf->Cell(25, 7, number_format($detail['amount'],2), 1, false, 'R', 0, '', 1);
                
            } else {
                $arrDesc = splitString($detail['description'], 45);
                
                foreach($arrDesc as $index => $remark){
                    
                        if($index==0){
                            $pdf->Cell(10, 4, $sr, 'LR', false, 'C', 0, '', 1);
                            $pdf->Cell(95, 4, $remark, 'LR', false, 'L', 0, '', 1);
                            $pdf->Cell(20, 4, number_format($detail['qty'],2), 'LR', false, 'R', 0, '', 1);
                            $pdf->Cell(20, 4, $detail['unit'], 'LR', false, 'C', 0, '', 1);
                            $pdf->Cell(25, 4, number_format($detail['rate'],2), 'LR', false, 'R', 0, '', 1);
                            $pdf->Cell(25, 4, number_format($detail['amount'],2), 'LR', false, 'R', 0, '', 1);
                        } else if($index<=count($arrDesc)-1){
                            $pdf->Cell(10, 4, '', 'LR', false, 'C', 0, '', 1);
                            $pdf->Cell(95, 4, $remark, 'LR', false, 'L', 0, '', 1);
                            $pdf->Cell(20, 4, '', 'LR', false, 'R', 0, '', 1);
                            $pdf->Cell(20, 4, '', 'LR', false, 'C', 0, '', 1);
                            $pdf->Cell(25, 4, '', 'LR', false, 'R', 0, '', 1);
                            $pdf->Cell(25, 4, '', 'LR', false, 'R', 0, '', 1);
                        } else {
                            $pdf->Cell(10, 4, '', 'LR', false, 'C', 0, '', 1);
                            $pdf->Cell(95, 4, $remark, 'LR', false, 'L', 0, '', 1);
                            $pdf->Cell(20, 4, '', 'LR', false, 'R', 0, '', 1);
                            $pdf->Cell(20, 4, '', 'LR', false, 'C', 0, '', 1);
                            $pdf->Cell(25, 4, '', 'LR', false, 'R', 0, '', 1);
                            $pdf->Cell(25, 4, '', 'LR', false, 'R', 0, '', 1);
                        }

                    $pdf->Ln(4);
                }
                
                $pdf->Ln(-2);
                $pdf->Cell(195.1, 1, '', 'B', false, 'C', 0, '', 0, false, 'M', 'M');
                $pdf->Ln(-5);
            }

            $total_amount += $detail['amount'];
            $total_discount += $detail['discount_amount'];
            $total_tax += $detail['tax_amount'];
            $total_net += $detail['total_amount'];
        }
        $amount = $total_amount-$total_discount;
        $net_amount = $amount+$total_tax;
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);

        $pdf->Ln(6);

        $pdf->Ln(-6);
        $pdf->Ln(7);
        
        $pdf->SetFont('helvetica', 'B', 8);
        
            $pdf->Cell(10+95+20+20+25, 7, 'Total', 1, false, 'C', 0, '', 1);
            $pdf->Cell(25, 7, number_format($total_amount,2), 1, false, 'R', 0, '', 1);


        $pdf->Ln(7);
        $pdf->Cell(10+55+15+15+20+20, 7, '', 0, false, 'R', 0, '', 1);
        $pdf->Cell(20, 7, 'Discount', 0, false, 'L', 0, '', 1);
        $pdf->Cell(20+20, 7, number_format($total_discount, 2), 0, false, 'R', 0, '', 1);

        $pdf->Ln(7);
        $pdf->Cell(10+55+15+15+20+20, 7, '', 0, false, 'R', 0, '', 1);
        $pdf->Cell(20, 7, 'Net Amount', 0, false, 'L', 0, '', 1);
        $pdf->Cell(20+20, 7, number_format($amount,2), 0, false, 'R', 0, '', 1);

        $pdf->Ln(7);
        $pdf->Cell(10+55+15+15+20+20, 7, '', 0, false, 'R', 0, '', 1);
        $pdf->Cell(20, 7, 'Tax Amount', 0, false, 'L', 0, '', 1);
        $pdf->Cell(20+20, 7, number_format($total_tax,2), 0, false, 'R', 0, '', 1);

        $pdf->Ln(7);
        $pdf->Cell(10+55+15+15+20+20, 7, '', 0, false, 'R', 0, '', 1);
        $pdf->Cell(20, 7, 'Total Amount', 0, false, 'L', 0, '', 1);
        $pdf->Cell(20+20, 7, number_format($net_amount,2), 0, false, 'R', 0, '', 1);

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);

        $arrRemarks = [];
        $arrRemarks[] = 'IN WORD: ' . Number2Words(number_format($invoice['net_amount'],2)). ' only';
        $arrRemarks[] = 'Remarks: ' . $invoice['remarks'];
        $pdf->MultiCell(10+55+15+15+20, 20, implode("\n", $arrRemarks), 0, 'L', false, 0, 7, $y-13, true, 0, false, true, 0, 'T', true);

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);

        //Close and output PDF document
        $pdf->Output($invoice['sale_type'].' - '.$invoice['document_identity'].'.pdf', 'I');

    }


     public function printSaleReceipt() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);
        $height = 0;

        //d(array('session' => $this->session->data, 'post' => $this->request->post, 'get' => $this->request->get), true);
        $lang = $this->load->language($this->getAlias());
        $sale_tax_invoice_id = $this->request->get['sale_tax_invoice_id'];
        $post = $this->request->post;
        $session = $this->session->data;
        
        $this->model['user'] = $this->load->model('user/user');
        $user = $this->model['user']->getRow(array('user_id' => $session['user_id']));
        $user_name = $user['user_name'];

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->model['sale_tax_invoice'] = $this->load->model('inventory/sale_tax_invoice');
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['sale_tax_invoice_detail'] = $this->load->model('inventory/sale_tax_invoice_detail');
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $invoice = $this->model['sale_tax_invoice']->getRow(array('sale_tax_invoice_id' => $sale_tax_invoice_id));
        $details = $this->model['sale_tax_invoice_detail']->getRows(array('sale_tax_invoice_id' => $sale_tax_invoice_id),array('sort_order asc'));
        $partner = $this->model['partner']->getRow(array('partner_id' => $invoice['partner_id']));
        $where = 'AND l.partner_id = "'.$partner['partner_id'].'"';
        $where.= 'AND l.coa_id = "'.$partner['outstanding_account_id'].'"';
        $where.= 'AND l.document_identity < "'.$invoice['document_identity'].'"';

        $previous_balance =  $this->model['sale_tax_invoice']->getPreviousBalance($where);



        $invoice_date = date_create($invoice['created_at']);
        $invoice_date = date_format($invoice_date,'d/m/Y h:i:s a');
        //d([$company, $branch], true);
        $pdf = new PDF('P', PDF_UNIT, array(74,240), true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Kashan Khalid');
        $pdf->SetTitle('RECEIPT');
        $pdf->SetSubject('RECEIPT');

        //Set Header
//        $pdf->data = array(
//            'company_name' => $session['company_name'],
//            'company_address' => $branch['address'],
//            'company_phone' => $branch['phone_no'],
//            'report_name' => $lang['heading_title'],
//            'company_logo' => $session['company_image']
//        );
        $pdf->data = array(
            'company_name' => $branch['company_name'],
            'company_address' => $branch['address'],
            'company_phone' => $branch['phone_no'],
            'report_name' => 'RECEIPT',
            'print_by' => $user_name,
            'company_logo' => $session['company_image']
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
            //$this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
            $pdf->Image($image_file, 0, 0, 32, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            $pdf->Ln(22);
        } else {
            // Set font
            $pdf->SetTextColor(0,0,0);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Ln(2);
            // Title
            $pdf->Cell(0, 10, $pdf->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        }
        $pdf->Ln(4);
        if($pdf->data['company_address']) {
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(0, 6, $pdf->data['company_address'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Ln(4);
        }
       
        if($pdf->data['company_phone']) {
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(0, 6, 'Phone: '.$pdf->data['company_phone'], 0, false, 'C',0, '', 0, false, 'M', 'M');
            $pdf->Ln(4);
        }
        $pdf->SetTextColor(0,0,0);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(0, 4, $pdf->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('times', 'B', 9);
        $pdf->Ln(4);
    

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
        $pdf->Cell(24, 4, 'Invoice To - '.$partner['name'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->setX(31,true);
        $pdf->Cell(20, 4, 'Invoice # '.$invoice['document_identity'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(4);
        $pdf->Cell(2.6, 4, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(24, 4, 'Print By : '.$pdf->data['print_by'], 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(19.1, 4, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(26, 4, 'Date : '.$invoice_date, 0, false, 'R', 0, '', 0, false, 'M', 'M');


       // $pdf->Cell(24, 4, $partner['mobile'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        //   if(strlen($invoice['remarks']) < 38){
        //     $pdf->Cell(40,5,$invoice['remarks'],0,false,'L',0,'',0,false,'M','M');
        //   }
        //   else{

        //    $remarks = splitString($invoice['remarks'],38);
        //     foreach ($remarks as $key => $value) {
        //         if($key == 0){
        //             $pdf->Cell(40, 5, trim($value) , 0, false, 'L', 0, '', 0, false, 'M', 'M');
        //         }
        //         else {
        //              $pdf->Cell(40, 5, trim($value) , 0, false, 'L', 0, '', 0, false, 'M', 'M');
        //         }
      
        //         $pdf->ln(5);
        //     }
        // }

        // if(strlen($invoice['remarks']) < 38 ){
        //     $pdf->Ln(6);
        // }
        // else {
        //     $pdf->Ln(1); 
        // }

        $pdf->Ln(4.5);
        $pdf->SetFont('times', 'B', 8);
        $pdf->Cell(80, 4, '', 'T', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(1);
        $pdf->setX(2);
        $pdf->Cell(5, 4, 'S.No', '', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(34, 4, 'PARTICULARS', '', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(4, 4, 'QTY', '', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setX(49);
        $pdf->Cell(7, 4, 'RATE', '', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setX(61);
        $pdf->Cell(12, 4, 'AMOUNT', '', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(5);
        $pdf->Cell(80, 4, '', 'T', false, 'L', 0, '', 0, false, 'M', 'M');


        $pdf->SetFont('times', '', 8);
        $sr = 0;
        $total_amount = 0;
        $total_net_amount = 0;
        $total_qty = 0;
        $total_balance = 0;

        // if(strlen($invoice['remarks']) < 38 ){
        //     $pdf->setY(27);
        // }
        // else {
        //     $pdf->setY(31);
        // }

        if($pdf->data['company_logo'] !== ''){
          $pdf->setY(50);
        }
        else {
          $pdf->setY(32);
        }
        foreach($details as $detail) {
            $sr++;
            if(strlen($detail['description']) < 15) {
            $pdf->Ln(4);
            $pdf->Cell(5, 4, $sr, 0,  false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->setX(7);
            $pdf->Cell(40, 4, $detail['description'], 0, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->setX(37);
            $pdf->Cell(8, 4, number_format($detail['qty'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->setX(48);
            $pdf->Cell(9, 4, number_format($detail['rate'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->setX(61);
            $pdf->Cell(12, 4, number_format($detail['amount'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');

            }
            else {
            $arrDesc = splitString($detail['description'], 18);

                foreach($arrDesc as $index => $remark){
                    
                        if($index==0){
                            $pdf->Ln(2);
                            $pdf->Cell(5, 2, $sr, '', false, 'R', 0, '', 1);
                            $pdf->setX(10);
                            $pdf->Cell(40, 2, $remark, '', false, 'L', 0, '', 1);
                            $pdf->Ln(2);
                            $pdf->setX(37);
                            $pdf->Cell(8, 4, number_format($detail['qty'],0), 0, false, 'C', 0, '', 0, false, 'M', 'M');
                            $pdf->setX(48);
                            $pdf->Cell(9, 4, number_format($detail['rate'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');
                            $pdf->setX(61);
                            $pdf->Cell(12, 4, number_format($detail['amount'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');
                            $pdf->Ln(2);
                        } 
                        else if($index<=count($arrDesc)-1){
                            $pdf->Cell(5, 4, '', '', false, 'R', 0, '', 1);
                            $pdf->setX(10);
                            $pdf->Cell(40, 4, $remark, '', false, 'L', 0, '', 1);
                            $pdf->Cell(8, 4, '', '', false, 'C', 0, '', 1);
                            $pdf->Cell(9, 4, '', '', false, 'R', 0, '', 1);
                            $pdf->Cell(12, 4, '', '', false, 'R', 0, '', 1);
                            $pdf->Ln(4);
                        } else {
                            $pdf->Cell(4, 4, '', '', false, 'R', 0, '', 1);
                            $pdf->setX(10);
                            $pdf->Cell(40, 4, $remark, '', false, 'L', 0, '', 1);
                            $pdf->Cell(8, 4, '', '', false, 'R', 0, '', 1);
                            $pdf->Cell(9, 4, '', '', false, 'R', 0, '', 1);
                            $pdf->Cell(12, 4, '', '', false, 'R', 0, '', 1);
                            $pdf->Ln(4);
                        }
                  }
            }
            $total_amount += $detail['amount'];
            $total_qty += $detail['qty'];
        }
        $total_net_amount = ($total_amount + $invoice['cartage']);
        $total_balance = ($total_net_amount + $previous_balance - $invoice['cash_received'] - $invoice['discount_amount']);

        $pdf->Ln(4);
        $pdf->Cell(80, 4, '', 'T', false, 'L',0, '', 0, false, 'M', 'M');
        $pdf->Ln(2);
        $pdf->setX(38);
        $pdf->Cell(10, 5, number_format($total_qty,0), 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setX(58);
        $pdf->Cell(15, 4, number_format($total_amount,0), 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(2);
        $pdf->SetFont('times', 'B', 9);
        $pdf->Ln(4);
        $pdf->setX(37);
        $pdf->Cell(50, 4, 'Discount :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->setX(57);
        $pdf->Cell(15, 4, number_format($invoice['discount_amount'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(4);
        $pdf->setX(37);
        $pdf->Cell(50, 4, 'Net Bill :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->setX(57);
        $pdf->Cell(15, 4, number_format($total_net_amount,0), 'T', false, 'R', 0, '', 0, false, 'M', 'M');
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
        $pdf->Cell(30, 5, number_format($total_net_amount,0), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(5);
        $pdf->setX(15);
        $pdf->Cell(58, 5, 'Discounted Amount :', 1, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->setX(42);
        $pdf->Cell(30, 5, number_format($invoice['discount_amount'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(5);
        $pdf->setX(15);
        $pdf->Cell(58, 5, 'Amount Received :', 1, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->setX(42);
        $pdf->Cell(30, 5, number_format($invoice['cash_received'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');
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
        $pdf->Output('Receipt:'.date('YmdHis').'.pdf', 'I');
    }


   

    public function printSalesTaxInvoice() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;



        $sale_tax_invoice_id = $this->request->get['sale_tax_invoice_id'];
        $with_previous_balance = isset($this->request->get['with_previous_balance'])?1:0;

        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['sale_tax_invoice'] = $this->load->model('inventory/sale_tax_invoice');
        $this->model['sale_tax_invoice_detail'] = $this->load->model('inventory/sale_tax_invoice_detail');
        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
        $this->model['company'] = $this->load->model('setup/company_branch');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $company_address = $this->model['company']->getRow(array('company_id' => $session['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $invoice = $this->model['sale_tax_invoice']->getRow(array('sale_tax_invoice_id' => $sale_tax_invoice_id));
        // d($invoice,true);
        $details = $this->model['sale_tax_invoice_detail']->getRows(array('sale_tax_invoice_id' => $sale_tax_invoice_id), array('sort_order asc'));
        $partner = $this->model['partner']->getRow(array('partner_id' => $invoice['partner_id']));
        $delivery_challan_id = $invoice['ref_document_id'];

        $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');
        $CustomerUnit = $this->model['customer_unit']->getRow(array('customer_unit_id' => $invoice['customer_unit_id']));

        
        $outstanding = $this->model['partner']->getOutstanding("l.`partner_id` = '".$invoice['partner_id']."' AND l.`created_at` < '".$invoice['created_at']."'");
        //d(array($sale_tax_invoice_id, $invoice, $details), true);
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

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

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
        $pdf->SetAuthor('kashan');
        $pdf->SetTitle('Sales Tax Invoice');
        $pdf->SetSubject('Sales Tas Invoice');

        //Set Header
        // $pdf->InvoiceCheck = "SalesTaxInvoice";
        $pdf->InvoiceCheck = $invoice['sale_type'];
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
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(7, 30, 7);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 0);

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

        $arrAddress = splitString($partner['address'], 30);


        $pdf->SetFont('freesans', 'B', 9);
        $pdf->Cell(140, 5, '', 0, false, 'R', 1, '', 1);
        $pdf->setXY(154,22);
        $pdf->Cell(30, 5, 'Invoice', 0, false, 'R', 1, '', 1);
        $pdf->SetFont('freesans', 'B', 8);
        $pdf->setXY(152,27);
        $pdf->Cell(25, 5, 'Date ', 1, false, 'C', 1, '', 1);
        $pdf->Cell(25, 5, 'Invoice # ', 1, false, 'C', 1, '', 1);
        $pdf->SetFont('freesans', '', 9);
        $pdf->setXY(152,32);
        $pdf->Cell(25, 5, stdDate($invoice['document_date']), 1, false, 'L', 1, '', 1);
        $pdf->Cell(25, 5, $invoice['document_identity'], 1, false, 'L', 1, '', 1);

        $pdf->ln(18);
        $pdf->SetFont('freesans', 'B', 9);
        $pdf->Cell(1,24, '', '', false, 'L', 1, '', 1);
        $pdf->Cell(86, 5, '    Bill To', 'TBLR', false, 'L', 1, '', 1);
        $pdf->ln(5);
        $pdf->Cell(1,24, '', '', false, 'L', 1, '', 1);
        $pdf->SetFont('freesans', 'B', 9);
        $pdf->Cell(26, 9, 'Name: ', 'LB', false, 'L', 1, '', 1);
        $pdf->SetFont('freesans', '', 9);
        $pdf->Cell(60, 9, $partner['name'], 'BR', false, 'L', 1, '', 1);
        

        $pdf->ln(6);
        $pdf->Cell(1,24, '', '', false, 'L', 1, '', 1);
        $pdf->SetFont('freesans', 'B', 9);
        $pdf->Cell(26, 8, 'Phone #: ', 'LB', false, 'L', 1, '', 1);
        $pdf->SetFont('freesans', '', 9);
        $pdf->Cell(60, 8, $invoice['customer_no'], 'BR', false, 'L', 1, '', 1);

        $pdf->ln(6);
        $pdf->Cell(1,24, '', '', false, 'L', 1, '', 1);
        $pdf->SetFont('freesans', 'B', 9);
        $pdf->Cell(26, 8, 'Address: ', 'LB', false, 'L', 1, '', 1);
       
        foreach ($arrAddress as $index => $add) {
            if($index == 0) {
            $pdf->SetFont('freesans', '', 9);
             $pdf->Cell(60, 8, $arrAddress[$index], 'BR', false, 'L', 1, '', 1);
            }
             else {
              $pdf->SetFont('freesans', '', 9);   
              $pdf->Cell(1,24, '', '', false, 'L', 1, '', 1);     
              $pdf->Cell(86, 8, $arrAddress[$index], 'LBR', false, 'L', 1, '', 1);
            }
             $pdf->ln(6);
        }
               
        $x= $pdf->GetX();
        $y= $pdf->GetY();
        $pdf->SetXY($x, $y);

        $y = $y + 10;

        $pdf->ln(4);
        // set font
        $pdf->SetFont('freesans', 'B', 9);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->Cell(1, 6, '', '', false, 'C', 1, '', 1);
        $pdf->Cell(17, 6, 'Qty', 'TBLR', false, 'C', 1, '', 1);
        $pdf->Cell(90, 6, 'Product Description', 'TBLR', false, 'C', 1, '', 1);
        $pdf->Cell(42, 6, 'Price', 'TBLR', false, 'C', 1, '', 1);
        // $pdf->Cell(22, 6, 'Total Disc', 'TBLR', false, 'C', 1, '', 1);
        $pdf->Cell(42, 6, 'Amount', 'TBLR', false, 'C', 1, '', 1);
        $pdf->Ln(-1);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $total_amount = 0;
        $total_net = 0;
        $sr = 0;
        $pdf->SetFont('helvetica', '', 9);

        $SalesTax='';
        foreach($details as $record_no => $detail) {
            $SalesTax = $detail['tax_percent'];

            $sr++;
                        
            $pdf->ln(7);
             $pdf->Cell(1, 6, '', '', false, 'C', 1, '', 1);
            if(strlen($detail['description'])<=45){
                    $pdf->Cell(17, 7, number_format($detail['qty'],2), 'LR', false, 'C', 0, '', 1);
                    $pdf->Cell(90, 7, html_entity_decode($detail['description']), 'LR', false, 'L', 0, '', 1);
                    $pdf->Cell(42, 7, (int) ($detail['rate']), 'LR', false, 'C', 0, '', 1);
                    // $pdf->Cell(22, 7, number_format($invoice['discount_amount'],2), 'LR', false, 'C', 0, '', 1);
                    $pdf->Cell(42, 7, (int) ($detail['amount']), 'LR', false, 'C', 0, '', 1);
            } else {
                $arrDesc = splitString($detail['description'], 45);
                
                   foreach($arrDesc as $index => $remark){
                    
                        if($index==0){
                            $pdf->Cell(17, 4, (int) ($detail['qty']), 'LR', false, 'C', 0, '', 1);
                            $pdf->Cell(90, 4, $remark, 'LR', false, 'L', 0, '', 1);
                            $pdf->Cell(42, 4, (int) ($detail['rate']), 'LR', false, 'C', 0, '', 1);
                            // $pdf->Cell(22, 4, number_format($invoice['discount_amount'],2), 'LR', false, 'C', 0, '', 1);
                            $pdf->Cell(42, 4, (int) ($detail['amount']), 'LR', false, 'C', 0, '', 1);
                        } else if($index<=count($arrDesc)-1){
                            $pdf->Cell(17, 4, '', 'LR', false, 'C', 0, '', 1);
                            $pdf->Cell(90, 4, $remark, 'LR', false, 'L', 0, '', 1);
                            $pdf->Cell(42, 4, '', 'LR', false, 'C', 0, '', 1);
                            // $pdf->Cell(22, 4, '', 'LR', false, 'C', 0, '', 1);
                            $pdf->Cell(42, 4, '', 'LR', false, 'C', 0, '', 1);
                        } else {
                            $pdf->Cell(17, 4, '', 'LR', false, 'C', 0, '', 1);
                            $pdf->Cell(90, 4, $remark, 'LR', false, 'L', 0, '', 1);
                            $pdf->Cell(42, 4, '', 'LR', false, 'C', 0, '', 1);
                            // $pdf->Cell(22, 4, '', 'LR', false, 'C', 0, '', 1);
                            $pdf->Cell(42, 4, '', 'LR', false, 'C', 0, '', 1);
                        }

                    $pdf->Ln(4);
                }
                
                $pdf->Ln(-2);
                $pdf->Cell(195.1, 1, '', 'B', false, 'C', 0, '', 0, false, 'M', 'M');
                $pdf->Ln(-5);
            }

            $total_amount += $detail['amount'];

            $y = $pdf->GetY();
            if($y>=240 && $record_no < (count($details)-1)) {
              $pdf->Ln(5);
              $pdf->Cell(1, 1, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
              $pdf->Cell(194.1, 1, '', 'B', false, 'C', 0, '', 0, false, 'M', 'M');
              $pdf->AddPage();

              $pdf->ln(4);
              $pdf->Cell(1, 6, '', '', false, 'C', 1, '', 1);
              $pdf->Cell(17, 6, 'Qty', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(90, 6, 'Product Description', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(42, 6, 'Price', 'TBLR', false, 'C', 1, '', 1);
              // $pdf->Cell(22, 6, 'Total Disc', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(42, 6, 'Amount', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Ln(-1);
            }

        }

         $total_net = ($total_amount - $invoice['discount_amount']);

           $x = $pdf->GetX();
           $y = $pdf->GetY();
           $pdf->setXY($x,$y);

           $pdf->Ln(7.3);
           $pdf->Cell(1, 6.5, '', '0', false, 'C', 0, '', 1);

           $pdf->Cell(17, 6.5, '', 'LR', false, 'C', 0, '', 1);
           $pdf->Cell(90, 6.5, '', 'LR', false, 'R', 0, '', 1);
           $pdf->Cell(42, 6.5, '', 'LR', false, 'R', 0, '', 1);
           $pdf->Ln(-1.2);

            $pdf->SetFont('freesans', 'B', 9);
            $pdf->Ln(8);
            $pdf->Cell(1, 7, '', '', false, 'C', 0, '', 1);
            $pdf->Cell(17, 7, '', '', false, 'C', 0, '', 1);
            $pdf->Cell(90, 7, '', '', false, 'C', 0, '', 1);  
            $pdf->Cell(36, 7, '', '', false, 'L', 0, '', 1);
            $pdf->Ln(-7);
            $pdf->Cell(108, 7, '', '', false, 'C', 0, '', 1);
            $pdf->Cell(42, 7, 'Discount', 'LTB', false, 'L', 0, '', 1);
            $pdf->Cell(42, 7, (int) ($invoice['discount_amount']), 'TBR', false, 'R', 0, '', 1);


           $pdf->Ln(7);
           $pdf->Cell(1, 6.5, '', '0', false, 'C', 0, '', 1);

           $pdf->Cell(17, 6.5, '', 'LR', false, 'C', 0, '', 1);
           $pdf->Cell(90, 6.5, '', 'LR', false, 'R', 0, '', 1);
           $pdf->Cell(42, 6.5, '', 'LR', false, 'R', 0, '', 1);
           $pdf->Ln(-1.2);

            $pdf->Ln(8);
            $pdf->Cell(1, 7, '', '', false, 'C', 0, '', 1);
            $pdf->Cell(17, 7, '', 'T', false, 'C', 0, '', 1);
            $pdf->Cell(90, 7, '', 'T', false, 'C', 0, '', 1);  
            $pdf->Cell(36, 7, '', 'T', false, 'L', 0, '', 1);
            $pdf->Ln(-7);
            $pdf->Cell(108, 7, '', '', false, 'C', 0, '', 1);
            $pdf->Cell(42, 7, 'Total          PKR', 'LTB', false, 'L', 0, '', 1);
            $pdf->Cell(42, 7, (int) ($total_net), 'TBR', false, 'R', 0, '', 1);


           $pdf->Ln(8.2);
           $pdf->setX(139);
           $pdf->Cell(60, 25, 'Signature', 'TBLR', false, 'C', 0, '', 1);


        //Close and output PDF document
        $pdf->Output($invoice['sale_type'].' - '.$invoice['document_identity'].'.pdf', 'I');

    }

//     public function printDocumentBill() {
//         ini_set('max_execution_time',0);
//         ini_set('memory_limit',-1);


//         $lang = $this->load->language($this->getAlias());
//         $post = $this->request->post;
//         $session = $this->session->data;
//         $sale_tax_invoice_id = $this->request->get['sale_tax_invoice_id'];
//         $with_previous_balance = isset($this->request->get['with_previous_balance'])?1:0;

//         $this->model['partner'] = $this->load->model('common/partner');
//         $this->model['sale_tax_invoice'] = $this->load->model('inventory/sale_tax_invoice');
//         $this->model['sale_tax_invoice_detail'] = $this->load->model('inventory/sale_tax_invoice_detail');
//         $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');

//         $invoice = $this->model['sale_tax_invoice']->getRow(array('sale_tax_invoice_id' => $sale_tax_invoice_id));
//         $details = $this->model['sale_tax_invoice_detail']->getRows(array('sale_tax_invoice_id' => $sale_tax_invoice_id), array('sort_order asc'));
//         $partner = $this->model['partner']->getRow(array('partner_id' => $invoice['partner_id']));
//         $outstanding = $this->model['partner']->getOutstanding("l.`partner_id` = '".$invoice['partner_id']."' AND l.`created_at` < '".$invoice['created_at']."'");
//         //d(array($sale_tax_invoice_id, $invoice, $details), true);

//         $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');
//         $CustomerUnit = $this->model['customer_unit']->getRow(array('customer_unit_id' => $invoice['customer_unit_id']));

//         $result_str = array();

//         $dcnos = json_decode($invoice['ref_document_id'],true);

//         foreach($dcnos as $item){

//             $delivery_challan = $this->model['delivery_challan']->getRow(array('delivery_challan_id' => $item));
//             //d($delivery_challan,true);

//             $result_str[] = $delivery_challan['document_identity'];
//         }
//         $abc = implode(", ",$result_str);

       

      
//         $this->model['party_ledger'] = $this->load->model('report/party_ledger');
// //            $where = "l.company_id = '".$this->session->data['company_id']."'";
// //            $where .= " AND l.company_branch_id = '".$this->session->data['company_branch_id']."'";
// //            $where .= " AND l.fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
// //            $where .= " AND l.document_date <= '".MySqlDate($invoice['document'])."'";
// //            $where .= " AND l.partner_id = '".$invoice['partner_id']."'";
// //
// //            $outstanding = $this->model['party_ledger']->getOutstanding($where);
// //
// //        $next_date= date('d-m-Y', strtotime(MySqlDate($invoice['document']). ' - 90 days'));
// //
// //        d($next_date,true);

//         $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

//         // set document information
//         $pdf->SetCreator(PDF_CREATOR);
//         $pdf->SetAuthor('kashan');
//         $pdf->SetTitle('Invoice Estimate');
//         $pdf->SetSubject('Invoice Estimate');

//         //Set Header

//         $pdf->InvoiceCheck = "Bill";
//         $pdf->data = array(
//             'company_name' => $session['company_name'],
//             //'report_name' => $lang['heading_title'],
//             'report_name' => 'Estimate',
//             'header_image' => HTTP_IMAGE.'header.jpg',
//             'footer_image' => HTTP_IMAGE.'footer.jpg',
//             'company_logo' => $session['company_image'],
//         );

//         if($invoice['po_date'] == "" || $invoice['po_date'] == '0000-00-00')
//         {
//             $invoice['po_date'] = null;
//         }
//         else
//         {
//             $invoice['po_date'] = stdDate($invoice['po_date']);
//         }

//         /* if($invoice['billty_date'] == "" || $invoice['billty_date'] == '0000-00-00')
//          {
//              $invoice['billty_date'] = null;
//          }

//          else
//          {
//              $invoice['billty_date'] = stdDate($invoice['billty_date']);
//          }*/

//         // set margins
//         //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//         $pdf->SetMargins(15, 60, 5);
//         $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//         $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//         // set auto page breaks
//         $pdf->SetAutoPageBreak(TRUE, 60);

//         // add a page
//         $pdf->AddPage();
//         // set font

//         $pdf->SetFont('helvetica', 'B', 10);
//         $pdf->Cell(10, 7, 'M/S: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//         // set font
//         $pdf->SetFont('helvetica', 'B', 10);
//         $pdf->Cell(80, 7, html_entity_decode($invoice['partner_name']), 'B', false, 'L', 0, '', 0, false, 'M', 'M');

//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(20, 7, 'Invoice No: ', 0, false, 'R', 0, '', 0, false, 'M', 'M');
//         // set font
//         $pdf->SetFont('helvetica', '', 9);
//         $pdf->Cell(40, 7, $invoice['document_identity'], 'B', false, 'C', 0, '', 0, false, 'M', 'M');

//         $pdf->Cell(10, 7, 'Date: ', 0, false, 'R', 0, '', 0, false, 'M', 'M');
//         // set font
//         $pdf->SetFont('helvetica', '', 9);
//         $pdf->Cell(30, 7, stdDate($invoice['document_date']), 'B', false, 'L', 0, '', 0, false, 'M', 'M');
//         $pdf->ln(8);


// //        $pdf->SetFont('helvetica', 'B', 10);
// //        $pdf->Cell(15, 7, 'Address: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
// //        // set font
// //        $pdf->SetFont('helvetica', '', 10);
// //        $pdf->Cell(175, 7, $partner['address'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');



//         $pdf->SetFont('helvetica', 'B', 10);
//         $pdf->Cell(15, 7, 'Address: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//         // set font
//         $pdf->SetFont('helvetica', '', 10);
// //        $pdf->Cell(110, 7, $partner['address'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');
//         $pdf->MultiCell(75, 10, $partner['address'], 'B', '', 0, 2, 30, 66, true);

//         $pdf->SetFont('helvetica', 'B', 10);
// //        $pdf->Cell(27, 7, 'Unit : ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//         // set font
//         $pdf->SetFont('helvetica', 'B', 10);
// //        $pdf->Cell(38, 7, $partner['address'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');
//         $pdf->MultiCell(100, 10, 'Unit : '. $CustomerUnit['customer_unit']. '  '.$invoice['customer_remarks'], 'B', '', 0, 2, 106, 66, true);

//         $pdf->ln(8);

//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(20, 7, 'Po No: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//         // set font
//         $pdf->SetFont('helvetica', '', 9);
//         $pdf->Cell(70, 7, $invoice['po_no'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');

//         $pdf->Cell(15, 7, 'PO Date: ', 0, false, 'R', 0, '', 0, false, 'M', 'M');
//         // set font
//         $pdf->SetFont('helvetica', '', 9);

//         $pdf->Cell(35, 7, $invoice['po_date'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');


//         $pdf->Cell(15, 7, 'Bilty Date: ', 0, false, 'R', 0, '', 0, false, 'M', 'M');
//         // set font
//         $pdf->SetFont('helvetica', '', 9);
//         $pdf->Cell(35, 7, $invoice['billty_date'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');

// //        $pdf->Cell(25, 7, 'Customer Unit : ', 0, false, 'R', 0, '', 0, false, 'M', 'M');
// //        // set font
// ////        $pdf->SetFont('helvetica', '', 9);
// ////        $pdf->Cell(25, 7, $CustomerUnit['customer_unit'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');


//         $pdf->ln(10);
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(20, 7, 'Challan No: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//         $pdf->SetFont('helvetica', '', 9);
//         $pdf->Cell(170, 7, $invoice['dc_no'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');


//         $pdf->ln(10);

//         // set font
//         $pdf->SetFont('helvetica', '', 8);
//         $pdf->SetFillColor(215, 215, 215);
//         $pdf->SetTextColor(0, 0, 0);
//         $pdf->Cell(20, 8, 'Qty', 1, false, 'C', 1, '', 1);
//         $pdf->Cell(110, 8, 'Particulars', 1, false, 'C', 1, '', 1);
//         $pdf->Cell(30, 8, 'Rate', 1, false, 'C', 1, '', 1);
//         $pdf->Cell(30, 8, 'Amount', 1, false, 'C', 1, '', 1);

//         $pdf->SetFillColor(255, 255, 255);
//         $pdf->SetTextColor(0, 0, 0);
//         $total_amount = 0;
//         foreach($details as $detail) {
//             $pdf->ln(8);
//             $pdf->Cell(20, 8, number_format($detail['qty'],0), 'L', false, 'C', 0, '', 1);
//             $pdf->Cell(110, 8, html_entity_decode($detail['description']), 'L', false, 'L', 0, '', 1);
//             $pdf->Cell(30, 8, number_format($detail['rate'],2), 'L', false, 'R', 0, '', 1);
//             $pdf->Cell(30, 8, number_format($detail['amount'],2), 'L,R', false, 'R', 0, '', 1);

//             $total_amount += $detail['amount'];
//         }

//         $x = $pdf->GetX();
//         $y = $pdf->GetY();

//         for ($i = $y; $i <= 220; $i++) {

//             $pdf->Ln(1);
//             $pdf->Cell(20, 8, '', 'L', false, 'R', 0, '', 1);
//             $pdf->Cell(110, 8, '', 'L', false, 'L', 0, '', 1);
//             $pdf->Cell(30, 8, '', 'L', false, 'R', 0, '', 1);
//             $pdf->Cell(30, 8, '', 'L,R', false, 'R', 0, '', 1);
//             $y =$i;
//         }
//         $pdf->Ln(-4);
//         $pdf->Ln(8);
//         $pdf->Cell(190, 8, '', 'B', false, 'C', 0, '', 0, false, 'M', 'M');
//         $pdf->setXY($x,$y);
//         $pdf->Ln(9);


//         $pdf->SetFont('helvetica', 'B', 9);
        
//         $pdf->Cell(130, 7, 'In wordX: ' . Number2Words(number_format($total_amount,2)). ' only', 0, false, 'L');
//         $pdf->Cell(30, 7, $lang['total_amount'].': ', 1, false, 'L');
//         $pdf->Cell(30, 7, number_format($total_amount,2), 1, false, 'R');


//         //Close and output PDF document
//         $pdf->Output('Invoice Estimate - '.$invoice['document_identity'].'.pdf', 'I');

//     }



//      public function printSalesNewInvoice() {
//         ini_set('max_execution_time',0);
//         ini_set('memory_limit',-1);

//         $lang = $this->load->language($this->getAlias());
//         $post = $this->request->post;
//         $session = $this->session->data;
//         $sale_tax_invoice_id = $this->request->get['sale_tax_invoice_id'];
//         $with_previous_balance = isset($this->request->get['with_previous_balance'])?1:0;
//         $type = $this->request->get['type'];

//         $this->model['partner'] = $this->load->model('common/partner');
//         $this->model['sale_tax_invoice'] = $this->load->model('inventory/sale_tax_invoice');
//         $this->model['sale_tax_invoice_detail'] = $this->load->model('inventory/sale_tax_invoice_detail');
//         $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
//         $this->model['company'] = $this->load->model('setup/company_branch');
//         $this->model['company_branch'] = $this->load->model('setup/company_branch');
//         $company_address = $this->model['company']->getRow(array('company_id' => $session['company_id']));
//         $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
//         $invoice = $this->model['sale_tax_invoice']->getRow(array('sale_tax_invoice_id' => $sale_tax_invoice_id));
//         // d($invoice,true);
//         $details = $this->model['sale_tax_invoice_detail']->getRows(array('sale_tax_invoice_id' => $sale_tax_invoice_id), array('sort_order asc'));
//         $partner = $this->model['partner']->getRow(array('partner_id' => $invoice['partner_id']));
//         $delivery_challan_id = $invoice['ref_document_id'];

//         $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');
//         $CustomerUnit = $this->model['customer_unit']->getRow(array('customer_unit_id' => $invoice['customer_unit_id']));
//         $where = 'AND l.partner_id = "'.$partner['partner_id'].'"';
//         $where.= 'AND l.coa_id = "'.$partner['outstanding_account_id'].'"';
//         $where.= 'AND l.document_identity < "'.$invoice['document_identity'].'"';
//         $previous_balance =  $this->model['sale_tax_invoice']->getPreviousBalance($where);
        
//         // $outstanding = $this->model['partner']->getOutstanding("l.`partner_id` = '".$invoice['partner_id']."' AND l.`created_at` < '".$invoice['created_at']."'");
//         //d(array($sale_tax_invoice_id, $invoice, $details), true);
//         $result_str = array();

//         $dcnos = json_decode($invoice['ref_document_id'],true);

//         foreach($dcnos as $item){

//             $delivery_challan = $this->model['delivery_challan']->getRow(array('delivery_challan_id' => $item));
//             //d($delivery_challan,true);

//             $result_str[] = $delivery_challan['document_identity'];
//         }
//         $abc = implode(", ",$result_str);

//         $this->model['setting'] = $this->load->model('common/setting');
//         $setting = $this->model['setting']->getRow(array(
//             'company_id' => $this->session->data['company_id'],
//             'company_branch_id' => $this->session->data['company_branch_id'],
//             'fiscal_year_id' => $this->session->data['fiscal_year_id'],
//             'module' => 'general',
//             'field' => 'company_logo',
//         ));
//         $company_logo = $setting['value'];

//         $setting = $this->model['setting']->getRow(array(
//             'company_id' => $this->session->data['company_id'],
//             'company_branch_id' => $this->session->data['company_branch_id'],
//             'fiscal_year_id' => $this->session->data['fiscal_year_id'],
//             'module' => 'general',
//             'field' => 'company_header_print',
//         ));
//         $company_header_print = $setting['value'];
        
//         $setting = $this->model['setting']->getRow(array(
//             'company_id' => $this->session->data['company_id'],
//             'company_branch_id' => $this->session->data['company_branch_id'],
//             'fiscal_year_id' => $this->session->data['fiscal_year_id'],
//             'module' => 'general',
//             'field' => 'company_footer_print',
//         ));
//         $company_footer_print = $setting['value'];

//         $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

//         if($invoice['po_date'] == "" || $invoice['po_date'] == '0000-00-00'){
//             $this->data['po_date'] = null;
//         }
//         else{
//             $this->data[$invoice['po_date']] = stdDate($invoice['po_date']);
//         }

//         /*if($invoice['billty_date'] == "" || $invoice['billty_date'] == '0000-00-00'){
//             $this->data['billty_date'] = null;
//         }
//         else{
//             $this->data[$invoice['billty_date']] = stdDate($invoice['billty_date']);
//         }
// */

//         $this->model['user'] = $this->load->model('user/user');
//         $user = $this->model['user']->getRow(array('user_id' => $session['user_id']));

//         $user_name=$user['user_name'];

//         $this->model['company_branch'] = $this->load->model('setup/company_branch');
//         $company_branch = $this->model['company_branch']->getRow(array('company_branch_id' => $this->session->data['company_branch_id']));

//         $company_branch_name=$company_branch['name'];

//         // set document information
//         $pdf->SetCreator(PDF_CREATOR);
//         $pdf->SetAuthor('kashan');
//         $pdf->SetTitle('Invoice');
//         $pdf->SetSubject('Invoice');

//         //Set Header
//         $pdf->InvoiceCheck = $invoice['sale_type'];
//         $pdf->data = array(
//             'company_name' => $branch['name'],
//             //'report_name' => $lang['heading_title'],
//             'type' => $type,
//             'company_logo' => $session['company_image'],
//             'company_header_print' => $company_header_print,
//             'company_footer_print' => $company_footer_print,
//             'user_name' => $user_name,
//             'company_branch_id' => $this->session->data['company_branch_id'],
//             'company_branch_name' => $company_branch_name,
//         );

//         // set margins
//         //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//         $pdf->SetMargins(7, 30, 7);
//         $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//         $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//         // set auto page breaks
//         $pdf->SetAutoPageBreak(TRUE, 0);

//         // add a page
//         $pdf->AddPage();
//         // set font

//        $styless = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 2, 'color' => array(0, 0, 0));
//        $styles = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
//        $pdf->Line(150, 7, 150, 204, $styless);
//        $pdf->Line(0, 0, 0, 0,$styles);
        
//         // $txt="21,Adnan Centre Jeswani Street, off. Aiwan-E-Tijarat Road,
//         // Karachi - 74000, Pakistan
//         // TEL: 92-21-32401236,92-21-32415063
//         // FAX: 92-21-32428040
//         // Email : info@hacsons.com Web: www.hacsons.com.
//         // 82/4 Railway Road Chowk Dalgarah Lahore.
//         // TEL: 042-37662521,042-37662527";

//         $txt = $company_address['address'].' , 
//         '.$company_address['name'].' 
//         TEL: '.$company_address['phone_no'];

// //        $txt = "";
//         if($invoice['po_date'] != '')
//         {
//             $invoice['po_date'] = stdDate($invoice['po_date']);
//         }
//         $pdf->SetFont('freesans', 'B', 9);
//         $pdf->SetFillColor(255,255,255);
//         $arrAddress = splitString($partner['address'], 40);

//         $pdf->setXY(8,17);
//         $pdf->SetFont('helvetica', 'B', 10);    

//         // $pdf->Cell(26, 9, 'M/s.  ', '', false, 'L', 1, '', 1);
//         // $pdf->setX(18);
//         // $pdf->ln(5);
//         // $pdf->SetFont('helvetica', '', 10);
//         $pdf->Cell(12, 8, 'M/s.', '', false, 'L', 0, '', 0, false, 'T', 'M');
//         $pdf->SetFont('helvetica', '', 10);
//         $pdf->Cell(82, 6, $invoice['partner_name'], 'B', false, 'L', 0, '', 0, false, 'T', 'M');

//         $pdf->setXY(80,16);
//         $pdf->Cell(35, 8, '', '', false, 'L', 0, '', 0, false, 'T', 'M');
//         $pdf->SetFont('helvetica', 'B', 10);
//         $pdf->Cell(30, 6, $invoice['document_identity'], 'B', false, 'L', 0, '', 0, false, 'T', 'M');

//         $invoice['document_date'] = date_create($invoice['document_date']);
//         $invoice['document_date'] = date_format($invoice['document_date'],'d/m/Y');

//         $pdf->setXY(115,24.4);
//         $pdf->Cell(20, 5,  $invoice['document_date'], 'B', false, 'L', 0, '', 0, false, 'T', 'M');


//         $pdf->setXY(156,16);
//         $pdf->SetFont('helvetica', 'B', 11);
//         $pdf->Cell(12, 8, 'M/s.', '', false, 'L', 0, '', 0, false, 'T', 'M');
//         $pdf->SetFont('helvetica', '', 10);
//         $pdf->Cell(82, 6, $invoice['partner_name'], 'B', false, 'L', 0, '', 0, false, 'T', 'M');


//         $pdf->setXY(227,16);
//         $pdf->Cell(35, 8, '', '', false, 'L', 0, '', 0, false, 'T', 'M');
//         $pdf->SetFont('helvetica', 'B', 10);
//         $pdf->Cell(30, 6, $invoice['document_identity'], 'B', false, 'L', 0, '', 0, false, 'T', 'M');

//         $pdf->setXY(262,24.4);
//         $pdf->Cell(20, 5,  $invoice['document_date'], 'B', false, 'L', 0, '', 0, false, 'T', 'M');


//          $pdf->setXY(7,24);
//          $pdf->Cell(1,24, '', '', false, 'L', 1, '', 1);
//          $pdf->SetFont('helvetica', 'B', 10);
//          $pdf->Cell(20, 8, 'Address. ', '', false, 'L', 1, '', 1);


//          foreach ($arrAddress as $index => $add) {

//             if($index == 0) {       
//               $pdf->SetFont('helvetica', '', 9);
//               $pdf->Cell(85, 8, $arrAddress[$index], '', false, 'L', 1, '', 1);
//             }
//              else {
//               $pdf->setXY(28,31);  
//               $pdf->SetFont('helvetica', '', 9);     
//               $pdf->Cell(85, 8, $arrAddress[$index], '', false, 'L', 1, '', 1);
//             }
//              $pdf->ln(4);
//         }
//           $pdf->setXY(155,24);
//           $pdf->Cell(1,24, '', '', false, 'L', 1, '', 1);
//           $pdf->SetFont('helvetica', 'B', 10);
//           $pdf->Cell(20, 8, 'Address. ', '', false, 'L', 1, '', 1);

//          foreach ($arrAddress as $index => $add) {

//             if($index == 0) {       
//               $pdf->SetFont('helvetica', '', 9);
//               $pdf->Cell(85, 8, $arrAddress[$index], '', false, 'L', 1, '', 1);
//             }
//              else {
//               $pdf->setXY(176,31);  
//               $pdf->SetFont('helvetica', '', 9);     
//               $pdf->Cell(85, 8, $arrAddress[$index], '', false, 'L', 1, '', 1);
//             }
//              $pdf->ln(6);
//         }
               
//         $x= $pdf->GetX();
//         $y= $pdf->GetY();
//         $pdf->SetXY($x, $y);

//         $y = $y + 10;

//         $pdf->ln(4.9);
//         // set font
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->SetFillColor(255, 255, 255);
//         $pdf->SetTextColor(0, 0, 0);

//         $pdf->Cell(2, 6, '', '', false, 'C', 1, '', 1);
//         $pdf->Cell(11, 7.4, 'S.#', 'TBLR', false, 'C', 1, '', 1);
//         $pdf->Cell(34, 7.4, 'Description', 'TBLR', false, 'L', 1, '', 1);
//         $pdf->Cell(28, 7.4, 'Category', 'TBLR', false, 'C', 1, '', 1);
//         $pdf->Cell(13, 7.4, 'Qty', 'TBLR', false, 'C', 1, '', 1);
//         $pdf->Cell(20, 7.4, 'Rate', 'TBLR', false, 'C', 1, '', 1);
//         $pdf->Cell(28, 7.4, 'Amount', 'TBLR', false, 'R', 1, '', 1);
//         // $pdf->Ln(-1);
//         $pdf->SetFillColor(255, 255, 255);
//         $pdf->SetTextColor(0, 0, 0);
        
//         $total_qty = 0;
//         $total_qty2 = 0;
//         $total_amount = 0;
//         $total_amount2 = 0;
//         $balance_amount = 0;
//         $sr = 0;
//         $sr2 = 0;
//         $pdf->SetFont('helvetica', '', 8);
//         $pdf->ln(7);

//         foreach($details as $record_no => $detail) {
//             $sr++;

//              $pdf->Cell(2, 6, '', '', false, 'C', 1, '', 1);
//             if(strlen($detail['product_name'])<=20){
//                     $pdf->Cell(11, 7, $sr, 'BLR', false, 'C', 0, '', 1);
//                     $pdf->Cell(34, 7, html_entity_decode($detail['product_name']), 'BLR', false, 'L', 0, '', 1);
//                     $pdf->Cell(28, 7, html_entity_decode($detail['product_category']), 'BLR', false, 'C', 0, '', 1);
//                     $pdf->Cell(13, 7, (int) ($detail['qty']), 'BLR', false, 'C', 0, '', 1);
//                     $pdf->Cell(20, 7, (int) ($detail['rate']), 'BLR', false, 'C', 0, '', 1);
//                     $pdf->Cell(28, 7, number_format($detail['amount'],0), 'BLR', false, 'R', 0, '', 1);
//                     $pdf->ln(7);
    
//             } else {
//                 $arrDesc = splitString($detail['product_name'], 20);
                
//                    foreach($arrDesc as $index => $remark){
                    
//                      if($index==0){

//                       $pdf->Cell(11, 5, $sr, 'LR', false, 'C', 0, '', 1);
//                       $pdf->Cell(34, 5, $remark, 'LR', false, 'L', 0, '', 1);
//                       $pdf->Cell(28, 5, html_entity_decode($detail['product_category']), 'LR', false, 'C', 0, '', 1);
//                       $pdf->Cell(13, 5, (int) ($detail['qty']), 'LR', false, 'C', 0, '', 1);
//                       $pdf->Cell(20, 5, (int) ($detail['rate']), 'LR', false, 'C', 0, '', 1);
//                       $pdf->Cell(28, 5,  number_format($detail['amount'],2), 'LR', false, 'R', 0, '', 1);

//                      } else if($index<=count($arrDesc)-1){
//                       $pdf->Cell(2, 5, '', '', false, 'C', 1, '', 1);
//                       $pdf->Cell(11, 5, '', 'LRB', false, 'C', 0, '', 1);
//                       $pdf->Cell(34, 5, $remark, 'LRB', false, 'L', 0, '', 1);
//                       $pdf->Cell(28, 5, '', 'LRB', false, 'C', 0, '', 1);
//                       $pdf->Cell(13, 5, '', 'LRB', false, 'C', 0, '', 1);
//                       $pdf->Cell(20, 5, '', 'LRB', false, 'C', 0, '', 1);
//                       $pdf->Cell(28, 5, '', 'LRB', false, 'R', 0, '', 1);
//                      } else {
//                       $pdf->Cell(2, 5, '', '', false, 'C', 1, '', 1);   
//                       $pdf->Cell(11, 5, '', 'LR', false, 'C', 0, '', 1);
//                       $pdf->Cell(34, 5, $remark, 'LR', false, 'L', 0, '', 1);
//                       $pdf->Cell(28, 5, '', 'LR', false, 'C', 0, '', 1);
//                       $pdf->Cell(13, 5, '', 'LR', false, 'C', 0, '', 1);
//                       $pdf->Cell(20, 5, '', 'LR', false, 'C', 0, '', 1);
//                       $pdf->Cell(28, 5, '', 'LR', false, 'R', 0, '', 1);
//                      }

//                     $pdf->Ln(5);
//                 }
//             }

//             $total_qty += $detail['qty'];
//             $total_amount += $detail['amount'];


//             $y = $pdf->GetY();
//             if($y>=200 && $record_no < (count($details)-1)) {
//               $pdf->Ln(5);
//               $pdf->Cell(1, 1, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//               $pdf->Cell(194.1, 1, '', 'B', false, 'C', 0, '', 0, false, 'M', 'M');
//               $pdf->AddPage();
//               $pdf->ln(10);
//               $pdf->Cell(2, 6, '', '', false, 'C', 1, '', 1);
//               $pdf->Cell(11, 7.4, 'S.#', 'TBLR', false, 'C', 1, '', 1);
//               $pdf->Cell(34, 7.4, 'Description', 'TBLR', false, 'L', 1, '', 1);
//               $pdf->Cell(28, 7.4, 'Category', 'TBLR', false, 'C', 1, '', 1);
//               $pdf->Cell(13, 7.4, 'Qty', 'TBLR', false, 'C', 1, '', 1);
//               $pdf->Cell(20, 7.4, 'Rate', 'TBLR', false, 'C', 1, '', 1);
//               $pdf->Cell(28, 7.4, 'Amount', 'TBLR', false, 'R', 1, '', 1);

//               $pdf->setXY(155,40);
//               $pdf->SetFont('helvetica', 'B', 9);
//               $pdf->Cell(2, 6, '', '', false, 'C', 1, '', 1);
//               $pdf->Cell(11, 7.4, 'S.#', 'TBLR', false, 'C', 1, '', 1);
//               $pdf->Cell(34, 7.4, 'Description', 'TBLR', false, 'L', 1, '', 1);
//               $pdf->Cell(28, 7.4, 'Category', 'TBLR', false, 'C', 1, '', 1);
//               $pdf->Cell(13, 7.4, 'Qty', 'TBLR', false, 'C', 1, '', 1);
//               $pdf->Cell(20, 7.4, 'Rate', 'TBLR', false, 'C', 1, '', 1);
//               $pdf->Cell(28, 7.4, 'Amount', 'TBLR', false, 'R', 1, '', 1);
//             }
//         }

//         $balance_amount = ($invoice['net_amount'] + $previous_balance - $invoice['cash_received']);

//         $pdf->ln(4);

//         $pdf->SetFont('helvetica', 'B', 9);

//        $pdf->Cell(3, 6, '', '', false, 'C', 1, '', 1);
//        $pdf->SetFont('helvetica', 'B', 9);
//        $pdf->Cell(130, 6, 'Amount in Words: ', 0, false, 'L', false, 'M', 'M');
//        $pdf->Ln(4.8);
//        $pdf->SetFont('helvetica', '', 9);
//        $pdf->Cell(3, 6, '', '', false, 'C', 1, '', 1);

//        $amountStr = Number2Words($invoice['net_amount']);

//           if(strlen($amountStr) < 35){

//            $pdf->Cell(50, 6, Number2Words($invoice['net_amount']) . ' Only ', 0, false, 'L');
//           }
//           else{
//            $arrAmount = splitString($amountStr,35);
//           foreach ($arrAmount as $key => $amount) {

//            if($key == 0){
//                $pdf->Cell(50, 6, $amount, 0, false, 'L');
//            }
//            else {
//                $pdf->Cell(3, 6, '', '', false, 'C', 1, '', 1);
//                $pdf->Cell(50, 6, $amount . 'Only', 0, false, 'L');
//            }
//            $pdf->Ln(4);
//           }
//        }

//         $pdf->ln(-4);
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(75, 5, ' ', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//         $pdf->Cell(13, 5.6 ,number_format($total_qty,0), 1, false, 'C', 0, '', 0, false, 'M', 'M');
//         $pdf->Ln(2.1);
//         $pdf->setX(100);
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(15, 5,'Total :', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//         $pdf->setX(114);
//         $pdf->Cell(28, 5.6 ,number_format($total_amount,0), 0, false, 'R', 0, '', 0, false, 'M', 'M');

//         $pdf->Ln(4.5);
//         $pdf->setX(100);
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(15, 5,'Freight:', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//         $pdf->setX(112);
//         $pdf->Cell(30, 5.6 ,number_format($invoice['cartage'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');

//         $pdf->Ln(5.1);
//         $pdf->setX(102);
//         $pdf->Cell(39, 4, '', 'T', false, 'L',0, '', 0, false, 'M', 'M');
//         $pdf->Ln(1);
//         $pdf->setX(100);
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(15, 5,'Net Bill:', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//         $pdf->setX(112);
//         $pdf->Cell(30, 5.6 ,number_format($invoice['net_amount'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');

//         $pdf->ln(-10);
//         $pdf->Cell(2, 5, ' ', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//         $pdf->Cell(134, 28, '', 'BLR', false, 'L',0, '', 0, false, 'M', 'M');
//         $pdf->Ln(24);
//         $pdf->setX(77);

//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(35, 6, 'Previous Balance: ', 0, false, 'L', false, 'M', 'M');
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(30, 6, number_format($previous_balance,0), 0, false, 'R', false, 'M', 'M');

//         $pdf->Ln(4.5);
//         $pdf->setX(77);

//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(35, 6, 'Bill Amount          : ', 0, false, 'L', false, 'M', 'M');
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(30, 6, number_format($invoice['net_amount'],0), 0, false, 'R', false, 'M', 'M');
//         $pdf->Ln(4.5);
//         $pdf->setX(77);


//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(35, 6, 'Amount Received: ', 0, false, 'L', false, 'M', 'M');
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(30, 6, number_format($invoice['cash_received'],0), 0, false, 'R', false, 'M', 'M');
//         $pdf->Ln(8.2);
//         $pdf->setX(78);
//         $pdf->Cell(64, 4, '', 'T', false, 'L',0, '', 0, false, 'M', 'M');
//         $pdf->Ln(-2);
//         $pdf->setX(77);
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(35, 6, 'Balance Amount  : ', 0, false, 'L', false, 'M', 'M');
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(30, 6, number_format($balance_amount,0), 0, false, 'R', false, 'M', 'M');

       
//         $pdf->ln(-2);
//         $pdf->SetFont('helvetica', '', 4);
//         $pdf->ln(3.5);
//         $pdf->setX(10);
//         $pdf->Cell(2, 1, '', 1, 0, 'C');
//         $pdf->ln(1);
//         $pdf->setX(12);
//         $pdf->SetFont('helvetica', 'B', 8);
//         $pdf->Cell(30, 5, 'No Claim', 0, false, 'L', 0, '', 0, false, 'M', 'M');


//         $pdf->SetFont('helvetica', '', 4);
//         $pdf->ln(-1);
//         $pdf->setX(26);
    
//         $pdf->Cell(2, 1, '', 1, 0, 'C');
//         $pdf->ln(0.9);
//         $pdf->setX(28);
//         $pdf->SetFont('helvetica', 'B', 8);
//         $pdf->Cell(30, 5, 'No Return', 0, false, 'L', 0, '', 0, false, 'M', 'M');


//         $pdf->SetFont('helvetica', '', 4);
//         $pdf->ln(-1);
//         $pdf->setX(44);
    
//         $pdf->Cell(2, 1, '', 1, 0, 'C');
//         $pdf->ln(0.9);
//         $pdf->setX(46);
//         $pdf->SetFont('helvetica', 'B', 8);
//         $pdf->Cell(30, 5, 'No Exchange', 0, false, 'L', 0, '', 0, false, 'M', 'M');




//         if(strlen($partner['address']) < 50){
//             $pdf->setXY(155,35);
//         }
//         else {
//             $pdf->setXY(155,40);
//         }
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->SetFillColor(255, 255, 255);
//         $pdf->SetTextColor(0, 0, 0);

//         $pdf->Cell(1, 6, '', '', false, 'C', 1, '', 1);
//         $pdf->Cell(11, 7.4, 'S.#', 'TBLR', false, 'C', 1, '', 1);
//         $pdf->Cell(34, 7.4, 'Description', 'TBLR', false, 'L', 1, '', 1);
//         $pdf->Cell(28, 7.4, 'Category', 'TBLR', false, 'C', 1, '', 1);
//         $pdf->Cell(13, 7.4, 'Qty', 'TBLR', false, 'C', 1, '', 1);
//         $pdf->Cell(20, 7.4, 'Rate', 'TBLR', false, 'C', 1, '', 1);
//         $pdf->Cell(28, 7.4, 'Amount', 'TBLR', false, 'R', 1, '', 1);


//         $pdf->SetFont('helvetica', '', 8);

//         $pdf->ln(7);
//         foreach($details as $record_no => $detail) {
//             $sr2++;
//             $pdf->setX(155);
//             // $pdf->ln(7);   
//              $pdf->Cell(1, 6, '', '', false, 'C', 1, '', 1);
//             if(strlen($detail['product_name'])<=20){
//                     $pdf->Cell(11, 7, $sr2, 'BLR', false, 'C', 0, '', 1);
//                     $pdf->Cell(34, 7, html_entity_decode($detail['product_name']), 'BLR', false, 'L', 0, '', 1);
//                     $pdf->Cell(28, 7, html_entity_decode($detail['product_category']), 'BLR', false, 'C', 0, '', 1);
//                     $pdf->Cell(13, 7, (int) ($detail['qty']), 'BLR', false, 'C', 0, '', 1);
//                     $pdf->Cell(20, 7, (int) ($detail['rate']), 'BLR', false, 'C', 0, '', 1);
//                     $pdf->Cell(28, 7, number_format($detail['amount'],2), 'BLR', false, 'R', 0, '', 1);
//                     $pdf->ln(7);
    
//             } else {
//                 $arrDesc = splitString($detail['product_name'], 20);
                
//                    foreach($arrDesc as $index => $remark){
//             $pdf->setX(156);
                    
//                      if($index==0){

//                       $pdf->Cell(11, 5, $sr2, 'LR', false, 'C', 0, '', 1);
//                       $pdf->Cell(34, 5, $remark, 'LR', false, 'L', 0, '', 1);
//                       $pdf->Cell(28, 5, html_entity_decode($detail['product_category']), 'LR', false, 'C', 0, '', 1);
//                       $pdf->Cell(13, 5, (int) ($detail['qty']), 'LR', false, 'C', 0, '', 1);
//                       $pdf->Cell(20, 5, (int) ($detail['rate']), 'LR', false, 'C', 0, '', 1);
//                       $pdf->Cell(28, 5,  number_format($detail['amount'],2), 'LR', false, 'R', 0, '', 1);

//                      } else if($index<=count($arrDesc)-1){
//                       $pdf->Cell(11, 5, '', 'LRB', false, 'C', 0, '', 1);
//                       $pdf->Cell(34, 5, $remark, 'LRB', false, 'L', 0, '', 1);
//                       $pdf->Cell(28, 5, '', 'LRB', false, 'C', 0, '', 1);
//                       $pdf->Cell(13, 5, '', 'LRB', false, 'C', 0, '', 1);
//                       $pdf->Cell(20, 5, '', 'LRB', false, 'C', 0, '', 1);
//                       $pdf->Cell(28, 5, '', 'LRB', false, 'R', 0, '', 1);
//                      } else {  
//                       $pdf->Cell(11, 5, '', 'LR', false, 'C', 0, '', 1);
//                       $pdf->Cell(34, 5, $remark, 'LR', false, 'L', 0, '', 1);
//                       $pdf->Cell(28, 5, '', 'LR', false, 'C', 0, '', 1);
//                       $pdf->Cell(13, 5, '', 'LR', false, 'C', 0, '', 1);
//                       $pdf->Cell(20, 5, '', 'LR', false, 'C', 0, '', 1);
//                       $pdf->Cell(28, 5, '', 'LR', false, 'R', 0, '', 1);
//                      }

//                     $pdf->Ln(5);
//                 }

            
//             }
//             $total_qty2 += $detail['qty'];
//             $total_amount2 += $detail['amount'];

//             $y = $pdf->GetY();
//             if($y>=200 && $record_no < (count($details)-1)) {
//               $pdf->Ln(5);
//               $pdf->Cell(1, 1, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//               $pdf->Cell(194.1, 1, '', 'B', false, 'C', 0, '', 0, false, 'M', 'M');
//               $pdf->AddPage();
//               $pdf->ln(10);
//               $pdf->Cell(2, 6, '', '', false, 'C', 1, '', 1);
//               $pdf->Cell(11, 7.4, 'S.no', 'TBLR', false, 'C', 1, '', 1);
//               $pdf->Cell(34, 7.4, 'Description', 'TBLR', false, 'L', 1, '', 1);
//               $pdf->Cell(28, 7.4, 'Category', 'TBLR', false, 'C', 1, '', 1);
//               $pdf->Cell(13, 7.4, 'Qty', 'TBLR', false, 'C', 1, '', 1);
//               $pdf->Cell(20, 7.4, 'Rate', 'TBLR', false, 'C', 1, '', 1);
//               $pdf->Cell(28, 7.4, 'Amount', 'TBLR', false, 'C', 1, '', 1);

//              $pdf->setXY(155,40);
//              $pdf->SetFont('helvetica', 'B', 9);
//              $pdf->Cell(2, 6, '', '', false, 'C', 1, '', 1);
//              $pdf->Cell(11, 7.4, 'S.no', 'TBLR', false, 'C', 1, '', 1);
//              $pdf->Cell(34, 7.4, 'Description', 'TBLR', false, 'L', 1, '', 1);
//              $pdf->Cell(28, 7.4, 'Category', 'TBLR', false, 'C', 1, '', 1);
//              $pdf->Cell(13, 7.4, 'Qty', 'TBLR', false, 'C', 1, '', 1);
//              $pdf->Cell(20, 7.4, 'Rate', 'TBLR', false, 'C', 1, '', 1);
//              $pdf->Cell(28, 7.4, 'Amount', 'TBLR', false, 'C', 1, '', 1);

//             }
          
//            }

//         $pdf->ln(4);

//        $pdf->setX(157);
//        $pdf->SetFont('helvetica', 'B', 9);
//        $pdf->Cell(130, 6, 'Amount in Words: ', 0, false, 'L', false, 'M', 'M');
//        $pdf->Ln(4.8);
//        $pdf->SetFont('helvetica', '', 9);
//        $pdf->setX(157);
     
//        $amountStr = Number2Words($invoice['net_amount']);

//           if(strlen($amountStr) < 35){

//            $pdf->Cell(50, 6, Number2Words($invoice['net_amount']) . ' Only ', 0, false, 'L');
//           }
//           else{
//            $arrAmount = splitString($amountStr,35);
//           foreach ($arrAmount as $key => $amount) {

//            if($key == 0){
//                $pdf->Cell(50, 6, $amount, 0, false, 'L');
//            }
//            else {
//                $pdf->Cell(3, 6, '', '', false, 'C', 1, '', 1);
//                $pdf->Cell(50, 6, $amount . 'Only', 0, false, 'L');
//            }
//            $pdf->Ln(4);
//           }
//        }

//         $pdf->ln(-4);
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(222, 5, ' ', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//         $pdf->Cell(13, 5.6 ,number_format($total_qty,0), 1, false, 'C', 0, '', 0, false, 'M', 'M');
//         $pdf->Ln(2.1);
//         $pdf->setX(247);
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(15, 5,'Total :', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//         $pdf->setX(262);
//         $pdf->Cell(28, 5.6 ,number_format($total_amount,0), 0, false, 'R', 0, '', 0, false, 'M', 'M');

//         $pdf->Ln(4.5);
//         $pdf->setX(247);
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(15, 5,'Freight:', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//         $pdf->setX(260);
//         $pdf->Cell(30, 5.6 ,number_format($invoice['cartage'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');

//         $pdf->Ln(5.1);
//         $pdf->setX(249);
//         $pdf->Cell(40, 4, '', 'T', false, 'L',0, '', 0, false, 'M', 'M');
//         $pdf->Ln(1);
//         $pdf->setX(247);
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(15, 5,'Net Bill:', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//         $pdf->setX(260);
//         $pdf->Cell(30, 5.6 ,number_format($invoice['net_amount'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');

//         $pdf->ln(-10);
//         $pdf->Cell(149, 5, ' ', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//         $pdf->Cell(134, 28, '', 'BLR', false, 'L',0, '', 0, false, 'M', 'M');
//         $pdf->Ln(24);
//         $pdf->setX(225);

//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(35, 6, 'Previous Balance: ', 0, false, 'L', false, 'M', 'M');
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(30, 6, number_format($previous_balance,0), 0, false, 'R', false, 'M', 'M');

//         $pdf->Ln(4.5);
//         $pdf->setX(225);

//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(35, 6, 'Bill Amount          : ', 0, false, 'L', false, 'M', 'M');
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(30, 6, number_format($invoice['net_amount'],0), 0, false, 'R', false, 'M', 'M');
//         $pdf->Ln(4.5);
//         $pdf->setX(225);


//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(35, 6, 'Amount Received: ', 0, false, 'L', false, 'M', 'M');
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(30, 6, number_format($invoice['cash_received'],0), 0, false, 'R', false, 'M', 'M');
//         $pdf->Ln(8.2);
//         $pdf->setX(226);
//         $pdf->Cell(64, 4, '', 'T', false, 'L',0, '', 0, false, 'M', 'M');
//         $pdf->Ln(-2);
//         $pdf->setX(225);
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(35, 6, 'Balance Amount  : ', 0, false, 'L', false, 'M', 'M');
//         $pdf->SetFont('helvetica', 'B', 9);
//         $pdf->Cell(30, 6, number_format($balance_amount,0), 0, false, 'R', false, 'M', 'M');

       
//         $pdf->ln(-2);
//         $pdf->SetFont('helvetica', '', 4);
//         $pdf->ln(3.5);
//         $pdf->setX(160);
//         $pdf->Cell(2, 1, '', 1, 0, 'C');
//         $pdf->ln(1);
//         $pdf->setX(162);
//         $pdf->SetFont('helvetica', 'B', 8);
//         $pdf->Cell(30, 5, 'No Claim', 0, false, 'L', 0, '', 0, false, 'M', 'M');


//         $pdf->SetFont('helvetica', '', 4);
//         $pdf->ln(-1);
//         $pdf->setX(176);
    
//         $pdf->Cell(2, 1, '', 1, 0, 'C');
//         $pdf->ln(0.9);
//         $pdf->setX(178);
//         $pdf->SetFont('helvetica', 'B', 8);
//         $pdf->Cell(30, 5, 'No Return', 0, false, 'L', 0, '', 0, false, 'M', 'M');


//         $pdf->SetFont('helvetica', '', 4);
//         $pdf->ln(-1);
//         $pdf->setX(194);
    
//         $pdf->Cell(2, 1, '', 1, 0, 'C');
//         $pdf->ln(0.9);
//         $pdf->setX(196);
//         $pdf->SetFont('helvetica', 'B', 8);
//         $pdf->Cell(30, 5, 'No Exchange', 0, false, 'L', 0, '', 0, false, 'M', 'M');


//         //Close and output PDF document
//         $pdf->Output('Invoice - '.$invoice['document_identity'].'.pdf', 'I');

//     }



  public function printSalesNewInvoice() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        $sale_tax_invoice_id = $this->request->get['sale_tax_invoice_id'];
        $with_previous_balance = isset($this->request->get['with_previous_balance'])?1:0;
        $type = $this->request->get['type'];

        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['sale_tax_invoice'] = $this->load->model('inventory/sale_tax_invoice');
        $this->model['sale_tax_invoice_detail'] = $this->load->model('inventory/sale_tax_invoice_detail');
        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
        $this->model['company'] = $this->load->model('setup/company_branch');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $company_address = $this->model['company']->getRow(array('company_id' => $session['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $invoice = $this->model['sale_tax_invoice']->getRow(array('sale_tax_invoice_id' => $sale_tax_invoice_id));
        // d($invoice,true);
        $details = $this->model['sale_tax_invoice_detail']->getRows(array('sale_tax_invoice_id' => $sale_tax_invoice_id), array('sort_order asc'));
        $partner = $this->model['partner']->getRow(array('partner_id' => $invoice['partner_id']));
        $delivery_challan_id = $invoice['ref_document_id'];

        $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');
        $CustomerUnit = $this->model['customer_unit']->getRow(array('customer_unit_id' => $invoice['customer_unit_id']));
        $where = 'AND l.partner_id = "'.$partner['partner_id'].'"';
        $where.= 'AND l.coa_id = "'.$partner['outstanding_account_id'].'"';
        $where.= 'AND l.document_identity < "'.$invoice['document_identity'].'"';
        $previous_balance =  $this->model['sale_tax_invoice']->getPreviousBalance($where);
        
        // $outstanding = $this->model['partner']->getOutstanding("l.`partner_id` = '".$invoice['partner_id']."' AND l.`created_at` < '".$invoice['created_at']."'");
        //d(array($sale_tax_invoice_id, $invoice, $details), true);
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
        $pdf->SetAuthor('kashan');
        $pdf->SetTitle('Invoice');
        $pdf->SetSubject('Invoice');

        //Set Header
        // $pdf->InvoiceCheck = $type;
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
            'type' => $type
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
        $arrAddress = splitString($partner['address'], 55);

        $pdf->setXY(8,17);
        $pdf->SetFont('helvetica', 'B', 11);    

        $pdf->Cell(26, 9, 'M/s.  ', '', false, 'L', 1, '', 1);
        $pdf->setXY(18,16.4);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(82, 6, $invoice['partner_name'], 'B', false, 'L', 0, '', 0, false, 'T', 'M');

        $invoice['document_date'] = date_create($invoice['document_date']);
        $invoice['document_date'] = date_format($invoice['document_date'],'d/m/Y');

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->setXY(117,18.1);
        $pdf->Cell(30, 5, $invoice['document_identity'], 'B', false, 'L', 1, '', 1);
        $pdf->setXY(117,25.4);
        $pdf->Cell(20, 5,  $invoice['document_date'], 'B', false, 'L', 1, '', 1);


        $pdf->setXY(156,16);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(24, 9, 'M/s.  ', '', false, 'L', 1, '', 1);
        $pdf->setXY(166,15.4);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(82, 6, $invoice['partner_name'], 'B', false, 'L', 0, '', 0, false, 'T', 'M');

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->setXY(263,18.2);
        $pdf->Cell(30, 5, $invoice['document_identity'], 'B', false, 'L', 1, '', 1);
        $pdf->setXY(263,25.4);
        $pdf->Cell(20, 5,  $invoice['document_date'],'B', false, 'L', 1, '', 1);



         $pdf->setXY(7,24);
         $pdf->Cell(1,24, '', '', false, 'L', 1, '', 1);
         $pdf->SetFont('helvetica', 'B', 10);
         $pdf->Cell(20, 8, 'Address. ', '', false, 'L', 1, '', 1);


         foreach ($arrAddress as $index => $add) {

            if($index == 0) {       
              $pdf->SetFont('helvetica', '', 9);
              $pdf->Cell(80, 8, $arrAddress[$index], 'B', false, 'L', 1, '', 1);
            }
             else {
              $pdf->setXY(28,31);  
              $pdf->SetFont('helvetica', '', 9);     
              $pdf->Cell(80, 8, $arrAddress[$index], 'TB', false, 'L', 1, '', 1);
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
              $pdf->Cell(80, 8, $arrAddress[$index], 'B', false, 'L', 1, '', 1);
            }
             else {
              $pdf->setXY(176,31);  
              $pdf->SetFont('helvetica', '', 9);     
              $pdf->Cell(80, 8, $arrAddress[$index], 'TB', false, 'L', 1, '', 1);
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
        $pdf->Cell(11, 7.4, 'S.#', 1, false, 'C', 1, '', 1);
        $pdf->Cell(37, 7.4, 'Description', 1, false, 'L', 1, '', 1);
        $pdf->Cell(31, 7.4, 'Category', 1, false, 'C', 1, '', 1);
        $pdf->Cell(13, 7.4, 'Qty', 1, false, 'C', 1, '', 1);
        $pdf->Cell(20, 7.4, 'Rate', 1, false, 'C', 1, '', 1);
        $pdf->Cell(22, 7.4, 'Amount', 1, false, 'R', 1, '', 1);


        $pdf->Cell(14, 6, '', 'L', false, 'C', 1, '', 1);
        $pdf->Cell(11, 7.4, 'S.#', 1, false, 'C', 1, '', 1);
        $pdf->Cell(37, 7.4, 'Description', 1, false, 'L', 1, '', 1);
        $pdf->Cell(31, 7.4, 'Category', 1, false, 'C', 1, '', 1);
        $pdf->Cell(13, 7.4, 'Qty', 1, false, 'C', 1, '', 1);
        $pdf->Cell(20, 7.4, 'Rate', 1, false, 'C', 1, '', 1);
        $pdf->Cell(22, 7.4, 'Amount', 1, false, 'R', 1, '', 1);


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
                    $pdf->Cell(37, 7, html_entity_decode($detail['product_name']), 'BLR', false, 'L', 0, '', 1);
                    $pdf->Cell(31, 7, html_entity_decode($detail['product_category']), 'BLR', false, 'C', 0, '', 1);
                    $pdf->Cell(13, 7, (int) ($detail['qty']), 'BLR', false, 'C', 0, '', 1);
                    $pdf->Cell(20, 7, (int) ($detail['rate']), 'BLR', false, 'C', 0, '', 1);
                    $pdf->Cell(22, 7, number_format($detail['amount'],0), 'BLR', false, 'R', 0, '', 1);
                    $pdf->Cell(14, 6, '', 'L', false, 'C', 1, '', 1);
                    $pdf->Cell(11, 7, $sr, 'BLR', false, 'C', 0, '', 1);
                    $pdf->Cell(37, 7, html_entity_decode($detail['product_name']), 'BLR', false, 'L', 0, '', 1);
                    $pdf->Cell(31, 7, html_entity_decode($detail['product_category']), 'BLR', false, 'C', 0, '', 1);
                    $pdf->Cell(13, 7, (int) ($detail['qty']), 'BLR', false, 'C', 0, '', 1);
                    $pdf->Cell(20, 7, (int) ($detail['rate']), 'BLR', false, 'C', 0, '', 1);
                    $pdf->Cell(22, 7, number_format($detail['amount'],0), 'BLR', false, 'R', 0, '', 1);

                    $pdf->ln(7);
    
            } else {
                $arrDesc = splitString($detail['product_name'], 20);
                
                   foreach($arrDesc as $index => $remark){
                    
                     if($index==0){

                      $pdf->Cell(11, 5, $sr, 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(37, 5, $remark, 'LR', false, 'L', 0, '', 1);
                      $pdf->Cell(31, 5, html_entity_decode($detail['product_category']), 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(13, 5, (int) ($detail['qty']), 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(20, 5, (int) ($detail['rate']), 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(22, 5,  number_format($detail['amount'],2), 'LR', false, 'R', 0, '', 1);
                      $pdf->Cell(1, 5, '', 'L', false, 'R', 0, '', 1);

                      $pdf->Cell(13, 6, '', '', false, 'C', 1, '', 1);
                      $pdf->Cell(11, 5, $sr, 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(34, 5, $remark, 'LR', false, 'L', 0, '', 1);
                      $pdf->Cell(28, 5, html_entity_decode($detail['product_category']), 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(13, 5, (int) ($detail['qty']), 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(20, 5, (int) ($detail['rate']), 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(22, 5,  number_format($detail['amount'],2), 'LR', false, 'R', 0, '', 1);


                     } else if($index<=count($arrDesc)-1){
                      $pdf->Cell(2, 5, '', '', false, 'C', 1, '', 1);
                      $pdf->Cell(11, 5, '', 'LRB', false, 'C', 0, '', 1);
                      $pdf->Cell(37, 5, $remark, 'LRB', false, 'L', 0, '', 1);
                      $pdf->Cell(31, 5, '', 'LRB', false, 'C', 0, '', 1);
                      $pdf->Cell(13, 5, '', 'LRB', false, 'C', 0, '', 1);
                      $pdf->Cell(20, 5, '', 'LRB', false, 'C', 0, '', 1);
                      $pdf->Cell(22, 5, '', 'LRB', false, 'R', 0, '', 1);
                      $pdf->Cell(1, 5, '', 'L', false, 'R', 0, '', 1);


                      $pdf->Cell(11, 6, '', '', false, 'C', 1, '', 1);
                      $pdf->Cell(2, 5, '', '', false, 'C', 1, '', 1);
                      $pdf->Cell(11, 5, '', 'LRB', false, 'C', 0, '', 1);
                      $pdf->Cell(37, 5, $remark, 'LRB', false, 'L', 0, '', 1);
                      $pdf->Cell(31, 5, '', 'LRB', false, 'C', 0, '', 1);
                      $pdf->Cell(13, 5, '', 'LRB', false, 'C', 0, '', 1);
                      $pdf->Cell(20, 5, '', 'LRB', false, 'C', 0, '', 1);
                      $pdf->Cell(22, 5, '', 'LRB', false, 'R', 0, '', 1);
                     } else {

                      $pdf->Cell(2, 5, '', '', false, 'C', 1, '', 1);   
                      $pdf->Cell(11, 5, '', 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(37, 5, $remark, 'LR', false, 'L', 0, '', 1);
                      $pdf->Cell(31, 5, '', 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(13, 5, '', 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(20, 5, '', 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(22, 5, '', 'LR', false, 'R', 0, '', 1);

                      $pdf->Cell(12, 6, '', '', false, 'C', 1, '', 1);
                      $pdf->Cell(2, 5, '', '', false, 'C', 1, '', 1);   
                      $pdf->Cell(11, 5, '', 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(37, 5, $remark, 'LR', false, 'L', 0, '', 1);
                      $pdf->Cell(31, 5, '', 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(13, 5, '', 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(20, 5, '', 'LR', false, 'C', 0, '', 1);
                      $pdf->Cell(22, 5, '', 'LR', false, 'R', 0, '', 1);
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
            if($y>125 && $record_no < (count($details)-1)) {
              $pdf->Cell(1, 1, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
              $pdf->AddPage();
              $pdf->SetFont('helvetica', 'B', 9);
              $pdf->ln(-12);
              $pdf->Cell(2, 6, '', '', false, 'C', 1, '', 1);
              $pdf->Cell(11, 7.4, 'S.#', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(37, 7.4, 'Description', 'TBLR', false, 'L', 1, '', 1);
              $pdf->Cell(31, 7.4, 'Category', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(13, 7.4, 'Qty', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(20, 7.4, 'Rate', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(22, 7.4, 'Amount', 'TBLR', false, 'C', 1, '', 1);

              $pdf->setX(154);
              $pdf->Cell(3, 6, '', '', false, 'C', 1, '', 1);
              $pdf->Cell(11, 7.4, 'S.#', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(37, 7.4, 'Description', 'TBLR', false, 'L', 1, '', 1);
              $pdf->Cell(31, 7.4, 'Category', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(13, 7.4, 'Qty', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(20, 7.4, 'Rate', 'TBLR', false, 'C', 1, '', 1);
              $pdf->Cell(22, 7.4, 'Amount', 'TBLR', false, 'C', 1, '', 1);
              $pdf->ln(7);
              $pdf->SetFont('helvetica', '', 8);
            }
        }


        $balance_amount = ($invoice['net_amount'] + $previous_balance - $invoice['cash_received']);




        $pdf->ln(4);

        $pdf->SetFont('helvetica', 'B', 9);

       $pdf->Cell(3, 6, '', '', false, 'C', 1, '', 1);
       $pdf->SetFont('helvetica', 'B', 9);
       $pdf->Cell(130, 6, 'Amount in Words: ', 0, false, 'L', false, 'M', 'M');
       $pdf->setX(158);
       $pdf->Cell(130, 6, 'Amount in Words: ', 0, false, 'L', false, 'M', 'M');
   
       $pdf->Ln(4.8);
       $pdf->SetFont('helvetica', '', 9);
       $pdf->Cell(3, 6, '', '', false, 'C', 1, '', 1);

       $amountStr = Number2Words($invoice['net_amount']);
           $arrAmount = splitString($amountStr  ,40);

          if(strlen($amountStr) >= 40){

           $pdf->Cell(50, 6, Number2Words($invoice['net_amount']) . ' Only ', 0, false, 'L');
           $pdf->setX(158); 
           $pdf->Cell(50, 6, Number2Words($invoice['net_amount']) . ' Only ', 0, false, 'L');
          
          }
          else{
          foreach ($arrAmount as $key => $amount) {

           if($key == 0){
               $pdf->Cell(50, 6, $amount, 0, false, 'L');
               $pdf->setX(158);
               $pdf->Cell(50, 6, $amount, 0, false, 'L');
           }
           else {
               $pdf->Cell(3, 6, '', '', false, 'C', 1, '', 1);
               $pdf->Cell(50, 6, $amount . 'Only', 0, false, 'L');
               $pdf->setX(158);
               $pdf->Cell(50, 6, $amount, 0, false, 'L');
           }
           $pdf->Ln(6);

          }
       }
        
       $length = 0;;  
       if(count($arrAmount) == 1){
        $pdf->ln(-4 - (count($arrAmount)-1) * 6);
         $length = 28;
       }
       else {
         $pdf->ln(-12 - (count($arrAmount)-1) * 6);
         $length = (15 * (count($arrAmount)));
       } 
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(81, 5, ' ', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(13, 5.6 ,number_format($total_qty,0), 1, false, 'C', 0, '', 0, false, 'M', 'M');

        $pdf->setX(155);
        $pdf->Cell(81, 5, ' ', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(13, 5.6 ,number_format($total_qty,0), 1, false, 'C', 0, '', 0, false, 'M', 'M');
        if(count($arrAmount) == 1){
         $pdf->ln(2.1);
       }
       else {
         $pdf->ln(8.1);
       } 

        $pdf->setX(106);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(15, 5,'Total :', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setX(114);
        $pdf->Cell(28, 5.6 ,number_format($total_amount,0), 0, false, 'R', 0, '', 0, false, 'M', 'M');

        $pdf->setX(255);
        $pdf->Cell(15, 5,'Total :', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setX(262);
        $pdf->Cell(28, 5.6 ,number_format($total_amount,0), 0, false, 'R', 0, '', 0, false, 'M', 'M');

        $pdf->Ln(4.5);
        $pdf->setX(106);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(15, 5,'Freight:', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setX(112);
        $pdf->Cell(30, 5.6 ,number_format($invoice['cartage'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');

        $pdf->setX(255);
        $pdf->Cell(15, 5,'Freight:', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setX(260);
        $pdf->Cell(30, 5.6 ,number_format($invoice['cartage'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');


        $pdf->Ln(5.1);
        $pdf->setX(106);
     
        $pdf->Cell(35, 4, '', 'T', false, 'L',0, '', 0, false, 'M', 'M');
        $pdf->setX(255);
        $pdf->Cell(35, 4, '', 'T', false, 'L',0, '', 0, false, 'M', 'M');


        $pdf->Ln(1);
        $pdf->setX(106);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(15, 5,'Net Bill:', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setX(112);
        $pdf->Cell(30, 5.6 ,number_format($invoice['net_amount'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');

        $pdf->setX(255);       
        $pdf->Cell(15, 5,'Net Bill:', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setX(260);
        $pdf->Cell(30, 5.6 ,number_format($invoice['net_amount'],0), 0, false, 'R', 0, '', 0, false, 'M', 'M');

        $pdf->ln(-10);
        $pdf->Cell(2, 5, ' ', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(134, $length, '', 'BLR', false, 'L',0, '', 0, false, 'M', 'M');
        
        $pdf->setX(155);
        $pdf->Cell(2, 5, ' ', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(134, $length, '', 'BLR', false, 'L',0, '', 0, false, 'M', 'M');
        

        $pdf->Ln(24);

        $pdf->setX(77);

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(35, 6, 'Previous Balance: ', 0, false, 'L', false, 'M', 'M');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(30, 6, number_format($previous_balance,0), 0, false, 'R', false, 'M', 'M');
        $pdf->setX(225);
        $pdf->Cell(35, 6, 'Previous Balance: ', 0, false, 'L', false, 'M', 'M');
        $pdf->Cell(30, 6, number_format($previous_balance,0), 0, false, 'R', false, 'M', 'M');


        $pdf->Ln(4.5);
        $pdf->setX(77);

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(35, 6, 'Bill Amount          : ', 0, false, 'L', false, 'M', 'M');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(30, 6, number_format($invoice['net_amount'],0), 0, false, 'R', false, 'M', 'M');
     
        $pdf->setX(225);
        $pdf->Cell(35, 6, 'Bill Amount          : ', 0, false, 'L', false, 'M', 'M');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(30, 6, number_format($invoice['net_amount'],0), 0, false, 'R', false, 'M', 'M');
     

        $pdf->Ln(4.5);
        $pdf->setX(77);


        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(35, 6, 'Amount Received: ', 0, false, 'L', false, 'M', 'M');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(30, 6, number_format($invoice['cash_received'],0), 0, false, 'R', false, 'M', 'M');
       
        $pdf->setX(225);
        $pdf->Cell(35, 6, 'Amount Received: ', 0, false, 'L', false, 'M', 'M');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(30, 6, number_format($invoice['cash_received'],0), 0, false, 'R', false, 'M', 'M');
       

        $pdf->Ln(8.2);
        $pdf->setX(78);
        $pdf->Cell(63, 4, '', 'T', false, 'L',0, '', 0, false, 'M', 'M');
        $pdf->setX(226);
        $pdf->Cell(63, 4, '', 'T', false, 'L',0, '', 0, false, 'M', 'M');
        $pdf->Ln(-2);
        $pdf->setX(77);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(35, 6, 'Balance Amount  : ', 0, false, 'L', false, 'M', 'M');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(30, 6, number_format($balance_amount,0), 0, false, 'R', false, 'M', 'M');


        $pdf->setX(225);
        $pdf->Cell(35, 6, 'Balance Amount  : ', 0, false, 'L', false, 'M', 'M');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(30, 6, number_format($balance_amount,0), 0, false, 'R', false, 'M', 'M');

       
        $pdf->ln(1);
        $pdf->SetFont('helvetica', '', 4);
        // $pdf->ln(3.5);
        $pdf->setX(10);
        $pdf->Cell(2, 1, '', 1, 0, 'C');
        $pdf->ln(1);
        $pdf->setX(12);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(30, 5, 'No Claim', 0, false, 'L', 0, '', 0, false, 'M', 'M');


        $pdf->SetFont('helvetica', '', 4);
        $pdf->ln(-1);
        $pdf->setX(26);
    
        $pdf->Cell(2, 1, '', 1, 0, 'C');
        $pdf->ln(0.9);
        $pdf->setX(28);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(30, 5, 'No Return', 0, false, 'L', 0, '', 0, false, 'M', 'M');


        $pdf->SetFont('helvetica', '', 4);
        $pdf->ln(-1);
        $pdf->setX(44);
    
        $pdf->Cell(2, 1, '', 1, 0, 'C');
        $pdf->ln(0.9);
        $pdf->setX(46);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(30, 5, 'No Exchange', 0, false, 'L', 0, '', 0, false, 'M', 'M');



        $pdf->ln(-4);
        $pdf->SetFont('helvetica', '', 4);
        $pdf->ln(3.5);
        $pdf->setX(160);
        $pdf->Cell(2, 1, '', 1, 0, 'C');
        $pdf->ln(1);
        $pdf->setX(162);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(30, 5, 'No Claim', 0, false, 'L', 0, '', 0, false, 'M', 'M');



        $pdf->SetFont('helvetica', '', 4);
        $pdf->ln(-1);
        $pdf->setX(176);
    
        $pdf->Cell(2, 1, '', 1, 0, 'C');
        $pdf->ln(0.9);
        $pdf->setX(178);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(30, 5, 'No Return', 0, false, 'L', 0, '', 0, false, 'M', 'M');


        $pdf->SetFont('helvetica', '', 4);
        $pdf->ln(-1);
        $pdf->setX(194);
    
        $pdf->Cell(2, 1, '', 1, 0, 'C');
        $pdf->ln(0.9);
        $pdf->setX(196);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(30, 5, 'No Exchange', 0, false, 'L', 0, '', 0, false, 'M', 'M');

       $styless = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 2, 'color' => array(128, 128, 128  ));
       $styles = array('width' => 0.1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
       $pdf->Line(150, 7, 150, 204, $styless);
       $pdf->Line(0, 0, 0, 0,$styles);
        //Close and output PDF document
        $pdf->Output('Invoice - '.$invoice['document_identity'].'.pdf', 'I');

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
            //$this->Image($image_file, 5, 5, 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        // else
        // {
        //     // Set font
        //     $this->SetFont('helvetica', 'B', 20);
        //     $this->Ln(2);
        //     //Title 
        //     if($this->data['company_branch_id']==22 ){
        //         $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        //     }
        //     else {
        //         $this->Cell(0, 10, $this->data['company_branch_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        //     }
        // }

        if($this->data['type'] == "sale_invoice")
        {

            $this->SetFont('helvetica', 'B', 11);
            $this->Ln(5);
            // Title
            $this->Cell(135, 12, 'Invoice', 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->setX(150);
            $this->Cell(135, 12, 'Invoice', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        }
        else {
            $this->SetFont('helvetica', 'B', 14);
            $this->Ln(8);
            // Title
            $this->Cell(105, 12, 'Bill', 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $this->Ln(10);

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
            $this->Image($this->data['header_image'], 0, 5, 205, "", "JPG", "", "T", false, 300, "", false, false, 0, false, false, false);
            $this->Ln(47);

            $this->SetFont('helvetica', 'B,I', 16);
            $this->Cell(0, 5, "Estimate", 0, false, 'C', 0, '', 0, false, 'M', 'M');


        }
        // if($this->InvoiceCheck == "sale_tax_invoice")
        // {

            // $this->SetFont('helvetica', 'B', 30);
            // $this->Cell(95, 12, html_entity_decode($this->data['company_name']), 0, false, 'C', 0, '', 0, false, 'M', 'M');
        // }
//         if($this->InvoiceCheck == "sale_invoice")
//         {
            
//             $this->SetFont('helvetica', 'B', 14);
//             $this->Ln(15);
//             // Title
//             // check branch id
//             if($this->data['company_branch_id']==22){
//                 $this->Cell(119, 25, 'SALES INVOICE', 0, false, 'R', 0, '', 0, false, 'M', 'M');
//             }
//             else if($this->data['company_branch_id']==24){
//                 $this->Cell(112, 25, 'CASH MEMO', 0, false, 'R', 0, '', 0, false, 'M', 'M');
//             }
//             // $this->SetFont('helvetica', 'B', 30);
//             // $this->Cell(95, 12, html_entity_decode($this->data['company_name']), 0, false, 'C', 0, '', 0, false, 'M', 'M');


//             $this->Ln(10);

//         }
//         if($this->InvoiceCheck == "CommercialInvoice")
//         {
//             $this->SetFont('helvetica', 'B', 14);
//             $this->Ln(20);
//             // Title

//             if($this->data['company_branch_id']==22){
//                 $this->Cell(100, 25, 'COMMERCIAL INVOICE', 0, false, 'R', 0, '', 0, false, 'M', 'M');
//             }
//             else {
//                 $this->Cell(125, 25, 'COMMERCIAL INVOICE', 0, false, 'R', 0, '', 0, false, 'M', 'M');
//             }
            
//             // $this->SetFont('helvetica', 'B', 30);
//             // $this->Cell(95, 12, html_entity_decode($this->data['company_name']), 0, false, 'C', 0, '', 0, false, 'M', 'M');


//         }

//         if($this->InvoiceCheck == "Exempted Bill")
//         {
// //            $this->Image($this->data['header_image'], 0, 5, 205, "", "JPG", "", "T", false, 300, "", false, false, 0, false, false, false);
//             $this->Image($this->data['header_image'], 0, 5, 205, "", "JPG", "", "T", false, 300, "", false, false, 0, false, false, false);
// //            $this->Ln(47);
//         }

    }

    
    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        // Set font

        $this->SetY(-25);
        $y = $this->GetY();

        // if($this->data['company_footer_print'] != '') {
        //     $image_file = DIR_IMAGE.$this->data['company_footer_print'];
        //     // $this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
        //     $this->Image($image_file, 5, ($y-10), 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // }

//         if($this->InvoiceCheck == "Bill")
//         {
//             $this->SetY(-55);
//             $this->SetFont('helvetica', 'B,I', 10);
//             $this->Cell(100, 5, "Goods once sold can not be taken back of exchanged", 0, false, 'L', 0, '', 0, false, 'M', 'M');
//             $this->SetFont('helvetica', 'B', 12);
//             $this->Cell(80, 5, 'For '.html_entity_decode($this->data['company_name']), 0, false, 'R', 0, '', 0, false, 'M', 'M');
//             $this->Ln(5);
// //            $this->SetFont('helvetica', 'I', 8);
// //            // Page number
// //            $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
//             $this->Image($this->data['footer_image'], 0, 250, 205, "", "JPG", "", "T", false, 300, "", false, false, 0, false, false, false);
//             $this->Ln(5);
//             $this->SetFont('helvetica', 'I', 8);
//             // Page number
//             $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

//         }
        // if($this->InvoiceCheck == "sale_tax_invoice")
        // {
        //     $this->SetY(-20);
        //     $this->SetFont('helvetica', 'I', 8);
        //     // Page number
        //     // $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        // }
//         if($this->InvoiceCheck == "sale_invoice" || $this->InvoiceCheck == "CommercialInvoice")
//         {
//             $this->SetY(-45);
//             $this->SetFont('helvetica', 'B', 8);
//             // Page number
//             // $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
//             $this->ln(10);
//             $this->Cell(10, 5, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//             $this->Cell(35, 5, $this->data['user_name'], "T", false, 'C', 0, '', 0, false, 'M', 'M');

//             $this->Cell(10, 5, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//             $this->Cell(35, 5, 'Checked By', "T", false, 'C', 0, '', 0, false, 'M', 'M');

//             $this->Cell(10, 5, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//             $this->Cell(35, 5, 'Approved By', "T", false, 'C', 0, '', 0, false, 'M', 'M');

//             $this->Cell(10, 5, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//             $this->Cell(45, 5, 'For '.html_entity_decode($this->data['company_branch_name']), "T", false, 'C', 0, '', 0, false, 'M', 'M');
                
//             // $this->Cell(10+55+15+15+20+10, 5, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//             // $this->Cell(20, 5, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//             // $this->Cell(45, 5, 'For ' . $this->session->data['company_name'], "T", false, 'C', 0, '', 0, false, 'M', 'M');

//         }

//         if($this->InvoiceCheck == "Exempted Bill")
//         {
//             $this->SetY(-55);
//             $this->SetFont('helvetica', 'B,I', 10);
//             $this->Image($this->data['footer_image'], 0, 250, 205, "", "JPG", "", "T", false, 300, "", false, false, 0, false, false, false);
// //            $this->Ln(5);
// //            $this->SetFont('helvetica', 'I', 8);
//             // Page number
// //            $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

//         }

    }
}




?>