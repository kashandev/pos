<?php

class ControllerGlTransferSettlement extends HController {

    protected $document_type_id = 40;

    protected function getAlias() {
        return 'gl/transfer_settlement';
    }

    protected function getPrimaryKey() {
        return 'transfer_settlement_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {

        $lang = $this->load->language('gl/bank_payment');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());

        $data = array();
        $aColumns = array('action','document_date', 'document_identity' ,'remarks','amount','created_at', 'check_box');

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
                } elseif ($aColumns[$i] == 'document_date') {
                    $row[] = stdDate($aRow['document_date']);
                } elseif ($aColumns[$i] == 'created_at') {
                    $row[] = stdDateTime($aRow['created_at']);
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
        $this->data['document_identity'] = $this->data['lang']['auto'];
        $this->data['document_date'] = stdDate();
        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";

        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencies'] = $this->model['currency']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['coa'] = $this->load->model('gl/coa_level3');
        $this->data['coas'] = $this->model['coa']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->data['company_branchs'] = $this->model['company_branch']->getRows(array('company_id' => $this->session->data['company_id']));

        if (isset($this->request->get['transfer_settlement_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $this->data['document_type_id'] = $this->document_type_id;
            $this->data['document_id'] = $this->request->get['transfer_settlement_id'];

            $result = $this->model[$this->getAlias()]->getRow(array('transfer_settlement_id' => $this->request->get['transfer_settlement_id']));
            foreach ($result as $field => $value) {
                if ($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }

//            $this->model['document'] = $this->load->model('common/document');
//            $this->model['transfer_settlement_detail'] = $this->load->model('gl/transfer_settlement_detail');
//            $filter = array(
//                'transfer_settlement_id' => $this->request->get['transfer_settlement_id']
//            );
//            $details = $this->model['transfer_settlement_detail']->getRows($filter,array('sort_order DESC'));
//            foreach($details as $detail) {
//                $row_id = $detail['sort_order'];
//                if(empty($detail['cheque_date']) || $detail['cheque_date']=='0000-00-00') {
//                    $detail['cheque_date'] = '';
//                } else {
//                    $detail['cheque_date'] = stdDate($detail['cheque_date']);
//                }
//                if($detail['ref_document_type_id'] && $detail['ref_document_identity']) {
//                    $ref_document = $this->model['document']->getRow(array('document_type_id' => $detail['ref_document_type_id'], 'document_identity' => $detail['ref_document_identity']));
//                    $detail['href'] = $this->url->link($ref_document['route'].'/update', 'token=' . $this->session->data['token'] . '&' . $ref_document['primary_key_field'] . '=' . $ref_document['primary_key_value'], 'SSL');
//                }
//                $this->data['transfer_settlement_details'][$row_id] = $detail;
//            }
        }

        $this->data['href_get_document_ledger'] = $this->url->link('common/function/getDocumentLedger', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_pending_document'] = $this->url->link($this->getAlias() .'/getPendingDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_branch_account'] = $this->url->link($this->getAlias() .'/getBranchAccount', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print'] = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['action_validate_date'] = $this->url->link('common/home/validateDate', 'token=' . $this->session->data['token']);
        $this->data['strValidation']= "{
            'rules':{
                'document_date': {'required':true},
                'conversion_rate': {'required':true},
                'document_debit' : {'required': true, 'min': 1},
                'document_credit' : {'required': true, 'min': 1, equalTo: '#document_debit'},
            },
          }";

        $this->response->setOutput($this->render());
    }

    public function getBranchAccount() {
        $post = $this->request->post;
        $to_branch_id = $post['to_branch_id'];

        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $company_branch = $this->model['company_branch']->getRow(array('company_branch_id' => $to_branch_id));

        $this->model['ledger'] = $this->load->model('gl/ledger');
        $outstanding_amount = $this->model['ledger']->getBranchOutstanding($this->session->data['company_branch_id'],$company_branch['branch_account_id'],$this->request->get['transfer_settlement_id']);

//        d(array($this->session->data['company_branch_id'],$company_branch['branch_account_id'],$this->request->get['transfer_settlement_id'],$outstanding_amount),true);

        $json = array(
            'success' => true,
            'outstanding_amount' => $outstanding_amount['outstanding_amount'],
        );


        echo json_encode($json);

    }


    protected function insertData($data) {

        $this->model['document_type'] = $this->load->model('common/document_type');
        $this->model['document'] = $this->load->model('common/document');
        //$this->model['mapping_account'] = $this->load->model('gl/mapping_coa');
        $this->model['transfer_settlement_detail'] = $this->load->model('gl/transfer_settlement_detail');
        $this->model['ledger'] = $this->load->model('gl/ledger');

        $document = $this->model['document_type']->getNextDocument($this->document_type_id);

        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['document_type_id'] = $this->document_type_id;
        $data['document_prefix'] = $document['document_prefix'];
        $data['document_no'] = $document['document_no'];
        $data['document_identity'] = $document['document_identity'];

        $data['document_date'] = MySqlDate($data['document_date']);
        $data['base_debit'] = $data['document_debit'] * $data['conversion_rate'];
        $data['base_credit'] = $data['document_credit'] * $data['conversion_rate'];
        $transfer_settlement_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $data['document_id'] = $transfer_settlement_id;
        //d($data, true);

        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $Current_Company_Branch = $this->model['company_branch']->getRow(array('company_id' => $this->session->data['company_id'],'company_branch_id' => $this->session->data['company_branch_id']));
        $To_Company_Branch = $this->model['company_branch']->getRow(array('company_id' => $this->session->data['company_id'],'company_branch_id' => $data['to_branch_id']));
        $Current_branch_account_id = $Current_Company_Branch['branch_account_id'];
        $To_branch_account_id = $To_Company_Branch['branch_account_id'];

//        d(array($Current_Company_Branch,$Current_branch_account_id,$To_Company_Branch,$To_branch_account_id),true);
        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $data['document_id'],
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['document_debit'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['base_debit'],
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

        $gl_data[] = array(
            'document_detail_id' => '',
            'coa_id' => $Current_branch_account_id,
            'document_currency_id' => $data['document_currency_id'],
            'document_credit' => 0,
            'document_debit' =>   $data['amount'],
            'base_currency_id' => $data['base_currency_id'],
            'conversion_rate' =>  $data['conversion_rate'],
            'credit' => 0,
            'debit' => $data['amount'] * $data['conversion_rate'],
            'company_branch_id' => $data['to_branch_id'],


        );

        $gl_data[] = array(
            'document_detail_id' => '',
            'coa_id' => $To_branch_account_id,
            'document_currency_id' => $data['document_currency_id'],
            'document_credit' => $data['amount'],
            'document_debit' =>   0,
            'base_currency_id' => $data['base_currency_id'],
            'conversion_rate' =>  $data['conversion_rate'],
            'credit' => $data['amount'] * $data['conversion_rate'],
            'debit' => 0,
            'company_branch_id' => $this->session->data['company_branch_id'],
        );
        //d(array($detail, $gl_data), true);


         foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $data['document_id'];
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['remarks'] = $data['remarks'];
            $ledger['ref_document_type_id'] = $this->document_type_id;
            $ledger['ref_document_identity'] = $data['document_identity'];

            $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);

        }

        return $transfer_settlement_id;
    }

    protected function updateData($primary_key, $data) {
        $this->model['document_type'] = $this->load->model('common/document_type');
        $this->model['document'] = $this->load->model('common/document');
        //  $this->model['mapping_account'] = $this->load->model('gl/mapping_coa');
        $this->model['ledger'] = $this->load->model('gl/ledger');

        $this->model['document']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));

        $data['document_date'] = MySqlDate($data['document_date']);
        $data['base_debit'] = $data['document_debit'] * $data['conversion_rate'];
        $data['base_credit'] = $data['document_credit'] * $data['conversion_rate'];
        $transfer_settlement_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        $data['document_id'] = $transfer_settlement_id;

        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $data['document_id'],
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['document_debit'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['base_debit'],
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $Current_Company_Branch = $this->model['company_branch']->getRow(array('company_id' => $this->session->data['company_id'],'company_branch_id' => $this->session->data['company_branch_id']));
        $To_Company_Branch = $this->model['company_branch']->getRow(array('company_id' => $this->session->data['company_id'],'company_branch_id' => $data['to_branch_id']));
        $Current_branch_account_id = $Current_Company_Branch['branch_account_id'];
        $To_branch_account_id = $To_Company_Branch['branch_account_id'];


        $gl_data[] = array(
            'document_detail_id' => '',
            'coa_id' => $Current_branch_account_id,
            'document_currency_id' => $data['document_currency_id'],
            'document_credit' => 0,
            'document_debit' =>   $data['amount'],
            'base_currency_id' => $data['base_currency_id'],
            'conversion_rate' =>  $data['conversion_rate'],
            'credit' => 0,
            'debit' => $data['amount'] * $data['conversion_rate'],
            'company_branch_id' => $data['to_branch_id'],


        );

        $gl_data[] = array(
            'document_detail_id' => '',
            'coa_id' => $To_branch_account_id,
            'document_currency_id' => $data['document_currency_id'],
            'document_credit' => $data['amount'],
            'document_debit' =>   0,
            'base_currency_id' => $data['base_currency_id'],
            'conversion_rate' =>  $data['conversion_rate'],
            'credit' => $data['amount'] * $data['conversion_rate'],
            'debit' => 0,
            'company_branch_id' => $this->session->data['company_branch_id'],
        );


        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
//            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $data['document_id'];
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['remarks'] = $data['remarks'];
            $ledger['ref_document_type_id'] = $this->document_type_id;
            $ledger['ref_document_identity'] = $data['document_identity'];

            $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);
        }
        return $transfer_settlement_id;
    }

    protected function deleteData($primary_key) {
        $this->model['document'] = $this->load->model('common/document');
        $this->model['ledger'] = $this->load->model('gl/ledger');

        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));
        $this->model['document']->delete($this->getAlias(), $primary_key);
        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);

    }

    public function printDocument() {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        $transfer_settlement_id = $this->request->get['transfer_settlement_id'];

        $this->model['transfer_settlement'] = $this->load->model('gl/transfer_settlement');
        $this->model['transfer_settlement_detail'] = $this->load->model('gl/transfer_settlement_detail');

        $invoice = $this->model['transfer_settlement']->getRow(array('transfer_settlement_id' => $transfer_settlement_id));
        $details = $this->model['transfer_settlement_detail']->getRows(array('transfer_settlement_id' => $transfer_settlement_id));

        $pdf = new PDF('L', PDF_UNIT, 'A5', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Transfer Settlement');
        $pdf->SetSubject('Transfer Settlement');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $session['company_image']
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(15, 35, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font
        $pdf->SetFont('times', '', 10);

        // add a page
        $pdf->AddPage();

        $pdf->Cell(25, 7, $lang['document_date'] .':', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7, stdDate($invoice['document_date']), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(55, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(40, 7, $lang['document_no'] .':', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(40, 7, $invoice['document_identity'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->ln(7);
        $pdf->Cell(25, 7, $lang['remarks'] .':', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(75, 7, $invoice['remarks'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->ln(10);

        $pdf->SetFont('times', '', 8);
        $pdf->SetFillColor(215, 215, 215);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(30, 8, $lang['partner_name'], 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $pdf->Cell(45, 8, $lang['coa'], 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $pdf->Cell(50, 8, $lang['remarks'], 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 8, $lang['cheque_no'], 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $pdf->Cell(18, 8, $lang['debit'], 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $pdf->Cell(18, 8, $lang['credit'], 1, false, 'C', 1, '', 0, false, 'M', 'M');
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $total_amount = 0;
        $pdf->ln(4);
        foreach($details as $detail) {
            $pdf->Cell(30, 8, $detail['partner_name'], 1, false, 'L', 0, '', 1);
            $pdf->Cell(45, 8, $detail['account'], 1, false, 'L', 0, '', 1);
            $pdf->Cell(50, 8, $detail['remarks'], 1, false, 'L', 0, '', 1);
            $pdf->Cell(20, 8, $detail['cheque_no'], 1, false, 'L', 0, '', 1);
            $pdf->Cell(18, 8, number_format($detail['document_debit'],2), 1, false, 'R', 0, '', 1);
            $pdf->Cell(18, 8, number_format($detail['document_credit'],2), 1, false, 'R', 0, '', 1);
            $pdf->ln(8);
        }

        $pdf->SetFont('times', 'B', 8);
        if($invoice['document_currency_id'] == $invoice['base_currency_id'] || $invoice['conversion_rate'] == 1) {
            $pdf->ln(8);
            $pdf->Cell(145, 8, 'IN WORD: ' . Number2Words($invoice['document_debit']), 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(18, 8, number_format($invoice['document_debit'],2), 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(18, 8, number_format($invoice['document_credit'],2), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        } else {
            $pdf->ln(8);
            $pdf->Cell(145, 8, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(18, 8, number_format($invoice['document_debit'],2), 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(18, 8, number_format($invoice['document_credit'],2), 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->ln(8);
            $pdf->Cell(145, 8, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(18, 8, $lang['conversion_rate'].': ', 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(18, 8, number_format($invoice['conversion_rate'],2), 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->ln(8);
            $pdf->Cell(145, 8, 'IN WORD: ' . Number2Words(round($invoice['base_debit'],2)), 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(18, 8, number_format($invoice['base_debit'],2), 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(18, 8, number_format($invoice['base_credit'],2), 0, false, 'R', 0, '', 0, false, 'M', 'M');
        }
        //Close and output PDF document
        $pdf->Output('Transfer Settlement - '.$invoice['document_identity'].'.pdf', 'I');

    }
}

class PDF extends TCPDF {
    public $data = array();

    //Page header
    public function Header() {
        // Logo
        if($this->data['company_logo'] != '') {
            $image_file = DIR_IMAGE.$this->data['company_logo'];
            //$this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
            $this->Image($image_file, 10, 10, 30, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        $this->Ln(2);
        // Title
        $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
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