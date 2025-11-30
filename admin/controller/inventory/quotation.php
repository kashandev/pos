<?php

class ControllerInventoryQuotation extends HController {
    protected $document_type_id = 38;

    protected function getAlias() {
        return 'inventory/quotation';
    }

    protected function getPrimaryKey() {
        return 'quotation_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }


    public function getAjaxLists() {
        $lang = $this->load->language('inventory/quotation');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action', 'document_date', 'document_identity', 'partner_name', 'created_at', 'check_box');

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
                'text' => $lang['print_with_header'],
                'target' => '_blank',
                'href' => $this->url->link($this->getAlias() . '/printDocumentHeaderWise', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
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

        $this->model['terms'] = $this->load->model('common/terms');
        $this->data['terms'] = $this->model['terms']->getRows();
        $this->model['product'] = $this->load->model('inventory/product');
        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']),array('name'));
        $this->model['unit'] = $this->load->model('inventory/unit');
        $this->data['units'] = $this->model['unit']->getRows(array('company_id' => $this->session->data['company_id']));
        $arrUnits = $this->model['unit']->getArrays('unit_id','name',array('company_id' => $this->session->data['company_id']));
        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $this->data['warehouses'] = $this->model['warehouse']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));
        $this->data['arrWarehouses'] = json_encode($this->data['warehouses']);
        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";

        $this->data['partner_types'] = $this->session->data['partner_types'];
        $this->data['partner_type_id'] = 2;

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partners'] = $this->model['partner']->getRows(array('company_id' => $this->session->data['company_id'], 'partner_type_id' => 2, 'company_branch_id' => $this->session->data['company_branch_id']));
        
        $this->model['stock'] = $this->load->model('common/stock_ledger');
        
        $this->model['salesman'] = $this->load->model('setup/salesman');
        $this->data['salesmans'] = $this->model['salesman']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['document_date'] = stdDate();
        if (isset($this->request->get['quotation_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                if ($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                } elseif($field=='customer_date') {
                    $this->data[$field] = stdDateTime($value);
                }elseif($field=='due_date') {
                    $this->data[$field] = stdDate($value);
                }elseif($field=='term_id' && !empty($value)) {
                    $this->data[$field] = json_decode($value);
                } else {
                    $this->data[$field] = $value;
                }
            }           
            $this->model['quotation_detail'] = $this->load->model('inventory/quotation_detail');
            $rows = $this->model['quotation_detail']->getRows(array('quotation_id' => $this->request->get['quotation_id']),array('sort_order asc'));            
            foreach($rows as $row_no => $row) {
                $filter = array(
                    'company_id' => $this->session->data['company_id'],
                    'company_branch_id' => $this->session->data['company_branch_id'],
                    'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                    'product_id' => $row['product_id'],
                );
                $stock = $this->model['stock']->getStock($filter);
                $this->data['quotation_details'][$row_no] = $row;
                $this->data['quotation_details'][$row_no]['unit'] = $arrUnits[$row['unit_id']];
                $this->data['quotation_details'][$row_no]['stock_qty'] = $stock['stock_qty'];
            }
        }

        $this->data['href_get_product_json'] = $this->url->link($this->getAlias() . '/getProductJson', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['restrict_out_of_stock'] = $this->session->data['restrict_out_of_stock'];
        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print'] = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print_header_wise'] = $this->url->link($this->getAlias() . '/printDocumentHeaderWise', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_get_excel_figures'] = $this->url->link($this->getAlias() . '/ExcelFigures', 'token=' . $this->session->data['token']. '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['strValidation'] = "{
            'rules': {
                'document_date': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'partner_id': {'required': true},
                'total_qty': {'required': true, 'min':1},
            },
            messages: {
            document_date:{
                remote: 'Invalid Date'
            }}
        }";

        $this->response->setOutput($this->render());
    }

    public function getProductJson() {
        $search = $this->request->post['q'];
        $page = $this->request->post['page'];

        $this->model['product'] = $this->load->model('inventory/product');
        $rows = $this->model['product']->getProductJson($search, $page);

        echo json_encode($rows);
    }

    protected function insertData($data) {

//d($data,true);
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
        $data['base_amount'] = $data['total_amount'] * $data['conversion_rate'];

        if($data['customer_date'] != '') {
            $data['customer_date'] = MySqlDate($data['customer_date']);
        } else {
            $data['customer_date'] = NULL;
        }
        if($data['due_date'] != '') {
            $data['due_date'] = MySqlDate($data['due_date']);
        } else {
            $data['due_date'] = NULL;
        }
        if($data['term_id'] != '') {
            $data['term_id'] = json_encode($data['term_id']);
        } else {
            $data['term_id'] = NULL;
        }

        $quotation_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $data['document_id'] = $quotation_id;

        $this->model['quotation_detail'] = $this->load->model('inventory/quotation_detail');
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

        $gl_data = array();
        $stock_ledger = array();
        foreach ($data['quotation_details'] as $sort_order => $detail) {
            $detail['quotation_id'] = $quotation_id;
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['sort_order'] = $sort_order;
            $detail['description'] = htmlspecialchars($detail['description']);
            $quotation_detail_id=$this->model['quotation_detail']->add($this->getAlias(), $detail);

        }

        return $quotation_id;
    }

    protected function insertRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias().'/update', 'token=' . $this->session->data['token'] . '&quotation_id=' . $id, 'SSL'));
    }

    protected function updateData($primary_key, $data) {
        $quotation_id = $primary_key;
        $data['document_date'] = MySqlDate($data['document_date']);

        if($data['customer_date'] != '') {
            $data['customer_date'] = MySqlDate($data['customer_date']);
        } else {
            $data['customer_date'] = NULL;
        }
        if($data['due_date'] != '') {
            $data['due_date'] = MySqlDate($data['due_date']);
        } else {
            $data['due_date'] = NULL;
        }

        if($data['term_id'] != '') {
            $data['term_id'] = json_encode($data['term_id']);
        } else {
            $data['term_id'] = NULL;
        }


        $this->model['quotation'] = $this->load->model('inventory/quotation');
        $this->model['quotation_detail'] = $this->load->model('inventory/quotation_detail');
        $this->model['document'] = $this->load->model('common/document');

        $this->model['quotation']->edit($this->getAlias(), $primary_key, $data);
        $this->model['quotation_detail']->deleteBulk($this->getAlias(), array('quotation_id' => $quotation_id));
        $this->model['document']->deleteBulk($this->getAlias(), array('document_id' => $quotation_id));

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
        foreach ($data['quotation_details'] as $sort_order => $detail) {
            $detail['quotation_id'] = $quotation_id;
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['sort_order'] = $sort_order;
            $detail['description'] = htmlspecialchars($detail['description']);
            $quotation_detail_id=$this->model['quotation_detail']->add($this->getAlias(), $detail);

        }


        return $quotation_id;
    }

    protected function updateRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias().'/update', 'token=' . $this->session->data['token'] . '&quotation_id=' . $id, 'SSL'));
    }

    protected function validateDelete($id='') {
        if (!$this->user->hasPermission('delete', $this->getAlias())) {
            $this->error['warning'] = $this->language->get('error_permission_delete');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    protected function deleteData($primary_key) {
        $this->model['quotation_detail'] = $this->load->model('inventory/quotation_detail');
        $this->model['quotation_detail']->deleteBulk($this->getAlias(),array('quotation_id' => $primary_key));

        $this->model['document'] = $this->load->model('common/document');
        $this->model['document']->deleteBulk($this->getAlias(), array('document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }


    public function ajaxValidateForm() {
        $post  = $this->request->post;
        $lang = $this->load->language('inventory/quotation');
        $error = array();

        if($post['voucher_date'] == '') {
            $error[] = $lang['error_voucher_date'];
        }

        if($post['supplier_id'] == '') {
            $error[] = $lang['error_supplier'];
        }

        $details = $post['quotation_details'];
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


    public  function ExcelFigures()
    {
        $QuotationId = $this->request->get['quotation_id'];
//        d($QuotationId,true);
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $lang = $this->load->language($this->getAlias());
        $QuotationId = $this->request->get['quotation_id'];
        $post = $this->request->post;
        $session = $this->session->data;

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->model['quotation'] = $this->load->model('inventory/quotation');
        $this->model['unit'] = $this->load->model('inventory/unit');
        $this->model['quotation_detail'] = $this->load->model('inventory/quotation_detail');


        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $Quotation = $this->model['quotation']->getRow(array('quotation_id' => $QuotationId));
        $QuotationDetails = $this->model['quotation_detail']->getRows(array('quotation_id' => $QuotationId),array('sort_order asc'));
        $Partner = $this->model['partner']->getRow(array('partner_type_id' => $Quotation['partner_type_id'],'partner_id' => $Quotation['partner_id']));


        $this->model['terms'] = $this->load->model('common/terms');
        if($Quotation['term_id']) {
            $rows = json_decode($Quotation['term_id'],true);
            foreach($rows as $row1 => $row) {
                $Term = $this->model['terms']->getRow(array('term_id' => $row['term_id']));
                $arrRows[] = array(
                    'term' => $Term['term']
                );
            }
        }

        $salesTax = '';
        foreach($QuotationDetails as $detail) {
            $salesTax = $detail['tax_percent'];
        }


        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $rowCount = 1;

        $objPHPExcel->getActiveSheet()->mergeCells('F'.$rowCount.':H'.$rowCount);
        $objPHPExcel->getActiveSheet()->getStyle('F'.$rowCount)->getAlignment()->applyFromArray(
            array('font' => array(
                'bold' => true,
                'size' => 14,
            ),'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
        );
        $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowCount,'NTN-1135326-7');
        $rowCount++;
        $objPHPExcel->getActiveSheet()->mergeCells('F'.$rowCount.':H'.$rowCount);
        $objPHPExcel->getActiveSheet()->getStyle('F'.$rowCount)->getAlignment()->applyFromArray(
            array('font' => array(
                'bold' => true,
                'size' => 14,
            ),'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
        );

        $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowCount,'GST-12-22-9999-050-91');
        $rowCount++;
        $objPHPExcel->getActiveSheet()->mergeCells('F'.$rowCount.':H'.$rowCount);
        $objPHPExcel->getActiveSheet()->getStyle('F'.$rowCount)->getAlignment()->applyFromArray(
            array('font' => array(
                'bold' => true,
                'size' => 18,
            ),'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,)
        );

        $objPHPExcel->getActiveSheet()->setCellValue('F'.$rowCount,'Quotation')->getStyle('F'.$rowCount)->getFont()->setBold( true )->setSize(14);
        $rowCount++;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':C'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,$Partner['name']);
        $objPHPExcel->getActiveSheet()->mergeCells('D'.$rowCount.':F'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowCount,'Quotation No : ');
        $objPHPExcel->getActiveSheet()->mergeCells('G'.$rowCount.':H'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowCount,$Quotation['document_identity']);
        $rowCount++;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':C'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,'');
        $objPHPExcel->getActiveSheet()->mergeCells('D'.$rowCount.':F'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowCount,'Quotation Date : ');
        $objPHPExcel->getActiveSheet()->mergeCells('G'.$rowCount.':H'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowCount,stdDate($Quotation['document_date']));
        $rowCount++;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':C'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,$Partner['address']);
        $objPHPExcel->getActiveSheet()->mergeCells('D'.$rowCount.':F'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowCount,'Ref No :');
        $objPHPExcel->getActiveSheet()->mergeCells('G'.$rowCount.':H'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowCount,$Quotation['customer_ref_no']);
        $rowCount++;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':C'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,'');
        $objPHPExcel->getActiveSheet()->mergeCells('D'.$rowCount.':F'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$rowCount,'Our Ref No :');
        $objPHPExcel->getActiveSheet()->mergeCells('G'.$rowCount.':H'.$rowCount);
        $objPHPExcel->getActiveSheet()->setCellValue('G'.$rowCount,$Quotation['ref_no']);
        $rowCount++;


        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Sr.')->getStyle('A'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Description')->getStyle('B'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Qty')->getStyle('C'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Unit')->getStyle('D'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Price.')->getStyle('E'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'Amt Excl Gst')->getStyle('F'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'GST ('.number_format($salesTax,0).'%)')->getStyle('G'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Amt Incl GST')->getStyle('H'.$rowCount)->getFont()->setBold( true );

        $rowCount++;
        $sr = 0;

        foreach($QuotationDetails as $detail) {

            $sr++;
            $Unit = $this->model['unit']->getRow(array('unit_id' => $detail['unit_id']));

            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $sr);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, html_entity_decode($detail['description']));
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $detail['qty']);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $Unit['name']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, number_format($detail['rate'],2,".",""));
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, number_format($detail['amount'],2,".",""));
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, number_format($detail['tax_amount'],2,".",""));
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, number_format($detail['net_amount'],2,".",""));
            $rowCount++;

        }

        $rowCount += 5;

        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Total');
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, number_format($Quotation['item_amount'],2,".",""));
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, number_format($Quotation['item_tax'],2,".",""));
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, number_format($Quotation['item_total'],2,".",""));

        $rowCount++;
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Terms & Condition');

        //$rowCount++;
        //$objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':H'.$rowCount+7);
        //$objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount,$Quotation['term_desc']);

        foreach($arrRows as $r => $row) {
            foreach($row as $term ) {
                $rowCount++;
                $objPHPExcel->getActiveSheet()->mergeCells('A'.$rowCount.':H'.$rowCount);
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, '* '.$term);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Quotation.xlsx"');
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
        $QuotationId = $this->request->get['quotation_id'];
        $post = $this->request->post;
        $session = $this->session->data;

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->model['quotation'] = $this->load->model('inventory/quotation');
        $this->model['unit'] = $this->load->model('inventory/unit');
        $this->model['quotation_detail'] = $this->load->model('inventory/quotation_detail');


        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $Quotation = $this->model['quotation']->getRow(array('quotation_id' => $QuotationId));
        $QuotationDetails = $this->model['quotation_detail']->getRows(array('quotation_id' => $QuotationId),array('sort_order asc'));
        $Partner = $this->model['partner']->getRow(array('partner_type_id' => $Quotation['partner_type_id'],'partner_id' => $Quotation['partner_id']));


        $this->model['terms'] = $this->load->model('common/terms');
        if($Quotation['term_id']) {
            $rows = json_decode($Quotation['term_id'],true);
            foreach($rows as $row1 => $row) {
                $Term = $this->model['terms']->getRow(array('term_id' => $row['term_id']));
                $arrRows[] = array(
                    'term' => $Term['term']
                );
            }
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


        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Fahad Siddiqui');
        $pdf->SetTitle('Quotation Voucher');
        $pdf->SetSubject('Quotation Voucher');

        //Set Header
        $pdf->data = array(
            'company_name' => $branch['name'],
            'company_address' => $branch['address'],
            'company_phone' => $branch['phone_no'],
            'report_name' => 'Quotation',
            'company_logo' => $session['company_image'],
            'status' => 'Without Header',
            'term_desc' => $Quotation['term_desc'],
            'company_header_print' => $company_header_print,
            'company_footer_print' => $company_footer_print,
        );

        $pdf->term = array(
            $arrRows
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(2, 28, 2);
        $pdf->SetHeaderMargin(2);
        $pdf->SetFooterMargin(2);

        // set auto page breaks
        //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font
        $pdf->SetFont('helvetica', 'B', 10);

        // add a page
        $pdf->AddPage();

        $pdf->SetTextColor(0,0,0);
        // $pdf->Ln(5);

        $salesTax = '';
        foreach($QuotationDetails as $detail) {
            $salesTax = $detail['tax_percent'];
        }
        // $pdf->SetFont('helvetica', '', 9);
        // $pdf->Cell(0, 6, 'NTN-1135326-7', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        // $pdf->Ln(6);
        // $pdf->Cell(0, 6, 'GST-12-22-9999-050-91', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        // $pdf->Ln(6);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 4, $pdf->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 10);

        
        $pdf->Ln(10);
        $pdf->Cell(120,7, "Attention:\n".html_entity_decode($Quotation['attn']), 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(36, 7, 'Quotation No :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(50, 7, $Quotation['document_identity'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        
        $pdf->Ln(7);
        $pdf->Cell(120,7, "M/s:\n".html_entity_decode($Partner['name']), 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(36, 7, 'Date :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(50, 7, stdDate($Quotation['document_date']), 0, false, 'L', 0, '', 0, false, 'M', 'M');
        
        $pdf->Ln(7);
        $address = splitString($Partner['address'], 55);
        $pdf->Cell(120,7, $address[0], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(36, 7, 'Customer Ref :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(50, 7, $Quotation['customer_ref_no'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        
        $pdf->Ln(7);
        $pdf->Cell(120,7, $address[1], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(36, 7, 'Dated :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(50, 7, ($Quotation['customer_date']!='' && $Quotation['customer_date'] != '0000-00-00') ? stdDate($Quotation['customer_date']) : '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        
        $pdf->Ln(7);
        $pdf->Cell(120,7, $address[2], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(36, 7, 'Due On :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(50, 7, ($Quotation['due_date']!='' && $Quotation['due_date'] != '0000-00-00') ? stdDate($Quotation['due_date']) : '', 0, false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->Ln(10);

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(6, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(100, 7, 'Description', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(15, 7,  'Qty', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(13, 7,  'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(16, 7,  'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7,  'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(17, 7,  'Tax Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7,  'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $sr = 0;

        $Amount = 0;
        $WhAmount = 0;
        $OtAmount = 0;
        $NetAmount = 0;
        
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Ln(6);
        foreach($QuotationDetails as $detail) {

            $Unit = $this->model['unit']->getRow(array('unit_id' => $detail['unit_id']));

            $description = $detail['description'];
            $sr++;

            if(strlen($description) <= 80) {
                
                $pdf->Cell(6, 7, $sr, 'L',  false, 'C', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(100, 7, html_entity_decode($description), 'L', false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(15, 7, $detail['qty'], 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(13, 7, $Unit['name'], 'L', false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(16, 7, number_format($detail['rate'],2), 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(20, 7, number_format($detail['amount'],2), 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(17, 7, number_format($detail['tax_amount'],2), 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(20, 7, number_format($detail['net_amount'],2), 'L,R', false, 'R', 0, '', 0, false, 'M', 'M');
                $pdf->Ln(5);
                
            } else {
                
                $arrDesc = splitString($description, 80);
                foreach($arrDesc as $index => $remark){
                    
                    if($index==0){
                        
                        $pdf->Cell(6,   4, $sr, 'L',  false, 'C', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(100, 4, html_entity_decode($remark), 'L', false, 'L', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(15,  4, $detail['qty'], 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(13,  4, $Unit['name'], 'L', false, 'L', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(16,  4, number_format($detail['rate'],2), 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(20,  4, number_format($detail['amount'],2), 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(17,  4, number_format($detail['tax_amount'],2), 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(20,  4, number_format($detail['net_amount'],2), 'L,R', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Ln(4);

                    } else if($index<=count($arrDesc)-1){
                        $pdf->Cell(6,   4, '', 'L',  false, 'C', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(100, 4, html_entity_decode($remark), 'L', false, 'L', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(15,  4, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(13,  4, '', 'L', false, 'L', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(16,  4, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(20,  4, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(17,  4, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(20,  4, '', 'L,R', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Ln(4);

                    } else {
                        $pdf->Cell(6,   4, '', 'L',  false, 'C', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(100, 4, html_entity_decode($remark), 'L', false, 'L', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(15,  4, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(13,  4, '', 'L', false, 'L', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(16,  4, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(20,  4, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(17,  4, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(20,  4, '', 'L,R', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Ln(4);
                    }

                    
                }
                
            }

        }

        $pdf->Ln(-6);
        $pdf->Ln(1);
        
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);        

        $pdf->Ln(6);
        $pdf->Cell(207, 4, '', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
        
        $pdf->Ln(1.5);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(187, 7, 'Grand Total: ', 'TLB', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7, number_format($Quotation['item_total'],2), 'TRB', false, 'C', 0, '', 0, false, 'M', 'M');
        
        
        $this->model['salesman'] = $this->load->model('setup/salesman');
        $Salesman = $this->model['salesman']->getRow(array('salesman_id' => $Quotation['salesman_id']));
        
        $pdf->Ln(9);
        $pdf->Cell(35, 7, 'Delivery : ', 'LTR', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(110, 7, $Quotation['delivery'], 'LTR', false, 'L', 0, '', 0, false, 'M', 'M');

        $salesmanName = splitString($Salesman['name'], 35);

        $pdf->Cell(62, 7, $salesmanName[0], 'TLR', false, 'C', 0, '', 0, false, 'M', 'M');
        
        $pdf->Ln(7);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(35, 7, 'Validity : ', 'LR', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(110, 7, $Quotation['validity'], 'LR', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(62, 7, $salesmanName[1], 'LR', false, 'C', 0, '', 0, false, 'M', 'M');
        
        $pdf->Ln(7);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(35, 7, 'Payment : ', 'LR', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(110, 7, $Quotation['payment'], 'LR', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(62, 7, $Salesman['mobile'], 'LR', false, 'C', 0, '', 0, false, 'M', 'M');
        
        $pdf->Ln(7);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(35, 7, 'Enclosure : ', 'LR', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(110, 7, $Quotation['enclosure'], 'LR', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(62, 7, $Salesman['email'], 'LR', false, 'C', 0, '', 0, false, 'M', 'M');
        
        $pdf->Ln(7);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(35, 7, 'Other : ', 'LBR', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(110, 7, $Quotation['other'], 'LBR', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(62, 7, '', 'LRB', false, 'C', 0, '', 0, false, 'M', 'M');

        
        $pdf->Ln(7);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);

        $pdf->SetFont('helvetica', '', 8);
        $pdf->MultiCell(0, 5, $Quotation['term_desc'], 0, 'J', 0, 2, $x, $y, true);

//        $pdf->Ln(15);
//        $pdf->Cell(0, 6, 'Authorized Signature.', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $data= $Quotation;
        //Close and output PDF document
        $pdf->Output('Quotation :'.date('YmdHis').'.pdf', 'I');
    }

    public function printDocumentHeaderWise() {

        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $lang = $this->load->language($this->getAlias());
        $QuotationId = $this->request->get['quotation_id'];
        $post = $this->request->post;
        $session = $this->session->data;

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->model['quotation'] = $this->load->model('inventory/quotation');
        $this->model['unit'] = $this->load->model('inventory/unit');
        $this->model['quotation_detail'] = $this->load->model('inventory/quotation_detail');


        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $Quotation = $this->model['quotation']->getRow(array('quotation_id' => $QuotationId));
        $QuotationDetails = $this->model['quotation_detail']->getRows(array('quotation_id' => $QuotationId),array('sort_order asc'));
        $Partner = $this->model['partner']->getRow(array('partner_type_id' => $Quotation['partner_type_id'],'partner_id' => $Quotation['partner_id']));

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


        $this->model['terms'] = $this->load->model('common/terms');
        if($Quotation['term_id']) {
            $rows = json_decode($Quotation['term_id'],true);
            foreach($rows as $row1 => $row) {
                $Term = $this->model['terms']->getRow(array('term_id' => $row['term_id']));
                $arrRows[] = array(
                    'term' => $Term['term']
                );
            }
        }


        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Fahad Siddiqui');
        $pdf->SetTitle('Quotation Voucher');
        $pdf->SetSubject('Quotation Voucher');


        $salesTax = '';
        foreach($QuotationDetails as $detail) {
            $salesTax = $detail['tax_percent'];
        }

        //Set Header
        $pdf->data = array(
            'company_name' => $branch['name'],
            'company_address' => $branch['address'],
            'company_phone' => $branch['phone_no'],
            'report_name' => 'Quotation',
            'company_logo' => $session['company_image'],
            'header_image' => HTTP_IMAGE.'header.jpg',
            'footer_image' => HTTP_IMAGE.'footer.jpg',
            'status' => 'With Header',
            'term_desc' => $Quotation['term_desc'],
            'ref_no' => $Quotation['ref_no'],
            'sales_tax' => $salesTax,
            'company_header_print' => $company_header_print,
            'company_footer_print' => $company_footer_print,
            'attn' => $Quotation['attn'],
            'document_identity' => $Quotation['document_identity'],
            'document_date' => $Quotation['document_date'],
            'customer_ref_no' => $Quotation['customer_ref_no'],
            'customer_date' => $Quotation['customer_date'],
            'due_date' => $Quotation['due_date'],
            'partner_name' => $Partner['name'],
            'partner_address' => $Partner['address'],
        );

        $pdf->term = array(
            $arrRows
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(2, 89, 2);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(50);

        // set auto page breaks
        //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font
        $pdf->SetFont('helvetica', 'B', 10);

        // add a page
        $pdf->AddPage();

        $pdf->SetTextColor(0,0,0);
        $sr = 0;

        $pdf->SetFont('helvetica', '', 7);
        foreach($QuotationDetails as $detail) {

            $Unit = $this->model['unit']->getRow(array('unit_id' => $detail['unit_id']));

            $description = $detail['description'];
            $sr++;

            if(strlen($description) <= 80) {
                
                $pdf->Cell(6, 7, $sr, 'L',  false, 'C', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(100, 7, html_entity_decode($description), 'L', false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(15, 7, $detail['qty'], 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(13, 7, $Unit['name'], 'L', false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(16, 7, number_format($detail['rate'],2), 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(20, 7, number_format($detail['amount'],2), 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(17, 7, number_format($detail['tax_amount'],2), 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(20, 7, number_format($detail['net_amount'],2), 'L,R', false, 'R', 0, '', 0, false, 'M', 'M');
                $pdf->Ln(5);
                
            } else {
                
                $arrDesc = splitString($description, 80);
                foreach($arrDesc as $index => $remark){
                    
                    if($index==0){
                        
                        $pdf->Cell(6,   4, $sr, 'L',  false, 'C', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(100, 4, html_entity_decode($remark), 'L', false, 'L', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(15,  4, $detail['qty'], 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(13,  4, $Unit['name'], 'L', false, 'L', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(16,  4, number_format($detail['rate'],2), 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(20,  4, number_format($detail['amount'],2), 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(17,  4, number_format($detail['tax_amount'],2), 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(20,  4, number_format($detail['net_amount'],2), 'L,R', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Ln(4);

                    } else if($index<=count($arrDesc)-1){
                        $pdf->Cell(6,   4, '', 'L',  false, 'C', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(100, 4, html_entity_decode($remark), 'L', false, 'L', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(15,  4, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(13,  4, '', 'L', false, 'L', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(16,  4, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(20,  4, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(17,  4, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(20,  4, '', 'L,R', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Ln(4);

                    } else {
                        $pdf->Cell(6,   4, '', 'L',  false, 'C', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(100, 4, html_entity_decode($remark), 'L', false, 'L', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(15,  4, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(13,  4, '', 'L', false, 'L', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(16,  4, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(20,  4, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(17,  4, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Cell(20,  4, '', 'L,R', false, 'R', 0, '', 0, false, 'M', 'M');
                        $pdf->Ln(4);
                    }

                    
                }
                
            }

        }

        
        $pdf->Ln(-1);

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);
        
        // for ($i = $y; $i <= 190; $i++) {
            //     $pdf->Cell(6, 1, '', 'L',  false, 'C', 0, '', 0, false, 'M', 'M');
            //     $pdf->Cell(100, 1,'', 'L', false, 'L', 0, '', 0, false, 'M', 'M');
            //     $pdf->Cell(15, 1, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
            //     $pdf->Cell(13, 1, '', 'L', false, 'L', 0, '', 0, false, 'M', 'M');
        //     $pdf->Cell(16, 1, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
        //     $pdf->Cell(20, 1, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
        //     $pdf->Cell(17, 1, '', 'L', false, 'R', 0, '', 0, false, 'M', 'M');
        //     $pdf->Cell(20, 1, '', 'LR', false, 'R', 0, '', 0, false, 'M', 'M');
        //     $y =$i;
        //     $pdf->Ln(1);
        // }
        $pdf->Ln(1);
        $pdf->Cell(207, 4, '', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
        
        $pdf->Ln(1.5);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(187, 7, 'Grand Total: ', 'TLB', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7, number_format($Quotation['item_total'],2), 'TRB', false, 'C', 0, '', 0, false, 'M', 'M');
        
        
        $this->model['salesman'] = $this->load->model('setup/salesman');
        $Salesman = $this->model['salesman']->getRow(array('salesman_id' => $Quotation['salesman_id']));
        
        $pdf->Ln(9);
        $pdf->Cell(35, 7, 'Delivery : ', 'LTR', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(110, 7, $Quotation['delivery'], 'LTR', false, 'L', 0, '', 0, false, 'M', 'M');

        $salesmanName = splitString($Salesman['name'], 35);

        $pdf->Cell(62, 7, $salesmanName[0], 'TLR', false, 'C', 0, '', 0, false, 'M', 'M');
        
        $pdf->Ln(7);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(35, 7, 'Validity : ', 'LR', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(110, 7, $Quotation['validity'], 'LR', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(62, 7, $salesmanName[1], 'LR', false, 'C', 0, '', 0, false, 'M', 'M');
        
        $pdf->Ln(7);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(35, 7, 'Payment : ', 'LR', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(110, 7, $Quotation['payment'], 'LR', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(62, 7, $Salesman['mobile'], 'LR', false, 'C', 0, '', 0, false, 'M', 'M');
        
        $pdf->Ln(7);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(35, 7, 'Enclosure : ', 'LR', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(110, 7, $Quotation['enclosure'], 'LR', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(62, 7, $Salesman['email'], 'LR', false, 'C', 0, '', 0, false, 'M', 'M');
        
        $pdf->Ln(7);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(35, 7, 'Other : ', 'LBR', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(110, 7, $Quotation['other'], 'LBR', false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(62, 7, '', 'LRB', false, 'C', 0, '', 0, false, 'M', 'M');

        
        $pdf->Ln(7);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);

        $pdf->SetFont('helvetica', '', 8);
        $pdf->MultiCell(0, 5, $Quotation['term_desc'], 0, 'J', 0, 2, $x, $y, true);

//        $pdf->Ln(15);
//        $pdf->Cell(0, 6, 'Authorized Signature.', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // $data= $Quotation;
        //Close and output PDF document
        $pdf->Output('Quotation :'.date('YmdHis').'.pdf', 'I');
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
        if($this->data['status'] == "With Header")
        {

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

            // $this->Image($this->data['header_image'], 0, 5, 205, "", "JPG", "", "T", false, 300, "", false, false, 0, false, false, false);

            $this->Ln(30);
            // $this->SetFont('helvetica', '', 9);
            // $this->Cell(0, 6, 'NTN-1135326-7', 0, false, 'R', 0, '', 0, false, 'M', 'M');
            // $this->Ln(6);
            // $this->Cell(0, 6, 'GST-12-22-9999-050-91', 0, false, 'R', 0, '', 0, false, 'M', 'M');
            // $this->Ln(6);
            $this->SetFont('helvetica', 'B', 12);
            $this->Cell(0, 4, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->SetFont('helvetica', '', 10);
    
            
            $this->Ln(10);
            $this->Cell(120,7, "Attention:\n".html_entity_decode($this->data['attn']), 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(36, 7, 'Quotation No :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(50, 7, $this->data['document_identity'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
            
            $this->Ln(7);
            $this->Cell(120,7, "M/s:\n".html_entity_decode($this->data['partner_name']), 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(36, 7, 'Date :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(50, 7, stdDate($this->data['document_date']), 0, false, 'L', 0, '', 0, false, 'M', 'M');
            
            $this->Ln(7);
            $address = splitString($this->data['partner_address'], 55);
            $this->Cell(120,7, $address[0], 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(36, 7, 'Customer Ref :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(50, 7, $this->data['customer_ref_no'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
            
            $this->Ln(7);
            $this->Cell(120,7, $address[1], 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(36, 7, 'Dated :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(50, 7, ($this->data['customer_date']!='' && $this->data['customer_date'] != '0000-00-00') ? stdDate($this->data['customer_date']) : '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
            
            $this->Ln(7);
            $this->Cell(120,7, $address[2], 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(36, 7, 'Due On :', 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Cell(50, 7, ($this->data['due_date']!='' && $this->data['due_date'] != '0000-00-00') ? stdDate($this->data['due_date']) : '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Ln(10);

            $this->SetFont('helvetica', 'B', 8);
            $this->Cell(6, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(100, 7, 'Description', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(15, 7,  'Qty', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(13, 7,  'Unit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(16, 7,  'Rate', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7,  'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(17, 7,  'Tax Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Cell(20, 7,  'Total Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');


        }

    }

    // Page footer
    public function Footer() {
        
        $this->SetY(-25);
        $y = $this->GetY();


//            foreach($rows as $r => $row) {
//                foreach($row as $term ) {
//                    $this->Cell(0, 5, '* '.$term['term'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
//                    $this->Ln(5);
//                }
//            }
//                            $this->MultiCell(0, 15, $this->data['term_desc']);
//        $this->MultiCell(0, 5, $this->data['term_desc'], 0, 'J', 0, 2, 5, 232, true);


        $this->SetFont('helvetica', 'B', 11);
//        $this->Ln(5);
//        $this->Cell(0, 5, 'In case of any clarification or query , please feel free to contact us.', 0, false, 'L', 0, '', 0, false, 'M', 'M');
//        $this->Ln(5);


        if($this->data['status'] == "With Header")
        {

            if($this->data['company_footer_print'] != '') {
                $image_file = DIR_IMAGE.$this->data['company_footer_print'];
                // $this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
                $this->Image($image_file, 5, ($y-10), 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
            }
    
            $rows = $this->term;
            $this->Ln(6);

            // $this->Image($this->data['footer_image'], 0, 250, 205, "", "JPG", "", "T", false, 300, "", false, false, 0, false, false, false);

        }
    }


}

?>