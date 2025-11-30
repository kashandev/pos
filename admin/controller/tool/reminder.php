<?php

class ControllerToolReminder extends HController {

    protected function validateDocument() {
        return false;
    }

    protected function getAlias() {
        return 'tool/reminder';
    }

    protected function getPrimaryKey() {
        return 'reminder_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {
        $this->load->language('tool/reminder');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action','event_title', 'event_date_time','repeat','created_at','check_box');

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
        $arrWhere[] = "`fiscal_year_id` = '".$this->session->data['fiscal_year_id']."'";
        $arrWhere[] = "`created_by_id` = '".$this->session->data['user_id']."'";
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

        //d(array($this->model[$this->getAlias()], $data), true);
        $results = $this->model[$this->getAlias()]->getLists($data);
        $iFilteredTotal = $results['total'];
        $iTotal = $results['table_total'];
        //d(array($data, $results), true);

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
                } elseif ($aColumns[$i] == 'repeat') {
                    $row[] = $aRow['repeat_no'] . ' ' . $aRow['repeat_type'];
                } elseif ($aColumns[$i] == 'event_date_time') {
                    $row[] = stdDateTime($aRow['event_date_time']);
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

        $this->data['remind_before_no'] = 0;
        $this->data['repeat_no'] = 0;
        if (isset($this->request->get['reminder_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach($result as $field => $value) {
                $this->data[$field] = $value;
            }

            $this->model['reminder_email'] = $this->load->model('tool/reminder_email');
            $this->data['reminder_emails'] = $this->model['reminder_email']->getRows(array('reminder_id' => $result['reminder_id']));
        }

        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&brand_id=' . $this->request->get['brand_id']);
        $this->data['strValidation']="{
            'rules':{
                'event_title': {'required':true},
                'event_date_time': {'required':true},
                'description': {'required':true},
                'remind_before_no': {'required':true},
                'repeat_no': {'required':true},
            },
        }";

        $this->response->setOutput($this->render());
    }

    protected function insertData($data) {
        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['event_date_time'] = MySqlDateTime($data['event_date_time']);
        $data['next_reminder'] = MySqlDateTime($data['event_date_time']);

        $reminder_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $this->model['reminder_email'] = $this->load->model('tool/reminder_email');
        foreach($data['reminder_emails'] as $email_data) {
            $email_data['company_id'] = $this->session->data['company_id'];
            $email_data['company_branch_id'] = $this->session->data['company_branch_id'];
            $email_data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $email_data['reminder_id'] = $reminder_id;

            $reminder_email_id = $this->model['reminder_email']->add($this->getAlias(), $email_data);
        }

        return $reminder_id;
    }

    protected function updateData($primary_key, $data) {
        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['event_date_time'] = MySqlDateTime($data['event_date_time']);

        $reminder_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        $this->model['reminder_email'] = $this->load->model('tool/reminder_email');
        $this->model['reminder_email']->deleteBulk($this->getAlias(), array('company_id' => $data['company_id'], 'company_branch_id' => $data['company_branch_id'], 'fiscal_year_id' => $data['fiscal_year_id'], 'reminder_id' => $reminder_id));
        foreach($data['reminder_emails'] as $email_data) {
            $email_data['company_id'] = $this->session->data['company_id'];
            $email_data['company_branch_id'] = $this->session->data['company_branch_id'];
            $email_data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $email_data['reminder_id'] = $reminder_id;

            $reminder_email_id = $this->model['reminder_email']->add($this->getAlias(), $email_data);
        }

        return $reminder_id;
    }

    protected function deleteData($primary_key) {
        $this->model['reminder_email'] = $this->load->model('tool/reminder_email');
        $this->model['reminder_email']->deleteBulk($this->getAlias(), array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id'], 'fiscal_year_id' => $this->session->data['fiscal_year_id'], 'reminder_id' => $primary_key));
        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

}

?>