<?php

class ControllerSetupCompany extends HController {

    protected function getAlias() {
        return 'setup/company';
    }

    protected function getPrimaryKey() {
        return 'company_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {

        $this->data['lang'] = $this->load->language('setup/company');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action', 'name', 'status', 'created_at');

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
        if($this->user->getUserPermissionId() != 1) {
            $arrWhere[] = "`company_id` = '".$this->session->data['company_id']."'";
        }
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

    protected function getForm() {
        parent::getForm();

        $this->model['partner_type'] = $this->load->model('common/partner_type');
        $partner_types = $this->model['partner_type']->getRows();
        if (isset($this->request->get['company_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->model['company_branch'] = $this->load->model('setup/company_branch');
            $this->model['currency'] = $this->load->model('setup/currency');
            $this->model['fiscal_year'] = $this->load->model('setup/fiscal_year');
            $this->model['user'] = $this->load->model('user/user');

            $row = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $row['company_branch_id']));
            $currency = $this->model['currency']->getRow(array('currency_id' => $row['base_currency_id']));
            $fiscal_year = $this->model['fiscal_year']->getRow(array('fiscal_year_id' => $row['fiscal_year_id']));
            $user = $this->model['user']->getRow(array('user_id' => $row['user_id']));
            //d(array('company'=> $row, 'branch'=>$branch, 'currency'=>$currency, 'fiscal_year'=>$fiscal_year, 'user'=>$user), true);
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach($result as $field => $value) {
                if($field == 'form_access') {
                    $form_access = unserialize($value);
                    if($form_access == '') {
                        $form_access = array();
                    }
                } elseif($field=='partner_types' && $value != '') {
                    $partner_types = unserialize($value);
                } else {
                    $this->data[$field] = $value;
                }
            }
            //d($partner_types, true);
            $this->data['company_name'] = $row['name'];
            $this->data['branch_code'] = $branch['branch_code'];
            $this->data['branch_name'] = $branch['name'];
            $this->data['default_currency'] = $currency['currency_code'];
            $this->data['fiscal_year_code'] = $fiscal_year['fy_code'];
            $this->data['fiscal_year_title'] = $fiscal_year['name'];
            $this->data['date_from'] = stdDate($fiscal_year['date_from']);
            $this->data['date_to'] = stdDate($fiscal_year['date_to']);
            $this->data['user_name'] = $user['login_name'];
        }

        $this->data['partner_types'] = $partner_types;
        //d($this->data, true);
        $ignore = array(
            'common/filemanager',
            'common/login',
            'common/preset',
            'common/logout',
            'common/forgotten',
            'common/reset',
            'error/not_found',
            'error/permission',
            'error/security',
            'common/header',
            'common/page_header',
            'common/column_left',
            'common/column_right',
            'common/page_footer',
            'common/footer',
            'common/home',
            'common/quick_search'
        );

        $this->data['forms'] = array();
        $files = glob(DIR_APPLICATION . 'controller/*/*.php');

        foreach ($files as $file) {
            $data = explode('/', dirname($file));

            $permission = end($data) . '/' . basename($file, '.php');

            if (!in_array($permission, $ignore)) {
                $this->data['forms'][$permission] = 0;
            }
        }

        if(!empty($form_access)) {
            $this->data['forms'] = array_merge($this->data['forms'], $form_access);
        }

        $this->data['action_validate_company_name'] = $this->url->link($this->getAlias() . '/validateCompanyName', 'token=' . $this->session->data['token'] . '&company_id=' . $this->request->get['company_id']);
        $this->data['action_validate_user_name'] = $this->url->link($this->getAlias() . '/validateUserName', 'token=' . $this->session->data['token'] . '&company_id=' . $this->request->get['company_id']);
        $this->data['strValidation']="{
            'rules':{
                'company_name': {'required':true, 'minlength': 3,'remote':  {url: '" . $this->data['action_validate_company_name'] . "', type: 'post'}},
		        'branch_code': {'required':true, 'minlength':3},
		        'branch_name': {'required':true, 'minlength':3},
		        'default_currency': {'required':true, 'minlength':3},
		        'fiscal_year_code': {'required':true, 'minlength':3},
		        'fiscal_year_title': {'required':true, 'minlength':3},
		        'date_from': {'required':true},
		        'date_to': {'required':true},
                'user_name': {'required':true, 'minlength': 3,'remote':  {url: '" . $this->data['action_validate_user_name'] . "', type: 'post'}},
            },
        }";

        $this->response->setOutput($this->render());
    }

    public function validateCompanyName() {
        $name = $this->request->post['company_name'];
        $company_id = $this->request->get['company_id'];
        $this->load->language('setup/company');
        if ($name) {
            $this->model['company'] = $this->load->model('setup/company');
            $where = "LOWER(name) = '".strtolower($name)."' AND company_id != '".$company_id."'";
            $company = $this->model['company']->getRow($where);
            if ($company) {
                echo json_encode($this->language->get('error_duplicate_company_name'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_name'));
        }
        exit;
    }

    public function validateUserName() {
        $name = $this->request->post['user_name'];
        $company_id = $this->request->get['company_id'];
        $this->load->language('setup/company');
        if ($name) {
            $this->model['user'] = $this->load->model('user/user');
            $where = "LOWER(login_name) = '".strtolower($name)."' AND company_id != '".$company_id."'";
            $company = $this->model['user']->getRow($where);
            if ($company) {
                echo json_encode($this->language->get('error_duplicate_user_name'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_name'));
        }
        exit;
    }

    protected function insertData($data) {
        //d($data, true);
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->model['document_type'] = $this->load->model('common/document_type');
        $this->model['company_branch_document_prefix'] = $this->load->model('setup/company_branch_document_prefix');
        $this->model['fiscal_year'] = $this->load->model('setup/fiscal_year');
        $this->model['user_permission'] = $this->load->model('user/user_permission');
        $this->model['user'] = $this->load->model('user/user');
        $this->model['user_branch_access'] = $this->load->model('user/user_branch_access');
        $this->model['currency'] = $this->load->model('setup/currency');
        $this->model['currency_rate'] = $this->load->model('setup/currency_rate');

        $this->db->beginTransaction();
        $dataCompany = array(
            'name' => $data['company_name'],
            'status' => 'Active',
            'form_access' => serialize($data['form_access']),
            'partner_types' => serialize($data['partner_types'])
        );
        $company_id = $this->model['company']->add($this->getAlias(), $dataCompany);

        $dataCompanyBranch = array(
            'company_id' => $company_id,
            'branch_code' => $data['branch_code'],
            'name' => $data['branch_name'],
        );
        $company_branch_id = $this->model['company_branch']->add($this->getAlias(), $dataCompanyBranch);

        $document_types = $this->model['document_type']->getRows();
        foreach($document_types as $document_type) {
            $dataCompanyBranchDocumentPrefix = array(
                'company_id' => $company_id,
                'company_branch_id' => $company_branch_id,
                'document_type_id' => $document_type['document_type_id'],
                'document_name' => $document_type['document_name'],
                'document_prefix' => $document_type['document_prefix'],
                'zero_padding' => $document_type['zero_padding'],
                'reset_on_fiscal_year' => $document_type['reset_on_fiscal_year'],
                'table_name' => $document_type['table_name'],
                'route' => $document_type['route'],
                'primary_key' => $document_type['primary_key'],
            );
            $company_branch_document_prefix_id = $this->model['company_branch_document_prefix']->add($this->getAlias(), $dataCompanyBranchDocumentPrefix);
        }

        $dataFiscalYear = array(
            'company_id' => $company_id,
            'name' => $data['fiscal_year_title'],
            'fy_code' => $data['fiscal_year_code'],
            'date_from' => MySqlDate($data['date_from']),
            'date_to' => MySqlDate($data['date_to']),
            'status' => 'Active',
        );
        $fiscal_year_id = $this->model['fiscal_year']->add($this->getAlias(), $dataFiscalYear);

        $permission = array();
        foreach($data['form_access'] as $form => $value) {
            $permission[$form] = array(
                'view' => 1,
                'insert' => 1,
                'update' => 1,
                'delete' => 1,
                'post' => 1
            );
        }
        $dataUserPermission = array(
            'company_id' => $company_id,
            'name' => 'System Admin',
            'permission' => serialize($permission)
        );
        $user_permission_id = $this->model['user_permission']->add($this->getAlias(), $dataUserPermission);

        $dataUser = array(
            'company_id' => $company_id,
            'user_permission_id' => $user_permission_id,
            'login_name' => $data['user_name'],
            'login_password' => md5($data['user_password']),
            'user_name' => $data['user_name'],
            'colour_theme' => 'skin-blue',
            'status' => 'Active'
        );
        $user_id = $this->model['user']->add($this->getAlias(), $dataUser);

        $dataUserBranchAccess = array(
            'user_id' => 1,
            'company_id' => $company_id,
            'company_branch_id' => $company_branch_id
        );
        $user_branch_access_id = $this->model['user_branch_access']->add($this->getAlias(), $dataUserBranchAccess);

        $dataUserBranchAccess = array(
            'user_id' => $user_id,
            'company_id' => $company_id,
            'company_branch_id' => $company_branch_id
        );
        $user_branch_access_id = $this->model['user_branch_access']->add($this->getAlias(), $dataUserBranchAccess);

        $dataCurrency = array(
            'company_id' => $company_id,
            'currency_code' => $data['default_currency'],
            'name' => $data['default_currency'],
            'value' => 1
        );
        $currency_id = $this->model['currency']->add($this->getAlias(), $dataCurrency);

        $dataCurrencyRate = array(
            'company_id' => $company_id,
            'currency_id' => $currency_id,
            'date' => date('Y-m-d'),
            'rate' => 1
        );
        $currency_rate_id = $this->model['currency_rate']->add($this->getAlias(), $dataCurrencyRate);

        $dataUpdateCompany = array(
            'company_branch_id' => $company_branch_id,
            'fiscal_year_id' => $fiscal_year_id,
            'user_permission_id' => $user_permission_id,
            'user_id' => $user_id,
            'base_currency_id' => $currency_id,
            'round_decimal_places' => 2,
            'user_branch_access_id' => $user_branch_access_id
        );
        $this->model['company']->edit($this->getAlias(), $company_id, $dataUpdateCompany);

        $this->db->commit();
    }

    protected function updateData($primary_key, $data) {
        $this->model['company'] = $this->load->model('setup/company');
        $dataCompany = array(
            'name' => $data['company_name'],
            'form_access' => serialize($data['form_access']),
            'partner_types' => serialize($data['partner_types'])
        );
        $company_id = $this->model['company']->edit($this->getAlias(), $primary_key, $dataCompany);
    }

    protected function deleteData($primary_key) {
        $tables = array();
        $sql = "SHOW TABLES";
        $query = $this->db->query($sql);
        $rows = $query->rows;
        $sql = '';
        $this->db->beginTransaction();
        $ignoreTables = array(
            'audit',
            'audit_detail',
            'language',
            'country',
            'zone',
            'document_type',
            'gl_type',
            'mapping_type',
            'partner_type',
        );

        foreach($rows as $tables) {
            foreach($tables as $table) {
                if(in_array($table, $ignoreTables) || strpos($table, 'vw_') !== false) {
                    // Do Nothing
                } else {
                    $sql = "DELETE FROM `".$table."` WHERE `company_id` = '".$primary_key."'";
                    $this->db->query($sql);
                }
            }
        }
        $this->db->commit();


        //$this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

    public function getBranches() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['company_id']) {
            $this->model['company_branch'] = $this->load->model('setup/company_branch');
            $branches = $this->model['company_branch']->getCompanyBranches(array('status' => 1, 'company_id' => $this->request->post['company_id']));
            $json = array(
                'success' => true,
                'company_branches' => $branches
            );
        } else {
//            d(array($this->request->server['REQUEST_METHOD'], $this->request->post));
            $this->load->language('setup/company');
            $json = array(
                'success' => false,
                'error' => $this->language->get('error_select_company')
            );
        }
        $this->response->setOutput(json_encode($json));
    }

    public function getFiscalYear() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['company_id']) {
            $this->model['fiscal_year'] = $this->load->model('setup/fiscal_year');
            $fiscal_years = $this->model['fiscal_year']->getFicalYears(array('status' => 1, 'company_id' => $this->request->post['company_id']));

            $json = array(
                'success' => true,
                'fiscal_years' => $fiscal_years
            );

        } else {
            $this->load->language('setup/company');
            $json = array(
                'success' => false,
                'error' => $this->language->get('error_select_company')
            );
        }
        $this->response->setOutput(json_encode($json));
    }

}

?>