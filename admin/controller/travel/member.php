<?php

class ControllerTravelMember extends HController {

    protected function validateDocument() {
        return false;
    }

    protected function getAlias() {
        return 'travel/member';
    }

    protected function getPrimaryKey() {
        return 'member_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {
        $this->load->language('travel/member');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action', 'name','phone','mobile','email','created_at');

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
        //$arrWhere[] = "`company_id` = '".$this->session->data['company_id']."'";
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

        $this->model['module_setting'] = $this->load->model('common/setting');
        $this->model['coa'] = $this->load->model('gl/coa_level3');

        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'travel',
            'field' => 'outstanding_account_id',
        );
        $accounts = $this->model['module_setting']->getRows($filter);
        foreach($accounts as $account) {
            $this->data['outstanding_accounts'][] = $this->model['coa']->getRow(array('coa_level3_id' => $account['value']));
        }

        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'travel',
            'field' => 'advance_account_id',
        );
        $accounts = $this->model['module_setting']->getRows($filter);
        foreach($accounts as $account) {
            $this->data['advance_accounts'][] = $this->model['coa']->getRow(array('coa_level3_id' => $account['value']));
        }


        if (isset($this->request->get['member_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach($result as $field => $value) {
                if($field == 'dob') {
                    $from = new DateTime($value);
                    $to   = new DateTime('today');
                    $this->data['age'] = $from->diff($to)->y;
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }

            $this->model['member_family'] = $this->load->model('travel/member_family');
            $family_members = $this->model['member_family']->getRows(array('member_id' => $result['member_id']), array('sort_order'));
            foreach($family_members as $member) {
                $from = new DateTime($member['dob']);
                $to   = new DateTime('today');
                $member['age'] = $from->diff($to)->y;
                $member['dob'] = stdDate($member['dob']);
                $this->data['family_members'][] = $member;
            }
        }

        $this->data['href_send_registration_code'] = $this->url->link($this->getAlias() . '/sendRegistrationCode', 'token=' . $this->session->data['token'] . '&member_id=' . $this->request->get['member_id']);
        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&member_id=' . $this->request->get['member_id']);
        $this->data['strValidation']="{
            'rules':{
                'name': {'required':true, 'minlength': 3, 'remote':  {url: '" . $this->data['action_validate_name'] . "', type: 'post'}},
                'dob': {'required':true,},
                'outstanding_account_id': {'required':true,},
                'revenue_account_id': {'required':true,},
            },
            ignore: [],
        }";

        $this->response->setOutput($this->render());
    }

    public function validateName()
    {
        $member_name = $this->request->post['name'];
        $member_id = $this->request->get['member_id'];

        $this->load->language('travel/member');
        if ($member_name) {
            $this->model['member'] = $this->load->model('travel/member');
            $arrWhere = array();
            //$arrWhere[] = "`company_id` = '".$company_id."'";
            $arrWhere[] = "LOWER(`name`) = '".strtolower($member_name)."'";
            $arrWhere[] = "`member_id` != '".$member_id."'";
            $where = implode(' AND ', $arrWhere);
            $row = $this->model['member']->getRow($where);
            if ($row) {
                echo json_encode($this->language->get('error_duplicate_name'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_name'));
        }
        exit;
    }

    protected function insertData($data) {
        $this->model['member_family'] = $this->load->model('travel/member_family');
        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['dob'] = MySqlDate($data['dob']);
        $member_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);

        $this->model['partner'] = $this->load->model('common/partner');
        $partner = $data;
        $partner['partner_type_id'] = 2;
        $partner['partner_type'] = 'Customer';
        $partner['partner_id'] = $member_id;
        $this->model['partner']->add($this->getAlias(), $partner);

        foreach($data['family_members'] as $sort_order => $member) {
            $member['member_id'] = $member_id;
            $member['sort_order'] = $sort_order;
            $member['dob'] = MySqlDate($member['dob']);

            $this->model['member_family']->add($this->getAlias(), $member);
        }
        return $member_id;
    }

    protected function updateData($primary_key, $data) {
        $this->model['member_family'] = $this->load->model('travel/member_family');
        $this->model['member_family']->deleteBulk($this->getAlias(), array('member_id' => $primary_key));
        $data['dob'] = MySqlDate($data['dob']);
        $member_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        foreach($data['family_members'] as $sort_order => $member) {
            $member['member_id'] = $member_id;
            $member['sort_order'] = $sort_order;
            $member['dob'] = MySqlDate($member['dob']);

            $this->model['member_family']->add($this->getAlias(), $member);
        }

        $this->model['partner'] = $this->load->model('common/partner');
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'partner_id' => $member_id
        );
        $partner = $this->model['partner']->getRow($filter);
        //d(array($filter, $partner), true);
        if(empty($partner)) {
            $data['company_id'] = $this->session->data['company_id'];
            $data['company_branch_id'] = $this->session->data['company_branch_id'];
            $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $data['partner_type_id'] = 2;
            $data['partner_type'] = 'Customer';
            $data['partner_id'] = $member_id;
            $this->model['partner']->add($this->getAlias(), $data);
        } else {
            $this->model['partner']->edit($this->getAlias(), $primary_key, $data);
        }
        return $member_id;

    }

    protected function deleteData($primary_key) {
        $this->model['member_family'] = $this->load->model('travel/member_family');
        $this->model['partner'] = $this->load->model('common/partner');
        $this->model['member_family']->deleteBulk($this->getAlias(), array('member_id' => $primary_key));
        $this->model['partner']->delete($this->getAlias(), $primary_key);
        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

    public function sendRegistrationCode() {
        require_once DIR_SYSTEM . '/library/WhatsApp/Registration.php';
        $user_name = $this->request->post['user_name'];
        $debug = true;
        $w = new Registration($user_name, $debug);
        try {
            $w->codeRequest("sms");
            echo json_encode("Verification Code Sent via SMS");
        } catch(Exception $e) {
            echo json_encode($e);
        }
        exit(0);
    }

}

?>