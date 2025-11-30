<?php

class ControllerSetupProject extends HController {

    protected function getAlias() {
        return 'setup/project';
    }

    protected function getPrimaryKey() {
        return 'project_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {

        $this->load->language('setup/project');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action', 'name', 'created_at', 'check_box');

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

    protected function insertData($data) {
        $this->model['project'] = $this->load->model('setup/project');

        $project_code = $this->request->post['code'];
        $project = $this->model['project']->getprojectCode();
        $code = $project['code'];
        $code++;
        $code = str_pad($code,4,'0',STR_PAD_LEFT);
        $data['code'] = ($project_code == 'AUTO') ? $code : $project_code;
        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $this->model[$this->getAlias()]->add($this->getAlias(), $data);
    }

    protected function getForm() {
        parent::getForm();

        $this->data['code'] = 'AUTO';

        if (isset($this->request->get['project_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array('project_id' => $this->request->get['project_id']));
            foreach($result as $field => $value) {
                $this->data[$field] = $value;
            }
        }

        $this->data['action_validate_code'] = $this->url->link($this->getAlias() . '/validateCode', 'token=' . $this->session->data['token'] . '&project_id=' . $this->request->get['project_id']);
        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&project_id=' . $this->request->get['project_id']);
        $this->data['strValidation']="{
            'rules':{
		        'name': {'required':true, 'minlength': 3, 'remote':  {url: '" . $this->data['action_validate_name'] . "', type: 'post'}},
            },
        }";

        $this->response->setOutput($this->render());
    }

    public function validateCode()
    {
        $code = $this->request->post['code'];
        $company_id = $this->session->data['company_id'];
        $company_branch_id = $this->session->data['company_branch_id'];
        $project_id = $this->request->get['project_id'];

        $this->load->language('setup/project');
        if ($code) {
            $this->model['project'] = $this->load->model('setup/project');
            $where = "company_id='" . $company_id . "' AND company_branch_id='" . $company_branch_id . "'";
            $where .= " AND LOWER(code) = '".strtolower($code)."' AND project_id != '".$project_id."'";
            $coa = $this->model['project']->getRow($where);
            if ($coa) {
                echo json_encode($this->language->get('error_duplicate_code'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_code'));
        }
        exit;
    }

    public function validateName()
    {
        $name = $this->request->post['name'];
        $company_id = $this->session->data['company_id'];
        $company_branch_id = $this->session->data['company_branch_id'];
        $project_id = $this->request->get['project_id'];

        $this->load->language('setup/project');
        if ($name) {
            $this->model['project'] = $this->load->model('setup/project');
            $where = "company_id='" . $company_id . "' AND company_branch_id='" . $company_branch_id . "'";
            $where .= " AND LOWER(name) = '".strtolower($name)."' AND project_id != '".$project_id."'";
            $coa = $this->model['project']->getRow($where);
            if ($coa) {
                echo json_encode($this->language->get('error_duplicate_name'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_name'));
        }
        exit;
    }


    protected function validateDelete() {

        $error = array();
        if (!$this->user->hasPermission('delete', $this->getAlias())) {
            $this->error['warning'] = $this->language->get('error_permission_delete');
        }

        if (!$this->error) {
            if(isset($this->request->post['selected'])) {
                $ids = $this->request->post['selected'];
            } else {
                $ids = array($this->request->get['id']);
            }

            $this->model['ledger'] = $this->load->model('gl/ledger');
            $lang = $this->load->language('setup/project');
            foreach($ids as $id) {
                $count = $this->model['ledger']->getCount(array('company_id' => $this->session->data['company_id'], 'project_id' => $id));

                if($count > 0) {
                    $project = $this->model['ledger']->getRow(array('company_id' => $this->session->data['company_id'], 'project_id' => $id));
                    $error[] = sprintf($lang['error_delete'], $project['project'], $count);
                }
            }

            $this->error = implode('<br />', $error);
        }

        if (!$this->error) {
            return true;
        } else {
            $this->session->data['error_warning'] = $this->error;
            return false;
        }
    }

}

?>