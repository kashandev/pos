<?php

class ControllerSchoolFeeTemplateClass extends HController {

    protected function getAlias() {
        return 'school/fee_template_class';
    }

    protected function getPrimaryKey() {
        return 'fee_template_class_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {
        $this->data['lang'] = $this->load->language($this->getAlias());
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action','class_name','section_name', 'created_at','check_box');

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
            for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
                if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
                    $arrOrder[] = "`" . $aColumns[intval($_GET['iSortCol_' . $i])] . "` " . ($_GET['sSortDir_' . $i] === 'asc' ? 'asc' : 'desc');
                }
            }

            if(empty($arrOrder)) {
                $data['criteria']['orderby'] = "";
            } else {
                $data['criteria']['orderby'] = " ORDER BY " . implode(',', $arrOrder);
            }
        }


        /*
         * Filtering
         * NOTE this does not match the built-in DataTables filtering which does it
         * word by word on any field. It's possible to do here, but concerned about efficiency
         * on very large tables, and MySQL's regex functionality is very limited
         */
        $sSearch = "";
        if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
            $arrWhere = array();
            for ($i = 0; $i < count($aColumns); $i++) {
                if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch'] != '') {
                    $arrWhere[] = "`" . $aColumns[$i] . "` LIKE '%" . $this->db->escape($_GET['sSearch']) . "%'";
                }
            }
            $sSearch .= '(' . implode(' OR ', $arrWhere) . ')';
        }

        /* Individual column filtering */
        $bSearch = "";
        $arrWhere = array();
        $arrWhere[] = "`company_id` = '".$this->session->data['company_id']."'";
        $arrWhere[] = "`company_branch_id` = '".$this->session->data['company_branch_id']."'";
        $arrWhere[] = "`fiscal_year_id` = '".$this->session->data['fiscal_year_id']."'";
        if($sSearch != '')
            $arrWhere[] = $sSearch;
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
                $arrWhere[] = "`" . $aColumns[$i] . "` LIKE '%" . $this->db->escape($_GET['sSearch_' . $i]) . "%' ";
            }
        }
        $bSearch .= implode(' AND ', $arrWhere);


        if ($bSearch != "") {
            $data['filter']['RAW'] = $bSearch;
        }

        //d($data);
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
                'click' => "ConfirmDelete('".$this->data['lang']['confirm_delete']."','" . $this->url->link($this->getAlias() . '/delete', 'token=' . $this->session->data['token'] . '&id=' . $aRow[$this->getPrimaryKey()], 'SSL') . "')",
                'btn_class' => 'btn btn-danger btn-xs',
                'class' => 'fa fa-times'
            );

            $actions[] = array(
                'text' => $this->data['lang']['clone'],
                'href' => $this->url->link($this->getAlias() . '/insert', 'token=' . $this->session->data['token'] . '&clone_id=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                'btn_class' => 'btn btn-primary btn-xs',
                'class' => 'fa fa-clone'
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
                } elseif ($aColumns[$i] == 'check_box') {
                    $row[] = '<input type="checkbox" name="selected[]" value="' . $aRow[$this->getPrimaryKey()] . '" />';
                } elseif ($aColumns[$i] == 'created_at') {
                    $row[] = stdDateTime($aRow['created_at']);
                } elseif ($aColumns[$i] == 'slot_date') {
                    $row[] = stdDate($aRow['slot_date']);
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

        $this->model['class'] = $this->load->model('school/class');
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id']
        );
        $this->data['classes'] = $this->model['class']->getRows($filter, array('sort_order', 'class_name'));

        $this->model['fee'] = $this->load->model('school/fee');
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
        );
        $this->data['fees'] = $this->model['fee']->getRows($filter, array('fee_name'));

        if (isset($this->request->get['fee_template_class_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array('fee_template_class_id' => $this->request->get['fee_template_class_id']));
            foreach($result as $field => $value) {
                if($field == 'slot_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }
            $this->model['class_section'] = $this->load->model('school/class_section');
            $this->data['sections'] = $this->model['class_section']->getRows(array('class_id' => $result['class_id']));

            $this->model['fee_template_class_detail'] = $this->load->model('school/fee_template_class_detail');
            $template_details = $this->model['fee_template_class_detail']->getRows(array('fee_template_class_id' => $this->request->get['fee_template_class_id']), array('sort_order'));
            foreach($template_details as $detail) {
                $detail['due_month'] = date('M Y', strtotime($detail['due_month']));
                $detail['fee_month'] = date('M Y', strtotime($detail['fee_month']));
                $this->data['template_details'][] = $detail;
            }
        }

        if (isset($this->request->get['clone_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->model['fee_template_class_detail'] = $this->load->model('school/fee_template_class_detail');
            $template_details = $this->model['fee_template_class_detail']->getRows(array('fee_template_class_id' => $this->request->get['clone_id']), array('sort_order'));
            foreach($template_details as $detail) {
                $detail['due_month'] = date('M Y', strtotime($detail['due_month']));
                $detail['fee_month'] = date('M Y', strtotime($detail['fee_month']));
                $this->data['template_details'][] = $detail;
            }
        }

        $this->data['href_get_class_section'] = $this->url->link($this->getAlias() . '/getClassSection', 'token=' . $this->session->data['token']);
        $this->data['href_validate_class_section'] = $this->url->link($this->getAlias() . '/validateClassSection', 'token=' . $this->session->data['token']);
        $this->data['strValidation']="{
            'rules':{
                'class_id': {'required':true},
                'class_section_id': {'required':true, 'remote':  {url: '" . $this->data['href_validate_class_section'] . "', type: 'post'}},
            },
        }";
        $this->response->setOutput($this->render());
    }

    public function getClassSection()
    {
        $class_id = $this->request->post['class_id'];
        $this->model['class_section'] = $this->load->model('school/class_section');
        $rows = $this->model['class_section']->getRows(array('class_id' => $class_id));
        $html = '<option value="">&nbsp;</option>';
        foreach($rows as $row) {
            $html .= '<option value="'.$row['class_section_id'].'">'.$row['section_name'].'</option>';
        }

        $json = array(
            'success' => true,
            'html' => $html
        );

        echo json_encode($json);
        exit;
    }

    public function validateClassSection() {
        $lang = $this->load->language($this->getAlias());
        $fee_template_class_id = $this->request->get['fee_template_class_id'];
        $class_section_id = $this->request->post['class_section_id'];
        $company_id = $this->session->data['company_id'];
        $company_branch_id = $this->session->data['company_branch_id'];
        $fiscal_year_id = $this->session->data['fiscal_year_id'];

        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $where = "`company_id` = '".$company_id."' AND `company_branch_id` = '".$company_branch_id."' AND `fiscal_year_id` = '".$fiscal_year_id."' AND `class_section_id` = '".$class_section_id."' AND `fee_template_class_id` != '".$fee_template_class_id."'";
        $row = $this->model[$this->getAlias()]->getRow($where);
        if($row) {
            echo json_encode($lang['error_duplicate']);
        } else {
            echo json_encode("true");
        }
        exit;
    }

    protected function validateDelete() {
        $lang = $this->load->language($this->getAlias());
        if (!$this->user->hasPermission('delete', $this->getAlias())) {
            $this->error['warning'] = $lang['error_permission_delete'];
        }

        if(isset($this->request->post['selected'])) {
            $ids = $this->request->post['selected'];
        } elseif(isset($this->request->get['id'])) {
            $ids = array($this->request->get['id']);
        }

        $arrError = array();
        $this->model['fee'] = $this->load->model('fee/fee');
        $this->model['fee_template_class_detail'] = $this->load->model('fee/fee_template_class_detail');

        foreach($ids as $fee_template_class_id) {
            $fee = $this->model['fee']->getRow(array('fee_template_class_id'=>$fee_template_class_id));
            $where = "fee_template_class_id = '" . $fee_template_class_id . "' AND (allotted = 'True' OR preregistration_identity != '')";
            $fee_template_class_details = $this->model['fee_template_class_detail']->getRows($where);
            if(count($fee_template_class_details) > 0) {
                $arrError[] = sprintf($lang['error_delete_slot'],'Date:' . stdDate($fee['slot_date']). ', From:' . $fee['time_from'] . ', To:'.$fee['time_to'],count($fee_template_class_details));
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
        $this->model['fee_template_class_detail'] = $this->load->model('school/fee_template_class_detail');
        $details = $data['template_details'];
        unset($data['template_details']);

        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];

        $fee_template_class_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        foreach($details as $sort_order => $detail) {
            $detail['fee_template_class_id'] = $fee_template_class_id;
            $detail['company_id'] = $data['company_id'];
            $detail['company_branch_id'] = $data['company_branch_id'];
            $detail['fiscal_year_id'] = $data['fiscal_year_id'];
            $detail['class_id'] = $data['class_id'];
            $detail['class_section_id'] = $data['class_section_id'];
            $detail['sort_order'] = $sort_order;
            $detail['due_month'] = getFormatedDate('M Y',$detail['due_month'],'Y-m-01');
            $detail['fee_month'] = getFormatedDate('M Y',$detail['fee_month'],'Y-m-01');

            $this->model['fee_template_class_detail']->add($this->getAlias(), $detail);
        }
        return $fee_template_class_id;
    }

    protected function updateData($primary_key, $data) {
        $this->model['fee_template_class_detail'] = $this->load->model('school/fee_template_class_detail');
        $this->model['fee_template_class_detail']->deleteBulk($this->getAlias(), array('fee_template_class_id' => $primary_key));
        $details = $data['template_details'];
        unset($data['template_details']);

        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];

        $fee_template_class_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        foreach($details as $sort_order => $detail) {
            $detail['fee_template_class_id'] = $fee_template_class_id;
            $detail['company_id'] = $data['company_id'];
            $detail['company_branch_id'] = $data['company_branch_id'];
            $detail['fiscal_year_id'] = $data['fiscal_year_id'];
            $detail['class_id'] = $data['class_id'];
            $detail['class_section_id'] = $data['class_section_id'];
            $detail['sort_order'] = $sort_order;
            $detail['due_month'] = getFormatedDate('M Y',$detail['due_month'],'Y-m-01');
            $detail['fee_month'] = getFormatedDate('M Y',$detail['fee_month'],'Y-m-01');

            $this->model['fee_template_class_detail']->add($this->getAlias(), $detail);
        }

        return $fee_template_class_id;
    }

    protected function deleteData($primary_key) {
        $this->model['fee_template_class_detail'] = $this->load->model('fee/fee_template_class_detail');
        $this->model['fee_template_class_detail']->deleteBulk($this->getAlias(), array('fee_template_class_id' => $primary_key));

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

}

?>