<?php

class ControllerUserUserPermission extends HController {

    protected function getAlias() {
        return 'user/user_permission';
    }

    protected function getPrimaryKey() {
        return 'user_permission_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {

        $this->data['lang'] = $this->load->language('user/user_permission');
        //d($this->data['lang'], true);
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action','name', 'check_box');

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

        $this->model['company'] = $this->load->model('setup/company');
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $form_access = unserialize($company['form_access']);
        $user_permissions = array();
        if (isset($this->request->get[$this->getPrimaryKey()]) && $this->request->server['REQUEST_METHOD'] != 'POST') {
            $result = $this->model[$this->getAlias()]->getRow(array('user_permission_id' => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                if($field == 'permission') {
                    $user_permissions = unserialize($value);
                } else {
                    $this->data[$field] = $value;
                }
            }
        }

        $this->data['permissions'] = array();
        foreach ($form_access as $permission => $v) {
            $this->data['permissions'][$permission] = array(
                'view' => $user_permissions[$permission]['view']?1:0,
                'insert' => $user_permissions[$permission]['insert']?1:0,
                'update' => $user_permissions[$permission]['update']?1:0,
                'delete' => $user_permissions[$permission]['delete']?1:0,
                'post' => $user_permissions[$permission]['post']?1:0,
            );
        }

        $this->data['action_validate_user_group'] = $this->url->link($this->getAlias() .'/validateUserGroup', 'token=' . $this->session->data['token'] . '&user_permission_id=' . $this->request->get['user_permission_id']);
        $this->data['strValidation'] = "{
            'rules': {
                'name': {'required': true, 'minlength': 3, 'maxlength': 32, 'remote':  {url: '".$this->data['action_validate_user_group']."', type: 'post'}},
            },
        }";

        $this->response->setOutput($this->render());
    }

    protected function validateForm() {
        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    protected function validateUpdate() {
        return true;
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
        foreach($ids as $id) {
            $row = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey()=>$id));

            $count = $this->model['user']->getCount(array($this->getPrimaryKey() => $id));
            if($count > 0) {
                $arrError[] = $row['name'] . ': ' . $count . ' User[s] attached.';
            }

        }
        if($arrError) {
            $strError = $this->data['lang']['error_delete'];
            $strError .= '<br /><b>' . implode('<br />', $arrError) . '</b>';
            $this->error['warning'] = $strError;
        }

        if (!$this->error) {
            return true;
        } else {
            $this->session->data['error'] = $this->error['warning'];
            return false;
        }
    }

    protected function insertData($data) {
        $data['permission'] = serialize($data['permission']);
        $data['company_id'] = $this->session->data['company_id'];
        return $this->model[$this->getAlias()]->add($this->getAlias(), $data);
    }

    protected function updateData($primary_key, $data) {
//        d($data,true);
        $data['permission'] = serialize($data['permission']);
        $data['company_id'] = $this->session->data['company_id'];
        return $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
    }

}

?>