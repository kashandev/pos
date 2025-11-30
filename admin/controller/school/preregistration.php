<?php

class ControllerSchoolPreregistration extends HController {

    protected function getAlias() {
        return 'school/preregistration';
    }

    protected function getPrimaryKey() {
        return 'preregistration_id';
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
        $aColumns = array('action','preregistration_date','preregistration_identity', 'student_name', 'father_name', 'sur_name', 'caption', 'for_class', 'for_academic_year', 'created_at','check_box', 'slot_date_time');

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
                'text' => $this->data['lang']['print_slip'],
                'target' => '_blank',
                'href' => $this->url->link($this->getAlias() . '/printSlip', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                'btn_class' => 'btn btn-default btn-xs',
                'class' => 'fa fa-print'
            );

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

            for ($i = 0; $i < count($aColumns); $i++) {
                if ($aColumns[$i] == 'action') {
                    $row[] = $strAction;
                } elseif ($aColumns[$i] == 'check_box') {
                    $row[] = '<input type="checkbox" name="selected[]" value="' . $aRow[$this->getPrimaryKey()] . '" />';
                } elseif ($aColumns[$i] == 'created_at') {
                    $row[] = stdDateTime($aRow['created_at']);
                } elseif ($aColumns[$i] == 'preregistration_date') {
                    $row[] = stdDate($aRow['preregistration_date']);
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

        $this->data['preregistration_date'] = stdDate();
        $this->data['preregistration_identity'] = $this->data['lang']['auto'];

        if (isset($this->request->get['preregistration_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array('preregistration_id' => $this->request->get['preregistration_id']));
            foreach($result as $field => $value) {
                if($field == 'preregistration_date') {
                    $this->data[$field] = stdDate($value);
                } elseif($field == 'dob') {
                        $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }
        }

        $this->model['academic_year'] = $this->load->model('school/academic_year');
        $this->model['class'] = $this->load->model('school/class');
        $this->model['interview_slot_detail'] = $this->load->model('school/interview_slot_detail');

        $this->data['academic_years'] = $this->model['academic_year']->getRows(array('company_id' => $this->session->data['company_id']));
        $this->data['classes'] = $this->model['class']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id']));

        //d(array($this->data['classes'], $this->session->data['company_id']), true);
        $where = "company_id = '" . $this->session->data['company_id'] . "'";
        $where .= " AND company_branch_id = '" . $this->session->data['company_branch_id'] . "'";
        $where .= " AND fiscal_year_id = '" . $this->session->data['fiscal_year_id'] . "'";
        $where .= " AND (allotted = 'False' OR preregistration_identity = '".$this->data['preregistration_identity']."')";

        $interview_slots = $this->model['interview_slot_detail']->getRows($where, array('slot_date ASC', 'time_from ASC'));
        foreach($interview_slots as $row_no => $interview_slot) {
            $this->data['interview_slots'][$row_no] = $interview_slot;
            $this->data['interview_slots'][$row_no]['title'] = stdDate($interview_slot['slot_date']) .' ('. date('H:i',strtotime($interview_slot['time_from'])) . ' - ' . date('H:i',strtotime($interview_slot['time_to'])) . ')';
        }

        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&preregistration_id=' . $this->request->get['preregistration_id']);
        $this->data['strValidation']="{
            'rules':{
                'pre_registration_date': {'required':true, 'date': true},
                'for_fiscal_year_id': {'required':true},
                'for_class_id': {'required':true},
                'student_name': {'required':true},
                'sur_name': {'required':true},
                'dob': {'required':true},
                'father_name': {'required':true},
            },
        }";
        $this->response->setOutput($this->render());
    }

    protected function insertData($data) {
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id']
        );
        $preregistration = $this->model[$this->getAlias()]->getMaxRegistrationNo($filter);
        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['preregistration_no'] = $preregistration['preregistration_no'];
        $data['preregistration_identity'] = $preregistration['preregistration_identity'];
        $data['preregistration_date'] = MySqlDate($data['preregistration_date']);
        $data['dob'] = MySqlDate($data['dob']);
        $preregistration_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);

        //Create Student. This is necessary for Admission Challan.
        $this->model['student'] = $this->load->model('school/student');
        $student_data = array(
            'preregistration_id' => $preregistration_id,
            'preregistration_identity' => $data['preregistration_identity'],
            'preregistration_date' => $data['preregistration_date'],
            'preregistration_amount' => $data['preregistration_amount'],
            'student_name' => $data['student_name'],
            'sur_name' => $data['sur_name'],
            'student_dob' => $data['dob'],
            'father_name' => $data['father_name'],
            'mother_name' => $data['mother_name'],
            'phone_no' => $data['phone_no'],
            'for_academic_year_id' => $data['for_academic_year_id'],
            'for_class_id' => $data['for_class_id'],
            'preregistration_amount' => $data['preregistration_amount'],
            'student_status' => 'Preregistered'
        );
        $student_id =  $this->model['student']->add($this->getAlias(), $student_data);

        //Create Student Session
        $this->model['student_session'] = $this->load->model('school/student_session');
        $student_session_data = array(
            'company_id' => $data['company_id'],
            'company_branch_id' => $data['company_branch_id'],
            'fiscal_year_id' => $data['fiscal_year_id'],
            'student_id' => $student_id,
            'class_id' => $data['for_class_id'],
            'academic_year_id' => $data['for_academic_year_id'],
            'student_status' => 'Preregistered'
        );
        $student_session_id =  $this->model['student_session']->add($this->getAlias(), $student_session_data);

        //Update Student ID in Preregistration
        $preregistration_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $preregistration_id, array('student_id' => $student_id));

        if($data['interview_slot_detail_id'] != '') {
            $this->model['interview_slot_detail'] = $this->load->model('school/interview_slot_detail');
            $update_data = array(
                'allotted' => 'True',
                'preregistration_id' => $preregistration_id,
                'preregistration_identity' => $data['preregistration_identity']
            );
            $this->model['interview_slot_detail']->edit($this->getAlias(), $data['interview_slot_detail_id'], $update_data);
        }
        return $preregistration_id;
    }

    protected function updateData($primary_key, $data) {
        $data['preregistration_date'] = MySqlDate($data['preregistration_date']);
        $data['dob'] = MySqlDate($data['dob']);

        $preregistration = $this->model[$this->getAlias()]->getRow(array('preregistration_id' => $primary_key));

        //Update Student
        $this->model['student'] = $this->load->model('school/student');
        $student_data = array(
            'preregistration_date' => $data['preregistration_date'],
            'preregistration_amount' => $data['preregistration_amount'],
            'student_name' => $data['student_name'],
            'sur_name' => $data['sur_name'],
            'student_dob' => $data['dob'],
            'father_name' => $data['father_name'],
            'mother_name' => $data['mother_name'],
            'phone_no' => $data['phone_no'],
            'for_academic_year_id' => $data['for_academic_year_id'],
            'for_class_id' => $data['for_class_id'],
            'preregistration_amount' => $data['preregistration_amount'],
        );
        $student_id =  $this->model['student']->edit($this->getAlias(), $data['student_id'], $student_data);

        //Student Session
        $this->model['student_session'] = $this->load->model('school/student_session');
        $filter = array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'student_id' => $student_id,
        );
        $student_session = $this->model['student_session']->getRow($filter);
        if(empty($student_session)) {
            $student_session_data = array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $this->session->data['company_branch_id'],
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'student_id' => $student_id,
                'class_id' => $data['for_class_id'],
                'academic_year_id' => $data['for_academic_year_id'],
                'student_status' => 'Preregistered'
            );
            $student_session_id =  $this->model['student_session']->add($this->getAlias(), $student_session_data);
        } else {
            $student_session_data = array(
                'class_id' => $data['for_class_id'],
                'academic_year_id' => $data['for_academic_year_id'],
                'student_status' => 'Preregistered'
            );
            $student_session_id =  $this->model['student_session']->edit($this->getAlias(), $student_session['student_session_id'], $student_session_data);
        }

        $interview_slot_detail_id = $preregistration['interview_slot_detail_id'];
        if($interview_slot_detail_id != '' && $data['interview_slot_detail_id'] != $interview_slot_detail_id) {
            $this->model['interview_slot_detail'] = $this->load->model('school/interview_slot_detail');
            $update_data = array(
                'allotted' => 'False',
                'preregistration_id' => null,
                'preregistration_identity' => null
            );
            $this->model['interview_slot_detail']->edit($this->getAlias(), $interview_slot_detail_id, $update_data);
        }

        $preregistration_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        if($data['interview_slot_detail_id'] != '') {
            $this->model['interview_slot_detail'] = $this->load->model('school/interview_slot_detail');
            $update_data = array(
                'allotted' => 'True',
                'preregistration_id' => $preregistration_id,
                'preregistration_identity' => $data['preregistration_identity']
            );
            $this->model['interview_slot_detail']->edit($this->getAlias(), $data['interview_slot_detail_id'], $update_data);
        }
        return $preregistration_id;
    }

    protected function deleteData($primary_key) {
        $preregistration = $this->model[$this->getAlias()]->getRow(array('preregistration_id' => $primary_key));
        $interview_slot_detail_id = $preregistration['interview_slot_detail_id'];
        $student_id = $preregistration['student_id'];

        $this->model['student'] = $this->load->model('school/student');
        $student = $this->model['student']->getRow(array('student_id' => $student_id));
        if($student['student_status'] == 'Preregistered') {
            $this->model['student']->delete($this->getAlias(), $student_id);

            $this->model['student_session'] = $this->load->model('school/student_session');
            $filter_delete = array(
                'company_id' => $this->session->data['company_id'],
                'company_branch_id' => $this->session->data['company_branch_id'],
                'fiscal_year_id' => $this->session->data['fiscal_year_id'],
                'student_id' => $student_id,
            );
            $this->model['student_session']->deleteBulk($this->getAlias(), array($filter_delete));
        }

        if($interview_slot_detail_id != '') {
            $this->model['interview_slot_detail'] = $this->load->model('school/interview_slot_detail');
            $update_data = array(
                'allotted' => 'False',
                'preregistration_id' => null,
                'preregistration_identity' => null
            );
            $this->model['interview_slot_detail']->edit($this->getAlias(), $interview_slot_detail_id, $update_data);
        }

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

    public function printSlip() {
        $preregistration_id = $this->request->get['preregistration_id'];
        //d($this->session->data, true);
        $company = array(
            'company_name' => $this->session->data['company_name'],
            'company_branch_name' => $this->session->data['company_branch_name'],
            'fiscal_title' => $this->session->data['fiscal_title'],
            'company_image' => $this->session->data['company_image'],
            'company_logo' => $this->session->data['company_logo'],
        );

        $this->model['preregistration'] = $this->load->model('school/preregistration');
        $row = $this->model['preregistration']->getRow(array('preregistration_id' => $preregistration_id));
        //d($row, true);
        $data = array(
            'lang' => $this->load->language($this->getAlias()),
            'company' => $company,
            'row' => $row
        );
        try
        {
            $pdf=new mPDF('utf-8','A5-L','','',6,6,35,5);

            $pdf->SetDisplayMode('fullpage');

            $pdf->setHTMLHeader($this->getPDFHeader($data));
            //$pdf->setHTMLFooter($this->getPDFFooter($data));
            $pdf->WriteHTML($this->getPDFStyle($data));
            $pdf->WriteHTML($this->getPDFBodyDetail($data));

            $pdf->Output();
        } catch(Exception $e) {
            echo $e;
            exit;
        }
    }

    private function getPDFStyle($data) {
        $html = '';
        $html .= '<style type="text/css">';
        $html .= 'body {';
        $html .= 'background: #FFFFFF;';
        $html .= '}';
        $html .= 'body, td, th, input, select, textarea, option, optgroup {';
        $html .= 'font-family: Arial, Helvetica, sans-serif;';
        $html .= 'font-size: 10px;';
        $html .= 'color: #000000;';
        $html .= '}';
        $html .= 'h1 {';
        $html .= 'text-transform: uppercase;';
        $html .= 'text-align: center;';
        $html .= 'font-size: 24px;';
        $html .= 'font-weight: normal;';
        $html .= 'margin: 5px 0;';
        $html .= '}';
        $html .= 'h2 {';
        $html .= 'text-transform: uppercase;';
        $html .= 'text-align: center;';
        $html .= 'font-size: 18px;';
        $html .= 'font-weight: normal;';
        $html .= 'padding: 0;';
        $html .= 'margin: 0;';
        $html .= '}';
        $html .= 'h3 {';
        $html .= 'text-align: center;';
        $html .= 'font-size: 16px;';
        $html .= 'font-weight: normal;';
        $html .= 'padding: 0;';
        $html .= 'margin: 5px 0 0 0;';
        $html .= '}';
        $html .= 'div.content {';
        $html .= 'font-size: 16px;';
        $html .= 'font-weight: normal;';
        $html .= 'padding: 0;';
        $html .= 'margin: 5px;';
        $html .= '}';
        $html .= 'table.page_header {width: 100%; border: none; background-color: #DDDDFF; border-bottom: solid 1mm #AAAADD; padding: 2mm }';
        $html .= 'table.page_body {width: 100%; border: solid 1px #DDDDDD; border-collapse: collapse; align="center" }';
        $html .= 'table.page_body th {border: solid 1px #000000; border-collapse: collapse; background-color: #CDCDCD; text-align: center; font-size: 12px; padding: 5px;}';
        $html .= 'table.page_body td {border: solid 1px #000000; border-collapse: collapse;font-size: 10px; padding: 5px;}';
        $html .= 'table.page_footer {width: 100%; border: none; background-color: #DDDDFF; border-top: solid 1mm #AAAADD; padding: 2mm}';
        $html .= '</style>';

        return $html;
    }

    private function getPDFHeader($data) {
        $filter = $data['filter'];
        $lang = $data['lang'];
        $company = $data['company'];


        $html  = '';
        $html .= '<table class="page_header">';
        $html .= '<tr>';
        $html .= '<td style="width: 20%; text-align: left;">';
        if($company['company_logo']) {
            $this->model['image'] = $this->load->model('tool/image');
            $company_image = $this->model['image']->resize($company['company_image'],50,50);
            $html .= '<img src="' . $company_image . '" alt="Logo" />';
        }
        $html .= '</td>';
        $html .= '<td style="width: 60%; text-align: center">';
        $html .= '<h1>' . $company['company_name'] .'</h1>';
        $html .= '<h2>' . $company['company_branch_name'] . '</h2>';
        $html .= '<h3>' . $lang['interview_slip'] . '</h3>';
        $html .= '</td>';
        $html .= '<td style="width: 20%;">';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        return $html;
    }

    private function getPDFFooter($data) {
        $html = '';
        $html .= '<table class="page_footer">';
        $html .= '<tr>';
        $html .= '<td style="width: 33%; text-align: left;">';
        $html .= '&nbsp;';
        $html .= '</td>';
        $html .= '<td style="width: 34%; text-align: center">';
        $html .= 'Page: {PAGENO}';
        $html .= '</td>';
        $html .= '<td style="width: 33%; text-align: right">';
        $html .= 'Date: {DATE '.STD_DATETIME.'}';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        return $html;
    }

    private function getPDFBodyDetail($data) {
        $row = $data['row'];
        //d($row, true);
        $lang = $data['lang'];
        $html = '';
        $html .= '<div style="width: 100%;">';
        $html .= '<div class="content" style="float: left; width: 45%">'.$lang['preregistration_date'].': '.stdDate($row['preregistration_date']).'</div>';
        $html .= '<div class="content" style="float: right; width: 45%">'.$lang['preregistration_no'].': '.$row['preregistration_identity'].'</div>';
        $html .= '</div>';
        $html .= '<div style="clear: both;"></div>';
        $html .= '<hr />';
        $html .= '<div style="width: 100%;">';
        $html .= '<div class="content" style="float: left; width: 45%">'.$lang['for_academic_year'].': '.$row['for_academic_year'].'</div>';
        $html .= '<div class="content" style="float: right; width: 45%">'.$lang['for_class'].': '.$row['for_class'].'</div>';
        $html .= '</div>';
        $html .= '<div style="clear: both;"></div>';
        $html .= '<hr />';
        $html .= '<div style="width: 100%;">';
        $html .= '<div class="content" style="float: left; width: 45%">'.$lang['student_name'].': '.$row['student_name'].'</div>';
        $html .= '<div class="content" style="float: right; width: 45%">'.$lang['sur_name'].': '.$row['sur_name'].'</div>';
        $html .= '</div>';
        $html .= '<div style="clear: both;"></div>';
        $html .= '<hr />';
        $html .= '<div style="width: 100%;">';
        $html .= '<div class="content" style="float: left; width: 45%">'.$lang['date_of_birth'].': '.stdDate($row['dob']).'</div>';
        $html .= '<div class="content" style="float: right; width: 45%">'.$lang['phone_no'].': '.$row['phone_no'].'</div>';
        $html .= '</div>';
        $html .= '<div style="clear: both;"></div>';
        $html .= '<hr />';
        $html .= '<div style="width: 100%;">';
        $html .= '<div class="content" style="float: left; width: 45%">'.$lang['father_name'].': '.$row['father_name'].'</div>';
        $html .= '<div class="content" style="float: right; width: 45%">'.$lang['mother_name'].': '.$row['mother_name'].'</div>';
        $html .= '</div>';
        $html .= '<div style="clear: both;"></div>';
        $html .= '<hr />';
        $html .= '<div style="width: 100%;">';
        $html .= '<div class="content" style="float: left; width: 45%">'.$lang['interview_time'].': '.$row['slot_date_time'].'</div>';
        $html .= '<div class="content" style="float: right; width: 45%">'.$lang['charges'].': '.number_format($row['preregistration_amount'],2).'</div>';
        $html .= '</div>';
        $html .= '<div style="clear: both;"></div>';

        return $html;
    }
}
?>