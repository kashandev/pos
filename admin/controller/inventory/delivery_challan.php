<?php
class ControllerInventoryDeliveryChallan extends HController {
    protected $document_type_id = 16;

    protected function getAlias() {
        return 'inventory/delivery_challan';
    }

    protected function getPrimaryKey() {
        return 'delivery_challan_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {
        $lang = $this->load->language('inventory/delivery_challan');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action', 'document_date', 'document_identity', 'partner_name','challan_type', 'total_qty', 'challan_status','invoice_status', 'created_at', 'check_box');

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
        // d($results, true);


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
//                $actions[] = array(
//                    'text' => $lang['post'],
//                    'href' => $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
//                    'btn_class' => 'btn btn-info btn-xs',
//                    'class' => 'fa fa-thumbs-up'
//                );

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
                } elseif ($aColumns[$i] == 'total_qty') {
                    $row[] = number_format($aRow['total_qty'],2);
                } elseif ($aColumns[$i] == 'created_at') {
                    $row[] = stdDateTime($aRow['created_at']);
                }  elseif ($aColumns[$i] == 'challan_status') {
                    $html = '';
                    if($aRow['challan_status'] == 'Cleared')
                    {
                        $html .= '<label class="btn btn-success btn-xs" style="margin:1px;">Cleared</label>';
                    }else{
                        $html .= '<label class="btn btn-danger btn-xs" style="margin:1px;">Pending</label>';
                    }
                    $row[] = $html;
                }  elseif ($aColumns[$i] == 'invoice_status') {
                    $html = '';
                    if($aRow['sale_qty'] > 0 || $aRow['sale_tax_qty'] > 0 || $aRow['return_qty'] > 0)
                    {
                        $html .= '<label class="btn btn-success btn-xs" style="margin:1px;">Invoice</label>';
                    }else{
                        $html .= '';
                    }
                    $row[] = $html;
                }
                elseif ($aColumns[$i] == 'check_box') {
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

        //d($_SERVER,true);

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

        $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');
        $this->data['customer_units'] = $this->model['customer_unit']->getRows(array('company_id' => $this->session->data['company_id']),array('customer_unit'));

        // $this->model['customer'] = $this->load->model('setup/customer');
        // $this->data['customers'] = $this->model['customer']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['salesman'] = $this->load->model('setup/salesman');
        $this->data['salesman'] = $this->model['salesman']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));

        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencys'] = $this->model['currency']->getRows();

        $this->model['unit'] = $this->load->model('inventory/unit');
        $this->data['units'] = $this->model['unit']->getRows(array('company_id' => $this->session->data['company_id']));
        $arrUnits = $this->model['unit']->getArrays('unit_id','name',array('company_id' => $this->session->data['company_id']));

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));
        $this->data['arrWarehouses'] = json_encode($this->data['warehouses']);

        $this->data['href_get_ref_document_no'] = $this->url->link($this->getAlias() . '/getReferenceDocumentNos', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_ref_document'] = $this->url->link($this->getAlias() . '/getReferenceDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

//        $this->data['href_get_sale_order'] =  $this->url->link($this->getAlias() . '/getData', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_sale_order'] =  $this->url->link($this->getAlias() . '/getReferenceDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";

//        $this->data['partner_types'] = $this->session->data['partner_types'];

        $this->data['validEdit'] = 1;
        $this->data['partner_type_id'] = 2;
        $this->data['document_date'] = stdDate();
        if (isset($this->request->get['delivery_challan_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                if ($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                }elseif ($field == 'po_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }

            $validateEdit = $this->model[$this->getAlias()]->validateEdit($result['delivery_challan_id']);
            // d($validateEdit);
            if($validateEdit['sale_qty'] > 0 || $validateEdit['sale_tax_qty'] > 0)
            {
                // d('in if');
                $this->data['validEdit'] = 0;
            }
            else
            {
                $this->data['validEdit'] = 1;
            }
            // d($this->data['validEdit'],true);
            $this->model['product'] = $this->load->model('inventory/product');
            $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']),array('name'));

            $this->model['delivery_challan_detail'] = $this->load->model('inventory/delivery_challan_detail');
            $rows = $this->model['delivery_challan_detail']->getRows(array('delivery_challan_id' => $this->request->get['delivery_challan_id']),array('sort_order asc'));
            // d($result, true);
            // d(   $rows,true);
            foreach($rows as $row_no => $row) {

                // $this->data['delivery_challan_details'][$row_no]['description'] = htmlentities($row['description']);
                $this->data['delivery_challan_details'][$row_no] = $row;
                $this->data['delivery_challan_details'][$row_no]['href'] = $this->url->link('inventory/sale_order1/update', 'token=' . $this->session->data['token'] . '&sale_order_id=' .$row['ref_document_id'], 'SSL');
//                $this->data['delivery_challan_details'][$row_no]['unit_id'] = $arrUnits[$row['unit_id']];
            }
// d($this->data['delivery_challan_details'],true);
        }

        $this->data['restrict_out_of_stock'] = $this->session->data['restrict_out_of_stock'];
        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print'] = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_customer'] = $this->url->link($this->getAlias() . '/getCustomer', 'token=' . $this->session->data['token']);
        $this->data['href_get_product_json'] = $this->url->link($this->getAlias() . '/getProductJson', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_get_excel_figures'] = $this->url->link($this->getAlias() . '/ExcelFigures', 'token=' . $this->session->data['token']. '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_partner_json'] = $this->url->link($this->getAlias() . '/getPartnerJson', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['strValidation'] = "{
            'rules': {
                'document_date': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'partner_id': {'required': true},
                'total_qty': {'required': true, 'min':0.01},
            },
            'ignore': [],
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

    public function getProductJson() {
        $search = $this->request->post['q'];
        $page = $this->request->post['page'];

        $this->model['product'] = $this->load->model('inventory/product');
        $rows = $this->model['product']->getProductJson($search, $page);

        echo json_encode($rows);
    }

    public function getCustomer() {

        $post = $this->request->post;

        $challan_type='';
//        d($post,true);
        $partner_id = $post['partner_id'];

        $this->model['customer'] = $this->load->model('setup/customer');

        $where = 'company_branch_id = ' . $this->session->data['company_branch_id'];

        $partners = $this->model['customer']->getRows($where,array('name'));
        // d($partners, true);
        
        $html = '<option value="">&nbsp;</option>';
        // $arrPartners = array();
        foreach($partners as $partner) {
            if($partner['customer_id'] == $partner_id) {
                $html .= '<option value="'.$partner['customer_id'].'" selected="true">'.$partner['name'].'</option>';
            } else {
                $html .= '<option value="'.$partner['customer_id'].'">'.$partner['name'].'</option>';
            }
            // $arrPartners[$partner['partner_id']]= $partner;
        }
        // d($arrPartners, true);

        // d($html, true);

        $json = array(
            'success' => true,
            'html' => $html,
            'partners' => $partners
        );

        $this->response->setOutput(json_encode($json));
    }


    public function getReferenceDocumentNos() {
        $delivery_challan_id = $this->request->get['delivery_challan_id'];
        $post = $this->request->post;
//        d(array($sale_order_id, $post), true);

        //Purchase Order
        $this->model['sale_order'] = $this->load->model('inventory/sale_order');
        $where = "company_id=" . $this->session->data['company_id'];
        $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
        $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
        $where .= " AND partner_type_id='" . 2 . "'";
        $where .= " AND partner_id='" . $post['partner_id'] . "'";
//        $where .= " AND is_post=1";

        $sale_orders = $this->model['sale_order']->getSaleOrders($where,$delivery_challan_id);
       // d($sale_orders, true);
        foreach($sale_orders as $sale_order_id => $sale_order) {
            foreach($sale_order['products'] as $product_id => $product) {
                if($product['order_qty'] <= $product['utilized_qty']) {
                    unset($sale_order['products'][$product_id]);
                }
            }
            if(empty($sale_order['products'])) {
                unset($sale_orders[$sale_order_id]);
            }
        }

        $html = "";
        if(count($sale_orders) != 1) {
//            $html .= '<option value="">&nbsp;</option>';
        }
        $html .= '<option value="">&nbsp;</option>';
        // d($sale_orders);
        foreach($sale_orders as $sale_order_id => $sale_order) {
            if($sale_order['document_identity'] == $post['ref_document_identity']) {
                $html .= '<option value="'.$sale_order_id.'" selected="true">'.$sale_order['document_identity'].'</option>';
            } else {
                $html .= '<option value="'.$sale_order_id.'">'.$sale_order['document_identity'].'</option>';
            }
        }

//        d($sale_order,true);
        $json = array(
            'success' => true,
            'sale_order_id' => $sale_order_id,
            'post' => $post,
            'where' => $where,
            'html' => $html
        );
        // d($json,true);

        echo json_encode($json);
    }


//    public function getReferenceDocumentNos() {
//        $sale_order_id = $this->request->get['sale_order_id'];
//        $post = $this->request->post;
//       //d($post,true);
//        //d($sale_order_id, true);
//
//        //Purchase Order
//        $this->model['sale_order'] = $this->load->model('inventory/sale_order');
//        $where = "company_id=" . $this->session->data['company_id'];
//        $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
//        $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
//        $where .= " AND partner_type_id='" . 2 . "'";
//        $where .= " AND partner_id='" . $post['partner_id'] . "'";
////        $where .= " AND is_post=1";
//
//        $sale_orders = $this->model['sale_order']->getRows($where);
//      //d($sale_orders, true);
//        /*foreach($sale_orders as $sale_order_id => $sale_order) {
//
//            foreach($sale_order['products'] as $sale_order => $product) {
//                if($product['order_qty'] <= $product['utilized_qty']) {
//                    unset($sale_order['products'][$product_id]);
//                }
//            }
//            if(empty($quotation['products'])) {
//                unset($quotations[$quotation_id]);
//            }
//        }
//        */
//
//        $html = "";
//
//        $html .= '<option value="">&nbsp;</option>';
//        foreach($sale_orders as  $sale_order) {
//                $html .= '<option value="'.$sale_order['document_identity'].'">'.$sale_order['document_identity'].'</option>';
//
//        }
//       // d($sale_orders,true);
//
////        d($quotation,true);
//        $json = array(
//            'success' => true,
//            'sale_order_id' => $sale_order_id,
//            'post' => $post,
//            'where' => $where,
//            'html' => $html
//        );
//
//        echo json_encode($json);
//    }

    public function getReferenceDocument() {
        //Acive
        $sale_order_id = $this->request->get['sale_order_id'];
        $post = $this->request->post;
        $this->model['stock'] = $this->load->model('common/stock_ledger');
        //sale_order
        $this->model['sale_order'] = $this->load->model('inventory/sale_order');
        $where = "company_id=" . $this->session->data['company_id'];
        $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
        $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
        $where .= " AND partner_id='" . $post['partner_id'] . "'";
        $where .= " AND document_identity='" . $post['ref_document_identity'] . "'";


        $sale_orders = $this->model['sale_order']->getSaleOrders($where,$sale_order_id);


        $sale_order = $sale_orders[$post['ref_document_identity']];
        // d(array($where,$sale_order),true);

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $warehouses = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));


        $details = array();
        $row_no = 0;
        $quantity_total  = 0;
        $Cog_total  = 0;


        $html = '';
        $MasterData = $this->model['sale_order']->getRow(array('document_identity' => $post['ref_document_identity']));
        // d($MasterData,true);

        if($MasterData['po_date'] != null || $MasterData['po_date']  != '')
        {
            $po_date = stdDate($MasterData['po_date']);
        }else{
            $po_date = null;
        }
        $po_no = $MasterData['po_no'];
        $customer_unit = $MasterData['customer_unit_id'];
        $salesman_id = $MasterData['salesman_id'];


    //    d($sale_order,true);
        foreach($sale_order['products'] as $product) {
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'product_id' => $product['product_id'],
        );
        $product['stock'] = $this->model['stock']->getStock($filter);
        // d($product,true);
            if($product['order_qty'] - $product['utilized_qty'] > 0)
            {

//                d($product,true);
//                $href = $this->url->link('inventory/purchase_order/update', 'token=' . $this->session->data['token'] . '&purchase_order_id=' . $sale_order['purchase_order_id']);
//                $details[$row_no] = $product;
//                $details[$row_no]['ref_document_identity'] = $sale_order['document_identity'];
//                $details[$row_no]['row_identity'] = $sale_order['document_identity'].'-'.$product['product_code'];
//                $details[$row_no]['href'] = $href;
//                $details[$row_no]['balanced_qty'] = ($product['order_qty'] - $product['utilized_qty']);
//                $details[$row_no]['utilized_qty'] = ($product['order_qty'] - $product['utilized_qty']);

//                d($product,true);

                $balanceQty = $product['order_qty'] - $product['utilized_qty'];

                $quantity_total += $balanceQty;
                $Cog_total = $balanceQty * $product['stock']['avg_stock_rate'];
                $html .= '<tr id="grid_row_'.$row_no.'" data-row_id="'.$row_no.'">';

                $html .= '<td>';
                // $html .= '<a title="Remove" class="btnRemoveGrid btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-helvetica"></i></a>';
                $html .= '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
                $html .= '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';

                $html .= '<td>';
                $html .= '<input type="text" class="form-control" id="delivery_challan_detail_ref_document_identity_'.$row_no.'" name="delivery_challan_details['.$row_no.'][ref_document_identity]" value="'.$sale_order['document_identity'].'" readonly/>
                        <input type="hidden" value="'.$MasterData['sale_order_id'].'" name="delivery_challan_details['.$row_no.'][ref_document_id]" />';
                $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail_ref_document_type_id_'.$row_no.'" name="delivery_challan_details['.$row_no.'][ref_document_type_id]" value="'.$sale_order['document_type_id'].'" />';
                $html .= '</td>';

                $html .= '<td>';
                $html .= '<input type="text" class="form-control" id="delivery_challan_detail_product_code_'.$row_no.'" name="delivery_challan_details['.$row_no.'][product_code]" value="'.$product['product_code'].'" readonly />';
                // $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail'.$row_no.'_product_code" name="delivery_challan['.$row_no.'][product_code]" value="'.$sale_order['product_code'].'" readonly />';
                $html .= '</td>';
                $html .= '<td>';
                $html .= '<select class="form-control" id="delivery_challan_detail_product_id_'.$row_no.'" name="delivery_challan_details['.$row_no.'][product_id]" >';
                $html .= '<option value="'.htmlentities($product['product_id']).'">'.$product['product_name'].'</option';
                $html .= '</select>';
                $html .= '</td>';
                // $html .= '<td>';
                // $html .= '<input type="text" class="form-control" id="delivery_challan_details'.$row_no.'_product_id" name="delivery_challan_details['.$row_no.'][product_id]" value="'.htmlentities($product['product_id']).'" readonly >';
                // $html .= '</td>';

                $html .= '<td>';
                $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail_document_identity_'.$row_no.'" name="delivery_challan_details['.$row_no.'][cog_amount]" value="'.$product['cog_amount'].'" />';
                $html .= '<input type="text" class="form-control" id="delivery_challan_detail_description_'.$row_no.'" name="delivery_challan_details['.$row_no.'][description]" value="'.htmlentities($product['description']).'" >';
                $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail_so_'.$row_no.'" value="1" ></td>';
                $html .= '<td>';
                $html .= '<input type="text" width="350px" class="form-control" id="delivery_challan_detail_remarks_'.$row_no.'" name="delivery_challan_details['.$row_no.'][remarks]"  value="'.htmlentities($sale_order['remarks']).'" />';
                $html .= '</td>';
                $html .= '<td>';
                $html .= '<select type="text" onchange="getWarehouseStock(this);" class="form-control" id="delivery_challan_detail_warehouse_id_'.$row_no.'" name="delivery_challan_details['.$row_no.'][warehouse_id]" >';
                $html .= ' <option value="">&nbsp;</option>';
                foreach($warehouses as $warehouse){
                    $html .= '<option value="'.$warehouse['warehouse_id'].'">'.$warehouse['name'].'</option>';
                }
                $html .= '</select>';
                $html .= '</td>';
                $html .= '<td><input type="text" class="form-control" id="delivery_challan_detail_stock_qty_'.$row_no.'" name="delivery_challan_details['.$row_no.'][stock_qty]" value="'.$product['stock']['stock_qty'].'" readonly>';
                $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail_available_stock_'.$row_no.'" name="delivery_challan_details['.$row_no.'][available_stock]" value="'.$product['stock']['stock_qty'].'" readonly></td>';
                $html .= '<td><input type="text" onchange="calculateRowTotal(this)" class="form-control fPDecimal" id="delivery_challan_detail_qty_'.$row_no.'" name="delivery_challan_details['.$row_no.'][qty]" value="'.$balanceQty.'" ></td>';
                $html .= '<td><input type="text" class="form-control" id="delivery_challan_detail_unit_'.$row_no.'" name="delivery_challan_details['.$row_no.'][unit]" value="'.$product['unit'].'" >';
                $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail_unit_id_'.$row_no.'" name="delivery_challan_details['.$row_no.'][unit_id]" value="'.$product['unit_id'].'" readonly></td>';
                $html .= '<td>';
                $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail_cog_rate_'.$row_no.'" name="delivery_challan_details['.$row_no.'][cog_rate]" value="'.$product['rate'].'" readonly>';
                $html .= '<input onchange="calculateTotal(this);" type="text" class="form-control" id="delivery_challan_detail_rate_'.$row_no.'" name="delivery_challan_details['.$row_no.'][rate]" value="'.$product['rate'].'">';
                $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail_cog_amount_'.$row_no.'" name="delivery_challan_details['.$row_no.'][cog_amount]" value="'.$Cog_total.'" readonly>';
                $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail'.$row_no.'_tax_percent" name="delivery_challan_details['.$row_no.'][tax_percent]" value="'.$product['tax_percent'].'" readonly>';
                $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail'.$row_no.'_tax_amount" name="delivery_challan_details['.$row_no.'][tax_amount]" value="'.$product['tax_amount'].'" readonly>';
                $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail'.$row_no.'_net_amount" name="delivery_challan_details['.$row_no.'][net_amount]" value="'.$product['net_amount'].'" readonly></td>';
                $html .= '<td>';
                $html .= '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
                $html .= '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
                $html .= '</tr>';

                $row_no++;
            }
        }

        $purchase_order['products'] = $details;
        $json = array(
            'success' => true,
            'goods_received_id' => $sale_order_id,
            'post' => $post,
            'html' => $html,
            'po_date' => $po_date,
            'po_no' => $po_no,
            'salesman_id' => $salesman_id,
            'customer_unit_id' => $customer_unit,
            'qty_total'=> $quantity_total

//            'data' => $sale_order_order,
        );
        //d($json,true);
        echo json_encode($json);
    }


    public function getData(){

        $post = $this->request->post;
        $this->model['sale_order_detail'] = $this->load->model('inventory/sale_order_detail');
        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        //$this->model['employee_registration'] = $this->load->model('employee/employee_registration');
        $this->model['stock'] = $this->load->model('common/stock_ledger');

        $id = 0;
        $html = '';
        $filter['company_id'] = $this->session->data['company_id'];
        $filter['document_identity'] = $post['ref_document_id'];




        $sale_orders = $this->model['sale_order_detail']->getRows($filter);
        $warehouses = $this->model['warehouse']->getRows();

        $this->model['sale_order'] = $this->load->model('inventory/sale_order');
        $SaleOrder = $this->model['sale_order']->getRow($filter);

        //d($SaleOrder,true);

        if($SaleOrder['po_date'] != null || $SaleOrder['po_date']  != '00:00:00')
        {
            $po_date = stdDate($SaleOrder['po_date']);
        }else{
            $po_date = null;
        }
        $po_no = $SaleOrder['po_no'];
        $customer_unit = $SaleOrder['customer_unit_id'];
   
//d(array($po_date,$po_no,$customer_unit),true);
        // d($filter,true);
//        d($sale_orders,true);

        $quantity_total  = 0;
        $Cog_total  = 0;


        foreach($sale_orders as $sale_order){
            $filter = array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $this->session->data['company_branch_id'],
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'product_id' => $sale_order['product_id'],
            );
            $product['stock'] = $this->model['stock']->getStock($filter);
            $id++;
            $quantity_total += $sale_order['qty'];
            $Cog_total = $sale_order['qty'] * $sale_order['rate'];
            $html .= '<tr id="grid_row_'.$id.'" data-row_id="'.$id.'">';

            $html .= '<td>';
            $html .= '<a title="Remove" class="btnRemoveGrid btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-helvetica"></i></a>';
            $html .= '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>';

            $html .= '<td>';
            $html .= '<input type="text" class="form-control" id="delivery_challan_detail'.$id.'_document_identity" name="delivery_challan_details['.$id.'][document_identity]" value="'.$sale_order['document_identity'].'" readonly/>';
            $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail'.$id.'_document_identity" name="delivery_challan_details['.$id.'][document_identity]" value="'.$sale_order['document_identity'].'" />';
            $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail'.$id.'_document_type_id" name="delivery_challan_details['.$id.'][document_type_id]" value="'.$sale_order['document_type_id'].'" />';
            $html .= '</td>';

            $html .= '<td>';
            $html .= '<input type="text" class="form-control" id="delivery_challan_detail'.$id.'_product_code" name="delivery_challan_details['.$id.'][product_code]" value="'.$sale_order['product_code'].'" readonly />';
            $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail'.$id.'_product_id" name="delivery_challan_detials['.$id.'][product_id]" value="'.$sale_order['product_id'].'" readonly />';
            $html .= '</td>';
            $html .= '<td>';
            $html .= '<input class="form-control hide" id="delivery_challan_detail'.$id.'_product_id" name="delivery_challan_details['.$id.'][product_id]" value="'.$sale_order['product_id'].'">';

            $html .= '<input type="text" class="form-control" id="delivery_challan_detail'.$id.'_product_name" name="delivery_challan_details['.$id.'][product_name]" value="'.htmlentities($sale_order['product_name']).'" readonly>';
            $html .= '</td>';

            $html .= '<td>';
            $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail'.$id.'_document_identity" name="delivery_challan_details['.$id.'][cog_amount]" value="'.$sale_order['cog_amount'].'" />';

            $html .= '<input type="text" class="form-control" id="delivery_challan_detail'.$id.'_description" name="delivery_challan_details['.$id.'][description]" value="'.htmlentities($sale_order['description']).'" readonly></td>';


            $html .= '<td>';
            $html .= '<td>';
            $html .= '<input type="text" width="350px" class="form-control" name="delivery_challan_details['.$id.'][remarks]" id="delivery_challan_detail'.$id.'_remarks" value="'.htmlentities($sale_order['remarks']).'" />';
            $html .= '</td>';

            $html .= '<select type="text" class="form-control" id="delivery_challan_details'.$id.'_warehouse_id" name="delivery_challan_details['.$id.'][warehouse_id]" >';
            $html .= ' <option value="">&nbsp;</option>';
            foreach($warehouses as $warehouse){
                $html .= '<option value="'.$warehouse['warehouse_id'].'">'.$warehouse['name'].'</option>';
            }
            $html .= '</select>';
            $html .= '</td>';
            $html .= '<td><input type="text" class="form-control" id="delivery_challan_detail_stock_qty_'.$id.'" name="delivery_challan_details['.$id.'][stock_qty]" value="'.$product['stock']['stock_qty'].'" readonly></td>';
            $html .= '<td><input type="text" onchange="calculateRowTotal(this)" class="form-control" id="delivery_challan_detail_qty_'.$id.'" name="delivery_challan_details['.$id.'][qty]" value="'.$sale_order['qty'].'" ></td>';
            $html .= '<td><input type="text" class="form-control" id="delivery_challan_detail'.$id.'_unit" name="delivery_challan_details['.$id.'][unit]" value="'.$sale_order['unit'].'" >';
            $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail'.$id.'_unit_id" name="delivery_challan_details['.$id.'][unit_id]" value="'.$sale_order['unit_id'].'" readonly></td>';
            $html .= '<td>';
            $html .= '<input type="text" class="form-control" id="delivery_challan_detail_cog_rate_'.$id.'" name="delivery_challan_details['.$id.'][cog_rate]" value="'.$sale_order['rate'].'" readonly>';
            $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail_cog_amount_'.$id.'" name="delivery_challan_details['.$id.'][cog_amount]" value="'.$Cog_total.'" readonly>';
            $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail'.$id.'_tax_percent" name="delivery_challan_details['.$id.'][tax_percent]" value="'.$sale_order['tax_percent'].'" readonly>';
            $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail'.$id.'_tax_amount" name="delivery_challan_details['.$id.'][tax_amount]" value="'.$sale_order['tax_amount'].'" readonly>';
            $html .= '<input type="hidden" class="form-control" id="delivery_challan_detail'.$id.'_net_amount" name="delivery_challan_details['.$id.'][net_amount]" value="'.$sale_order['net_amount'].'" readonly></td>';
            $html .= '<td>';
            $html .= '<a title="Remove" class="btnRemoveGrid btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-helvetica"></i></a>';
            $html .= '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>';
            $html .= '</tr>';
        }
        // }

        // else{
        //   $html .= '';
        //}

        $json = array(
            'success' => true,
            'sale_orders' => $sale_orders,
            'html' => $html,
            'po_date' => $po_date,
            'po_no' => $po_no,
            'customer_unit_id' => $customer_unit,
            'qty_total'=> $quantity_total
        );
//d($json,true);


        echo json_encode($json);
    }



    protected function insertData($data) {
        // d($data,true);
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

        $this->model['customer_rate']= $this->load->model('inventory/customer_rate');

        if($data['po_date'] != '') {
            $data['po_date'] = MySqlDate($data['po_date']);
        } else {
            $data['po_date'] = NULL;
        }

        $data['base_amount'] = $data['total_amount'] * $data['conversion_rate'];
        $data['partner_type_id'] = 2;

        $delivery_challan_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $data['document_id'] = $delivery_challan_id;

        $this->model['delivery_challan_detail'] = $this->load->model('inventory/delivery_challan_detail');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');


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
        //d($insert_document,true);
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);
        
        $gl_data = array();
        $stock_ledger = array();
        foreach ($data['delivery_challan_details'] as $sort_order => $detail) {
            // d($detail,true);
            $this->model['stock'] = $this->load->model('common/stock_ledger');
            $stock = $this->model['stock']->getWarehouseStock($detail['product_id'], $detail['warehouse_id'], $data['document_identity'], $data['document_date']);

            $detail['delivery_challan_id'] = $delivery_challan_id;
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];

            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['sort_order'] = $sort_order;
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_cog_rate'] = ($detail['cog_rate'] * $data['conversion_rate']);
            $detail['base_cog_amount'] = ($detail['cog_amount'] * $data['conversion_rate']);


            $delivery_challan_detail_id=$this->model['delivery_challan_detail']->add($this->getAlias(), $detail);
            // d($this->model['delivery_challan_detail']->add($this->getAlias(), $detail),true);

            $product = $this->model['product']->getRow(array('product_id' => $detail['product_id']));
            $gl_data[] = array(
                'document_detail_id' => $delivery_challan_detail_id,
                'ref_document_type_id' => $detail['ref_document_type_id'],
                'ref_document_identity' => $detail['ref_document_identity'],
                'coa_id' => $product['inventory_account_id'],
                'document_credit' => $detail['cog_amount'],
                'document_debit' => 0,
                'credit' => ($detail['cog_amount'] * $data['conversion_rate']),
                'debit' => 0,
                'remarks' => $data['remarks'],
                'product_id' => $detail['product_id'],
                'qty' => $detail['qty'],
                'document_amount' => $detail['cog_amount'],
                'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
            );
            $gl_data[] = array(
                'document_detail_id' => $delivery_challan_detail_id,
                'ref_document_type_id' => $detail['ref_document_type_id'],
                'ref_document_identity' => $detail['ref_document_identity'],
                'coa_id' => $product['cogs_account_id'],
                'document_debit' => $detail['cog_amount'],
                'document_credit' => 0,
                'debit' => ($detail['cog_amount'] * $data['conversion_rate']),
                'credit' => 0,
                'remarks' => $data['remarks'],
                'manual_ref_no' => $data['manual_ref_no'],
                //zee
            );

            $stock_ledger[] = array(
                'document_detail_id' => $delivery_challan_detail_id,
                'warehouse_id' => $detail['warehouse_id'],
                'product_id' => $detail['product_id'],
                'document_unit_id' => $detail['unit_id'],
                'document_qty' => $detail['qty'],
                'unit_conversion' => 1,
                'base_unit_id' => $detail['unit_id'],
                'base_qty' => (-1 * $detail['qty']),
                'document_rate' => $stock['avg_stock_rate'],
                'document_amount' => (-1 * ($detail['qty'] * $stock['avg_stock_rate'])),
                'base_rate' => ($stock['avg_stock_rate'] * $detail['conversion_rate']),
                'base_amount' => (-1 * ($detail['qty'] * $stock['avg_stock_rate'] * $detail['conversion_rate'])),
                'remarks' => $data['remarks'],
            );
        }



        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $data['document_id'];
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

        foreach($stock_ledger as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $data['document_id'];
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['sort_order'] = $sort_order;
            $ledger['base_currency_id'] = $data['base_currency_id'];
            $ledger['document_currency_id'] = $data['document_currency_id'];
            $ledger['conversion_rate'] = $data['conversion_rate'];
            $ledger['partner_type_id'] = $data['partner_type_id'];
            $ledger['partner_id'] = $data['partner_id'];

            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $ledger);
        }

        return $delivery_challan_id;
    }

    protected function insertRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias().'/update', 'token=' . $this->session->data['token'] . '&delivery_challan_id=' . $id, 'SSL'));
    }

    protected function updateData($primary_key, $data) {
        $data['partner_type_id'] = 2;
        // d($data,true);
        $delivery_challan_id = $primary_key;
        $data['document_date'] = MySqlDate($data['document_date']);

        if($data['po_date'] != '') {
            $data['po_date'] = MySqlDate($data['po_date']);
        } else {
            $data['po_date'] = NULL;
        }


        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
        $this->model['delivery_challan_detail'] = $this->load->model('inventory/delivery_challan_detail');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['product'] = $this->load->model('inventory/product');
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['stock_ledger'] = $this->load->model('common/stock_ledger');

        $this->model['delivery_challan']->edit($this->getAlias(), $primary_key, $data);
        $this->model['delivery_challan_detail']->deleteBulk($this->getAlias(), array('delivery_challan_id' => $delivery_challan_id));
        $this->model['document']->deleteBulk($this->getAlias(), array('document_id' => $delivery_challan_id));
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_id' => $delivery_challan_id));
        $this->model['stock_ledger']->deleteBulk($this->getAlias(), array('document_id' => $delivery_challan_id));

        $this->model['document'] = $this->load->model('common/document');
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
            'base_amount' => $data['total_amount'] * $data['conversion_rate'],
        );
        
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);
        
        $gl_data = array();
        $stock_ledger = array();
        foreach ($data['delivery_challan_details'] as $sort_order => $detail) {

            $this->model['stock'] = $this->load->model('common/stock_ledger');
            $stock = $this->model['stock']->getWarehouseStock($detail['product_id'], $detail['warehouse_id'], $data['document_identity'], $data['document_date']);

            
            $detail['delivery_challan_id'] = $delivery_challan_id;
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['sort_order'] = $sort_order;
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_cog_rate'] = ($detail['cog_rate'] * $data['conversion_rate']);
            $detail['base_cog_amount'] = ($detail['cog_amount'] * $data['conversion_rate']);
            $delivery_challan_detail_id=$this->model['delivery_challan_detail']->add($this->getAlias(), $detail);
            $product = $this->model['product']->getRow(array('product_id' => $detail['product_id']));
            $gl_data[] = array(
                'document_detail_id' => $delivery_challan_detail_id,
                'ref_document_type_id' => $detail['ref_document_type_id'],
                'ref_document_identity' => $detail['ref_document_identity'],
                'coa_id' => $product['inventory_account_id'],
                'document_credit' => $detail['cog_amount'],
                'document_debit' => 0,
                'credit' => ($detail['cog_amount'] * $data['conversion_rate']),
                'debit' => 0,
                'remarks' => $data['remarks'],
                'product_id' => $detail['product_id'],
                'qty' => $detail['qty'],
                'document_amount' => $detail['cog_amount'],
                'amount' => ($detail['cog_amount'] * $data['conversion_rate']),
            );
            $gl_data[] = array(
                'document_detail_id' => $delivery_challan_detail_id,
                'ref_document_type_id' => $detail['ref_document_type_id'],
                'ref_document_identity' => $detail['ref_document_identity'],
                'coa_id' => $product['cogs_account_id'],
                'document_debit' => $detail['cog_amount'],
                'document_credit' => 0,
                'debit' => ($detail['cog_amount'] * $data['conversion_rate']),
                'credit' => 0,
                'remarks' => $data['remarks'],
            );

            $stock_ledger[] = array(
                'document_detail_id' => $delivery_challan_detail_id,
                'warehouse_id' => $detail['warehouse_id'],
                'product_id' => $detail['product_id'],
                'document_unit_id' => $detail['unit_id'],
                'document_qty' => $detail['qty'],
                'unit_conversion' => 1,
                'base_unit_id' => $detail['unit_id'],
                'base_qty' => (-1 * $detail['qty']),
                'document_rate' => $stock['avg_stock_rate'],
                'document_amount' => (-1 * ($detail['qty'] * $stock['avg_stock_rate'])),
                'base_rate' => ($stock['avg_stock_rate'] * $detail['conversion_rate']),
                'base_amount' => (-1 * ($detail['qty'] * $stock['avg_stock_rate'] * $detail['conversion_rate'])),
                'remarks' => $data['remarks'],
            );
        }
        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $data['document_id'];
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

        foreach($stock_ledger as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $data['document_id'];
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['sort_order'] = $sort_order;
            $ledger['base_currency_id'] = $data['base_currency_id'];
            $ledger['document_currency_id'] = $data['document_currency_id'];
            $ledger['conversion_rate'] = $data['conversion_rate'];
            $ledger['partner_type_id'] = $data['partner_type_id'];
            $ledger['partner_id'] = $data['partner_id'];

            $stock_ledger_id = $this->model['stock_ledger']->add($this->getAlias(), $ledger);
        }

        return $delivery_challan_id;
    }

    protected function updateRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias().'/update', 'token=' . $this->session->data['token'] . '&delivery_challan_id=' . $id, 'SSL'));
    }

    protected function validateDelete($primary_key) {
        if (!$this->user->hasPermission('delete', $this->getAlias())) {
            $this->error['warning'] = $this->language->get('error_permission_delete');
        }

        $this->model['delivery_challan_detail'] = $this->load->model($this->getAlias());
        $delivery_challan_detail = $this->model['delivery_challan_detail']->getRow([$this->getPrimaryKey() => $primary_key]);

        $this->model['sale_tax_invoice_detail'] = $this->load->model('inventory/sale_tax_invoice_detail');
        $sale_tax_invoice_detail = $this->model['sale_tax_invoice_detail']->getRow(['ref_document_identity' => $delivery_challan_detail['document_identity']]);

        if( ($delivery_challan_detail['document_identity'] == $sale_tax_invoice_detail['ref_document_identity']) ) {
            $this->session->data['error_warning'] = $this->data['lang']['ref_document_identity_error'] . $sale_tax_invoice_detail['ref_document_identity'];
            return false;
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    protected function deleteData($primary_key) {

        $this->model['delivery_challan_detail'] = $this->load->model('inventory/delivery_challan_detail');
        $this->model['delivery_challan_detail']->deleteBulk($this->getAlias(),array('delivery_challan_id' => $primary_key));

        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->deleteBulk($this->getAlias(), array('document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model['stock'] = $this->load->model('common/stock_ledger');
        $this->model['stock']->deleteBulk($this->getAlias(), array('document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

//    public function getPurchaseOrder() {
//        if($this->request->server['REQUEST_METHOD'] == 'POST') {
//
//            $document_id = $this->request->post['ref_document_id'];
//            $supplier_id = $this->request->post['supplier_id'];
//            $delivery_challan_id = $this->request->get['delivery_challan_id'];
//
//            $this->model['product'] = $this->load->model('setup/product');
//            $products = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));
//
//            $this->model['warehouse'] = $this->load->model('inventory/warehouse');
//            $warehouses = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id']));
//
//            $this->model['unit'] = $this->load->model('setup/unit');
//            $units = $this->model['unit']->getRows(array('company_id' => $this->session->data['company_id']));
//
//            $this->model['currency'] = $this->load->model('setup/currency');
//            $currencys = $this->model['currency']->getArrays('currency_id','name',array('company_id' => $this->session->data['company_id']));
//
//            $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
//
//            $rows = $this->model['delivery_challan']->getPurchaseOrder($document_id,$supplier_id);
//
//            $this->model['purchase_order'] = $this->load->model('inventory/purchase_order');
//            $html = '';
//            foreach($rows as $grid_row => $row) {
//                $order_qty = $this->model['purchase_order']->getOrderQty($document_id, $row['product_id']);
//                $received_qty = $this->model['purchase_order']->getUtilizedProduct($document_id, $row['product_id'], $delivery_challan_id);
//                $html .= '<tbody id="grid_row_' . $grid_row . '" row_id="'. $grid_row .'">';
//                $html .='<tr>';
//                $html .='<td><input  type="text" name="delivery_challan_details['. $grid_row .'][product_code]" id="delivery_challan_detail_code_'. $grid_row .'" class="" value="'.$row['product_code'].'" readonly="true" /></td>';
//                $html .='<td>';
////                $html .='<div class="form-group input-group">';
//                $html .='<input  type="hidden" name="delivery_challan_details['. $grid_row .'][product_id]" id="delivery_challan_detail_id_'. $grid_row .'" class="" value="'.$row['product_id'].'" />';
//                $html .='<select class="" id="delivery_challan_detail_product_id_' . $grid_row . '" name="delivery_challan_details[' . $grid_row . '][product_id]" disabled="true">';
//                $html .='<option value=""></option>';
//                foreach($products as $product) {
//                    $html .='<option value="' . $product['product_id'] . '" '.($product['product_id'] == $row['product_id']?'selected="selected"':'').' >' . $product['name'] .'</option>';
//                }
//                $html .='</select>';
////                $html .='<span class="input-group-btn ">';
////                $html .='<button type="button"  model="setup/product" ref_id="delivery_challan_detail_product_id_' . $grid_row . '" callback="getProductInformation"  value="..." class="QSearch btn btn-default" ><i class="fa fa-search"></i></button>';
////                $html .='</span>';
////                $html .='</div>';
//                $html .='</td>';
//                $html .='<td><select class="" name="delivery_challan_details['. $grid_row .'][warehouse_id]">';
//                $html .='<option value=""></option>';
//                foreach($warehouses as $warehouse) {
//                    $html .='<option value="' . $warehouse['warehouse_id'] .'" '.($warehouse['warehouse_id'] == $row['warehouse_id']?'selected="selected"':'').' >' . $warehouse['name'] . '</option>';
//                }
//                $html .='</select></td>';
//                $html .='<td>';
//                $html .='<input type="hidden" class="fDecimal" id="delivery_challan_detail_order_qty_'. $grid_row .'" name="delivery_challan_details['. $grid_row .'][order_qty]" value="'.$order_qty.'" />';
//                $html .='<input type="hidden" class="fDecimal" id="delivery_challan_detail_received_qty_'. $grid_row .'" name="delivery_challan_details['. $grid_row .'][received_qty]" value="'.$received_qty.'" />';
//                $html .='<input type="text" class="fDecimal" id="delivery_challan_detail_qty_'. $grid_row .'" name="delivery_challan_details['. $grid_row .'][qty]" value="'.($order_qty-$received_qty).'" onchange="calcRowTotal('. $grid_row .',\'qty\')" title="OQ:'.$order_qty.',RQ:'.$received_qty.',BQ:'.($order_qty-$received_qty).'" />';
//                $html .='</td>';
//                $html .='<td>';
//                $html .='<input  type="hidden" name="delivery_challan_details['. $grid_row .'][unit_id]" id="delivery_challan_detail_unit_id_'. $grid_row .'" class="" value="'.$row['unit_id'].'" />';
//                $html .='<select class="" id="delivery_challan_detail_unit_id_' . $grid_row . '" name="delivery_challan_details['. $grid_row .'][unit_id]" disabled="true">';
//                $html .='<option value=""></option>';
//                foreach($units as $unit) {
//                    $html .='<option value="' . $unit['unit_id'] . '" '.($unit['unit_id'] == $row['unit_id']?'selected="selected"':'').' >' . $unit['name'] . '</option>';
//                }
//                $html .='</select></td>';
//                $html .='<td><input type="text" class="fDecimal" id="delivery_challan_detail_rate_'. $grid_row .'" name="delivery_challan_details['. $grid_row .'][rate]" value="'.$row['rate'].'" onchange="calcRowTotal('. $grid_row .',\'rate\')" readonly="true" /></td>';
//                $html .='<td>';
//                $html .='<input  type="hidden" name="delivery_challan_details['. $grid_row .'][document_currency_id]" id="delivery_challan_detail_document_currency_id_'. $grid_row .'" class="" value="'.$row['document_currency_id'].'" />';
//                $html .= '<select class="" id="delivery_challan_detail_document_currency_id_'. $grid_row .'" name="delivery_challan_details['. $grid_row .'][document_currency_id]" onchange="getCurrencyRate('. $grid_row .');" disabled="true">';
//                $html .='<option value=""></option>';
//                foreach($currencys as $currency_id => $value) {
//                    $html .='<option value="' . $currency_id . '" '.($currency_id == $row['document_currency_id']?'selected="selected"':'').'>' . $value . '</option>';
//                }
//                $html .='</select></td>';
//                $html .='<td><input type="text" class="fDecimal" id="delivery_challan_detail_conversion_rate_'. $grid_row .'" name="delivery_challan_details['. $grid_row .'][conversion_rate]" value="'.$row['conversion_rate'].'" onchange="calcRowTotal('. $grid_row .',\'conversion_rate\')" readonly="true" /></td>';
//                $html .='<td><input type="text" class="fDecimal" id="delivery_challan_detail_amount_'. $grid_row .'" name="delivery_challan_details['. $grid_row .'][amount]" value="'.$row['amount'].'" readonly="readonly" /></td>';
//                $html .='<td>&nbsp;</td>';
//                $html .='</tr>';
//                $html .='</tbody>';
//            }
//            $html .= "</tbody>";
//
////d($document,true);
//            $json = array(
//                'success' => true,
//                'html' => $html,
//                'row' => $row,
//            );
//            // d($json,true);
//        }
//        else {
//            $this->load->language('setup/product');
//            $json = array(
//                'success' => false,
//                'error' => $this->language->get('error_select_product')
//            );
//        }
//        echo json_encode($json);
//        exit;
//    }
//
//    public function getPOrderByDocType() {
//        if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['supplier_id'] && $this->request->post['ref_document_type_id']) {
//            $ref_document_id = $this->request->post['ref_document_id'];
//            $this->model['document'] = $this->load->model('common/document');
//            $documents = $this->model['document']->getRows(array('company_id' =>$this->session->data['company_id'],'fiscal_year_id' =>$this->session->data['fiscal_year_id'],'company_branch_id' =>$this->session->data['company_branch_id'],'document_type_id' => $this->request->post['ref_document_type_id'],'people_id' => $this->request->post['supplier_id']));
//            $html = '<option value="">&nbsp;</option>';
//            foreach($documents as $document) {
//                $html .= '<option value="' . $document['document_id'] . '" '.($document['document_id'] == $ref_document_id ? 'selected="true"': '').'  >'.$document['document_identity'].'</option>';
//            }
//            echo json_encode(array('success' => true, 'identity' => $html));
//        }
//    }





    public function ajaxValidateForm() {
        $post  = $this->request->post;
        $lang = $this->load->language('inventory/delivery_challan');
        $error = array();

        if($post['voucher_date'] == '') {
            $error[] = $lang['error_voucher_date'];
        }

        $details = $post['delivery_challan_details'];
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


    public  function ExcelFigures()
    {

        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $lang = $this->load->language($this->getAlias());
        $DeliveryChallanId = $this->request->get['delivery_challan_id'];
        $post = $this->request->post;
        $session = $this->session->data;

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
        $this->model['unit'] = $this->load->model('inventory/unit');
        $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');
        $this->model['delivery_challan_detail'] = $this->load->model('inventory/delivery_challan_detail');


        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $DeliveryChallan = $this->model['delivery_challan']->getRow(array('delivery_challan_id' => $DeliveryChallanId));
        $DeliveryChallanDetails = $this->model['delivery_challan_detail']->getRows(array('delivery_challan_id' => $DeliveryChallanId),array('sort_order asc'));

        $Partner = $this->model['partner']->getRow(array('partner_type_id' => $DeliveryChallan['partner_type_id'],'partner_id' => $DeliveryChallan['partner_id']));
        $CustomerUnit = $this->model['customer_unit']->getRow(array('customer_unit_id' => $DeliveryChallan['customer_unit_id']));

        if($DeliveryChallan['po_date'] != "")
        {
            $DeliveryChallan['po_date'] = stdDate($DeliveryChallan['po_date']);
        }
        else {
            $DeliveryChallan['po_date']="";
        }

//        $pdf->Ln(7);
//        $pdf->Cell(120,7, '', 'B', false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(5, 7, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(25, 7, 'Challan Date :', 'B', false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(45, 7, stdDate($DeliveryChallan['document_date']), 'B', false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->Ln(7);
//
//        $pdf->Cell(120,7, $Partner['address'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(5, 7, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(25, 7, 'Po No :', 'B', false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(45, 7, $DeliveryChallan['po_no'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');
//
//        $pdf->Ln(7);
//        $pdf->Cell(120,7, '', 'B', false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(5, 7, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(25, 7, 'Po Date :', 'B', false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(45, 7, $DeliveryChallan['po_date'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->Ln(7);
//        $pdf->Cell(25, 7, 'Customer Unit :', 'B', false, 'L', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(95, 7, $CustomerUnit['customer_unit'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');
//
//        $pdf->Ln(10);
//
//        $pdf->SetFont('helvetica', 'B', 10);
//        $pdf->Cell(15, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(120, 7, 'Description', 1, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(30, 7, 'Qty', 1, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(30, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');


        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $rowCount = 1;


        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':D'.$rowCount);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getAlignment()->applyFromArray(
            array('font' => array(
                'bold' => true,
                'size' => 14,
            ),'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,)
        );
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,'Delivery Challan')->getStyle('A'.$rowCount)->getFont()->setBold( true )->setSize(14);
        $rowCount++;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':B'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,$Partner['name']);
        $objPHPExcel->getActiveSheet()->mergeCells('C'.$rowCount.':D'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowCount,'Challan No :   '.$DeliveryChallan['document_identity']);
        $rowCount++;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':B'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,'');
        $objPHPExcel->getActiveSheet()->mergeCells('C'.$rowCount.':D'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowCount,'Challan Date : '.stdDate($DeliveryChallan['document_date']));
        $rowCount++;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':B'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,$Partner['address']);
        $objPHPExcel->getActiveSheet()->mergeCells('C'.$rowCount.':D'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowCount,'Po No :        '.$DeliveryChallan['po_no']);
        $rowCount++;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':B'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,'');
        $objPHPExcel->getActiveSheet()->mergeCells('C'.$rowCount.':D'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$rowCount,'Po Date :      '.$DeliveryChallan['po_date']);
        $rowCount++;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':B'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,'Customer Unit : '.$CustomerUnit['customer_unit']);
        $rowCount++;
        $rowCount++;


        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Sr.')->getStyle('A'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Description')->getStyle('B'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Qty')->getStyle('C'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Unit')->getStyle('D'.$rowCount)->getFont()->setBold( true );

        $rowCount++;
        $sr = 0;

        foreach($DeliveryChallanDetails as $detail) {

            $sr++;
            $Unit = $this->model['unit']->getRow(array('unit_id' => $detail['unit_id']));

            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $sr);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, html_entity_decode($detail['description']));
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $detail['qty']);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $Unit['name']);
            $rowCount++;

        }
        $rowCount += 5;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':D'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,'Received the above goods in goods order and conditions');


        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Delivery Challan.xlsx"');
        header('Cache-Control: max-age=0');
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        //$objWriter->save('some_excel_file.xlsx');
        $objWriter->save('php://output');
        exit;

        // d($html,true);

    }


    public function printDocument() {

        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $lang = $this->load->language($this->getAlias());
        $DeliveryChallanId = $this->request->get['delivery_challan_id'];
        $post = $this->request->post;
        $session = $this->session->data;

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->model['delivery_challan'] = $this->load->model('inventory/delivery_challan');
        $this->model['unit'] = $this->load->model('inventory/unit');
        $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');
        $this->model['delivery_challan_detail'] = $this->load->model('inventory/delivery_challan_detail');
      

        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $DeliveryChallan = $this->model['delivery_challan']->getRow(array('delivery_challan_id' => $DeliveryChallanId));
        $DeliveryChallanDetails = $this->model['delivery_challan_detail']->getRows(array('delivery_challan_id' => $DeliveryChallanId),array('sort_order asc'));

        $Partner = $this->model['partner']->getRow(array('partner_type_id' => $DeliveryChallan['partner_type_id'],'partner_id' => $DeliveryChallan['partner_id']));
        $CustomerUnit = $this->model['customer_unit']->getRow(array('customer_unit_id' => $DeliveryChallan['customer_unit_id']));



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

        $partner_id=$DeliveryChallan['partner_id'];
        $this->model['customer'] = $this->load->model('setup/customer');

        $where = "customer_id = '" . $partner_id."'";

        $partner = $this->model['customer']->getRow($where);
//        d($this->data['company_footer'],true);
//        $this->model['image'] = $this->load->model('tool/image');
//        $this->data['company_footer'] = $this->model['image']->resize('footer.jpeg',200,50);

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        $pdf->footer = HTTP_IMAGE.'footer.jpeg';
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Fahad Siddiqui');
        $pdf->SetTitle('Delivery Challan');
        $pdf->SetSubject('Delivery Challan');


        if($DeliveryChallan['status'] == "Normal")
        {
            $report_name = "Delivery Challan";
        }
        else{
            $report_name = "Sample Delivery Challan";

        }
        //Set Header
        $pdf->data = array(
            'company_name' => $branch['name'],
            'company_address' => $branch['address'],
            'company_phone' => $branch['phone_no'],
            'report_name' => $report_name,
            'company_logo' => $session['company_image'],
            'header_image' => HTTP_IMAGE.'header.jpg',
            'footer_image' => HTTP_IMAGE.'footer.jpg',
            'company_header_print' => $company_header_print,
            'company_footer_print' => $company_footer_print,
            'remarks' => $DeliveryChallan['remarks'],
        );

        // d($pdf->data,true);

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 55, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(2);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 65);

        // set font
        $pdf->SetFont('helvetica', 'B', 16);

        // add a page
        $pdf->AddPage();

        $pdf->SetTextColor(0,0,0);
        $pdf->Ln(-10);

        if($DeliveryChallan['po_date'] != "")
        {
            $DeliveryChallan['po_date'] = stdDate($DeliveryChallan['po_date']);
        }
        else {
            $DeliveryChallan['po_date']="";
        }
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(120,7, 'M/s.      '.html_entity_decode($Partner['name']), '', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(5, 0, '', 0, false, 'T', 0, '', 0, false, 'M', 'M');

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(25, 7, 'Challan No :', '', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(45, 7, $DeliveryChallan['document_identity'], '', false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->Ln(15);
        

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(255,255,255);
        $pdf->MultiCell(80 ,10, 'Address: ', '', 'L', 1, 2, 10, 50, true);
        $pdf->MultiCell(90 ,10, $partner['address'], '', 'L', 1, 2, 28, 50, true);
        $pdf->Cell(5, 0, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->MultiCell(80, 10, 'Challan Date:        ' . stdDate($DeliveryChallan['document_date']), '', 'L', 1, 2, 135, 50, true);
        
        $pdf->Ln(1);
        $pdf->Cell(18, 7, 'Po No :', '', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(42, 7, $DeliveryChallan['po_no'], '', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(65, 7, '', '', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7, 'Po Date :', '', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(40, 7, $DeliveryChallan['po_date'], '', false, 'L', 0, '', 0, false, 'M', 'M');
        
        $pdf->Ln(7);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(25, 7, 'Customer Unit :', '', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(95, 7, $CustomerUnit['customer_unit'], '', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(5, 7, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->Ln(-1);
        $pdf->Cell(15, 7, '', '', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(45, 7, '', '', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(65, 7, '', '', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(25, 7, 'Manual Ref No:', '', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(40, 7, $DeliveryChallan['manual_ref_no'], '', false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->Ln(10);

        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->Cell(15, 7, 'SNo.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(60, 7, 'Product Name', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(60, 7, 'Remarks', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(35, 7, 'Warehouse', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(15, 7, 'Qty', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(10, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $sr = 1;
        $pdf->Ln(5.5);

        $pdf->SetFont('helvetica', '', 7);
        foreach($DeliveryChallanDetails as $detail) {
            $description = $detail['description'];
            $remarks = $detail['remarks'];
            if(multili_var_length_check(array($description), 45) && multili_var_length_check(array($remarks), 45)) {
                $pdf->Cell(15, 7, $sr, 1,  false, 'C', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(60, 7, html_entity_decode($detail['description']), 1, false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(60, 7, html_entity_decode($detail['remarks']), 1, false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(35, 7, $detail['warehouse'], 1, false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(15, 7, $detail['qty'], 1, false, 'C', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(10, 7, $detail['unit'], 1, false, 'C', 0, '', 0, false, 'M', 'M');

                $pdf->Ln(7);
            
            } else {

                $arrDesc = splitString($description, 45);
                $arrRemarks = splitString($remarks, 45);
                $decLength = max_array_index_count($arrDesc);
                $remarksLength = max_array_index_count($arrRemarks);
                if($decLength > $remarksLength)
                {
                    for($index=0; $index <= ($decLength-1); $index++){
                        if($index==0){
                            $pdf->Cell(15, 4, $sr, 'TLR',  false, 'C', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(60, 4, html_entity_decode($arrDesc[$index]), 'TLR', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(60, 4, html_entity_decode($arrRemarks[$index]), 'TLR', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(35, 4, $detail['warehouse'], 'TLR', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(15, 4, $detail['qty'], 'TLR', false, 'C', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(10, 4, $detail['unit'], 'TLR', false, 'C', 0, '', 0, false, 'M', 'M');
    
                        } else if($index<($decLength-1)){
                            $pdf->Cell(15, 4, '', 'LR',  false, 'C', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(60, 4, html_entity_decode($arrDesc[$index]), 'LR', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(60, 4, html_entity_decode($arrRemarks[$index]), 'LR', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(35, 4, '', 'LR', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(15, 4, '', 'LR', false, 'C', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(10, 4, '', 'LR', false, 'C', 0, '', 0, false, 'M', 'M');
    
                        } else {
                            $pdf->Cell(15, 4, '', 'LRB',  false, 'C', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(60, 4, html_entity_decode($arrDesc[$index]), 'LRB', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(60, 4, html_entity_decode($arrRemarks[$index]), 'LRB', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(35, 4, '', 'LRB', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(15, 4, '', 'LRB', false, 'C', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(10, 4, '', 'LRB', false, 'C', 0, '', 0, false, 'M', 'M');
    
                        }
                        $pdf->ln(4);
                    }
                }else
                {
                    for($index=0; $index <= ($remarksLength-1); $index++){
                        if($index==0){
                            $pdf->Cell(15, 4, $sr, 'TLR',  false, 'C', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(60, 4, html_entity_decode($arrDesc[$index]), 'TLR', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(60, 4, html_entity_decode($arrRemarks[$index]), 'TLR', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(35, 4, $detail['warehouse'], 'TLR', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(15, 4, $detail['qty'], 'TLR', false, 'C', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(10, 4, $detail['unit'], 'TLR', false, 'C', 0, '', 0, false, 'M', 'M');
    
                        } else if($index<($remarksLength-1)){
                            $pdf->Cell(15, 4, '', 'LR',  false, 'C', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(60, 4, html_entity_decode($arrDesc[$index]), 'LR', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(60, 4, html_entity_decode($arrRemarks[$index]), 'LR', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(35, 4, '', 'LR', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(15, 4, '', 'LR', false, 'C', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(10, 4, '', 'LR', false, 'C', 0, '', 0, false, 'M', 'M');
    
                        } else {
                            $pdf->Cell(15, 4, '', 'LRB',  false, 'C', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(60, 4, html_entity_decode($arrDesc[$index]), 'LRB', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(60, 4, html_entity_decode($arrRemarks[$index]), 'LRB', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(35, 4, '', 'LRB', false, 'L', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(15, 4, '', 'LRB', false, 'C', 0, '', 0, false, 'M', 'M');
                            $pdf->Cell(10, 4, '', 'LRB', false, 'C', 0, '', 0, false, 'M', 'M');
    
                        }
                        $pdf->ln(4);
                    }
                }
                

            }
            
            $sr++;
        
        }

        $x = $pdf->GetX();
        $y = $pdf->GetY();


        for ($i = $y; $i <= 220; $i++) {

            $pdf->Ln(1);
            $pdf->Cell(15, 7, '', 'L',  false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(60, 7, '', 'L', false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(60, 7, '', 'L', false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(35, 7, '', 'L', false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(15, 7, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(10, 7, '', 'L,R', false, 'C', 0, '', 0, false, 'M', 'M');
            $y =$i;
        }

        $pdf->Ln(-1);
        $pdf->Ln(1);
        $pdf->Cell(195, 7, '', 'B', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setXY($x,$y);

//s        d(array($x,$y),true);

        $pdf->SetFont('helvetica', 'B', 8);

//        $pdf->Ln(7);
//        $pdf->Cell(129, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(29, 7, number_format($DeliveryChallan['item_total'],2), 1, false, 'R', 0, '', 0, false, 'M', 'M');

//        $pdf->Ln(15);
//        $pdf->Cell(0, 6, 'Authorized Signature.', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        //Close and output PDF document

        ob_start();
//        $pdf->Output('Delivery Challan:'.date('YmdHis').'.pdf', 'D');
        $pdf->Output('Delivery-'.$DeliveryChallan['document_identity'].'.pdf', 'I');
        ob_end_flush();


    }

}
class PDF extends TCPDF {
    public $data = array();
    public $term = array();
    public $txt;

    //Page header
    public function Header() {
        // d($this->data, true);
        // Logo
//        if($this->data['company_logo'] != '') {
//            $image_file = DIR_IMAGE.$this->data['company_logo'];
//            //$this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
//            $this->Image($image_file, 10, 10, 30, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
//        }

        if($this->data['company_header_print'] != '') {
            $image_file = DIR_IMAGE.$this->data['company_header_print'];
            // d($image_file, true);
            // $this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
            $this->Image($image_file, 10, 5, 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }else
        {
            // Set font
            $this->SetFont('helvetica', 'B', 20);
            $this->Ln(2);
            //Title
            $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        }

//        // Set font
    //    $this->SetTextColor(0,0,0);
    //    $this->SetFont('helvetica', 'B,I', 20);
    //    $this->Ln(8);
//        // Title
        // $this->Cell(0, 4, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        // $this->Ln(9);
        // if($this->data['company_address']) {
        //     $this->Cell(0, 4, $this->data['company_address'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        //     $this->Ln(12);
        // }
        // $this->SetFont('helvetica', '', 10);
        // $this->SetFillColor(255,255,255);
        // $this->SetTextColor(0,0,0);
        // $this->MultiCell(200, 5, $this->txt, 0, 'C', 1, 2, 5, 18, true);
    //    if($this->data['company_phone']) {
    //        $this->SetFont('helvetica', '', 10);
    //        $this->Cell(0, 4, 'Phone: '.$this->data['company_phone'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
    //        $this->Ln(4);
    //    }
    //    $this->SetTextColor(0,0,0);
    //    $this->Cell(0, 4, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        // $this->Image($this->data['header_image'], 0, 5, 205, "", "JPG", "", "T", false, 300, "", false, false, 0, false, false, false);
        $this->Ln(20);
        $this->SetFont('helvetica', 'B', 14);
        $this->Cell(120, 7, $this->data['report_name'], 0, false, 'R', 0, '', 0, false, 'M', 'B');
    }

    // Page footer
    public function Footer() {

        $this->SetY(-25);
        $y = $this->GetY();

        if($this->data['company_footer_print'] != '') {
            $image_file = DIR_IMAGE.$this->data['company_footer_print'];
            // $this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
            $this->Image($image_file, 5, ($y-10), 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
//        // Position at 15 mm from bottom
       // $this->SetY(-45);
//        // Set font

        $this->SetY(-72);

        $this->SetFont('helvetica', '', 8);

        $x = $this->GetX();
        $y = $this->GetY();
        $this->setXY($x, $y);

        $this->MultiCell(0, 14, 'Remarks: ' . $this->data['remarks'], 0, 'L', false, 0, 10, $y, true, 0, false, true, 0, 'T', true);
        
        $this->SetFont('helvetica', 'B', 8);

        $this->Ln(25);
        $this->Cell(10, 5, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(60, 5, 'Signature Warehouse Incharge', "T", false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(50, 5, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(60, 5, 'Recieved By', "T", false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(7);
        $this->Cell(0, 5, 'Please Return Duplicate Copy For Office Record', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        // $this->Image($this->data['footer_image'], 0, 0, 200, '', 'JPG', '', 'B', false, 300, '', false, false, 0, false, false, false);
        // $this->Image($this->data['footer_image'], 0, 250, 205, "", "JPG", "", "T", false, 300, "", false, false, 0, false, false, false);
        //$this->cell($this->data['footer'], 55, 19, '', '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);


//        $this->SetFont('helvetica', 'B', 11);
//        $this->Ln(5);
//        $this->Cell(0, 5, 'In case of any clarification or query , please feel free to contact us.', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//        $this->Ln(5);

    }
}
?>