<?php

class ControllerGlOpeningAccount extends HController {

    protected $document_type_id = 7;

    protected function getAlias() {
        return 'gl/opening_account';
    }
    
    protected function getPrimaryKey() {
        return 'opening_account_id';
    }
    
    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {

        $lang = $this->load->language('gl/opening_account');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action','document_date', 'document_identity', 'remarks', 'created_at', 'check_box');

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

        $this->data['document_identity'] = $this->data['lang']['auto'];
        $this->data['document_date'] = stdDate();
        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";

        $this->data['partner_types'] = $this->session->data['partner_types'];

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partners'] = $this->model['partner']->getRows(array('company_id' => $this->session->data['company_id'],'company_branch_id' => $this->session->data['company_branch_id']));
        //d($this->data['partners'], true);

        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencies'] = $this->model['currency']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['coa'] = $this->load->model('gl/coa_level3');
        $this->data['coas'] = $this->model['coa']->getRows(array('company_id' => $this->session->data['company_id']));
        //d($this->data['partners']);
        //d($this->data['coas'],true);
        if (isset($this->request->get['opening_account_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $this->data['document_type_id'] = $this->document_type_id;
            $this->data['document_id'] = $this->request->get['opening_account_id'];

            $result = $this->model[$this->getAlias()]->getRow(array('opening_account_id' => $this->request->get['opening_account_id']));
            foreach ($result as $field => $value) {
                if ($field == 'document_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }

            $this->model['opening_account_detail'] = $this->load->model('gl/opening_account_detail');
            $filter = array(
                'opening_account_id' => $this->request->get['opening_account_id']
            );
            $details = $this->model['opening_account_detail']->getRows($filter,array('sort_order'));

//            d($details,true);
            foreach($details as $detail) {
                $row_id = $detail['sort_order'];
                if(empty($detail['ref_document_date']) || $detail['ref_document_date']=='0000-00-00') {
                    $detail['ref_document_date'] = '';
                } else {
                    $detail['ref_document_date'] = stdDate($detail['ref_document_date']);
                }
                $this->data['opening_account_details'][$row_id] = $detail;
            }

        }

        $this->data['href_get_document_ledger'] = $this->url->link('common/function/getDocumentLedger', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_partner_account'] = $this->url->link($this->getAlias() .'/getPartnerAccount', 'token=' . $this->session->data['token']);
        $this->data['href_get_partner'] = $this->url->link('common/function/getPartner', 'token=' . $this->session->data['token']);
        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['strValidation']= "{
            'rules':{
                'document_date': {
                    'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}
                },
                'conversion_rate': {'required':true},
                'document_debit' : {'required': true, 'min': 1},
                'document_credit' : {'required': true, 'min': 1, equalTo: '#document_debit'},
            },
            messages: {
            document_date:{
                remote: 'Invalid Date'
            }
        }
        }";

        $this->response->setOutput($this->render());
    }

    public function getPartnerAccount()
    {
        $post = $this->request->post;
        // d($post);
        $partner_id = $post['partner_id'];
        $company_branch_id = $this->session->data['company_branch_id'];
        $this->model['partner'] = $this->load->model('common/partner');
        $partner = $this->model['partner']->getRow(array('partner_id' => $partner_id, 'company_branch_id' => $company_branch_id));
        $partner_account = $partner['outstanding_account_id'];
        $json = array(
               'success' => true,
               'account' => $partner_account,
           );
           echo json_encode($json);
       exit;
        // d($partner,true);
    }

    protected function insertData($data) {
        $this->model['document_type'] = $this->load->model('common/document_type');
        $document = $this->model['document_type']->getNextDocument($this->document_type_id);

        $data['document_date'] = MySqlDate($data['document_date']);
        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['document_type_id'] = $this->document_type_id;
        $data['document_prefix'] = $document['document_prefix'];
        $data['document_no'] = $document['document_no'];
        $data['document_identity'] = $document['document_identity'];
        $data['base_debit'] = $data['document_debit'] * $data['conversion_rate'];
        $data['base_credit'] = $data['document_credit'] * $data['conversion_rate'];
        $opening_account_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $data['document_id'] = $opening_account_id;
        //d(array($data, $opening_account_id), true);

        $this->model['document'] = $this->load->model('common/document');
        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_type' => $document['document_type'],
            'document_id' => $data['document_id'],
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'route' => $document['route'],
            'primary_key_field' => $document['primary_key'],
            'primary_key_value' => $data['document_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['total_amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['base_amount'],
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

        $this->model['opening_account_detail'] = $this->load->model('gl/opening_account_detail');
        foreach ($data['opening_account_details'] as $sort_order => $detail) {
            $detail['opening_account_id'] = $opening_account_id;
            $detail['sort_order'] = $sort_order;
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_debit'] = $data['conversion_rate'] * $detail['document_debit'];
            $detail['base_credit'] = $data['conversion_rate'] * $detail['document_credit'];
            if($detail['ref_document_date'] != '') {
                $detail['ref_document_date'] = MySqlDate($detail['ref_document_date']);
            }

            $opening_account_detail_id =  $this->model['opening_account_detail']->add($this->getAlias(), $detail);
//            d($opening_account_detail_id,true);
            if($detail['ref_document_type_id'] != '' && $detail['ref_document_identity'] !='') {
                $insert_document = array(
                    'company_id' => $this->session->data['company_id'],
                    'company_branch_id' => $this->session->data['company_branch_id'],
                    'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                    'document_type_id' => $detail['ref_document_type_id'],
                    'document_id' => $opening_account_detail_id,
                    'document_identity' => $detail['ref_document_identity'],
                    'document_date' => $detail['ref_document_date'],
                    'partner_type_id' => $detail['partner_type_id'],
                    'partner_id' => $detail['partner_id'],
                    'document_currency_id' => $data['document_currency_id'],
                    'document_amount' => $detail['ref_document_amount'],
                    'conversion_rate' => $data['conversion_rate'],
                    'base_currency_id' => $data['base_currency_id'],
                    'base_amount' => ($detail['ref_document_amount'] * $data['conversion_rate']),
                );
                $document_id = $this->model['document']->add($this->getAlias(), $insert_document);
            }

            $gl = array(
                'document_detail_id' => $opening_account_detail_id,
                'coa_id' => $detail['coa_level3_id'],
                'sort_order' => $sort_order,
                'document_currency_id' => $data['document_currency_id'],
                'document_debit' => $detail['document_debit'],
                'document_credit' => $detail['document_credit'],
                'base_currency_id' => $data['base_currency_id'],
                'conversion_rate' => $data['conversion_rate'],
                'debit' => $detail['base_debit'],
                'credit' => $detail['base_credit'],
                'partner_type_id' => $detail['partner_type_id'],
                'partner_id' => $detail['partner_id'],
                'ref_document_type_id' => $detail['ref_document_type_id'],
                'ref_document_identity' => $detail['ref_document_identity'],
            );

            $gl_data[] = $gl;
        }
        // d($gl_data,true);
        $this->model['ledger'] = $this->load->model('gl/ledger');
        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $opening_account_id;
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['remarks'] = $data['remarks'];
            $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);
        }
        
        //d(array($data,$detail,$gl_data,$ledger),true);
        return $opening_account_id;    
    }

    protected function updateData($primary_key, $data) {
        $data['document_date'] = MySqlDate($data['document_date']);
        $data['base_debit'] = $data['document_debit'] * $data['conversion_rate'];
        $data['base_credit'] = $data['document_credit'] * $data['conversion_rate'];
        $data['document_id'] = $primary_key;
        $this->model['opening_account'] = $this->load->model('gl/opening_account');
        $this->model['opening_account_detail'] = $this->load->model('gl/opening_account_detail');
        $this->model['document'] = $this->load->model('common/document');
        $this->model['document_type'] = $this->load->model('common/document_type');
        $document = $this->model['document_type']->getRow(array('document_type_id' => $this->document_type_id));

        $details = $this->model['opening_account_detail']->getRows(array('opening_account_id' => $primary_key));
        foreach($details as $detail) {
            $this->model['document']->delete($this->getAlias(), $detail['opening_account_detail_id']);
        }
        $this->model['opening_account']->edit($this->getAlias(), $primary_key, $data);
        $this->model['opening_account_detail']->deleteBulk($this->getAlias(), array('opening_account_id' => $primary_key));
        $this->model['document']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));

        $insert_document = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'document_type_id' => $this->document_type_id,
            'document_id' => $data['document_id'],
            'document_identity' => $data['document_identity'],
            'document_date' => $data['document_date'],
            'document_type' => $document['document_type'],
            'route' => $document['route'],
            'primary_key_field' => $document['primary_key'],
            'primary_key_value' => $data['document_id'],
            'document_currency_id' => $data['document_currency_id'],
            'document_amount' => $data['total_amount'],
            'conversion_rate' => $data['conversion_rate'],
            'base_currency_id' => $data['base_currency_id'],
            'base_amount' => $data['base_amount'],
        );
        $document_id = $this->model['document']->add($this->getAlias(), $insert_document);

        foreach ($data['opening_account_details'] as $sort_order => $detail) {
            $detail['opening_account_id'] = $primary_key;
            $detail['sort_order'] = $sort_order;
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_debit'] = $data['conversion_rate'] * $detail['document_debit'];
            $detail['base_credit'] = $data['conversion_rate'] * $detail['document_credit'];
            if($detail['ref_document_date'] != '') {
                $detail['ref_document_date'] = MySqlDate($detail['ref_document_date']);
            }
            $opening_account_detail_id =  $this->model['opening_account_detail']->add($this->getAlias(), $detail);

            if($detail['ref_document_type_id'] != '' && $detail['ref_document_identity'] !='') {
                $insert_document = array(
                    'company_id' => $this->session->data['company_id'],
                    'company_branch_id' => $this->session->data['company_branch_id'],
                    'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                    'document_type_id' => $detail['ref_document_type_id'],
                    'document_id' => $opening_account_detail_id,
                    'document_identity' => $detail['ref_document_identity'],
                    'document_date' => $detail['ref_document_date'],
                    'partner_type_id' => $detail['partner_type_id'],
                    'partner_id' => $detail['partner_id'],
                    'document_type' => $document['document_type'],
                    'route' => $document['route'],
                    'primary_key_field' => $document['primary_key'],
                    'primary_key_value' => $data['document_id'],
                    'document_currency_id' => $data['document_currency_id'],
                    'document_amount' => $detail['ref_document_amount'],
                    'conversion_rate' => $data['conversion_rate'],
                    'base_currency_id' => $data['base_currency_id'],
                    'base_amount' => ($detail['ref_document_amount'] * $data['conversion_rate']),
                );
                $document_id = $this->model['document']->add($this->getAlias(), $insert_document);
            }

            $gl = array(
                'document_detail_id' => $opening_account_detail_id,
                'coa_id' => $detail['coa_level3_id'],
                'document_identity' => $data['document_identity'],
                'sort_order' => $sort_order,
                'document_currency_id' => $data['document_currency_id'],
                'document_debit' => $detail['document_debit'],
                'document_credit' => $detail['document_credit'],
                'base_currency_id' => $data['base_currency_id'],
                'conversion_rate' => $data['conversion_rate'],
                'debit' => $detail['base_debit'],
                'credit' => $detail['base_credit'],
                'partner_type_id' => $detail['partner_type_id'],
                'partner_id' => $detail['partner_id'],
                'ref_document_type_id' => $detail['ref_document_type_id'],
                'ref_document_identity' => $detail['ref_document_identity'],
                'document_date' => $detail['ref_document_date'],
            );

            $gl_data[] = $gl;
        }
       // d(array($data,$primary_key),true);

        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        foreach($gl_data as $sort_order => $ledger) {
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $primary_key;
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_date'] = $data['document_date'];
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['remarks'] = $data['remarks'];

            $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);
        }
        //d(array($data,$gl_data),true);
        return $primary_key;

    }

    protected function deleteData($primary_key) {
        $this->model['document'] = $this->load->model('common/document');
        $this->model['opening_account_detail'] = $this->load->model('gl/opening_account_detail');

        $details = $this->model['opening_account_detail']->getRows(array('opening_account_id' => $primary_key));
        foreach($details as $detail) {
            $this->model['document']->delete($this->getAlias(), $detail['opening_account_detail_id']);
        }
        $this->model['document']->delete($this->getAlias(), $primary_key);
        $this->model['opening_account_detail']->deleteBulk($this->getAlias(), array('opening_account_id' => $primary_key));

        $this->model['ledger'] = $this->load->model('gl/ledger');
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_type_id' => $this->document_type_id, 'document_id' => $primary_key));

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

    public function printDocument() {

        $post = $this->request->post;
        $lang = $this->load->language($this->getAlias());
        $session = $this->session->data;
        $opening_account_id = $this->request->get['opening_account_id'];

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['opening_account'] = $this->load->model('gl/opening_account');
        $this->model['opening_account_detail'] = $this->load->model('gl/opening_account_detail');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $company_id = $this->session->data['company_id'];

        $opening_account =  $this->model['opening_account']->getRow(array('opening_account_id'=>$opening_account_id));
        $opening_account_details = $this->model['opening_account_detail']->getRows(array('opening_account_id'=>$opening_account_id));

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


      $company = $this->model['company']->getRow(array('company_id' => $company_id));
      $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Muhammad Salman');
        $pdf->SetTitle('Opening Stock');
        $pdf->SetSubject('Opening Stock');

        //Set Header

        $pdf->data = array(
            'company_name' => $branch['name'],
             'report_name' => 'Opening Account',
             'company_header_print' => $company_header_print,
             'company_footer_print' => $company_footer_print,
        );



        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(7, 2, 7);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(2);

        // set auto page breaks
        //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font
        $pdf->SetFont('helvetica', '', 10);

        // add a page
        $pdf->AddPage();

        $pdf->SetTextColor(0,0,0);

        $pdf->Ln(30);
        $pdf->Cell(20,7,'Invoice No : ',0,false,'L',0,'',0,false,'M','M');
        $pdf->Cell(20,7,$opening_account['document_identity'],0,false,'L',0,'',0,false,'M','M');

        $pdf->Cell(120,7,'Invoice Date : ',0,false,'R',0,'',0,false,'M','M');
        $pdf->Cell(20,7,$opening_account['document_date'],0,false,'L',0,'',0,false,'M','M');

        $pdf->Ln(8);
        $pdf->Cell(30,7,'Remarks : ',0,false,'L',0,'',0,false,'M','M');
        $pdf->Cell(75,7,$opening_account['remarks'],0,false,'L',0,'',0,false,'M','M');

        $pdf->Ln(12);

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(135, 8, 'Chart Of Account', 1, false, 'L', 1, '', 1);
        $pdf->Cell(30, 8, 'Debit', 1, false, 'C', 1, '', 1);
        $pdf->Cell(30, 8, 'Credit', 1, false, 'C', 1, '', 1);

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);

        $total_amount = 0;
        $total_discount = 0;
        $total_tax = 0;
        $total_net_amount = 0;

        // d($opening_account_details, true);
        foreach($opening_account_details as $details){
            $pdf->Ln(8);
            $pdf->Cell(135, 8, $details['account'], 1, false, 'L', 1, '', 1);
            $pdf->Cell(30, 8, $details['base_debit'], 1, false, 'R', 1, '', 1);
            $pdf->Cell(30, 8, $details['base_credit'], 1, false, 'R', 1, '', 1);

            $total_amount += $details['total_price_pkr'];
            // $total_discount += $details['discount_amount'];
            $total_tax += ($details['sales_tax'] + $details['additional_sales_tax'] + $details['income_tax']); // by taikhum
            $total_net_amount += ($details['total_price_pkr'] + ($details['sales_tax'] + $details['additional_sales_tax'] + $details['income_tax']));
        }

        // $x = $pdf->GetX();
        // $y = $pdf->GetY();
        // for ($i = $y; $i <= 200; $i++) {

        //     $pdf->Ln(1);
        //     $pdf->Cell(25, 8,'', 'L', false, 'C', 0, '', 1);
        //     $pdf->Cell(60, 8,'', 'L', false, 'C', 0, '', 1);
        //     $pdf->Cell(30, 8, '', 'L', false, 'L', 0, '', 1);
        //     $pdf->Cell(15, 8, '', 'L', false, 'R', 0, '', 1);
        //     $pdf->Cell(15, 8, '', 'L', false, 'C', 0, '', 1);
        //     $pdf->Cell(25, 8, '', 'L', false, 'R', 0, '', 1);
        //     $pdf->Cell(25, 8, '', 'L,R', false, 'R', 0, '', 1);
        //     $y =$i;
        // }
        // $pdf->Ln(-1);
        // $pdf->Ln(5);
        // $pdf->Cell(190, 8, '', 'B', false, 'C', 0, '', 0, false, 'M', 'M');
        // $pdf->setXY($x,$y);


        // $pdf->SetFillColor(255, 255, 255);
        // $pdf->SetTextColor(0, 0, 0);

        // $pdf->SetFont('helvetica', '', 9);
        // $pdf->ln(9);
        // // $pdf->Cell(130, 7, '', 0, false, 'L');
        // $pdf->Cell(160, 7, $lang['total_amount'].': ', 0, false, 'R');
        // $pdf->Cell(30, 7, number_format($total_amount,2), 0, false, 'R');

        // $pdf->ln(7);

        // // // $pdf->Cell(130, 7, '', 0, false, 'L');
        // // $pdf->Cell(160, 7, $lang['discount_amount'].': ', 0, false, 'R');
        // // $pdf->Cell(30, 7, number_format($total_discount,2), 0, false, 'R');

        // // $pdf->ln(7);


        // // $pdf->Cell(130, 7, '', 0, false, 'L');
        // $pdf->Cell(160, 7, $lang['tax_amount'].': ', 0, false, 'R');
        // $pdf->Cell(30, 7, number_format($total_tax,2), 0, false, 'R');

        // $pdf->ln(7);


        // // $pdf->Cell(130, 7, '', 0, false, 'L');
        // $pdf->Cell(160, 7, $lang['net_amount'].': ', 0, false, 'R');
        // $pdf->Cell(30, 7, number_format($total_net_amount,2), 0, false, 'R');

        // $pdf->ln(7);
        // $pdf->Cell(190, 7, 'Amount In words: ' . Number2Words(round($total_net_amount,2)). ' only', 0, false, 'L');

       // $data= $Quotation;//Close and output PDF document
        $pdf->Output('Opening Stock :'.date('YmdHis').'.pdf', 'I');


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
//            $this->Image($image_file, 10, 5, 90, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
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

        // Set font
        // $this->SetFont('helvetica', 'B', 20);
        // $this->Ln(2);
        // Title
        // $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(18);
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
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
        // $this->SetFont('helvetica', 'I', 8);
        // Page number
        // $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        // Set font
        $this->SetFont('helvetica', '', 8);
        // Page number
        // $this->Ln(-8);
        // $this->Cell(7, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        // $this->Cell(25, 7, 'Prepared By', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
        // $this->Cell(20, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        // $this->Cell(25, 7, 'Production Manager',  'T', false, 'C', 0, '', 0, false, 'M', 'M');
        // $this->Cell(20, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        // $this->Cell(25, 7, 'Finance Manager',  'T', false, 'C', 0, '', 0, false, 'M', 'M');


        // $this->Ln(-7);
        // $this->Cell(7+25+20+25+20+25+15, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        // $this->Cell(45, 7, 'For '. $this->data['company_name'],  'B', false, 'C', 0, '', 0, false, 'M', 'M');

    }
}
