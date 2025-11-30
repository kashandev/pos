<?php

class ControllerGlFundTransfer extends HController {

    protected $document_type_id = 45;

    protected function getAlias() {
        return 'gl/fund_transfer';
    }

    protected function getPrimaryKey() {
        return 'fund_transfer_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {

        $lang = $this->load->language('gl/fund_transfer');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());

        $data = array();
        $aColumns = array('action','document_date', 'document_identity', 'remarks','total_amount','created_at', 'check_box');

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
            $action_update = $this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL');
            for ($i = 0; $i < count($aColumns); $i++) {
                if ($aColumns[$i] == 'action') {
                    $row[] = $strAction;
                } elseif ($aColumns[$i] == 'document_identity') {
                    $row[] = '<a href="'.$action_update.'">'.$aRow['document_identity'].'</a>';
                }elseif ($aColumns[$i] == 'created_at') {
                    $row[] = stdDateTime($aRow['created_at']);
                } elseif ($aColumns[$i] == 'document_date') {
                    $row[] = stdDate($aRow['document_date']);
                } elseif ($aColumns[$i] == 'check_box') {
                    if($aRow['is_post']==0){
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

        $this->data['document_identity'] = $this->data['lang']['auto'];
        $this->data['document_date'] = stdDate();
        $this->data['base_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['base_currency'] = $this->session->data['base_currency_name'];
        $this->data['document_currency_id'] = $this->session->data['base_currency_id'];
        $this->data['conversion_rate'] = "1.00";

        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencies'] = $this->model['currency']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['coa'] = $this->load->model('gl/coa_level3');
        /*$this->data['coa_l3'] = $this->model['gl/coa_level3']->getRows(['company_id'=>$this->session->data['company_id']]);*/

        $this->model['project'] = $this->load->model('setup/project');
        $this->data['projects'] = $this->model['project']->getRows(['company_id'=>$this->session->data['company_id']]);

        $this->model['setting']= $this->load->model('common/setting');
        $accounts = $this->model['setting']->getRows(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'gl',
            'field' => 'transaction_account_id',
        ));
        foreach($accounts as $account) {
            $this->data['transaction_accounts'][] = $this->model['coa']->getRow(array('company_id' => $this->session->data['company_id'], 'coa_level3_id' => $account['value']));
        }

        $this->data['coas'] = $this->data['transaction_accounts'];
        $this->data['partner_types'] = $this->session->data['partner_types'];

        if (isset($this->request->get['fund_transfer_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $this->data['document_type_id'] = $this->document_type_id;
            $this->data['document_id'] = $this->request->get['fund_transfer_id'];

            $result = $this->model[$this->getAlias()]->getRow(array('fund_transfer_id' => $this->request->get['fund_transfer_id']));
            // d($result,true);
            foreach ($result as $field => $value) {
                if ($field == 'document_date' || $field == 'instrument_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }

            $this->model['document'] = $this->load->model('common/document');
            $this->model['fund_transfer_detail'] = $this->load->model('gl/fund_transfer_detail');
            $filter = array(
                'fund_transfer_id' => $this->request->get['fund_transfer_id']
            );
            $details = $this->model['fund_transfer_detail']->getRows($filter,array('sort_order DESC'));
            foreach($details as $detail) {
                $row_id = $detail['sort_order'];
                if(empty($detail['cheque_date']) || $detail['cheque_date']=='0000-00-00') {
                    $detail['cheque_date'] = '';
                } else {
                    $detail['cheque_date'] = stdDate($detail['cheque_date']);
                }
                if($detail['ref_document_type_id'] && $detail['ref_document_identity']) {
                    $ref_document = $this->model['document']->getRow(array('document_type_id' => $detail['ref_document_type_id'], 'document_identity' => $detail['ref_document_identity']));
                    $detail['href'] = $this->url->link($ref_document['route'].'/update', 'token=' . $this->session->data['token'] . '&' . $ref_document['primary_key_field'] . '=' . $ref_document['primary_key_value'], 'SSL');
                }
                $this->data['fund_transfer_details'][$row_id] = $detail;
            }
            $this->model['partner'] = $this->load->model('common/partner');
            $this->data['partners'] = $this->model['partner']->getRows(array('company_id' => $this->session->data['company_id'], 'partner_type_id' => $result['partner_type_id']));
        }

        // $this->data['partner_type_id'] = 1;

        $this->data['href_get_sub_projects'] = $this->url->link($this->getAlias() . '/getSubProjects', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['href_get_partner_json'] = $this->url->link($this->getAlias() . '/getPartnerJson', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['href_get_document_ledger'] = $this->url->link('common/function/getDocumentLedger', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['href_get_partner'] = $this->url->link($this->getAlias() . '/getPartner', 'token=' . $this->session->data['token']);
        $this->data['href_get_documents'] = $this->url->link($this->getAlias() . '/getDocuments', 'token=' . $this->session->data['token']);
        $this->data['href_get_document_data'] = $this->url->link($this->getAlias() . '/getDocumentData', 'token=' . $this->session->data['token']);
        $this->data['action_post'] = $this->url->link($this->getAlias() . '/post', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_print'] = $this->url->link($this->getAlias() . '/printDocument', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');
        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['strValidation'] = "{
            'rules': {
                'document_date': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'transaction_account_id': {'required': true},
                'total_net_amount': {'required': true},
                'total_amount':{'required': true, 'min':'1'}
            },
            messages: {
            document_date:{
                remote: 'Invalid Date'
            }}
        }";

        $this->response->setOutput($this->render());
    }

    public function getSubProjects() {
        $project_id = $this->request->post['project_id'];
        $sub_project_id = $this->request->post['sub_project_id'];

        $this->model['sub_project'] = $this->load->model('setup/sub_project');
        $sub_projects = $this->model['sub_project']->getRows(array('company_id' => $this->session->data['company_id'], 'project_id' => $project_id));

         $html = '<option value="">&nbsp;</option>';
        foreach($sub_projects as $sub_project) {

            if( $sub_project_id == $sub_project['sub_project_id'] )
            {
                $html .= '<option value="'.$sub_project['sub_project_id'].'" selected="true">'.$sub_project['name'].'</option>';
            }
            else
            {
                $html .= '<option value="'.$sub_project['sub_project_id'].'">'.$sub_project['name'].'</option>';
            }
        }

        $json = array(
            'success' => true,
            'html' => $html
        );

        echo json_encode($json);
    }

    public function getPartnerJson() {
        $search = $this->request->post['q'];
        $page = $this->request->post['page'];

        $this->model['partner'] = $this->load->model('common/partner');
        $rows = $this->model['partner']->getPartnerJson($search, $page, 25, ['partner_type_id' => 1]);

        echo json_encode($rows);
    }

    public function getPartner() {
        $partner_type_id = $this->request->post['partner_type_id'];
        $partner_id = $this->request->post['partner_id'];
        // $this->model['partner'] = $this->load->model('common/partner');
        // $partners = $this->model['partner']->getPartners(array('company_id'=>$this->session->data['company_id'], 'partner_type_id' => $partner_type_id));

        $this->model['partner'] = $this->load->model('common/partner');
        $partners = $this->model['partner']->getRows(array('company_id' => $this->session->data['company_id'], 'partner_type_id' => $partner_type_id));

        // $html = '<option value="">&nbsp;</option>';
        // foreach($partners as $partner) {
        //     $html .= '<option data-wht_tax="'.$partner['wht_tax'].'" data-other_tax="'.$partner['other_tax'].'" value="'.$partner['partner_id'].'" '.($partner['partner_id']==$partner_id?'selected="true"':'').'>'.$partner['name'].'</option>';
        // }

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

    public function getDocuments() {
        $partner_type_id = $this->request->post['partner_type_id'];
        $partner_id = $this->request->post['partner_id'];
        $this->model['document'] = $this->load->model('common/document');
        $this->model['partner'] = $this->load->model('common/partner');

        $partner = $this->model['partner']->getRow(array('partner_type_id' => $partner_type_id, 'partner_id' => $partner_id));
        $COAS = array();
        $COAS[$partner['outstanding_account_id']] = array(
            'coa_level3_id' => $partner['outstanding_account_id'],
            'level3_display_name' => $partner['outstanding_account']
        );
//        $COAS[$partner['cash_account_id']] = array(
//            'coa_level3_id' => $partner['cash_account_id'],
//            'level3_display_name' => $partner['cash_account']
//        );
        $COAS[$partner['advance_account_id']] = array(
            'coa_level3_id' => $partner['advance_account_id'],
            'level3_display_name' => $partner['advance_account']
        );

        $where = " `l`.`partner_type_id` = '".$partner_type_id."' AND `l`.`partner_id` = '".$partner_id."'";
        $where .= "  AND l.company_branch_id = '".$this->session->data['company_branch_id']."' AND l.company_id = '".$this->session->data['company_id']."' ";

        if( $partner_type_id == 1 )
       {
        // Supplier
        $where .= " AND l.ref_document_type_id = '1'";
       } else if( $partner_type_id == 2 ) {
        // Customer
        $where .= " AND l.ref_document_type_id = '3'";
       }

        // $where .= " AND is_post=1";
        $documents = $this->model['document']->getPendingDocuments($where,array('document_date'));
        // d($documents,true);
        $arrDocuments = array();
        $html = '<option value="">&nbsp;</option>';
        foreach($documents as $document) {
            // $model_document_actual = $this->load->model($document['route']);
            // $row = $model_document_actual->getRow(array($document['primary_key_field'] => $document['primary_key_value']));
            // $document['document_tax'] = $row['item_tax'];
            $arrDocuments[$document['ref_document_identity']] = $document;
            $arrDocuments[$document['ref_document_identity']]['href'] = $this->url->link($document['route'] . '/update', 'token=' . $this->session->data['token'] . '&' . $document['primary_key_field'] . '=' . $document['primary_key_value'], 'SSL');

            $html .= '<option value="'.$document['ref_document_identity'].'">'.$document['ref_document_identity'].'</option>';
        }

        $json = array(
            'success' => true,
            'html' => $html,
            'documents' => $arrDocuments,
            'partner_coas' => $COAS,
        );

        echo json_encode($json);
    }

    protected function insertData($data) {
        // d($data,true);
        $this->model['document_type'] = $this->load->model('common/document_type');
        $this->model['document'] = $this->load->model('common/document');
       // $this->model['mapping_account'] = $this->load->model('gl/mapping_coa');
        $this->model['fund_transfer_detail'] = $this->load->model('gl/fund_transfer_detail');
        $this->model['ledger'] = $this->load->model('gl/ledger');

        $document = $this->model['document_type']->getNextDocument($this->document_type_id);

        $data['document_date'] = MySqlDate($data['document_date']);
        $data['instrument_date'] = MySqlDate($data['instrument_date']);
        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['document_type_id'] = $this->document_type_id;
        $data['document_prefix'] = $document['document_prefix'];
        $data['document_no'] = $document['document_no'];
        $data['document_identity'] = $document['document_identity'];
        $data['base_total_amount'] = $data['total_amount'] * $data['conversion_rate'];
        $data['base_total_wht_amount'] = $data['total_wht_amount'] * $data['conversion_rate'];
        $data['base_total_other_tax_amount'] = $data['total_other_tax_amount'] * $data['conversion_rate'];
        $data['base_total_net_amount'] = $data['total_net_amount'] * $data['conversion_rate'];
        $fund_transfer_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $data['document_id'] = $fund_transfer_id;

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

        $gl_data[] = array(
            'coa_id' => $data['transaction_account_id'],
            'document_debit' => 0,
            'document_credit' => $data['total_net_amount'],
            'debit' => 0,
            'credit' => $data['total_net_amount'] * $data['conversion_rate'],
            'remarks' => $data['remarks'],
        );

        $this->model['setting'] = $this->load->model('common/setting');
        $config = $this->model['setting']->getArrays('field','value',array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'gl',
        ));
        $other_tax_account_id = $config['other_tax_account_id'];
        $wht_account_id = $config['withholding_tax_account_id'];

        //d($data, true);
        foreach ($data['fund_transfer_details'] as $sort_order => $detail) {
            $detail['fund_transfer_id'] = $fund_transfer_id;
            $detail['sort_order'] = $sort_order;
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_amount'] = $detail['amount'] * $data['conversion_rate'];
            $detail['base_wht_amount'] = $detail['wht_amount'] * $data['conversion_rate'];
            $detail['base_other_tax_amount'] = $detail['other_tax_amount'] * $data['conversion_rate'];
            $detail['base_net_amount'] = $detail['net_amount'] * $data['conversion_rate'];
            if($detail['cheque_date'] != '') {
                $detail['cheque_date'] = MySqlDate($detail['cheque_date']);
            } else {
                unset($detail['cheque_date']);
            }
            $fund_transfer_detail_id =  $this->model['fund_transfer_detail']->add($this->getAlias(), $detail);

            $gl_data[] = array(
                'document_detail_id' => $fund_transfer_detail_id,
                'coa_id' => $detail['coa_id'],
                'document_currency_id' => $data['document_currency_id'],
                'document_debit' => $detail['amount'],
                'document_credit' => 0,
                'base_currency_id' => $data['base_currency_id'],
                'conversion_rate' => $data['conversion_rate'],
                'debit' => $detail['amount'] * $data['conversion_rate'],
                'credit' => 0,
                'partner_type_id' => $data['partner_type_id'],
                'partner_id' => $data['partner_id'],
                'ref_document_type_id' => $detail['ref_document_type_id'],
                'ref_document_identity' => $detail['ref_document_identity'],
                'remarks' => $detail['remarks'],
            );

            if($detail['wht_amount'] > 0)
                {
                    $gl_data[] = array(
                        'document_detail_id' => $fund_transfer_detail_id,
                        'coa_id' => $wht_account_id,
                        'document_currency_id' => $data['document_currency_id'],
                        'document_debit' => 0,
                        'document_credit' => $detail['wht_amount'],
                        'base_currency_id' => $data['base_currency_id'],
                        'conversion_rate' => $data['conversion_rate'],
                        'debit' => 0,
                        'credit' => $detail['wht_amount'] * $data['conversion_rate'],
                        'partner_type_id' => $data['partner_type_id'],
                        'partner_id' => $data['partner_id'],
                        'ref_document_type_id' => $detail['ref_document_type_id'],
                        'ref_document_identity' => $detail['ref_document_identity'],
                        'remarks' => $detail['remarks'],
                    );        
                }
            

            if($detail['other_tax_amount'] > 0)
                {
                    $gl_data[] = array(
                        'document_detail_id' => $fund_transfer_detail_id,
                        'coa_id' => $other_tax_account_id,
                        'document_currency_id' => $data['document_currency_id'],
                        'document_debit' => 0,
                        'document_credit' => $detail['other_tax_amount'],
                        'base_currency_id' => $data['base_currency_id'],
                        'conversion_rate' => $data['conversion_rate'],
                        'debit' => 0,
                        'credit' => $detail['other_tax_amount'] * $data['conversion_rate'],
                        'partner_type_id' => $data['partner_type_id'],
                        'partner_id' => $data['partner_id'],
                        'ref_document_type_id' => $detail['ref_document_type_id'],
                        'ref_document_identity' => $detail['ref_document_identity'],
                        'remarks' => $detail['remarks'],
                    );        
                }
            
        }

        // d($gl_data,true);

        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $data['document_id'];
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];

            $ledger['project_id'] = $data['project_id'];
            $ledger['sub_project_id'] = $data['sub_project_id'];
            $ledger['job_cart_id'] = $data['job_cart_id'];

            $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);
        }

        return $fund_transfer_id;
    }

    protected function updateData($primary_key, $data) {
        // d($data,true);
        $this->model['document_type'] = $this->load->model('common/document_type');
        $this->model['document'] = $this->load->model('common/document');
        //$this->model['mapping_account'] = $this->load->model('gl/mapping_coa');
        $this->model['fund_transfer_detail'] = $this->load->model('gl/fund_transfer_detail');
        $this->model['ledger'] = $this->load->model('gl/ledger');

        $this->model['document']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));
        $this->model['fund_transfer_detail']->deleteBulk($this->getAlias(), array('fund_transfer_id' => $primary_key));
        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));

        $data['document_date'] = MySqlDate($data['document_date']);
        $data['instrument_date'] = MySqlDate($data['instrument_date']);
        $data['base_total_amount'] = $data['total_amount'] * $data['conversion_rate'];
        $data['base_total_wht_amount'] = $data['total_wht_amount'] * $data['conversion_rate'];
        $data['base_total_other_tax_amount'] = $data['total_other_tax_amount'] * $data['conversion_rate'];
        $data['base_total_net_amount'] = $data['total_net_amount'] * $data['conversion_rate'];
        $fund_transfer_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        $data['document_id'] = $fund_transfer_id;

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

        $gl_data[] = array(
            'coa_id' => $data['transaction_account_id'],
            'document_debit' => 0,
            'document_credit' => $data['total_net_amount'],
            'debit' => 0,
            'credit' => $data['total_net_amount'] * $data['conversion_rate'],
            'remarks' => $data['remarks'],
        );

        $this->model['setting'] = $this->load->model('common/setting');
        $config = $this->model['setting']->getArrays('field','value',array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'gl',
        ));
        $other_tax_account_id = $config['other_tax_account_id'];
        $wht_account_id = $config['withholding_tax_account_id'];

        //d($data, true);
        foreach ($data['fund_transfer_details'] as $sort_order => $detail) {
            $detail['fund_transfer_id'] = $fund_transfer_id;
            $detail['sort_order'] = $sort_order;
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['document_currency_id'] = $data['document_currency_id'];
            $detail['base_currency_id'] = $data['base_currency_id'];
            $detail['conversion_rate'] = $data['conversion_rate'];
            $detail['base_amount'] = $detail['amount'] * $data['conversion_rate'];
            $detail['base_wht_amount'] = $detail['wht_amount'] * $data['conversion_rate'];
            $detail['base_other_tax_amount'] = $detail['other_tax_amount'] * $data['conversion_rate'];
            $detail['base_net_amount'] = $detail['net_amount'] * $data['conversion_rate'];
            if($detail['cheque_date'] != '') {
                $detail['cheque_date'] = MySqlDate($detail['cheque_date']);
            } else {
                unset($detail['cheque_date']);
            }
            $fund_transfer_detail_id =  $this->model['fund_transfer_detail']->add($this->getAlias(), $detail);

            $gl_data[] = array(
                'document_detail_id' => $fund_transfer_detail_id,
                'coa_id' => $detail['coa_id'],
                'document_currency_id' => $data['document_currency_id'],
                'document_debit' => $detail['amount'],
                'document_credit' => 0,
                'base_currency_id' => $data['base_currency_id'],
                'conversion_rate' => $data['conversion_rate'],
                'debit' => $detail['amount'] * $data['conversion_rate'],
                'credit' => 0,
                'partner_type_id' => $data['partner_type_id'],
                'partner_id' => $data['partner_id'],
                'ref_document_type_id' => $detail['ref_document_type_id'],
                'ref_document_identity' => $detail['ref_document_identity'],
                'remarks' => $detail['remarks'],
            );

            if($detail['wht_amount'] > 0)
                {
                    $gl_data[] = array(
                        'document_detail_id' => $fund_transfer_detail_id,
                        'coa_id' => $wht_account_id,
                        'document_currency_id' => $data['document_currency_id'],
                        'document_debit' => 0,
                        'document_credit' => $detail['wht_amount'],
                        'base_currency_id' => $data['base_currency_id'],
                        'conversion_rate' => $data['conversion_rate'],
                        'debit' => 0,
                        'credit' => $detail['wht_amount'] * $data['conversion_rate'],
                        'partner_type_id' => $data['partner_type_id'],
                        'partner_id' => $data['partner_id'],
                        'ref_document_type_id' => $detail['ref_document_type_id'],
                        'ref_document_identity' => $detail['ref_document_identity'],
                        'remarks' => $detail['remarks'],
                    );        
                }
            

            if($detail['other_tax_amount'] > 0)
                {
                    $gl_data[] = array(
                        'document_detail_id' => $fund_transfer_detail_id,
                        'coa_id' => $other_tax_account_id,
                        'document_currency_id' => $data['document_currency_id'],
                        'document_debit' => 0,
                        'document_credit' => $detail['other_tax_amount'],
                        'base_currency_id' => $data['base_currency_id'],
                        'conversion_rate' => $data['conversion_rate'],
                        'debit' => 0,
                        'credit' => $detail['other_tax_amount'] * $data['conversion_rate'],
                        'partner_type_id' => $data['partner_type_id'],
                        'partner_id' => $data['partner_id'],
                        'ref_document_type_id' => $detail['ref_document_type_id'],
                        'ref_document_identity' => $detail['ref_document_identity'],
                        'remarks' => $detail['remarks'],
                    );        
                }
        }

        foreach($gl_data as $sort_order => $ledger) {
            $ledger['company_id'] = $this->session->data['company_id'];
            $ledger['company_branch_id'] = $this->session->data['company_branch_id'];
            $ledger['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $ledger['document_type_id'] = $this->document_type_id;
            $ledger['document_id'] = $data['document_id'];
            $ledger['document_identity'] = $data['document_identity'];
            $ledger['document_date'] = $data['document_date'];


            $ledger['project_id'] = $data['project_id'];
            $ledger['sub_project_id'] = $data['sub_project_id'];
            $ledger['job_cart_id'] = $data['job_cart_id'];


            $ledger_id = $this->model['ledger']->add($this->getAlias(), $ledger);
        }
    }

    protected function deleteData($primary_key) {
        $this->model['document'] = $this->load->model('common/document');
        $this->model['fund_transfer_detail'] = $this->load->model('gl/fund_transfer_detail');
        $this->model['ledger'] = $this->load->model('gl/ledger');

        $this->model['ledger']->deleteBulk($this->getAlias(), array('document_id' => $primary_key));
        $this->model['fund_transfer_detail']->deleteBulk($this->getAlias(), array('fund_transfer_id' => $primary_key));
        $this->model['document']->delete($this->getAlias(), $primary_key);
        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);

    }

    public function ajaxValidateForm() {
        $post  = $this->request->post;
        $ID = $this->request->get;
//        d($ID,true);
//       d($post,true);
        $lang = $this->load->language($this->getAlias());
        $error = array();

        if($post['voucher_date'] == '') {
            $error[] = $lang['error_voucher_date'];
        }
        if($post['transaction_account_id'] == '') {
            $error[] = $lang['error_transaction_account'];
        }
        if($post['document_currency_id'] == '') {
            $error[] = $lang['error_document_currency'];
        }
        if($post['conversion_rate'] == '' || $post['conversion_rate'] <= 0 ) {
            $error[] = $lang['error_conversion_rate'];
        }

        $conversation = $post['conversion_rate'];
        $details = $post['fund_transfer_details'];
        //d($details,true);
        if($post['people_id'] && $post['people_id'] != "") {
            $filter = array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $this->session->data['company_branch_id'],
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'people_type_id' => $post['people_type_id'],
                'people_id' => $post['people_id'],
            );

            $this->model['ledger'] = $this->load->model('gl/ledger');

            $Amount =array();
            foreach($details as $stock){
                if(isset($Amount[$stock['ref_document_identity']])) {
                    $Amount[$stock['ref_document_identity']] += $stock['amount'];
                } else
                {
                    $Amount[$stock['ref_document_identity']] = $stock['amount'];
                }
            }
//d($Amount,true);
            foreach($Amount as  $ref_document_id => $amount)
            {

                $filter ['ref_document_id'] = $ref_document_id;
                $filter ['document_id'] = $ID['fund_transfer_id'];

                $outstanding = $this->model['ledger']->getDocumentOutstanding($filter);
//                    d(array($outstanding,$amount),true);
                if($outstanding['outstanding'] < $amount * $conversation)
                {
                    $error[] =  $lang['error_amounts'] ;
                    $error[] =   ' Ref Document No= ' . $ref_document_id .' , Invoice Amount= ' . $outstanding['outstanding'] . ' , Pay Amount= '. $amount * $conversation;
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

                if($detail['coa_id'] == '') {
                    $error[] = $lang['error_coa_id'] . ' for Row ' . $row_no;
                }

                if($detail['amount'] == '') {
                    $error[] = $lang['error_amount'] . ' for Row ' . $row_no;
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
    }

    public function printDocument() {

        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $lang = $this->load->language($this->getAlias());
        $fund_transfer_id = $this->request->get['fund_transfer_id'];
        $post = $this->request->post;
        $session = $this->session->data;

        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->model['fund_transfer'] = $this->load->model('gl/fund_transfer');
        $this->model['fund_transfer_detail'] = $this->load->model('gl/fund_transfer_detail');

        $this->model['coa'] = $this->load->model('gl/coa');


        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $voucher = $this->model['fund_transfer']->getRow(array('fund_transfer_id' => $fund_transfer_id));
        $TransactionAccount = $this->model['coa']->getRow(array('coa_level3_id' => $voucher['transaction_account_id']));

        $voucherDetails = $this->model['fund_transfer_detail']->getRows(array('fund_transfer_id' => $fund_transfer_id));
//        d($voucherDetails,true);


        $this->model['ledger'] = $this->load->model('gl/ledger');
        $LedgerDetails = $this->model['ledger']->getTransactionLedger($this->document_type_id,$fund_transfer_id);

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
        $pdf->SetAuthor('Muhammad Salman');
        $pdf->SetTitle('Fund Transfer');
        $pdf->SetSubject('Fund Transfer');

        //Set Header
        $pdf->data = array(
            'company_name' => $branch['name'],
            'address' => $company['address'],
            'ntn' => $company['gst_no'],
            'email_address' => $company['email'],
            'company_phone' => $company['phone_no'],
            'report_name' => 'Fund Transfer',
            'partner_name' => $voucher['partner_name'],
            'cost_center_name' => $voucher['cost_center_name'],
            'company_logo' => $session['company_image'],
            'company_header_print' => $company_header_print,
            'company_footer_print' => $company_footer_print
        );

        // set margins
//        $pdf->SetMargins(15, 5, 5);
//        $pdf->SetHeaderMargin(2);
//        $pdf->SetFooterMargin(2);

        $pdf->SetMargins(15, 30, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 40);

        // add a page
        $pdf->AddPage();
        //$pdf->ln(20);
        $pdf->SetFont('times', 'B', 12);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(190, 7, 'FUND TRANSFER', 'T,B', false, 'C', 1, '', 1);
        $pdf->ln(10);

        $pdf->SetFont('times', 'B', 8);
        $pdf->Cell(20, 6, 'Doc. No: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->SetFont('times', '', 8);
        $pdf->Cell(40, 6, $voucher['document_identity'], 0, false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->SetFont('times', 'B', 8);
        $pdf->Cell(20, 6, 'Doc. Date: ', 0, false, 'R', 0, '', 0, false, 'M', 'M');

        $pdf->SetFont('times', '', 8);
        $pdf->Cell(20, 6, stdDate($voucher['document_date']), 0, false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->ln(6);
        
        $pdf->SetFont('times', 'B', 8);
        $pdf->Cell(20, 6, 'Instrument No: ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        
        $pdf->SetFont('times', '', 8);
        $pdf->Cell(40, 6, $voucher['instrument_no'], 0, false, 'L', 0, '', 0, false, 'M', 'M');

        $pdf->SetFont('times', 'B', 8);
        $pdf->Cell(20, 6, 'Instrument Date: ', 0, false, 'R', 0, '', 0, false, 'M', 'M');

        $pdf->SetFont('times', '', 8);
        $pdf->Cell(20, 6, stdDate($voucher['instrument_date']), 0, false, 'L', 0, '', 0, false, 'M', 'M');
        

        $pdf->ln(6);


        $pdf->SetFont('times', 'B', 8);

        $pdf->Cell(18, 6, 'Remarks : ', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $pdf->SetFont('times','', 8);
        $pdf->SetFillColor(255,255,255);
        $pdf->MultiCell(160, 8, html_entity_decode($voucher['remarks']), 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');

        $pdf->ln(9);

        $pdf->SetFont('times', 'B', 8);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(25, 7, 'Code.', 'T,B', false, 'L', 1, '', 1);
        $pdf->Cell(105, 7, 'Accounts Description', 'T,B', false, 'L', 1, '', 1);
        $pdf->Cell(30, 7, 'Debit', 'T,B', false, 'C', 1, '', 1);
        $pdf->Cell(30, 7, 'Credit', 'T,B', false, 'C', 1, '', 1);

        $pdf->SetFont('times', '', 8);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $total_debit = 0;
        $total_credit = 0;
        $pdf->ln(15);

        foreach($LedgerDetails as $LedgerDetail) {
            if($LedgerDetail['debit'] > 0 || $LedgerDetail['credit'] > 0)
            {
                $pdf->Cell(25, 6, $LedgerDetail['level1_code'].'-'.$LedgerDetail['level2_code'].'-'.$LedgerDetail['level3_code'], 0, false, 'LB', 0, '', 1,false, 'T', 'T');
                $pdf->MultiCell(105, 15, html_entity_decode($LedgerDetail['level3_name'])."\n".html_entity_decode($LedgerDetail['remarks']), 0, 'L', 1, 0, '', '', true, 0, false, true, 40, 'T');
                //$pdf->Cell(115, 6, html_entity_decode($LedgerDetail['level4_name']), 0, false, 'C', 0, '', 1);
                $pdf->Cell(30, 6, number_format($LedgerDetail['debit'],2), 0, false, 'R', 0, '', 1,false, 'T', 'T');
                $pdf->Cell(30, 6, number_format($LedgerDetail['credit'],2), 0, false, 'R', 0, '', 1,false, 'T', 'T');
                $pdf->ln(18);
            $total_debit += $LedgerDetail['debit'];
            $total_credit += $LedgerDetail['credit'];
        }
        }
        //$pdf->Ln(6);
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);
        $pdf->SetFont('times', 'B', 8);

        //$pdf->Ln(10);
        $pdf->Cell(1, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(134, 5, 'Totals', 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(25, 5, number_format($total_debit,2), 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(30, 5, number_format($total_credit,2), 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Ln(7);
        $pdf->SetFont('times', '', 8);
        $pdf->Cell(30, 6, 'Amount in Word: ', 0, false, 'L', 0, '', 1);
        $pdf->SetFont('times', 'B', 8);

        $pdf->Cell(160, 6, 'Rupees '.Number2Words(round($total_debit,2)).' Only.', 0, false, 'L', 0, '', 1);

        $pdf->Ln(30);
        $pdf->SetFont('times', 'B', 8);
        //$pdf->Cell(180, 4, '', 'B', false, 'L', 0, '', 0, false, 'M', 'M');
        //$pdf->Ln(10);

        $pdf->Cell(33, 7, 'Prepared By', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(16, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(33, 7, 'Checked By', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(16, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(33, 7, 'Approved By', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(16, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(33, 7, 'Received By', 'T', false, 'C', 0, '', 0, false, 'M', 'M');

        $pdf->SetMargins(15, 36, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->AddPage();

        if(count($voucherDetails) > 0)
        {
            $pdf->setPage(2);
        }
        $pdf->SetFont('times', '', 7);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        $total_amount = 0;
        $pdf->ln(22);
        // d($voucherDetails, true);

        $this->model['coa'] = $this->load->model('gl/coa_level3');

        foreach($voucherDetails as $detail) {
            $pdf->ln(7);

            $account = $this->model['coa']->getRow(array(
                'company_id' => $this->session->data['company_id'],
                'coa_level3_id' => $detail['coa_id']
            ));

            $pdf->Cell(82, 7, $account['level3_display_name'], 0, false, 'L', 0, '', 1);
            $pdf->Cell(82, 7, $detail['remarks'], 0, false, 'L', 0, '', 1);
            $pdf->Cell(25, 7, number_format($detail['amount']), 0, false, 'R', 0, '', 1);
            $total_amount += $detail['amount'];
        }
        $pdf->ln(2);


        $pdf->SetFont('times', '', 7);


        $pdf->Ln(10);
        $pdf->Cell(82, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(82, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(25, 7, number_format($total_amount,2), 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');

        $pdf->Ln(30);
        $pdf->SetFont('times', 'B', 8);

        $pdf->Cell(33, 7, 'Prepared By', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(16, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(33, 7, 'Checked By', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(16, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(33, 7, 'Approved By', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(16, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(33, 7, 'Received By', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
        //Close and output PDF document
        $pdf->Output('Bank Receipt:'.date('YmdHis').'.pdf', 'I');
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
        if ($this->page == 1) {
            $this->SetFont('helvetica', 'B', 20);
            //$this->Ln(22);
            // Title
            //$this->Cell(0, 10, $this->data['company_name'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
           //$this->Ln(8);
            $this->SetFont('helvetica', '', 8);
            //$this->Cell(0, 8, $this->data['address'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Ln(4);
            //$this->Cell(0, 8,'Email: '.$this->data['email_address'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->Ln(4);
           // $this->Cell(60, 8,'NTN: '.$this->data['ntn'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
            //$this->Ln(4);
            // $this->Cell(0, 8,'STRN: '.$this->data['strn'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
            // $this->Ln(10);

        } else {

            $this->SetFont('helvetica', 'B', 20);
            $this->Ln(22);
            // Title
            //$this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
            //$this->Ln(10);

            $this->SetFont('helvetica', '', 16);
            $this->Cell(0, 8, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
            $this->Ln(10);

            $this->SetFont('times', 'B', 10);
            $this->SetFillColor(215, 215, 215);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(190, 6, 'Transfer Detail', 0, false, 'C', 1, '', 1);


            $this->ln(8);
            $this->SetFont('times', '', 7);
            $this->SetFillColor(255, 255, 255);
            $this->Cell(82, 7, 'Account', 'T,B', false, 'L', 1, '', 1);
            $this->Cell(82, 7, 'Remarks', 'T,B', false, 'L', 1, '', 1);
            $this->Cell(25, 7, 'Amount', 'T,B', false, 'R', 1, '', 1);

        }
    }

    // Page footer
    public function Footer() {
        $this->SetY(-25);
        $y = $this->GetY();
        if($this->data['company_footer_print'] != '') {
            $image_file = DIR_IMAGE.$this->data['company_footer_print'];
            // $this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
            //$this->Image($image_file, 5, ($y-10), 200, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        // Set font
//        $this->SetFont('helvetica', 'B', 7);
        // Page number
        //      $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
//        if ($this->page != 1) {
//        $this->Cell(180, 4, '', 'B', false, 'L', 0, '', 0, false, 'M', 'M');
//        $this->Ln(10);
//        $this->Ln(5);
//        $this->Cell(25, 7, 'Prepared By', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
//        $this->Cell(10, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//        $this->Cell(25, 7, 'Checked By', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
//        $this->Cell(10, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//        $this->Cell(25, 7, 'Approved By', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
//        $this->Cell(10, 7, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//        $this->Cell(40, 7, 'Vice President Ind.Division', 'T', false, 'C', 0, '', 0, false, 'M', 'M');
//        }
        //           $this->Ln(7);
//        $this->SetFont('helvetica', 'B', 8);
//        $this->Cell(0, 5, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

}

?>