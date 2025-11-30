<?php

class ControllerGlAdvanceReceipt extends HController {

    protected $document_type_id = 15;

    protected function getAlias() {
        return 'gl/advance_receipt';
    }

    protected function getPrimaryKey() {
        return 'advance_receipt_id';
    }
    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');

        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {

        $lang = $this->load->language('gl/advance_receipt');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());

        $data = array();
        $aColumns = array('action','document_date', 'document_identity' ,'partner_type','partner_name', 'remarks','amount','created_at', 'check_box');

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
            /*
            $actions[] = array(
                'text' => $lang['print'],
                'target' => '_blank',
                'href' => $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                'btn_class' => 'btn btn-info btn-xs',
                'class' => 'fa fa-print'
            );
            */
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
                $strAction .= '<a '.(isset($action['btn_class'])?'class="'.$action['btn_class'].'"':'').' href="' . $action['href'] . '" data-toggle="tooltip" title="' . $action['text'] . '" ' . (isset($action['click']) ? 'onClick="' . $action['click'] . '"' : '') . '>';
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
                } elseif ($aColumns[$i] == 'document_date') {
                    $row[] = stdDate($aRow['stdDate']);
                } elseif ($aColumns[$i] == 'check_box') {
                    if($aRow['is_post']==0) {
                        $row[] = '<input type="checkbox" name="selected[]" value="' . $aRow[$this->getPrimaryKey()] . '" />';
                    } else {
                        $row[] = '';
                    }
                }else {
                    $row[] = $aRow[$aColumns[$i]];
                }

            }
            $output['aaData'][] = $row;
        }

        echo json_encode($output);
    }

    protected function getForm() {
        parent::getForm();

        $this->data['document_identity'] = $this->data['lang']['auto'];
        $this->data['document_date'] = stdDate();
        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";

        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencies'] = $this->model['currency']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['coa'] = $this->load->model('gl/coa_level3');
        $this->model['setting']= $this->load->model('common/setting');
        $bank_accounts = $this->model['setting']->getRows(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'gl',
            'field' => 'cash_account_id',
        ));
        foreach($bank_accounts as $bank_account) {
            $this->data['transaction_accounts'][] = $this->model['coa']->getRow(array('company_id' => $this->session->data['company_id'], 'coa_level3_id' => $bank_account['value']));
        }
        $bank_accounts = $this->model['setting']->getRows(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'gl',
            'field' => 'bank_account_id',
        ));
        foreach($bank_accounts as $bank_account) {
            $this->data['transaction_accounts'][] = $this->model['coa']->getRow(array('company_id' => $this->session->data['company_id'], 'coa_level3_id' => $bank_account['value']));
        }

        $this->data['partner_types'] = $this->session->data['partner_types'];

        if (isset($this->request->get['advance_receipt_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $this->data['document_type_id'] = $this->document_type_id;
            $this->data['document_id'] = $this->request->get['advance_receipt_id'];
            $result = $this->model[$this->getAlias()]->getRow(array('advance_receipt_id' => $this->request->get['advance_receipt_id']));
            //d($result, true);
            foreach($result as $field => $value) {
                if($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                } elseif($field == 'cheque_date') {
                    if($value == '') {
                        $this->data[$field] = '';
                    } else {
                        $this->data[$field] = stdDate($value);
                    }
                } else
                    $this->data[$field] = $value;
            }

            $this->model['ledger'] = $this->load->model('gl/ledger');
            $this->data['ledgers'] = $this->model['ledger']->getRows(array('document_type_id' => $this->document_type_id, 'document_id' => $this->request->get[$this->getPrimaryKey()]),array('sort_order ASC'));

            $this->model['partner'] = $this->load->model('common/partner');
            $this->data['partners'] = $this->model['partner']->getRows(array('partner_type_id' => $result['partner_type_id']));

        }


        $this->data['href_get_document_ledger'] = $this->url->link('common/function/getDocumentLedger', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_partner'] = $this->url->link('common/function/getPartner', 'token=' . $this->session->data['token']);
        $this->data['href_get_bank'] = $this->url->link('gl/advance_payment/getAccounts', 'token=' . $this->session->data['token']);
        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print'] = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['strValidation'] = "{
            'rules': {
                'document_date': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'transaction_account_id': {'required': true},
                'amount': {'required': true},
            },
            messages: {
            document_date:{
                remote: 'Invalid Date'
            }}
        }";

        $this->response->setOutput($this->render());
    }

    protected function insertData($data) {
        $this->model['document_type'] = $this->load->model('common/document_type');
        $this->model['document'] = $this->load->model('common/document');
        //$this->model['mapping_account'] = $this->load->model('gl/mapping_coa');
        //$this->model['advance_receipt_detail'] = $this->load->model('gl/advance_receipt_detail');
        $this->model['ledger'] = $this->load->model('gl/ledger');

        $document = $this->model['document_type']->getNextDocument($this->document_type_id);

        $data['document_date'] = MySqlDate($data['document_date']);
        $data['cheque_date'] = MySqlDate($data['cheque_date']);
        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['document_type_id'] = $this->document_type_id;
        $data['document_prefix'] = $document['document_prefix'];
        $data['document_no'] = $document['document_no'];
        $data['document_identity'] = $document['document_identity'];
        $data['base_amount'] = $data['amount'] * $data['conversion_rate'];

        $advance_receipt_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $data['document_id'] = $advance_receipt_id;

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
            'document_amount' => $data['amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['base_amount'],
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

        $this->model['partner'] = $this->load->model('common/partner');
        $partner = $this->model['partner']->getRow(array('partner_type_id' => $data['partner_type_id'], 'partner_id' => $data['partner_id']));

        $gl_data = array();
        $gl_data[] = array(
            'coa_id' => $partner['advance_account_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_credit' => $data['amount'],
            'document_debit' => 0,
            'base_currency_id' => $data['base_currency_id'],
            'conversion_rate' => $data['conversion_rate'],
            'credit' => $data['amount'] * $data['conversion_rate'],
            'debit' => 0,
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'ref_document_type_id' => $data['document_type_id'],
            'ref_document_identity' => $data['document_identity'],
        );

        $gl_data[] = array(
            'coa_id' => $data['transaction_account_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_credit' => 0,
            'document_debit' => $data['amount'],
            'base_currency_id' => $data['base_currency_id'],
            'conversion_rate' => $data['conversion_rate'],
            'credit' => 0,
            'debit' => $data['amount'] * $data['conversion_rate'],
            //'partner_type_id' => $data['partner_type_id'],
            //'partner_id' => $data['partner_id'],
            //'ref_document_type_id' => $data['document_type_id'],
            //'ref_document_identity' => $data['document_identity'],
        );


        $this->model['gl'] = $this->load->model('gl/ledger');
        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $advance_receipt_id;
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['base_currency_id'] = $data['base_currency_id'];
            $ledger['document_currency_id'] = $data['document_currency_id'];
            $ledger['remarks'] = $data['remarks'];
            $ledger['sort_order'] = $sort_order;

            $this->model['gl']->add($this->getAlias(),$ledger);
        }

        return $advance_receipt_id;
    }

    protected function insertRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias().'/update', 'token=' . $this->session->data['token'] . $url . '&print=1&advance_receipt_id=' . $id, 'SSL'));
    }

    protected function updateData($primary_key, $data) {
        $this->model['document_type'] = $this->load->model('common/document_type');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['mapping_account'] = $this->load->model('gl/mapping_coa');
        $this->model['advance_receipt_detail'] = $this->load->model('gl/advance_receipt_detail');
        $this->model['ledger'] = $this->load->model('gl/ledger');

        $this->model['document']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));

        $data['document_date'] = MySqlDate($data['document_date']);
        $data['base_amount'] = $data['amount'] * $data['conversion_rate'];

        $advance_receipt_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        $data['document_id'] = $advance_receipt_id;

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
            'document_amount' => $data['amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['base_amount'],
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

        $this->model['partner'] = $this->load->model('common/partner');
        $partner = $this->model['partner']->getRow(array('partner_type_id' => $data['partner_type_id'], 'partner_id' => $data['partner_id']));

        $gl_data = array();
        $gl_data[] = array(
            'coa_id' => $partner['advance_account_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_credit' => $data['amount'],
            'document_debit' => 0,
            'base_currency_id' => $data['base_currency_id'],
            'conversion_rate' => $data['conversion_rate'],
            'credit' => $data['amount'] * $data['conversion_rate'],
            'debit' => 0,
            'partner_type_id' => $data['partner_type_id'],
            'partner_id' => $data['partner_id'],
            'ref_document_type_id' => $data['document_type_id'],
            'ref_document_identity' => $data['document_identity'],
        );

        $gl_data[] = array(
            'coa_id' => $data['transaction_account_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_credit' => 0,
            'document_debit' => $data['amount'],
            'base_currency_id' => $data['base_currency_id'],
            'conversion_rate' => $data['conversion_rate'],
            'credit' => 0,
            'debit' => $data['amount'] * $data['conversion_rate'],
            //'partner_type_id' => $data['partner_type_id'],
            //'partner_id' => $data['partner_id'],
            //'ref_document_type_id' => $data['document_type_id'],
            //'ref_document_identity' => $data['document_identity'],
        );


        $this->model['gl'] = $this->load->model('gl/ledger');
        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $advance_receipt_id;
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['base_currency_id'] = $data['base_currency_id'];
            $ledger['document_currency_id'] = $data['document_currency_id'];
            $ledger['remarks'] = $data['remarks'];
            $ledger['sort_order'] = $sort_order;

            $this->model['gl']->add($this->getAlias(),$ledger);
        }

        return $advance_receipt_id;

    }

    protected function deleteData($primary_key) {
        $this->model['document'] = $this->load->model('common/document');
        $this->model['ledger'] = $this->load->model('gl/ledger');

        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));
        $this->model['document']->delete($this->getAlias(), $primary_key);
        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);

    }

//    public function getpartner() {
//        $partner_id = $this->request->get['partner_id'];
//        $partner_type_id = $this->request->post['partner_type_id'];
//
//        $filter = array(
//            'company_id' => $this->session->data['company_id'],
//            'partner_type_id' => $partner_type_id,
//        );
//        $this->model['partner'] = $this->load->model('common/partner');
//        $partners = $this->model['partner']->getRows($filter);
//        $html = '<option value="">&nbsp;</option>';
//        foreach($partners as $partner) {
//            $html .= '<option value="' . $partner['partner_id'] . '" '.($partner_id == $partner['partner_id']?'selected="true"':'').'>'.$partner['partner_name'].'</option>';
//        }
//
//        echo json_encode(array('success' => true, 'html' => $html));
//    }
//
//    public function getAccounts() {
//        $bank_account = $this->request->post['bank_account'];
//
//        $this->model['mapping_coa'] = $this->load->model('gl/mapping_coa');
//        $transaction_accounts = $this->model['mapping_coa']->getRows(array('mapping_type_code' => $bank_account),array('level3_display_name'));
//
//        $html = '<option value="">&nbsp;</option>';
//        foreach($transaction_accounts as $transaction_account) {
//            $html .= '<option value="' . $transaction_account['coa_level3_id'] . '" '.($transaction_account['coa_level3_id']?'selected="true"':'').'>'.$transaction_account['level3_display_name'].'</option>';
//        }
//
//        echo json_encode(array('success' => true, 'html' => $html));
//    }
//
//
//    public function getReferenceDocumentTypes() {
//        $filter = $this->request->post;
//        $filter['company_id'] = $this->session->data['company_id'];
//        $filter['company_branch_id'] = $this->session->data['company_branch_id'];
//        $filter['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
//
//
//        $this->model['document'] = $this->load->model('common/document');
//        $document_types = $this->model['document']->getDocumentTypes($filter);
//        $html = '<option value="">&nbsp;</option>';
//        foreach($document_types as $document_type) {
//            $html .= '<option value="'.$document_type['document_type_id'].'">'.$document_type['document_type'].'</option>';
//        }
//
//        echo json_encode(array('success' => true, 'filter' => $filter, 'document_types' => $document_types, 'html' => $html));
//    }
//
//    public function getReferenceDocumentNos() {
//        $excl_document_id = $this->request->get['advance_receipt_id'];
//        $post = $this->request->post;
//        $html = "";
//        $html .= '<option value="">&nbsp;</option>';
//
//        $documents = $this->getReferenceDocs($post,$excl_document_id);
//        $this->model['purchase_order'] = $this->load->model('transaction/purchase_order');
//
////        d($documents,true);
//        foreach($documents as $document_id => $document) {
//            $purchase_order = $this->model['purchase_order']->getRow(array('purchase_order_id' => $document['document_id']));
//
//            $html .= '<option value="'.$document_id.'">'.$document['document_identity'].' '.'('.$purchase_order['manual_ref_no'].')'. '</option>';
//        }
////        d($document,true);
//
//        echo json_encode(array('success' => true, 'post' => $post, 'html' => $html));
//    }
//
//    private function getReferenceDocs($post, $excl_document_id = '') {
//        $document_type_id = $post['document_type_id'];
//        if($document_type_id == 4) {
//            //Purchase Order
//            $this->model['purchase_order'] = $this->load->model('transaction/purchase_order');
//            $where = "company_id=" . $this->session->data['company_id'];
//            $where .= " AND company_branch_id='" . $this->session->data['company_branch_id'] . "'";
//            $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
//            $where .= " AND supplier_id='" . $post['partner_id'] . "'";
//            $where .= " AND document_currency_id='" . $post['document_currency_id'] . "'";
////            $where .= " AND is_post=1";
//
//            $purchase_orders = $this->model['purchase_order']->getPurchaseOrders($where,$excl_document_id);
//            foreach($purchase_orders as $purchase_order_id => $purchase_order) {
//                foreach($purchase_order['products'] as $product_id => $product) {
//                    if($product['order_qty'] <= $product['utilized_qty']) {
//                        unset($purchase_order['products'][$product_id]);
//                    }
//                }
//                if(empty($purchase_order['products'])) {
//                    unset($purchase_orders[$purchase_order_id]);
//                }
//            }
//        }
//
//        return $purchase_orders;
//    }
//
//    public function getpartnerOptions() {
//        $filter = array(
//            'company_id' => $this->session->data['company_id'],
//            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
//            'partner_type_id' => $this->request->post['partner_type_id'],
//            'partner_id' => $this->request->post['partner_id']
//        );
//        echo json_encode($this->partnerOptions($filter));
//    }
//
//    private function partnerOptions($filter= array()) {
//        $optionDocumentIdentity = '';
//        $optionCOA = '';
//        $arrOptionDocumentIdentity = '';
//        $arrOptionCOA = '';
//        if($filter['partner_id'] && $filter['partner_id'] != "") {
//            $arrFilter['EQ'] = array(
//                'l.company_id' => $this->session->data['company_id'],
//                'l.fiscal_year_id' => $this->session->data['fiscal_year_id'],
//                'l.partner_type_id' => $filter['partner_type_id'],
//                'l.partner_id' => $filter['partner_id']
//            );
//            if($filter['document_id'])
//                $arrFilter['NEQ'] = array('l.document_id' => $filter['document_id']);
//
//            $filterString = getFilterString($arrFilter);
//
//            $this->model['ledger'] = $this->load->model('gl/ledger');
//            $out_standings = $this->model['ledger']->getOutStanding($filterString);
//
//            $this->model['document'] = $this->load->model('common/document');
//            foreach($out_standings as $out_standing) {
//                $filterDocument = array(
//                    'company_id' => $this->session->data['company_id'],
//                    'fiscal_year_id' => $this->session->data['fiscal_year_id'],
//                    'document_type_id' => $out_standing['ref_document_type_id'],
//                    'document_identity' => $out_standing['ref_document_identity']
//                );
//                $document_invoice = $this->model['document']->getRow($filterDocument);
//                $optionDocumentIdentity .= '<option value="' . $out_standing['ref_document_identity'] . '" document_outstanding="' . $out_standing['outstanding'] . '" >'.$out_standing['ref_document_identity'] . ' (' . $document_invoice["document_amount"] .')</option>';
//                $arrOptionDocumentIdentity[] = array(
//                    'value' => $out_standing['ref_document_identity'],
//                    'document_outstanding' => $out_standing['outstanding'],
//                    'option' => $out_standing['ref_document_identity'] . ' (' . $document_invoice["document_amount"] .')'
//                );
//            }
//
//            $this->model['partner'] = $this->load->model('common/partner');
//            $partner = $this->model['partner']->getRow($filter);
//
//            $optionCOA .= '<option value="' . $partner['outstanding_account_id'] . '">'.$partner['outstanding_account'].'</option>';
//            $arrOptionCOA[] = array(
//                'value' => $partner['outstanding_account_id'],
//                'option' => $partner['outstanding_account']
//            );
//            $arrOptions = array(
//                'success' => true,
//                'optionDocumentIdentity' => $optionDocumentIdentity,
//                'optionCOA' => $optionCOA,
//                'arrOptionDocumentIdentity' => $arrOptionDocumentIdentity,
//                'arrOptionCOA' => $arrOptionCOA,
//            );
//        } else {
//            $this->model['gl'] = $this->load->model('gl/coa');
//            $coas = $this->model['gl']->getRows(array('company_id' => $this->session->data['company_id']));
//
//            foreach($coas as $coa) {
//                $optionCOA .= '<option value="' . $coa['coa_id'] . '">'.$coa['display_name'].'</option>';
//                $arrOptionCOA[] = array(
//                    'value' => $coa['coa_id'],
//                    'option' => $coa['display_name']
//                );
//            }
//
//            $arrOptions = array(
//                'success' => true,
//                'optionDocumentIdentity' => '',
//                'optionCOA' => $optionCOA,
//                'arrOptionDocumentIdentity' => $arrOptionDocumentIdentity,
//                'arrOptionCOA' => $arrOptionCOA,
//            );
//        }
//        return $arrOptions;
//    }
//
//    public function post() {
//        $data = array(
//            'is_post' => 1,
//            'post_date' => date('Y-m-d H:i:s')
//        );
//        //d($data,true);
//        $this->model['advance_receipt'] = $this->load->model('gl/advance_receipt');
//        $this->model['advance_receipt']->edit($this->getAlias(),$this->request->get['advance_receipt_id'],$data);
////        d(array($this->request->get['advance_receipt_id'],$data),true);
//
//        $this->redirect($this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL'));
//    }

    public function printDocument() {
        $advance_receipt_id = $this->request->get['advance_receipt_id'];
        $this->data['lang'] = $this->load->language($this->getAlias());

        $this->model['company'] = $this->load->model('setup/company');
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $this->data['company'] = $company;

        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->data['company_branch'] = $this->model['company_branch']->getRow(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));

        $this->load->model('tool/image');
        if ($company['company_logo'] && file_exists(DIR_IMAGE . $company['company_logo'])) {
            $company_logo = $this->model_tool_image->resize($company['company_logo'], 75, 75);
        } else {
            $company_logo = "";
        }

        $this->data['company_logo'] = $company_logo;

        $this->model['mapping_coa'] = $this->load->model('gl/mapping_coa');
        $arrBankAccounts = $this->model['mapping_coa']->getArrays('coa_level3_id','level3_display_name',array('code' => 'CABK'),array('level3_display_name'));

        $this->model['advance_receipt'] = $this->load->model('gl/advance_receipt');
        $row = $this->model['advance_receipt']->getRow(array('advance_receipt_id' => $advance_receipt_id));

        $this->model['partner_type'] = $this->load->model('common/partner_type');
        $arrpartnerTypes = $this->model['partner_type']->getArrays('partner_type_id','name');

        $this->model['partner'] = $this->load->model('common/partner');
        $arrpartners = $this->model['partner']->getArrays('partner_id','partner_name',array('company_id' => $this->session->data['company_id']));

        $this->model['currency'] = $this->load->model('setup/currency');
        $currency = $this->model['currency']->getRow(array('currency_id' => $row['document_currency_id']));
//        d(array($row,$arrpartnerTypes,$arrpartners),true);

        $this->data['account_type'] = $row['account_type'];
        $this->data['document_date'] = $row['document_date'];
        $this->data['document_identity'] = $row['document_identity'];
        $this->data['document_date'] = $row['document_date'];
        $this->data['remarks'] = $row['remarks'];
        $this->data['amount'] = $row['amount'];
        $this->data['partner_type'] = $arrpartnerTypes[$row['partner_type_id']];
        $this->data['partner'] = $arrpartners[$row['partner_id']];
        $this->data['currency'] = $currency['name'];
        $this->data['conversion_rate'] = $currency['value'];
        $this->data['amount'] = $row['amount'];

        $this->template = 'gl/advance_receipt_print.tpl';
        $contents = $this->render();

//        d($contents,true);

        try
        {
            // init HTML2PDF
            $html2pdf = new HTML2PDF('L', 'A5', 'en', true, 'UTF-8', array(0, 0, 0, 0));

            // display the full page
            $html2pdf->pdf->SetDisplayMode('fullpage');

            // convert
            $html2pdf->writeHTML($contents);

            // send the PDF
            $html2pdf->Output('Advance_Receipt.pdf');
        }
        catch(HTML2PDF_exception $e) {
            echo $e;
            exit;
        }
    }


}

?>