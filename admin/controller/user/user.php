<?php

class ControllerUserUser extends HController
{

    protected function getAlias()
    {
        return 'user/user';
    }

    protected function getPrimaryKey()
    {
        return 'user_id';
    }

    protected function getList()
    {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists()
    {

        $this->data['lang'] = $this->load->language('user/user');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        // d($this->model[$this->getAlias()],true);
        $data = array();
        if($this->user->getCompanyId()==0) {
            $aColumns = array('action', 'company_id', 'login_name', 'user_name', 'email', 'created_at', 'check_box');
        } else {
            $aColumns = array('action', 'login_name', 'user_name', 'email', 'created_at', 'check_box');
        }

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

    protected function getForm()
    {
        parent::getForm();

        $this->model['company'] = $this->load->model('setup/company');
        $this->data['companies'] = $this->model['company']->getRows(array('status' => 'Active'));

        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $this->data['branches'] = $this->model['company_branch']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['user_permission'] = $this->load->model('user/user_permission');
        $this->data['user_permissions'] = $this->model['user_permission']->getRows(array('company_id' => $this->session->data['company_id']));

        if (isset($this->request->get['user_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                $this->data[$field] = $value;
            }
            $this->data['companies'] = $this->model['company']->getRows();

            $this->model['user_branch_access'] = $this->load->model('user/user_branch_access');
            $this->data['arrBranchAccess'] = $this->model['user_branch_access']->getArrays('company_branch_id','company_branch_id', array('user_id' => $this->request->get['user_id']));
        }

        $this->model['image'] = $this->load->model('tool/image');
        $this->data['no_image'] = $this->model['image']->resize('no_user.jpg', 300, 300);

        if ($this->data['user_image'] && file_exists(DIR_IMAGE . $this->data['user_image']) && is_file(DIR_IMAGE . $this->data['user_image'])) {
            $this->data['src_user_image'] = $this->model['image']->resize($this->data['user_image'], 300, 300);
        } else {
            $this->data['src_user_image'] = $this->model['image']->resize('no_user.jpg', 300, 300);
        }

        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&user_id=' . $this->request->get['user_id']);
        $this->data['action_validate_email'] = $this->url->link($this->getAlias() . '/validateEmail', 'token=' . $this->session->data['token'] . '&user_id=' . $this->request->get['user_id']);
        $this->data['strValidation'] = "{
            'rules':{
                'login_name': {'required':true, 'minlength': 3, 'remote':  {url: '" . $this->data['action_validate_name'] . "', type: 'post'}},
                'user_name': {'required':true},
                'company_id': {'required': true},
                'email': {'email': true, 'required': true, 'remote':  {url: '" . $this->data['action_validate_email'] . "', type: 'post'}},
                'user_permission_id': {'required': true},
                'login_password': {'minlength': 8},
                'confirm': {'equalTo': '#login_password'},
            },
        }";

        $this->response->setOutput($this->render());
    }

    public function validateName()
    {
        $login_name = $this->request->post['login_name'];
        $user_id = $this->request->get['user_id'];
        $this->load->language('user/user');
        if ($login_name) {
            $this->model['user'] = $this->load->model('user/user');
            $where = "LOWER(`login_name`)='".strtolower($login_name)."' AND `user_id` != '".$user_id."'";
            $user = $this->model['user']->getRow($where);
            if ($user) {
                echo json_encode($this->language->get('error_duplicate_login_name'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_invalid'));
        }
        exit;
    }

    public function validateEmail()
    {
        $email = $this->request->post['email'];
        $user_id = $this->request->get['user_id'];
        $this->load->language('user/user');
        if ($email) {
            $this->model['user'] = $this->load->model('user/user');
            $where = "LOWER(`email`)='".strtolower($email)."' AND `user_id` != '".$user_id."'";
            $user = $this->model['user']->getRow($where);
            if ($user) {
                echo json_encode($this->language->get('error_duplicate_email'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_invalid'));
        }
        exit;
    }

    protected function insertData($data)
    {
        //d($data, true);
        if ($data['login_password']) {
            $data['login_password'] = md5($data['login_password']);
        } else {
            unset($data['login_password']);
        }
        //d($data,true);
        $user_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $this->model['user_branch_access'] = $this->load->model('user/user_branch_access');
        $this->model['user_branch_access']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'],'user_id' => $user_id));
        foreach($data['company_branches'] as $user_branch) {
            $user_branch['company_id'] = $this->session->data['company_id'];
            $user_branch['user_id'] = $user_id;

            $this->model['user_branch_access']->add($this->getAlias(), $user_branch);
        }
    }

    protected function updateData($primary_key, $data)
    {
        //d($data, true);
        if ($data['login_password']) {
            $data['login_password'] = md5($data['login_password']);
        } else {
            unset($data['login_password']);
        }

        $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        $this->model['user_branch_access'] = $this->load->model('user/user_branch_access');
        $this->model['user_branch_access']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'], 'user_id' => $primary_key));
        foreach($data['company_branches'] as $user_branch) {
            $user_branch['company_id'] = $this->session->data['company_id'];
            $user_branch['user_id'] = $primary_key;

            $this->model['user_branch_access']->add($this->getAlias(), $user_branch);
        }

    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('delete', $this->getAlias())) {
            $this->error['warning'] = $this->language->get('error_permission_delete');
        }

        if(isset($this->request->post['selected'])) {
            $ids = $this->request->post['selected'];
        } elseif(isset($this->request->get['id'])) {
            $ids = array($this->request->get['id']);
        }

        $arrError = array();

        $this->model['user'] = $this->load->model('user/user');
        foreach($ids as $user_id) {
            if($user_id == $this->user->getId()) {
                $this->error['warning'] = $this->data['lang']['error_delete'];
            }
        }

        if (!$this->error) {
            return true;
        } else {
            $this->session->data['error'] = $this->error['warning'];
            return false;
        }
    }

    protected function validateInsert() {
        if (!$this->user->hasPermission('insert', $this->getAlias())) {
            $this->error['warning'] = $this->language->get('error_permission_insert');
        }

        $company_id = $this->request->post['company_id'];
        if($company_id != 0) {
            $this->model['user'] = $this->load->model($this->getAlias());
            $users = $this->model['user']->getRows(array('company_id' => $company_id));

//            if(count($users) >= 3 ) {
//                $this->error['warning'] = $this->language->get('error_max_user');
//            }
        }

        $this->validateForm();

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

}

?>