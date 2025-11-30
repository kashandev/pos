<?php

class ControllerSchoolAdmissionChallan extends HController {

    protected function getAlias() {
        return 'school/admission_challan';
    }

    protected function getPrimaryKey() {
        return 'fee_challan_id';
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
        $aColumns = array('action','challan_date','challan_identity', 'student_name', 'father_name', 'sur_name', 'class_name', 'created_at','delete');

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
        $arrWhere[] = "`challan_type` = 'Admission Challan'";
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
                'text' => $this->data['lang']['print_challan'],
                'href' => $this->url->link($this->getAlias() . '/printChallan', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                'target' => '_blank',
                'btn_class' => 'btn btn-default btn-xs',
                'class' => 'fa fa-print'
            );

            $actions[] = array(
                'text' => $this->data['lang']['print_form'],
                'href' => $this->url->link($this->getAlias() . '/printForm', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                'target' => '_blank',
                'btn_class' => 'btn btn-default btn-xs',
                'class' => 'fa fa-print'
            );

            $strAction = '';
            foreach ($actions as $action) {
                $strAction .= '<a '.(isset($action['target'])?'target="'.$action['target'].'"':'').' '.(isset($action['btn_class'])?'class="'.$action['btn_class'].'"':'').' href="' . $action['href'] . '" data-toggle="tooltip" title="' . $action['text'] . '" ' . (isset($action['click']) ? 'onClick="' . $action['click'] . '"' : '') . '>';
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
                } elseif ($aColumns[$i] == 'delete') {
                    $row[] = '<input type="checkbox" name="selected[]" value="' . $aRow[$this->getPrimaryKey()] . '" />';
                } elseif ($aColumns[$i] == 'created_at') {
                    $row[] = stdDateTime($aRow['created_at']);
                } elseif ($aColumns[$i] == 'challan_date') {
                    $row[] = stdDate($aRow['challan_date']);
                } elseif ($aColumns[$i] == 'dob') {
                    $row[] = stdDate($aRow['dob']);
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

        $this->model['academic_year'] = $this->load->model('school/academic_year');
        $this->data['academic_years'] = $this->model['academic_year']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id'], 'fiscal_year_id' => $this->session->data['fiscal_year_id']));

        $this->model['class'] = $this->load->model('school/class');
        $this->data['classes'] = $this->model['class']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id'], 'fiscal_year_id' => $this->session->data['fiscal_year_id']));

        $this->model['fee'] = $this->load->model('school/fee');
        $this->data['fees'] = $this->model['fee']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));

        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
        );
        $arrFees = $this->model['fee']->getArrays('fee_id', 'fee_name', $filter);

        $this->data['fee_challan_identity'] = $this->data['lang']['auto'];

        if (isset($this->request->get['fee_challan_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array('fee_challan_id' => $this->request->get['fee_challan_id']));
            //d(array($this->getAlias(), $result), true);
            foreach($result as $field => $value) {
                if($field == 'dob') {
                    $this->data[$field] = stdDate($value);
                } elseif($field == 'due_month') {
                    $this->data[$field] = date('M-Y', strtotime($value));
                } elseif($field == 'last_date') {
                    $this->data[$field] = stdDate($value);
                } elseif($field == 'validity_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }

            $this->model['student'] = $this->load->model('school/student');
            $student = $this->model['student']->getRow(array('student_id' => $result['partner_id']));

            $this->data['for_academic_year_id'] = $student['for_academic_year_id'];
            $this->data['for_class_id'] = $student['for_class_id'];
            $this->data['student_name'] = $student['student_name'];
            $this->data['sur_name'] = $student['sur_name'];
            $this->data['dob'] = stdDate($student['dob']);
            $this->data['father_name'] = $student['father_name'];
            $this->data['mother_name'] = $student['mother_name'];
            $this->data['phone_no'] = $student['phone_no'];

            $this->model['challan_detail'] = $this->load->model('school/fee_challan_detail');
            $details = $this->model['challan_detail']->getRows(array('fee_challan_id' => $this->request->get['fee_challan_id']));
            foreach($details as $grid_row => $detail) {
                $this->data['challan_details'][$grid_row] = $detail;
                $this->data['challan_details'][$grid_row]['due_month'] = date('M-Y', strtotime($detail['due_month']));
                $this->data['challan_details'][$grid_row]['fee_month'] = date('M-Y', strtotime($detail['fee_month']));
                $this->data['challan_details'][$grid_row]['fee_name'] = $arrFees[$detail['fee_id']];
            }
        }

        $this->data['href_get_pre_registration'] = $this->url->link($this->getAlias() . '/getPreRegistration', 'token=' . $this->session->data['token'] . '&challan_id=' . $this->request->get['challan_id']);
        $this->data['strValidation']="{
            'rules':{
                'challan_date': {'required':true, 'date': true},
                'for_fiscal_year_id': {'required':true},
                'for_class_id': {'required':true},
                'student_name': {'required':true},
                'sur_name': {'required':true},
                'dob': {'required':true},
                'father_name': {'required':true},
                'challan_title': {'required':true},
                'due_month': {'required':true},
                'last_date': {'required':true},
                'validity_date': {'required':true},
            },
        }";

        $this->response->setOutput($this->render());
    }

    protected function insertData($data) {
        if($data['dob'] != '') {
            $data['dob'] = MySqlDate($data['dob']);
        }
        if($data['partner_id'] == '') {
            $student_data = array(
                'for_fiscal_year_id' => $data['for_fiscal_year_id'],
                'for_class_id' => $data['for_class_id'],
                'student_first_name' => $data['student_name'],
                'student_last_name' => $data['sur_name'],
                'father_name' => $data['father_name'],
                'mother_name' => $data['mother_name'],
                'phone_no' => $data['phone_no'],
                'dob' => $data['dob'],
            );

            $this->model['student'] = $this->load->model('school/student');
            $student_id = $this->model['student']->add($this->getAlias(), $student_data);
            $data['student_id'] = $student_id;

            $this->model['student_session'] = $this->load->model('student/student_session');
            $student_session_data = array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $this->session->data['company_branch_id'],
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'student_id' => $data['student_id'],
                'student_status' => 'Preregistered'
            );
            $student_session_id = $this->model['student_session']->add($this->getAlias(), $student_session_data);

        }
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id']
        );
        $challan = $this->model[$this->getAlias()]->getMaxChallanNo($filter);

        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['challan_type'] = 'Admission Challan';
        $data['challan_no'] = $challan['challan_no'];
        $data['challan_identity'] = $challan['challan_identity'];
        $data['due_month'] = date('Y-m-01', strtotime($data['due_month']));
        $data['last_date'] = MySqlDate($data['last_date']);
        $data['validity_date'] = MySqlDate($data['validity_date']);

        $fee_challan_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);

        $this->model['fee_challan_detail'] = $this->load->model('school/fee_challan_detail');
        foreach($data['fee_challan_details'] as $detail) {
            $challan_detail_data = array(
                'fee_challan_id' => $fee_challan_id,
                'due_month' => $data['due_month'],
                'fee_month' => date('Y-m-01', strtotime($detail['fee_month'])),
                'fee_id' => $detail['fee_id'],
                'fee_amount' => $detail['fee_amount'],
                'student_amount' => $detail['fee_amount'],
                'challan_amount' => $detail['fee_amount']
            );
            $fee_challan_detail_id = $this->model['fee_challan_detail']->add($this->getAlias(), $challan_detail_data);

        }
        //d(array($student_data, $student_id, $data, $challan_id), true);
        return $fee_challan_id;
    }

    protected function updateData($primary_key, $data) {
        $data['due_month'] = date('Y-m-d', strtotime('01-'.$data['due_month']));
        $data['last_date'] = MySqlDate($data['last_date']);
        $data['validity_date'] = MySqlDate($data['validity_date']);

        $this->model['fee_challan_detail'] = $this->load->model('school/fee_challan_detail');
        $this->model['fee_challan_detail']->deleteBulk($this->getAlias(), array('fee_challan_id' => $primary_key));
        foreach($data['fee_challan_details'] as $detail) {
            $challan_detail_data = array(
                'fee_challan_id' => $primary_key,
                'due_month' => $data['due_month'],
                'fee_month' => date('Y-m-01', strtotime($detail['fee_month'])),
                'fee_id' => $detail['fee_id'],
                'fee_amount' => $detail['fee_amount'],
                'student_amount' => $detail['fee_amount'],
                'challan_amount' => $detail['fee_amount']
            );
            $fee_challan_detail_id = $this->model['fee_challan_detail']->add($this->getAlias(), $challan_detail_data);
        }

        $challan_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        return $challan_id;
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
        //ToDo
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

    protected function deleteData($primary_key) {
        try {
            $this->model['fee_challan_detail'] = $this->load->model('fee/fee_challan_detail');
            $this->model['fee_challan_detail']->deleteBulk($this->getAlias(), array('fee_challan_id' => $primary_key));
            $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
        } catch (Exception $e) {
            d($e, true);
        }
    }

    public function getPreRegistration() {
        $lang = $this->load->language('school/preregistration');
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'challan_id' => $this->request->get['challan_id']
        );

        $this->model['preregistration'] = $this->load->model('school/preregistration');
        $rows = $this->model['preregistration']->unallottedPreregistration($filter);
        $html = '';
        $html .= '<table id="tbl_preregistration" class="table table-bordered">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th class="text-centre">'.$lang['action'].'</th>';
        $html .= '<th class="text-centre">'.$lang['preregistration_no'].'</th>';
        $html .= '<th class="text-centre">'.$lang['student_name'].'</th>';
        $html .= '<th class="text-centre">'.$lang['father_name'].'</th>';
        $html .= '<th class="text-centre">'.$lang['sur_name'].'</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $data = array();
        foreach($rows as $row_no => $row) {
            $html .= '<tr>';
            $html .= '<td>';
            $html .= '<button type="button" class="btn btn-sm btn-primary" onclick="getRecord('.$row_no.');">';
            $html .= '<i class="fa fa-check"></i>';
            $html .= '</button>';
            $html .= '</td>';
            $html .= '<td>'.$row['preregistration_identity'].'</td>';
            $html .= '<td>'.$row['student_name'].'</td>';
            $html .= '<td>'.$row['father_name'].'</td>';
            $html .= '<td>'.$row['sur_name'].'</td>';
            $html .= '</tr>';

            $data[$row_no] = $row;
            $data[$row_no]['dob'] = stdDate($row['dob']);
        }
        $html .= '</tbody>';
        $html .= '</table>';

        $json = array(
            'success' => true,
            'html' => $html,
            'rows' => $data
        );
        echo json_encode($json);
        exit;
    }

    public function printChallan() {
        $this->data['lang'] = $this->load->language('school/admission_challan');
        $this->model['setting'] = $this->load->model('common/setting');
        $this->model['admission_challan'] = $this->load->model('school/admission_challan');
        $this->model['admission_challan_detail'] = $this->load->model('school/admission_challan_detail');

        $this->data['config'] = $this->model['setting']->getArrays('field','value',array('module' => 'school'));

        $fee_challan_id = $this->request->get['fee_challan_id'];
        $this->data['challan'] = $this->model['admission_challan']->getRow(array('fee_challan_id' => $fee_challan_id));
        $this->data['challan_details'] = $this->model['admission_challan_detail']->getRows(array('fee_challan_id' => $fee_challan_id));
        //d($this->data['challan_details'], true);

        $this->template = 'school/admission_challan_print.tpl';
        $html = $this->render();
        //echo $html;
        //exit;

        // init HTML2PDF
        $html2pdf = new HTML2PDF('L', 'A4', 'en', true, 'UTF-8', array(0, 0, 0, 0));

        // display the full page
        $html2pdf->pdf->SetDisplayMode('fullpage');

        // convert
        $html2pdf->writeHTML($html);

        // send the PDF
        $html2pdf->Output('AdmissionFeeChallan.pdf');
        exit;
    }

}

?>