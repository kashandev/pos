<?php

class ControllerInventorySaleInvoice extends HController {

    protected $document_type_id = 2;

    protected function getAlias() {
        return 'inventory/sale_invoice';
    }

    protected function getPrimaryKey() {
        return 'sale_invoice_id';
    }

    protected function getList() {
        parent::getList();

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partners'] = $this->model['partner']->getRows(array('company_id' => $this->session->data['company_id']),array('name'));
//d($this->data['partners'],true);
        $url='';
        if(isset($this->request->get['partner_id'])) {
            $url .= "&partner_id=".$this->request->get['partner_id'];
            $this->data['partner_id'] = $this->request->get['partner_id'];
        }

        $this->data['action_list'] = $this->url->link($this->getAlias(), 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'].$url, 'SSL');

//        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {
        $lang = $this->load->language($this->getAlias());
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action', 'document_date' , 'document_identity', 'partner_type','partner_name', 'net_amount','invoice_status', 'created_at', 'check_box');

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
        if(isset($this->request->get['partner_id'])) {
            $arrWhere[] = "`partner_id` ='".$this->request->get['partner_id']."'";
        }
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
                'text' => $lang['print_bill'],
                'target' => '_blank',
                'href' => $this->url->link($this->getAlias() . '/printDocumentBill', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                'btn_class' => 'btn btn-info btn-xs',
                'class' => 'fa fa-print'
            );

//            $actions[] = array(
//                'text' => $lang['print_local_bill'],
//                'target' => '_blank',
//                'href' => $this->url->link($this->getAlias() . '/printDocumentLocalBill', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()].'&with_previous_balance=1', 'SSL'),
//                'btn_class' => 'btn btn-info btn-xs',
//                'class' => 'fa fa-print'
//            );

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
                $strAction .= '<a '.(isset($action['btn_class'])?'class="'.$action['btn_class'].'"':'').' '.(isset($action['target'])?'target="'.$action['target'].'"':'').' href="' . $action['href'] .'" '. (isset($action['target']) ? 'target="' . $action['target'] . '"' : '') . ' data-toggle="tooltip" title="' . $action['text'] . '" ' . (isset($action['click']) ? 'onClick="' . $action['click'] . '"' : '') . '>';
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
                }elseif ($aColumns[$i] == 'invoice_status') {
                    $html = '';
                    //$balance = $aRow['net_amount'] - ($aRow['bank_receipt_amount'] + $aRow['cash_receipt_amount']);
                    if($aRow['invoice_status'] == "Cleared")
                    {
                        $html .= '<label class="btn btn-success btn-xs" style="margin:1px;">Cleared</label>';
                    }else{
                        $html .= '<label class="btn btn-danger btn-xs" style="margin:1px;">Pending</label>';
                    }
                    $row[] = $html;
                }
                elseif ($aColumns[$i] == 'check_box') {
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
       // d($this->session->data,true);
        parent::getForm();

        $this->model['product'] = $this->load->model('inventory/product');
        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']),array('name'));

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

//        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
//        $this->data['ref_documents'] = $this->model['delivery_challan']->getRows(array('company_id' => $this->session->data['company_id']));


        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";

        // $this->data['partner_type_id'] = "2";
        $this->model['customer'] = $this->load->model('setup/customer');
        $this->data['partner_types'] = $this->session->data['partner_types'];

        $where = "company_id=" . $this->session->data['company_id'];
        $where .= " AND partner_category_id!=" .'1';

        $this->data['partners'] = $this->model['customer']->getRows($where,array('name'));



        $this->data['document_date'] = stdDate();
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        if (isset($this->request->get['sale_invoice_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $result = $this->model[$this->getAlias()]->getRow(array('sale_invoice_id' => $this->request->get[$this->getPrimaryKey()]));

            foreach ($result as $field => $value) {
                if ($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                } elseif($field=='po_date') {
                    $this->data[$field] = stdDate($value);
                }elseif($field=='bilty_date') {
                    $this->data[$field] = stdDate($value);
                }
                elseif($field=='ref_document_id') {
                    $this->data[$field] = json_decode($value);
                }
                else {
                    $this->data[$field] = $value;
                }
            }
            // $this->data['partner_type_id'] = "2";

//            $where = " company_id=".$this->session->data['company_id'];
//            $where .= " AND delivery_challan_id IN('" . implode("','", json_decode($result['ref_document_id'])) . "')";

//            $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
//            $this->data['ref_documents'] = $this->model['delivery_challan']->getRows($where);
//
//            $this->data['ref_document_id'] = json_decode($result['ref_document_id']);

            $this->model['sale_invoice_detail'] = $this->load->model('inventory/sale_invoice_detail');
            $details = $this->model['sale_invoice_detail']->getRows(array('sale_invoice_id' => $this->request->get['sale_invoice_id']), array('sort_order asc'));

            $this->model['partner'] = $this->load->model('common/partner');
            $this->data['partners'] = $this->model['partner']->getRows(array('partner_type_id' => $result['partner_type_id']));

            $this->data['sale_invoice_details'] = $details;
        }

        $this->data['href_get_ref_document_json'] = $this->url->link($this->getAlias() . '/getRefDocumentJson', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_document_detail'] = $this->url->link($this->getAlias() . '/getDocumentDetails', 'token=' . $this->session->data['token'] . '&announcement_id=' . $this->request->get['announcement_id']);
        $this->data['href_get_container_products'] = $this->url->link($this->getAlias() . '/getContainerProducts', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['get_ref_document'] = $this->url->link($this->getAlias() . '/getRefDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['get_ref_document_record'] = $this->url->link($this->getAlias() . '/getRefDocumentRecord', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print_bill'] = $this->url->link($this->getAlias() . '/printDocumentBill', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        //$this->data['action_print_local_bill'] = $this->url->link($this->getAlias() . '/printDocumentLocalBill', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()].'&with_previous_balance=1', 'SSL');
        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['get_Customer'] = $this->url->link($this->getAlias() . '/getCustomer', 'token=' . $this->session->data['token']);
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

    public function getCustomer() {

        $post = $this->request->post;

        $challan_type='';
//        d($post,true);
        $partner_id = $post['partner_id'];

        $this->session->data['customer_id'] = $partner_id;

        $this->model['customer'] = $this->load->model('setup/customer');
        $where = "company_id=" . $this->session->data['company_id'];
        $where .= " AND customer_id='" . $partner_id . "'";

        $suscess='';

        $partner = $this->model['customer']->getRow($where,array('name'));

//        d($partner,true);
        if($partner['customer_code'] != '')
        {
            $suscess = true;
        }
        else{
            $suscess = false;

        }

        $json = array(
            'success' => $suscess,
            'error'=> 'Customer Code Not Exists'
        );

        $this->response->setOutput(json_encode($json));
    }


    public function getRefDocumentJson() {
        //d('Hi',true);
        $search = $this->request->post['q'];
        $page = $this->request->post['page'];
        $challan_type = 'Non GST';
        $type = 'sale_invoice';

        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
        $rows = $this->model['delivery_challan']->getRefDocumentJson($search, $page,$challan_type,$type);

//        d($rows,true);
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
        $post = $this->request->post;
        $partner_id = $post['partner_id'];
        $ref_document_id = $post['ref_document_id'];


        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'partner_id' => $partner_id,
            'challan_type' => 'Non GST'

        );

        $delivery_challans = $this->model['delivery_challan']->getRows($filter);

        $html = '<option value="">&nbsp;</option>';
        foreach($delivery_challans as $delivery_challan) {
            if($delivery_challan['delivery_challan_id'] == $ref_document_id) {
                $html .= '<option value="'.$delivery_challan['delivery_challan_id'].'" selected="true">'.$delivery_challan['document_identity'].'</option>';
            } else {
                $html .= '<option value="'.$delivery_challan['delivery_challan_id'].'">'.$delivery_challan['document_identity'].'</option>';
            }
        }

        $json = array(
            'success' => true,
            'html' => $html,
            'partners' => $delivery_challans
        );
//d($json,true);
        $this->response->setOutput(json_encode($json));
    }


    public function getDocumentDetails() {
        $post = $this->request->post;

        $sale_invoice_id = $this->request->get['sale_invoice_id'];
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

        $Delivery_Challans = $this->model['delivery_challan']->getDeliveryChallans($where,'sale_invoice',$sale_invoice_id);


//        $this->model['delivery_challan_detail'] = $this->load->model('inventory/delivery_challan_detail');
//
//        if($post['ref_document_id'] != '')
//        {
//            $where = " delivery_challan_id IN('" . implode("','", array_values($post['ref_document_id'])) . "')";
//        }
//        $rows = $this->model['delivery_challan_detail']->getRows($where);

        $details = array();
        $row_no = 0;


        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
        if($post['ref_document_id'] != '')
        {
            $where = " delivery_challan_id IN('" . implode("','", array_values($post['ref_document_id'])) . "')";
        }
        $DeliveryChallans = $this->model['delivery_challan']->getRows($where);

        $resultstr = array();
        foreach($DeliveryChallans as $DeliveryChallan)
        {
            $resultstr[] = $DeliveryChallan['document_identity'];
            $PoNo = $DeliveryChallan['po_no'];
            $PoDate = stdDate($DeliveryChallan['po_date']);
//            $PoNo = $row['po_no'];
            $CustomerUnitId = $DeliveryChallan['customer_unit_id'];

            $delivery_challan = $Delivery_Challans[$DeliveryChallan['document_identity']];

            foreach($delivery_challan['products'] as $product) {
//            $details[$row_no]['balanced_qty'] = ($product['order_qty'] - $product['utilized_qty']);
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


                    $arrPartner[$delivery_challan['partner_id']] = $delivery_challan['document_identity']['document_identity'];
                    $Partner = $delivery_challan['partner_id'];
//
//                $details[$row_no]['row_identity'] = $row['document_identity'].'-'.$row['product_code'];
//                $details[$row_no]['href'] = $href;
//                $details[$row_no]['amount'] = ($row['qty']) * ($row['cog_rate']);

                    $row_no++;
                }
            }
        }





//        foreach($rows as $row_no => $row) {
//
//            $arrPartner[$row['partner_id']] = $row['document_identity'];
//            $Partner = $row['partner_id'];
//            $PoDate = stdDate($row['po_date']);
////            $PoNo = $row['po_no'];
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


        $delivery_challan['products'] = $details;

        $DcNo = implode(",",$resultstr);

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

        $details = $post['sale_invoice_details'];
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
                    $filter ['document_id'] = $ID['sale_invoice_id'];

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
//d($data,true);
        // get Customer //

        $this->model['customer'] = $this->load->model('setup/customer');
        $customer = $this->model['customer']->getRow(array('customer_id' => $data['partner_id']));

        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['product']= $this->load->model('inventory/product');
        $this->model['setting']= $this->load->model('common/setting');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['sale_invoice_detail'] = $this->load->model('inventory/sale_invoice_detail');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['document_type'] = $this->load->model('common/document_type');
        $this->model['customer_rate']= $this->load->model('inventory/customer_rate');

        // get Document //
        // $document = $this->model[$this->getAlias()]->getNextDocument($this->document_type_id,html_entity_decode($customer['customer_code']),$data['partner_id']);
        
        // now getting document number without customer code
        $document = $this->model[$this->getAlias()]->getNextDocument($this->document_type_id);
        // d($document,true);

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
        // $data['partner_type_id'] = 2;

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

        // insert Sale Invoice
        $sale_invoice_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $data['document_id'] = $sale_invoice_id;

        // document add
        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $sale_invoice_id,
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['net_amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['net_amount'],
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


        //  Add Sale Invoice Detail
        foreach ($data['sale_invoice_details'] as $sort_order => $detail) {
            $detail['sale_invoice_id'] = $sale_invoice_id;
            $detail['sort_order'] = $sort_order;
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_total'] = $detail['total_amount'] * $data['conversion_rate'];
            $sale_invoice_detail_id = $this->model['sale_invoice_detail']->add($this->getAlias(), $detail);



            $stock_ledger = array(
                'company_id' => $detail['company_id'],
                'company_branch_id' => $detail['company_branch_id'],
                'fiscal_year_id' => $detail['fiscal_year_id'],
                'document_type_id' => $data['document_type_id'],
                'document_id' => $data['document_id'],
                'document_identity' => $data['document_identity'],
                'document_date' => $data['document_date'],
                'document_detail_id' => $sale_invoice_detail_id,
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
                'document_amount' => (-1 * ($detail['qty'] * $detail['cog_rate'])),
                'currency_conversion' => $detail['conversion_rate'],
                'base_currency_id' => $detail['base_currency_id'],
                'base_rate' => ($detail['cog_rate'] * $detail['conversion_rate']),
                'base_amount' => (-1 * ($detail['qty'] * $detail['cog_rate'] * $detail['conversion_rate'])),
            );

            // Add Stock Ledger //
            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $stock_ledger);


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
                $gl_data[] = array(
                    'document_detail_id' => $sale_invoice_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $gr_ir_account_id,
                    'document_credit' => $detail['cog_amount'],
                    'document_debit' => 0,
                    'credit' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'debit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['cog_amount'],
                    'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'comment' => 'GRIR Account Debit',
                );
                $gl_data[] = array(
                    'document_detail_id' => $sale_invoice_detail_id,
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
                    'comment' => 'COG Account',
                );
            } else {
                // If we are making direct invoice.
                $gl_data[] = array(
                    'document_detail_id' => $sale_invoice_detail_id,
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
                    'document_detail_id' => $sale_invoice_detail_id,
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

            $gl_data[] = array(
                'document_detail_id' => $sale_invoice_detail_id,
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

            if(floatval($detail['discount_amount']) > 0) {
                $gl_data[] = array(
                    'document_detail_id' => $sale_invoice_detail_id,
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
                    'comment' => 'Item Discount Account',
                );
            }

            if(floatval($detail['tax_amount']) > 0) {
                $gl_data[] = array(
                    'document_detail_id' => $sale_invoice_detail_id,
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
                    'comment' => 'Sales Tax Account',
                );
            }
        }

        $partner = $this->model['partner']->getRow(array('partner_type_id' => $data['partner_type_id'], 'partner_id' => $data['partner_id']));
        $outstanding_account_id = $partner['outstanding_account_id'];
        // echo $data['partner_type_id'];
        // d(array($data, $partner, $outstanding_account_id), true);

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
            $ledger['document_id'] = $sale_invoice_id;
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
//            d(array($data,$detail,$gl_data,$ledger),true);
        }


        return $sale_invoice_id;

    }

    protected function updateData($primary_key, $data) {
        // $data['partner_type_id'] = 2;
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['product']= $this->load->model('inventory/product');
        $this->model['setting']= $this->load->model('common/setting');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['sale_invoice_detail'] = $this->load->model('inventory/sale_invoice_detail');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['customer_rate']= $this->load->model('inventory/customer_rate');

        //d(array($primary_key, $data), true);
        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['document_date'] = MySqlDate($data['document_date']);
        $data['base_amount'] = $data['net_amount'] * $data['conversion_rate'];
        $sale_invoice_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        $data['document_id'] = $sale_invoice_id;

        $this->model['sale_invoice_detail']->deleteBulk($this->getAlias(), array('sale_invoice_id' => $sale_invoice_id));
        $this->model['document']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));
        $this->model['ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $sale_invoice_id,
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['net_amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['net_amount'],
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
        foreach ($data['sale_invoice_details'] as $sort_order => $detail) {
            $detail['sale_invoice_id'] = $sale_invoice_id;
            $detail['sort_order'] = $sort_order;
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_total'] = $detail['total_amount'] * $data['conversion_rate'];
            $sale_invoice_detail_id = $this->model['sale_invoice_detail']->add($this->getAlias(), $detail);

            $stock_ledger = array(
                'company_id' => $detail['company_id'],
                'company_branch_id' => $detail['company_branch_id'],
                'fiscal_year_id' => $detail['fiscal_year_id'],
                'document_type_id' => $data['document_type_id'],
                'document_id' => $data['document_id'],
                'document_identity' => $data['document_identity'],
                'document_date' => $data['document_date'],
                'document_detail_id' => $sale_invoice_detail_id,
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
                'document_amount' => (-1 * ($detail['total_cubic_feet'] * $detail['cog_rate'])),
                'currency_conversion' => $detail['conversion_rate'],
                'base_currency_id' => $detail['base_currency_id'],
                'base_rate' => ($detail['cog_rate'] * $detail['conversion_rate']),
                'base_amount' => (-1 * ($detail['total_cubic_feet'] * $detail['cog_rate'] * $detail['conversion_rate'])),
            );
            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $stock_ledger);
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
                $gl_data[] = array(
                    'document_detail_id' => $sale_invoice_detail_id,
                    'ref_document_type_id' => $detail['ref_document_type_id'],
                    'ref_document_id' => $detail['ref_document_id'],
                    'ref_document_identity' => $detail['ref_document_identity'],
                    'coa_id' => $gr_ir_account_id,
                    'document_credit' => $detail['cog_amount'],
                    'document_debit' => 0,
                    'credit' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'debit' => 0,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'document_amount' => $detail['cog_amount'],
                    'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
                    'comment' => 'GRIR Account Debit',
                );
                $gl_data[] = array(
                    'document_detail_id' => $sale_invoice_detail_id,
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
                    'comment' => 'COG Account',
                );
            } else {
                // If we are making direct invoice.
                $gl_data[] = array(
                    'document_detail_id' => $sale_invoice_detail_id,
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
                    'document_detail_id' => $sale_invoice_detail_id,
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

            $gl_data[] = array(
                'document_detail_id' => $sale_invoice_detail_id,
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

            if(floatval($detail['discount_amount']) > 0) {
                $gl_data[] = array(
                    'document_detail_id' => $sale_invoice_detail_id,
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
                    'comment' => 'Item Discount Account',
                );
            }

            if(floatval($detail['tax_amount']) > 0) {
                $gl_data[] = array(
                    'document_detail_id' => $sale_invoice_detail_id,
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
                    'comment' => 'Sales Tax Account',
                );
            }
        }

        $partner = $this->model['partner']->getRow(array('partner_type_id' => $data['partner_type_id'], 'partner_id' => $data['partner_id']));
        $outstanding_account_id = $partner['outstanding_account_id'];
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
        //d($gl_data, true);
        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $sale_invoice_id;
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

        return $sale_invoice_id;
    }

    protected function deleteData($primary_key) {
        $this->model['sale_invoice_detail'] = $this->load->model('inventory/sale_invoice_detail');
        $this->model['sale_invoice_detail']->deleteBulk($this->getAlias(), array('sale_invoice_id' => $primary_key));

        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->deleteBulk($this->getAlias(), array('document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

    public function getReferenceDocumentNos() {
        $sale_invoice_id = $this->request->get['sale_invoice_id'];
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
            'sale_invoice_id' => $sale_invoice_id,
            'post' => $post,
            'where' => $where,
            'orders' => $orders,
            'html' => $html
        );

        echo json_encode($json);
    }

    public function getReferenceDocument() {
        $sale_invoice_id = $this->request->get['sale_invoice_id'];
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
            'sale_invoice_id' => $sale_invoice_id,
            'post' => $post,
            'where' => $where,
            'details' => $details);
        echo json_encode($json);
    }


    public function printDocumentLocalBill() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        $sale_invoice_id = $this->request->get['sale_invoice_id'];
        $with_previous_balance = isset($this->request->get['with_previous_balance'])?1:0;

        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['sale_invoice'] = $this->load->model('inventory/sale_invoice');
        $this->model['sale_invoice_detail'] = $this->load->model('inventory/sale_invoice_detail');



        $invoice = $this->model['sale_invoice']->getRow(array('sale_invoice_id' => $sale_invoice_id));
        $details = $this->model['sale_invoice_detail']->getRows(array('sale_invoice_id' => $sale_invoice_id),array('sort_order asc'));

        $partner = $this->model['partner']->getRow(array('partner_id' => $invoice['partner_id']));
        $outstanding = $this->model['partner']->getOutstanding("l.`partner_id` = '".$invoice['partner_id']."' AND l.`created_at` < '".$invoice['created_at']."'");

        $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');
        $CustomerUnit = $this->model['customer_unit']->getRow(array('customer_unit_id' => $invoice['customer_unit_id']));

        $this->model['aging_report'] = $this->load->model('report/aging_report');
        $filter = array(

            'partner_id' => $invoice['partner_id'],
            'partner_type_id' => $invoice['partner_type_id'],
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
        );
        $AgingRows = $this->model['aging_report']->getAging($filter);




        //d(array($sale_invoice_id, $invoice, $details), true);
        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Sale Invoice Local Bill');
        $pdf->SetSubject('Sale Invoice Local Bill');

        //Set Header
        $pdf->InvoiceCheck = "LocalBill";
        $pdf->data = array(
            'company_name' => $session['company_name'],
            //'report_name' => $lang['heading_title'],
            'report_name' => 'Bill',
         //   'header_image' => HTTP_IMAGE.'header.jpg',
          //  'footer_image' => HTTP_IMAGE.'footer.jpg',
           // 'company_logo' => $session['company_image']
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(15, 40, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
//        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetAutoPageBreak(TRUE, 65);

        // add a page
        $pdf->AddPage();
        // set font


        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell(45, 10, stdDate($invoice['document_date']), 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('times', 'B', 16);
        $pdf->Cell(100, 10, $invoice['document_identity'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->SetFont('times', 'B', 10);
//        $pdf->Cell(50, 15, $invoice['document_identity'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->ln(12);

        $pdf->SetFont('times', 'B', 10);
        $pdf->Cell(0, 5, 'To, ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->ln(5);

        // set font
        $pdf->SetFont('times', 'B', 10);
        $pdf->Cell(140, 7, html_entity_decode($invoice['partner_name']), 0, false, 'L', 0, '', 0, false, 'M', 'M');


//        $pdf->SetFont('times', 'B', 9);
//        $pdf->Cell(30, 5, 'Customer Unit : ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(80, 7, $CustomerUnit['customer_unit'], 0, false, 'L', 0, '', 0, false, 'M', 'M');


        $pdf->ln(12);

        // set font
        $pdf->SetFont('times', 'B', 9);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(10, 7, 'Sr.No', 1, false, 'C', 1, '', 1);
        $pdf->Cell(100,7, 'Particulars', 1, false, 'C', 1, '', 1);
        $pdf->Cell(20, 7, 'Qty', 1, false, 'C', 1, '', 1);
        $pdf->Cell(20, 7, 'Unit', 1, false, 'C', 1, '', 1);
        $pdf->Cell(20, 7, 'Rate', 1, false, 'C', 1, '', 1);
        $pdf->Cell(20, 7, 'Amount', 1, false, 'C', 1, '', 1);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $total_amount = 0;
        $sr = 0;
        $pdf->SetFont('times', '', 9);

        foreach($details as $detail) {
            $sr++;
            $pdf->ln(7);
            $pdf->Cell(10, 7, $sr, 0, false, 'C', 0, '', 1);
            $pdf->Cell(100, 7, html_entity_decode($detail['description']), 0, false, 'L', 0, '', 1);
            $pdf->Cell(20, 7, number_format($detail['qty'],0), 0, false, 'R', 0, '', 1);
            $pdf->Cell(20, 7, $detail['unit'], 0, false, 'C', 0, '', 1);
            $pdf->Cell(20, 7, number_format($detail['rate'],2), 0, false, 'R', 0, '', 1);
            $pdf->Cell(20, 7, number_format($detail['amount'],2), 0, false, 'R', 0, '', 1);

            $total_amount += $detail['amount'];
        }

        $x = $pdf->GetX();
        $y = $pdf->GetY();

        for ($i = $y; $i <= 170; $i++) {

            $pdf->Ln(1);
            $pdf->Cell(10, 7, '', 0, false, 'C', 0, '', 1);
            $pdf->Cell(100, 7, '', 0, false, 'L', 0, '', 1);
            $pdf->Cell(20, 7, '', 0, false, 'R', 0, '', 1);
            $pdf->Cell(20, 7, '', 0, false, 'C', 0, '', 1);
            $pdf->Cell(20, 7, '', 0, false, 'R', 0, '', 1);
            $pdf->Cell(20, 7, '', 0, false, 'R', 0, '', 1);
            $y =$i;
        }
        $pdf->Ln(-1);
        $pdf->Ln(1);
        $pdf->setXY($x,$y);


        $pdf->SetFont('times', 'B', 9);
        $pdf->ln(9);
        $pdf->Cell(60, 7, 'Gross Total :       '.number_format($total_amount,2) , 1, false, 'L');
        $pdf->Cell(60, 7, 'Cartage Amount:       '.number_format($invoice['cartage'],2)  , 1, false, 'C');
        $pdf->Cell(70, 7, 'Net Total PKR:        '.number_format($invoice['net_amount'],2) , 1, false, 'R');
        $pdf->ln(8);
        $pdf->Cell(130, 7, 'Rupees :     ' . Number2Words(round($total_amount,2)). ' only', 0, false, 'L');
//
//        $pdf->ln(8);
//        $pdf->Cell(40, 7, 'Goods Dispatched From :', 0, false, 'L');
//        $pdf->SetFont('times', '', 10);
//        $pdf->Cell(150, 7, $invoice['remarks'], 0, false, 'L');

        $pdf->ln(10);
        $pdf->SetFont('times', 'B', 9);

        $pdf->Cell(20, 7, 'Cargo Name : ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('times', '', 9);
        $pdf->Cell(90, 7, $invoice['billty_remarks'], 0, false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell(16, 7, 'Bilty No : ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('times', '', 9);
        $pdf->Cell(30, 7, $invoice['billty_no'], 0, false, 'L', 0, '', 0, false, 'M', 'M');


        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell(17, 7, 'BiltyDate : ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('times', '', 9);
        $pdf->Cell(30, 7, $invoice['billty_date'], 0, false, 'L', 0, '', 0, false, 'M', 'M');


        $pdf->ln(8);
        $pdf->SetFont('times', 'B', 9);
        $pdf->SetFillColor(215, 215, 215);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(38, 8, 'Amount', 1, false, 'C', 1, '', 1);
        $pdf->Cell(38, 8, '30 Days', 1, false, 'C', 1, '', 1);
        $pdf->Cell(38, 8, '60 Days', 1, false, 'C', 1, '', 1);
        $pdf->Cell(38, 8, '90 Days', 1, false, 'C', 1, '', 1);
        $pdf->Cell(38, 8, 'Above 90 Days', 1, false, 'C', 1, '', 1);
        $pdf->ln(11);


        $total_current = 0;
        $total_30 = 0;
        $total_60 = 0;
        $total_90 = 0;
        $total_Above90 = 0;

        foreach($AgingRows as $AgingRow)
        {

            $total_current += $AgingRow['document_amount'];
            $total_30 += $AgingRow['30_days'];
            $total_60 += $AgingRow['60_days'];
            $total_90 += $AgingRow['90_days'];
            $total_Above90 += $AgingRow['above_90'];
        }

        $pdf->Cell(38, 8, $total_current, 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(38, 8, $total_30, 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(38, 8, $total_60, 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(38, 8, $total_90, 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(38, 8, $total_Above90, 0, false, 'C', 0, '', 0, false, 'M', 'M');


        //Close and output PDF document
        $pdf->Output('Sale Invoice - '.$invoice['document_identity'].'.pdf', 'I');

    }

    public function printDocumentBill() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);


        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
//        d($session,true);
        $sale_invoice_id = $this->request->get['sale_invoice_id'];
        $with_previous_balance = isset($this->request->get['with_previous_balance'])?1:0;

        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['sale_invoice'] = $this->load->model('inventory/sale_invoice');
        $this->model['sale_invoice_detail'] = $this->load->model('inventory/sale_invoice_detail');

        $invoice = $this->model['sale_invoice']->getRow(array('sale_invoice_id' => $sale_invoice_id));
        $details = $this->model['sale_invoice_detail']->getRows(array('sale_invoice_id' => $sale_invoice_id),array('sort_order asc'));
        $partner = $this->model['partner']->getRow(array('partner_id' => $invoice['partner_id']));
        $outstanding = $this->model['partner']->getOutstanding("l.`partner_id` = '".$invoice['partner_id']."' AND l.`created_at` < '".$invoice['created_at']."'");
        //d(array($sale_invoice_id, $invoice, $details), true);

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

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Sale Invoice Bill');
        $pdf->SetSubject('Sale Invoice Bill');


        //$pdf->footer = HTTP_IMAGE.'footer.jpeg';
        //Set Header

//        print_r(HTTP_IMAGE);
//        exit;

        $pdf->InvoiceCheck = "Bill";
        $pdf->data = array(
            'company_name' => $session['company_name'],
            //'report_name' => $lang['heading_title'],
            'report_name' => 'Bill'
            //'company_logo' => $session['company_image'],
            //'header_image' => HTTP_IMAGE.'header.jpg',
            //'footer_image' => HTTP_IMAGE.'footer.jpg'

        );
//        d($pdf->data);


        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(15, 60, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
//        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetAutoPageBreak(TRUE, 65);


        // add a page
        $pdf->AddPage();
        // set font
        $pdf->SetFont('times', 'B', 10);
        $pdf->Cell(10, 7, 'M/S: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');

        // set font
        $pdf->SetFont('times', 'B', 10);
        $pdf->Cell(80, 7, html_entity_decode($invoice['partner_name']), 'B', false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell(20, 7, 'Invoice No: ', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        // set font
        $pdf->SetFont('times', '', 9);
        $pdf->Cell(40, 7, $invoice['document_identity'], 'B', false, 'C', 0, '', 0, false, 'M', 'M');

        $pdf->Cell(10, 7, 'Date: ', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        // set font
        $pdf->SetFont('times', '', 9);
        $pdf->Cell(30, 7, stdDate($invoice['document_date']), 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->ln(8);


        $pdf->SetFont('times', 'B', 10);
        $pdf->Cell(15, 7, 'Address: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // set font
        $pdf->SetFont('times', '', 10);
//        $pdf->Cell(110, 7, $partner['address'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->MultiCell(75, 10, $partner['address'], 'B', '', 0, 2, 30, 67, true);

        $pdf->SetFont('times', 'B', 10);
//        $pdf->Cell(27, 7, 'Unit : ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // set font
        $pdf->SetFont('times', 'B', 10);
//        $pdf->Cell(38, 7, $partner['address'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->MultiCell(100, 10, 'Unit : '. $CustomerUnit['customer_unit']. '  '.$invoice['customer_remarks'], 'B', '', 0, 2, 106, 67, true);



        $pdf->ln(7);
        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell(20, 7, 'Challan No: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('times', '', 9);
        $pdf->Cell(130, 7, $invoice['dc_no'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->Cell(10, 7, 'Date: ', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        // set font
        $pdf->SetFont('times', '', 9);
        $pdf->Cell(30, 7, stdDate($invoice['dc_date']), 'B', false, 'L', 0, '', 0, false, 'M', 'M');


        $pdf->ln(7);

        $pdf->SetFont('times', 'B', 9);
        $pdf->Cell(20, 7, 'Po No: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // set font
        $pdf->SetFont('times', '', 9);
        $pdf->Cell(130, 7, $invoice['po_no'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->Cell(10, 7, 'Date: ', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        // set font
        $pdf->SetFont('times', '', 9);
        $pdf->Cell(30, 7, stdDate($invoice['po_date']), 'B', false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->ln(10);

        // set font
        $pdf->SetFont('times', '', 8);
        $pdf->SetFillColor(215, 215, 215);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(20, 8, 'Qty', 1, false, 'C', 1, '', 1);
        $pdf->Cell(110, 8, 'Particulars', 1, false, 'C', 1, '', 1);
        $pdf->Cell(30, 8, 'Rate', 1, false, 'C', 1, '', 1);
        $pdf->Cell(30, 8, 'Amount', 1, false, 'C', 1, '', 1);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $total_amount = 0;
        foreach($details as $detail) {
            $pdf->ln(7);
            $pdf->Cell(20, 7, number_format($detail['qty'],0), 'L', false, 'C', 0, '', 1);
            $pdf->Cell(110, 7, html_entity_decode($detail['description']), 'L', false, 'L', 0, '', 1);
            $pdf->Cell(30, 7, number_format($detail['rate'],2), 'L', false, 'R', 0, '', 1);
            $pdf->Cell(30, 7, number_format($detail['amount'],2), 'L,R', false, 'R', 0, '', 1);

            $total_amount += $detail['amount'];
        }

        $x = $pdf->GetX();
        $y = $pdf->GetY();

        for ($i = $y; $i <= 203; $i++) {

            $pdf->Ln(1);
            $pdf->Cell(20, 7, '', 'L', false, 'R', 0, '', 1);
            $pdf->Cell(110, 7, '', 'L', false, 'L', 0, '', 1);
            $pdf->Cell(30, 7, '', 'L', false, 'R', 0, '', 1);
            $pdf->Cell(30, 7, '', 'L,R', false, 'R', 0, '', 1);
            $y =$i;
        }
        $pdf->Ln(-3);
        $pdf->Ln(7);
        $pdf->Cell(190, 7, '', 'B', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setXY($x,$y);
        $pdf->Ln(9);


        $pdf->SetFont('times', 'B', 8);
        $pdf->Cell(130,6, 'In words: ' . Number2Words(round($invoice['net_amount'],2)). ' only', 0, false, 'L');
        $pdf->Cell(30, 6, $lang['total_amount'].': ', 1, false, 'L');
        $pdf->Cell(30, 6, number_format($total_amount,2), 1, false, 'R');
        $pdf->Ln(6);
        $pdf->Cell(130,6, '', 0, false, 'L');
        $pdf->Cell(30, 6, 'Cartage Amount:', 1, false, 'L');
        $pdf->Cell(30, 6, number_format($invoice['cartage'],2), 1, false, 'R');
        $pdf->Ln(6);
        $pdf->Cell(130,6, '', 0, false, 'L');
        $pdf->Cell(30, 6, 'Total:', 1, false, 'L');
        $pdf->Cell(30, 6, number_format($invoice['net_amount'],2), 1, false, 'R');


        //Close and output PDF document
        $pdf->Output('Sale Invoice - '.$invoice['document_identity'].'.pdf', 'I');

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

        if($this->InvoiceCheck == "Bill")
        {
//            $txt="TEL: 92-21-32401236 92-21-32415063
//        FAX: 92-21-32428040";
//
//            $this->Ln(6);
            $this->SetFont('helvetica', 'B', 30);
//            $this->Ln(2);
//            // Title
            $this->Cell(190, 12, html_entity_decode($this->data['company_name']), 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Ln(12);
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

//            $this->Image($this->data['header_image'], 0, 5, 205, "", "JPG", "", "T", false, 300, "", false, false, 0, false, false, false);
//            $this->Ln(40);
            $this->SetFont('helvetica', 'B,I', 20);
            $this->Cell(0, 10, "Bill", 0, false, 'C', 0, '', 0, false, 'M', 'M');
//            $this->Ln(7);


        }
        if($this->InvoiceCheck == "LocalBill")
        {
            $txt="TEL: 92-21-32401236/92-21-32415063 FAX: 92-21-32428040
Email : info@hacsons.com";

            $address="Shop # 21,Adnan Centre Jeswani Street, off. Aiwan-E-Tijarat Road,
Karachi - 74000";

//            $txt="";
//            $address = "";
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
        $this->SetY(-55);
        // Set font
        $this->SetFont('times', 'B,I', 10);
        $this->Cell(100, 5, "Goods once sold can not be taken back of exchanged", 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->SetFont('times', 'B', 12);
        $this->Cell(80, 5, 'For '.html_entity_decode($this->data['company_name']), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $this->Ln(5);
        if($this->InvoiceCheck == "Bill")
        {
           // $this->Image($this->data['footer_image'], 0, 250, 205, "", "JPG", "", "T", false, 300, "", false, false, 0, false, false, false);
        }
//        $this->SetFont('helvetica', 'I', 8);
//        // Page number
//        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
?>