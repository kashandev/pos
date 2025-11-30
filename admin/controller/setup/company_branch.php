<?php

class ControllerSetupCompanyBranch extends HController {

    protected function getAlias() {
        return 'setup/company_branch';
    }

    protected function getPrimaryKey() {
        return 'company_branch_id';
    }

    protected function validateDocument() {
        return false;
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    protected function getForm() {
        parent::getForm();
        $this->model['image'] = $this->load->model('tool/image');
        $this->data['no_image'] = $this->model['image']->resize('no_logo.jpg', 300, 100);

        $this->model['core_setting'] = $this->load->model('common/setting');
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->request->get['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
        );
        $results = $this->model['core_setting']->getRows($filter);
        foreach ($results as $result) {
            if($result['field']=='inventory_account_id') {
                $this->data[$result['field']][] = $result['value'];
            } elseif($result['field']=='revenue_account_id') {
                $this->data[$result['field']][] = $result['value'];
            } elseif($result['field']=='cogs_account_id') {
                $this->data[$result['field']][] = $result['value'];
            } elseif($result['field']=='adjustment_account_id') {
                $this->data[$result['field']][] = $result['value'];
            } else {
                $this->data[$result['field']] = $result['value'];
            }
        }
        $this->model['company'] = $this->load->model('setup/company');
        $this->data['companies'] = $this->model['company']->getRows();

        $this->data['no_image'] = $this->data['src_company_header_print'] = $this->model['image']->resize('no_image.png', 1000, 200);
        $this->data['no_image'] = $this->data['src_company_footer_print'] = $this->model['image']->resize('no_image.png', 1000, 200);

        if ($this->data['company_header_print'] && file_exists(DIR_IMAGE . $this->data['company_header_print']) && is_file(DIR_IMAGE . $this->data['company_header_print'])) {
            $this->data['src_company_header_print'] = $this->model['image']->resize($this->data['company_header_print'], 1000, 200);
        } else {
            $this->data['src_company_header_print'] = $this->model['image']->resize('no_image.png', 1000, 200);
        }

        if ($this->data['company_footer_print'] && file_exists(DIR_IMAGE . $this->data['company_footer_print']) && is_file(DIR_IMAGE . $this->data['company_footer_print'])) {
            $this->data['src_company_footer_print'] = $this->model['image']->resize($this->data['company_footer_print'],  1000, 200);
        } else {
            $this->data['src_company_footer_print'] = $this->model['image']->resize('no_image.png',  1000, 200);
        }

        $this->model['document_type'] = $this->load->model('common/document_type');
        $document_types = $this->model['document_type']->getRows();
        foreach($document_types as $row => $document_type) {
            $this->data['document_types'][$document_type['document_type_id']] = $document_type;
        }

        $this->model['coa_level3'] = $this->load->model('gl/coa_level3');
        $this->data['coas'] = $this->model['coa_level3']->getRows(array('company_id' => $this->session->data['company_id']));

        if (isset($this->request->get['company_branch_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array('company_branch_id' => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                $this->data[$field] = $value;
            }

            $this->model['document_prefix'] = $this->load->model('setup/company_branch_document_prefix');
            $document_prefixes = $this->model['document_prefix']->getRows(array('company_branch_id' => $this->request->get['company_branch_id']));
            foreach($document_prefixes as $document_prefix) {
                $this->data['document_types'][$document_prefix['document_type_id']] = $document_prefix;
            }
        }

        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&company_branch_id=' . $this->request->get['company_branch_id']);
        $this->data['action_validate_branch_code'] = $this->url->link($this->getAlias() . '/validateBranchCode', 'token=' . $this->session->data['token'] . '&company_branch_id=' . $this->request->get['company_branch_id']);
        $this->data['strValidation']="{
            'rules':{
                'company_id': {'required':true,},
                'branch_account_id': {'required':true,},
		        'name': {'required':true, 'minlength': 3, 'remote':  {url: '" . $this->data['action_validate_name'] . "', type: 'post', data: {'company_id': function () { return $('#company_id').val(); }}}},
		        'branch_code': {'required':true, 'minlength': 3, 'maxlength': 3, 'remote':  {url: '" . $this->data['action_validate_branch_code'] . "', type: 'post', data: {'company_id': function () { return $('#company_id').val(); }}}},
            },
        }";
        
        $this->response->setOutput($this->render());
    }

    protected function insertData($data) {
        $data['company_id'] = $this->session->data['company_id'];
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $primary_key = $this->model['company_branch']->add($this->getAlias(), $data);
        $this->model['core_setting'] = $this->load->model('common/setting');

        if(isset($data['company_header_print']))
        {
            $insert_data = array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $primary_key,
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'module' => 'general',
                'field' => 'company_header_print',
                'value' => $data['company_header_print'],
            );
            $this->model['core_setting']->add($this->getAlias(), $insert_data);
        }
        if(isset($data['company_footer_print']))
        {
            $insert_data = array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $primary_key,
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'module' => 'general',
                'field' => 'company_footer_print',
                'value' => $data['company_footer_print'],
            );
            $this->model['core_setting']->add($this->getAlias(), $insert_data);
        }
        
        $document_prefixes = $data['company_branch_document_prefixes'];

        $this->model['company_branch_document_prefix'] = $this->load->model('setup/company_branch_document_prefix');
        foreach ($document_prefixes as $document_prefix) {
            $document_prefix['company_id'] = $this->session->data['company_id'];
            $document_prefix['company_branch_id'] = $primary_key;
            $this->model['company_branch_document_prefix']->add($this->getAlias(), $document_prefix);
        }
    }

    protected function updateData($primary_key, $data) {
        // $this->model['company_branch'] = $this->load->model('setup/company_branch');
        // $this->model['company_branch']->edit($this->getAlias(), $primary_key, $data);

        $document_prefixes = $data['company_branch_document_prefixes'];

        $this->model['core_setting'] = $this->load->model('common/setting');

        if(isset($data['company_header_print']))
        {
            $insert_data = array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $primary_key,
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'module' => 'general',
                'field' => 'company_header_print',
                'value' => $data['company_header_print'],
            );
            // d($insert_data,true);
            $where = "company_id=" . $this->session->data['company_id'];
            $where .= " AND company_branch_id='" . $primary_key . "'";
            $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
            $where .= " AND module='general'";
            $where .= " AND field='company_header_print'";
            $this->model['core_setting']->updateSetting($where, $insert_data);
        }
        if(isset($data['company_footer_print']))
        {
            $insert_data = array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $primary_key,
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'module' => 'general',
                'field' => 'company_footer_print',
                'value' => $data['company_footer_print'],
            );
            $where = "company_id=" . $this->session->data['company_id'];
            $where .= " AND company_branch_id='" . $primary_key . "'";
            $where .= " AND fiscal_year_id=" . $this->session->data['fiscal_year_id'];
            $where .= " AND module='general'";
            $where .= " AND field='company_footer_print'";
            $this->model['core_setting']->updateSetting($where, $insert_data);
        }

        $this->model['company_branch_document_prefix'] = $this->load->model('setup/company_branch_document_prefix');
        $this->model['company_branch_document_prefix']->deleteBulk($this->getAlias(), array('company_branch_id' => $primary_key));
        foreach ($document_prefixes as $document_prefix) {
            $document_prefix['company_id'] = $this->session->data['company_id'];
            $document_prefix['company_branch_id'] = $primary_key;
            $this->model['company_branch_document_prefix']->add($this->getAlias(), $document_prefix);
        }
    }

    public function validateName()
    {
        $name = $this->request->post['name'];
        $company_branch_id = $this->request->get['company_branch_id'];
        $company_id = $this->request->post['company_id'];
        $this->load->language('setup/company_branch');
        if ($name) {
            $this->model['company_branch'] = $this->load->model('setup/company_branch');
            $where = "company_id = '".$company_id."' AND LOWER(name) = '".strtolower($name)."' AND company_branch_id != '".$company_branch_id."'";
            $company_branch = $this->model['company_branch']->getRow($where);
            if ($company_branch) {
                echo json_encode($this->language->get('error_duplicate_branch_name'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_invalid'));
        }
        exit;
    }

    public function validateBranchCode() {
        $company_branch_id = $this->request->get['company_branch_id'];
        $branch_code = $this->request->post['branch_code'];
        $company_id = $this->request->post['company_id'];

        $this->load->language('setup/company_branch');
        if ($branch_code) {
            $this->model['company_branch'] = $this->load->model('setup/company_branch');
            $where = "company_id = '".$company_id."' AND LOWER(branch_code) = '".strtolower($branch_code)."' AND company_branch_id != '".$company_branch_id."'";
            $company_branch = $this->model['company_branch']->getRow($where);
            if ($company_branch) {
                echo json_encode($this->language->get('error_duplicate_branch_code'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_invalid'));
        }
        exit;
    }

    public function getAjaxLists()
    {
        $this->init();
        $data = array();
        $aColumns = array('action', 'company_name', 'name', 'created_at');

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
                'text' => $this->data['lang']['edit'],
                'href' => $this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                'btn_class' => 'btn btn-primary btn-xs',
                'class' => 'fa fa-pencil'
            );

            $actions[] = array(
                'text' => $this->data['lang']['delete'],
                'href' => 'javascript:void(0);',
                'click' => "ConfirmDelete('" . $this->url->link($this->getAlias() . '/delete', 'token=' . $this->session->data['token'] . '&id=' . $aRow[$this->getPrimaryKey()], 'SSL') . "')",
                'btn_class' => 'btn btn-danger btn-xs',
                'class' => 'fa fa-times'
            );


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
                } else {
                    $row[] = $aRow[$aColumns[$i]];
                }

            }
            $output['aaData'][] = $row;
        }

        echo json_encode($output);
    }

    protected function validateDelete($id='') {
        if (!$this->user->hasPermission('delete', $this->getAlias())) {
            $this->error['warning'] = $this->language->get('error_permission_delete');
        }

        $company_branch_id = $id;
        $this->model['ledger'] = $this->load->model('gl/ledger');
        $ledgers = $this->model['ledger']->getRows(array('company_branch_id' => $company_branch_id));
        if($ledgers) {
            $this->error['warning'] = $this->language->get('error_delete_branch');
        }
        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }


}

?>