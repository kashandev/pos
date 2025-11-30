<?php

class ControllerInventorySaleOrder1 extends HController{
    protected $document_type_id = 5;
    protected function getAlias() {
        return 'inventory/sale_order1';
    }

    protected function getPrimaryKey() {
        return 'sale_order_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {
        $lang = $this->load->language('inventory/sale_order');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action', 'document_date', 'document_identity', 'partner_type', 'partner_name', 'item_total', 'created_at');

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
                $strAction .= '<a '.(isset($action['btn_class'])?'class="'.$action['btn_class'].'"':'').' '.(isset($action['target'])?'target="'.$action['target'].'"':'').' href="' . $action['href'] . '" data-toggle="tooltip" title="' . $action['text'] . '" ' . (isset($action['click']) ? 'onClick="' . $action['click'] . '"' : '') . '>';
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
                }elseif ($aColumns[$i] == 'document_date') {
                    $row[] = stdDate($aRow['document_date']);
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
        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']),array('name'));

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partners'] = $this->model['partner']->getRows(array('company_id' => $this->session->data['company_id'], 'partner_type_id' => 2, 'company_branch_id' => $this->session->data['company_branch_id']));
        // d($this->data['partners'],true);

        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencys'] = $this->model['currency']->getRows();

        // $this->model['partner_category'] = $this->load->model('setup/partner_category');
        // $this->data['partner_categorys'] = $this->model['partner_category']->getRows();

        $this->model['unit'] = $this->load->model('inventory/unit');
        $this->data['units'] = $this->model['unit']->getRows(array('company_id' => $this->session->data['company_id']));
        $arrUnits = $this->model['unit']->getArrays('unit_id','name',array('company_id' => $this->session->data['company_id']));

        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));
        $this->data['arrWarehouses'] = json_encode($this->data['warehouses']);

        $this->model['salesman'] = $this->load->model('setup/salesman');
        $this->data['salesmans'] = $this->model['salesman']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));

        $this->model['stock'] = $this->load->model('common/stock_ledger');

        $this->data['href_get_ref_document_no'] = $this->url->link($this->getAlias() . '/getReferenceDocumentNos', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_ref_document'] = $this->url->link($this->getAlias() . '/getReferenceDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['href_get_sale_order'] =  $this->url->link($this->getAlias() . '/getData', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";
        $this->data['partner_types'] = $this->session->data['partner_types'];
        $this->data['partner_type_id'] = 2;

        $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');
        $this->data['customer_units'] = $this->model['customer_unit']->getRows(array('company_id' => $this->session->data['company_id']),array('customer_unit'));
        $this->data['document_date'] = stdDate();
        if (isset($this->request->get['sale_order_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {

                if ($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                }elseif ($field == 'po_date') {
                    if($value != ''){
                        $this->data[$field] = stdDate($value);
                    }
                } else {
                    $this->data[$field] = $value;
                }
            }


            $this->model['sale_order_detail'] = $this->load->model('inventory/sale_order_detail');
            $rows = $this->model['sale_order_detail']->getRows(array('sale_order_id' => $this->request->get['sale_order_id']),array('sort_order asc'));
            foreach($rows as $row_no => $row) {
                $filter = array(
                    'company_id' => $this->session->data['company_id'],
                    'company_branch_id' => $this->session->data['company_branch_id'],
                    'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                    'product_id' => $row['product_id'],
                );
                $stock = $this->model['stock']->getStock($filter);
                $this->data['sale_order_details'][$row_no] = $row;
                $this->data['sale_order_details'][$row_no]['stock_qty'] = $stock['stock_qty'];
                //$this->data['sale_order_details'][$row_no]['unit'] = $arrUnits[$row['unit_id']];
            }

        }

        $this->data['restrict_out_of_stock'] = $this->session->data['restrict_out_of_stock'];
        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print'] = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_partner'] = $this->url->link($this->getAlias() . '/getPartner', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['strValidation'] = "{
            'rules': {
                'document_date': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'partner_id': {'required': true},
                'total_qty': {'required': true, 'min':1},
                'item_total': {'required': true, 'min':1},
                'total_quantity': {'required': true, 'min':1},
            },
            messages: {
            document_date:{
                remote: 'Invalid Date'
            }}
        }";

        $this->response->setOutput($this->render());
    }

    public function getPartner() {
        $post = $this->request->post;
        $partner_category_id = $post['partner_category_id'];
        $partner_id = $post['partner_id'];

        $this->model['partner'] = $this->load->model('common/partner');
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'partner_category_id' => $partner_category_id
        );

        $partners = $this->model['partner']->getRows($filter,array('name'));

        $html = '<option value="">&nbsp;</option>';
        $arrPartners = array();
        foreach($partners as $partner) {
            if($partner['partner_id'] == $partner_id) {
                $html .= '<option value="'.$partner['partner_id'].'" selected="true">'.$partner['name'].'</option>';
            } else {
                $html .= '<option value="'.$partner['partner_id'].'">'.$partner['name'].'</option>';
            }
            $arrPartners[$partner['partner_id']]= $partner;
        }
        $json = array(
            'success' => true,
            'html' => $html,
            'partners' => $arrPartners
        );

        $this->response->setOutput(json_encode($json));
    }


    public function getReferenceDocumentNos() {
        $sale_order_id = $this->request->get['sale_order_id'];
        $post = $this->request->post;
        //d(array($sale_order_id, $post), true);

        //Purchase Order
        $this->model['quotation'] = $this->load->model('inventory/quotation');
        $where = "company_id=" . $this->session->data['company_id'];
        $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
        $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
        $where .= " AND partner_type_id='" . $post['partner_type_id'] . "'";
        $where .= " AND partner_id='" . $post['partner_id'] . "'";
//        $where .= " AND is_post=1";

        $quotations = $this->model['quotation']->getQuotations($where,$sale_order_id);
//        d($quotations, true);
        foreach($quotations as $quotation_id => $quotation) {
            foreach($quotation['products'] as $product_id => $product) {
                if($product['order_qty'] <= $product['utilized_qty']) {
                    unset($quotation['products'][$product_id]);
                }
            }
            if(empty($quotation['products'])) {
                unset($quotations[$quotation_id]);
            }
        }

        $html = "";

        $html .= '<option value="">&nbsp;</option>';
        foreach($quotations as $quotation_id => $quotation) {
            if($quotation['quotation_id']==$post['ref_document_id']) {
                $html .= '<option value="'.$quotation_id.'" selected="true">'.$quotation['document_identity'].'</option>';
            } else {
                $html .= '<option value="'.$quotation_id.'">'.$quotation['document_identity'].'</option>';
            }

        }

//        d($quotation,true);
        $json = array(
            'success' => true,
            'sale_order_id' => $quotation_id,
            'post' => $post,
            'where' => $where,
            'html' => $html
        );

        echo json_encode($json);
    }

    public function getReferenceDocument() {
        $sale_order_id = $this->request->get['sale_order_id'];
        $post = $this->request->post;
        $this->model['stock'] = $this->load->model('common/stock_ledger');
        //Purchase Order
        $this->model['quotation'] = $this->load->model('inventory/quotation');
        $where = "company_id=" . $this->session->data['company_id'];
        $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
        $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
        $where .= " AND partner_id='" . $post['partner_id'] . "'";
        $where .= " AND document_identity='" . $post['ref_document_identity'] . "'";

        $quotations = $this->model['quotation']->getQuotations($where,$sale_order_id);
        $quotation = $quotations[$post['ref_document_identity']];
//        d($quotation,true);


        $details = array();
        $row_no = 0;
        foreach($quotation['products'] as $product) {
//            $details[$row_no]['balanced_qty'] = ($product['order_qty'] - $product['utilized_qty']);
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'product_id' => $product['product_id'],
        );
        $stock = $this->model['stock']->getStock($filter);
            if($product['order_qty'] - $product['utilized_qty'] > 0)
            {
                $href = $this->url->link('inventory/quotation/update', 'token=' . $this->session->data['token'] . '&quotation_id=' . $quotation['quotation_id']);
                $details[$row_no] = $product;
                $details[$row_no]['ref_document_identity'] = $quotation['document_identity'];
                $details[$row_no]['row_identity'] = $quotation['document_identity'].'-'.$product['product_code'];
                $details[$row_no]['href'] = $href;
                $details[$row_no]['balanced_qty'] = ($product['order_qty'] - $product['utilized_qty']);
                $details[$row_no]['utilized_qty'] = ($product['order_qty'] - $product['utilized_qty']);
                $details[$row_no]['stock_qty'] = ($stock['stock_qty']==null?0:$stock['stock_qty']);

                $row_no++;
            }
        }
        // d($stock['stock_qty'],true);
        $quotation['products'] = $details;
        $json = array(
            'success' => true,
            'quotation_id' => '',
            'post' => $post,
            'data' => $quotation,
        );

        echo json_encode($json);
    }

    protected function insertData($data){

        $this->model['document_type'] = $this->load->model('common/document_type');
        $document = $this->model['document_type']->getNextDocument($this->document_type_id);

        if($data['po_date'] != '') {
            $data['po_date'] = MySqlDate($data['po_date']);
        } else {
            $data['po_date'] = NULL;
        }

        $data['document_date'] = MySqlDate($data['document_date']);
        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['document_type_id'] = $this->document_type_id;
        $data['document_prefix'] = $document['document_prefix'];
        $data['document_no'] = $document['document_no'];
        $data['document_identity'] = $document['document_identity'];
        $data['base_amount'] = $data['item_total'] * $data['conversion_rate'];


        $sale_order_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $data['document_id'] =$sale_order_id;
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
            'base_amount' => $data['base_amount'],
        );

        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

        $this->model['sale_order_detail'] = $this->load->model('inventory/sale_order_detail');

        foreach ($data['sale_order_details'] as $sort_order =>$detail){
            $detail['sale_order_id'] = $sale_order_id;

            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];

            $detail['company_id'] = $this->session->data['company_id'];

            $detail['company_branch_id'] = $this->session->data['company_branch_id'];

            $detail['sort_order'] = $sort_order;

            $sale_order_detail_id = $this->model['sale_order_detail']->add($this->getAlias(), $detail);
        }
        return $sale_order_id;
    }

    protected function updateData($primary_key, $data) {
        $sale_order_id = $primary_key;
        $data['document_date'] = MySqlDate($data['document_date']);
        $this->model['sale_order1'] = $this->load->model('inventory/sale_order1');
        $this->model['sale_order_detail'] = $this->load->model('inventory/sale_order_detail');

        if($data['po_date'] != '') {
            $data['po_date'] = MySqlDate($data['po_date']);
        } else {
            $data['po_date'] = NULL;
        }

        $this->model['sale_order1']->edit($this->getAlias(), $primary_key, $data);
        $this->model['sale_order_detail']->deleteBulk($this->getAlias(), array('sale_order_id' => $sale_order_id));

        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->deleteBulk($this->getAlias(), array('document_id' => $sale_order_id));
        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $sale_order_id,
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['net_amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => ($data['item_total'] * $data['conversion_rate']),
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

        foreach ($data['sale_order_details'] as $sort_order => $detail) {
            $detail['sale_order_id'] = $sale_order_id;
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['sort_order'] = $sort_order;
            $sale_order_detail_id=$this->model['sale_order_detail']->add($this->getAlias(), $detail);
        }

        return $sale_order_id;
    }


    protected function deleteData($primary_key) {
        $this->model['sale_order_detail'] = $this->load->model('inventory/sale_order_detail');
        $this->model['sale_order_detail']->deleteBulk($this->getAlias(),array('sale_order_id' => $primary_key));

        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));

        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }


    public function printDocument() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);
        $lang = $this->load->language($this->getAlias());
        $Sale_Order_Id = $this->request->get['sale_order_id'];
        $post = $this->request->post;
        $session = $this->session->data;

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->model['sale_order1'] = $this->load->model('inventory/sale_order1');
        $this->model['unit'] = $this->load->model('inventory/unit');
        $this->model['sale_order_detail'] = $this->load->model('inventory/sale_order_detail');


        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $SaleOrder = $this->model['sale_order1']->getRow(array('sale_order_id' => $Sale_Order_Id));
        $SaleOrderDetails = $this->model['sale_order_detail']->getRows(array('sale_order_id' => $Sale_Order_Id),array('sort_order asc'));
        $Partner = $this->model['partner']->getRow(array('partner_type_id' => $SaleOrder['partner_type_id'],'partner_id' => $SaleOrder['partner_id']));

        $this->model['customer_unit'] = $this->load->model('inventory/customer_unit');
        $CustomerUnit = $this->model['customer_unit']->getRow(array('customer_unit_id' => $SaleOrder['customer_unit_id']));


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

//        d($this->data['company_footer'],true);
//        $this->model['image'] = $this->load->model('tool/image');
//        $this->data['company_footer'] = $this->model['image']->resize('footer.jpeg',200,50);

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        $pdf->footer = HTTP_IMAGE.'footer.jpeg';
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Aamir Shakil');
        $pdf->SetTitle('Sale Order');
        $pdf->SetSubject('Sale Order');

        //Set Header
        $pdf->data = array(
            'company_name' => $branch['name'],
            'company_address' => $branch['address'],
            'company_phone' => $branch['phone_no'],
            'report_name' => 'Sale Order',
            'company_logo' => $session['company_image'],
            'header_image' => HTTP_IMAGE.'header.jpg',
            'footer_image' => HTTP_IMAGE.'footer.jpg',
            'company_header_print' => $company_header_print,
            'company_footer_print' => $company_footer_print,
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 35, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(2);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 65);

        // set font
        $pdf->SetFont('helvetica', 'B', 10);

        // add a page
        $pdf->AddPage();

        $pdf->SetTextColor(0,0,0);
//        $pdf->Ln(55);

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(120,7, 'M/s. '.html_entity_decode($Partner['name']), 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(5, 7, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(25, 7, 'Document No :', 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(45, 7, $SaleOrder['document_identity'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->Ln(7);
        $pdf->Cell(120,7, '', 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(5, 7, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(25, 7, 'Date :', 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(45, 7, stdDate($SaleOrder['document_date']), 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(7);

        $pdf->Cell(120,7, $Partner['address'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(5, 7, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(25, 7, 'Po No :', 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(45, 7, $SaleOrder['po_no'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->Ln(7);
        $pdf->Cell(120,7, '', 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(5, 7, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(25, 7, 'Po Date :', 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(45, 7, stdDate($SaleOrder['po_date']), 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(7);
        $pdf->Cell(25, 7, 'Customer Unit :', 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(95, 7, $CustomerUnit['customer_unit'], 'B', false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->Ln(10);

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(8, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(55, 7, 'Product', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(17, 7, 'Qty', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(17, 7, 'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(17, 7, 'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(18, 7, 'Tax %', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7, 'Tax Amt', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(23, 7, 'Net Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');

        $sr = 0;
        $pdf->Ln(0);

        $Amount = 0;
        $taxAmount = 0;
        $NetAmount = 0;

        $pdf->SetFont('helvetica', '', 8);
        foreach($SaleOrderDetails as $detail) {

            //$Unit = $this->model['unit']->getRow(array('unit_id' => $detail['unit_id']));

            $sr++;
            $pdf->Ln(7);
            $pdf->Cell(8, 7, $sr, 'L',  false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(55, 7, html_entity_decode($detail['description']), 'L', false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(17, 7, $detail['qty'], 'L', false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(17, 7, $detail['unit'], 'L', false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(17, 7, $detail['rate'], 'L', false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, number_format($detail['amount'],2), 'L', false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(18, 7, number_format($detail['tax_percent'],2), 'L', false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, number_format($detail['tax_amount'],2), 'L', false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(23, 7, number_format($detail['net_amount'],2), 'L,R', false, 'R', 0, '', 0, false, 'M', 'M');
            $Amount+=$detail['amount'];
            $taxAmount+=$detail['tax_amount'];
            $NetAmount+=$detail['net_amount'];
        }

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        for ($i = $y; $i <= 200; $i++) {

            $pdf->Ln(1);
            $pdf->Cell(8, 8,'', 'L', false, 'C', 0, '', 1);
            $pdf->Cell(55, 8, '', 'L', false, 'L', 0, '', 1);
            $pdf->Cell(17, 8, '', 'L', false, 'R', 0, '', 1);
            $pdf->Cell(17, 8, '', 'L', false, 'C', 0, '', 1);
            $pdf->Cell(17, 8, '', 'L', false, 'R', 0, '', 1);
            $pdf->Cell(20, 8, '', 'L', false, 'R', 0, '', 1);
            $pdf->Cell(18, 8, '', 'L', false, 'C', 0, '', 1);
            $pdf->Cell(20, 8, '', 'L', false, 'R', 0, '', 1);
            $pdf->Cell(23, 8, '', 'L,R', false, 'R', 0, '', 1);
            $y =$i;
        }
        $pdf->Ln(-1);
        $pdf->Ln(5);
        $pdf->Cell(195, 8, '', 'B', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->setXY($x,$y);
        $pdf->Ln(13);
        $pdf->Cell(114, 7, '', 1, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7, number_format($Amount,2), 1, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(18, 7, '', 1, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7, number_format($taxAmount,2), 1, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(23, 7, number_format($NetAmount,2), 1, false, 'R', 0, '', 0, false, 'M', 'M');

        //Close and output PDF document

        $pdf->Output('Sale Order :'.date('YmdHis').'.pdf', 'I');
}
}
class PDF extends TCPDF {

    public $data = array();
    public $term = array();

    //Page header
    public function Header() {
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
            $this->Image($image_file, 5, 5, 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }else
        {
            // Set font
            $this->SetFont('helvetica', 'B', 20);
            $this->Ln(2);
            //Title
            $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        }


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
//        $this->Image($this->data['header_image'], 0, 5, 205, "", "JPG", "", "T", false, 300, "", false, false, 0, false, false, false);
        // $this->Ln(10);
        // $this->SetFont('times', 'B,I', 26);
        // $this->Cell(0, 10, html_entity_decode($this->data['company_name']), 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(20);
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 4, $this->data['report_name'], 0, false, 'R', 0, '', 0, false, 'M', 'M');

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
    }


}

?>