<?php

class ControllerSchoolStudent extends HController {

    protected function getAlias() {
        return 'school/student';
    }

    protected function getPrimaryKey() {
        return 'student_id';
    }

    protected function init() {
        $this->model[$this->getAlias()] = $this->load->model('student/student');
        $this->data['lang'] = $this->load->language($this->getAlias());
        $this->document->setTitle($this->data['lang']['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    protected function getList() {
        parent::getList();

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['lang']['dashboard'],
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'class' => 'fa fa-dashboard',
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['lang']['student_information'],
            'href' => $this->url->link('student/student', 'token=' . $this->session->data['token'], 'SSL'),
            'class' => 'fa fa-child',
            'separator' => false
        );

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    protected function getForm() {
        parent::getForm();

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array (
            'text' => $this->data['lang']['dashboard'],
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'class' => 'fa fa-dashboard',
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array (
            'text' => $this->data['lang']['student_information'],
            'href' => $this->url->link('student/student', 'token=' . $this->session->data['token'], 'SSL'),
            'class' => 'fa fa-child',
            'separator' => false
        );

        $this->model['academic_year'] = $this->load->model('setup/academic_year');
        $this->model['class'] = $this->load->model('setup/class');
        $this->model['class_section'] = $this->load->model('setup/class_section');
        $this->model['country'] = $this->load->model('location/country');
        $this->model['house'] = $this->load->model('setup/house');

        $this->data['academic_years'] = $this->model['academic_year']->getRows(array('school_id' => $this->session->data['school_id']));
        $this->data['classes'] = $this->model['class']->getRows(array('school_id' => $this->session->data['school_id'], 'branch_id' => $this->session->data['branch_id']));
        $this->data['countries'] = $this->model['country']->getRows();
        $this->data['houses'] = $this->model['house']->getRows(array('school_id' => $this->session->data['school_id'], 'branch_id' => $this->session->data['branch_id']), array('sort_order'));

        $this->model['image'] = $this->load->model('tool/image');
        $this->data['no_image'] = $this->model['image']->resize('no_user.jpg', 300, 300);

        if (isset($this->request->get['student_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $filter = array(
                'school_id' => $this->session->data['school_id'],
                'branch_id' => $this->session->data['branch_id'],
                'academic_year_id' => $this->session->data['academic_year_id'],
                'student_id' => $this->request->get['student_id']
            );
            $result = $this->model[$this->getAlias()]->getRow($filter);
            foreach($result as $field => $value) {
                if($field == 'student_dob') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }

            $this->model['class_section'] = $this->load->model('setup/class_section');
            $this->data['class_sections'] = $this->model['class_section']->getRows(array('class_id' => $result['class_id']));

            $this->data['student_sessions'] = $this->model[$this->getAlias()]->getRows(array('student_id' => $this->request->get['student_id']));
        }

        if ($this->data['student_image'] && file_exists(DIR_IMAGE . $this->data['student_image']) && is_file(DIR_IMAGE . $this->data['student_image'])) {
            $this->data['src_student_image'] = $this->model['image']->resize($this->data['student_image'], 300, 300);
        } else {
            $this->data['src_student_image'] = $this->model['image']->resize('no_user.jpg', 300, 300);
        }

        //d($this->data, true);

        $this->data['url_get_preregistrations'] = $this->url->link($this->getAlias() . '/getPreregistrations', 'token=' . $this->session->data['token'] . '&student_id=' . $this->request->get['student_id']);
        $this->data['url_get_sections'] = $this->url->link($this->getAlias() . '/getSections', 'token=' . $this->session->data['token'] . '&student_id=' . $this->request->get['student_id']);

        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&student_id=' . $this->request->get['student_id']);
        $this->data['strValidation']="{
            'rules':{
                'gr_no': {'required':true},
                'student_name': {'required':true},
                'sur_name': {'required':true},
                'gender': {'required':true},
                'student_dob': {'required':true},
                'class_id': {'required':true},
                'class_section_id': {'required':true},
                'father_name': {'required':true},
                'father_contact_no': {'required':true},
                'mother_name': {'required':true},
                'mother_contact_no': {'required':true},
            },
            ignore : []
        }";
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {
        $this->data['lang'] = $this->load->language($this->getAlias());
        $this->model[$this->getAlias()] = $this->load->model('student/student');
        $data = array();
        $aColumns = array('action','class_name','section_name', 'student_name', 'father_name', 'sur_name', 'gr_no', 'roll_no', 'house_name', 'created_at','check_box', 'slot_date_time');

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
        $arrWhere[] = "`school_id` = '".$this->session->data['school_id']."'";
        $arrWhere[] = "`branch_id` = '".$this->session->data['branch_id']."'";
        $arrWhere[] = "`academic_year_id` = '".$this->session->data['academic_year_id']."'";
        $arrWhere[] = "`student_status` = 'Admitted'";
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
                } elseif ($aColumns[$i] == 'admission_date') {
                    $row[] = stdDate($aRow['admission_date']);
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

    protected function insertData($data) {
        $this->model['student'] = $this->load->model('student/student');
        $this->model['student_session'] = $this->load->model('student/student_session');
        if($data['student_dob'] != '')
            $data['student_dob'] = MySqlDate($data['student_dob']);

        if($data['student_id'] == '') {
            unset($data['student_id']);
            $data['for_academic_year_id'] = $this->session->data['academic_year_id'];
            $data['for_class_id'] = $data['class_id'];
            $student_id = $this->model['student']->add($this->getAlias(), $data);
        } else {
            $student_id = $data['student_id'];
            $student_id = $this->model['student']->edit($this->getAlias(), $student_id, $data);
        }

        $filter = array(
            'school_id' => $this->session->data['school_id'],
            'branch_id' => $this->session->data['branch_id'],
            'academic_year_id' => $this->session->data['academic_year_id'],
            'student_id' => $student_id
        );
        $student_session = $this->model['student_session']->getRow($filter);
        if(empty($student_session)) {
            $session_data = array(
                'school_id' => $this->session->data['school_id'],
                'branch_id' => $this->session->data['branch_id'],
                'academic_year_id' => $this->session->data['academic_year_id'],
                'student_id' => $student_id,
                'class_id' => $data['class_id'],
                'class_section_id' => $data['class_section_id'],
                'house_id' => $data['house_id'],
                'gr_no' => $data['gr_no'],
                'roll_no' => $data['roll_no'],
                'student_status' => 'Admitted',
            );
            $this->model['student_session']->add($this->getAlias(), $session_data);
        } else {
            $student_session_id = $student_session['student_session_id'];
            $session_data = array(
                'class_id' => $data['class_id'],
                'class_section_id' => $data['class_section_id'],
                'house_id' => $data['house_id'],
                'gr_no' => $data['gr_no'],
                'roll_no' => $data['roll_no'],
                'student_status' => 'Admitted',
            );
            $this->model['student_session']->edit($this->getAlias(), $student_session_id, $session_data);
        }

        return $student_id;
    }

    protected function updateData($primary_key, $data) {
        $this->model['student'] = $this->load->model('student/student');
        $this->model['student_session'] = $this->load->model('student/student_session');

        if($data['student_dob'] != '')
            $data['student_dob'] = MySqlDate($data['student_dob']);

        $student_id = $primary_key;
        $student_id = $this->model['student']->edit($this->getAlias(), $student_id, $data);

        $filter = array(
            'school_id' => $this->session->data['school_id'],
            'branch_id' => $this->session->data['branch_id'],
            'academic_year_id' => $this->session->data['academic_year_id'],
            'student_id' => $student_id
        );
        $student_session = $this->model['student_session']->getRow($filter);
        if(empty($student_session)) {
            $session_data = array(
                'school_id' => $this->session->data['school_id'],
                'branch_id' => $this->session->data['branch_id'],
                'academic_year_id' => $this->session->data['academic_year_id'],
                'student_id' => $student_id,
                'class_id' => $data['class_id'],
                'class_section_id' => $data['class_section_id'],
                'house_id' => $data['house_id'],
                'gr_no' => $data['gr_no'],
                'roll_no' => $data['roll_no'],
                'student_status' => 'Admitted',
            );
            $this->model['student_session']->add($this->getAlias(), $session_data);
        } else {
            $student_session_id = $student_session['student_session_id'];
            $session_data = array(
                'class_id' => $data['class_id'],
                'class_section_id' => $data['class_section_id'],
                'house_id' => $data['house_id'],
                'gr_no' => $data['gr_no'],
                'roll_no' => $data['roll_no'],
                'student_status' => 'Admitted',
            );
            $this->model['student_session']->edit($this->getAlias(), $student_session_id, $session_data);
        }

        return $student_id;
    }

    protected function deleteData($primary_key) {
        d($primary_key, true);
        $admission = $this->model[$this->getAlias()]->getRow(array('student_id' => $primary_key));
        $interview_slot_detail_id = $admission['interview_slot_detail_id'];
        $student_id = $admission['student_id'];

        $this->model['student'] = $this->load->model('student/student');
        $this->model['student']->delete($this->getAlias(), $student_id);

        $this->model['student_session'] = $this->load->model('student/student_session');
        $filter_delete = array(
            'school_id' => $this->session->data['school_id'],
            'branch_id' => $this->session->data['branch_id'],
            'academic_year_id' => $this->session->data['academic_year_id'],
            'student_id' => $student_id,
        );
        $this->model['student_session']->deleteBulk($this->getAlias(), array($filter_delete));

        if($interview_slot_detail_id != '') {
            $this->model['interview_slot_detail'] = $this->load->model('admission/interview_slot_detail');
            $update_data = array(
                'allotted' => 'False',
                'student_id' => null,
                'student_identity' => null
            );
            $this->model['interview_slot_detail']->edit($this->getAlias(), $interview_slot_detail_id, $update_data);
        }

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

    public function getSections() {
        $class_id = $this->request->post['class_id'];
        $this->model['class_section'] = $this->load->model('setup/class_section');
        $class_sections = $this->model['class_section']->getRows(array('class_id' => $class_id), array('sort_order'));


        $html = '';
        $html .= '<option value="" >&nbsp;</option>';
        foreach($class_sections as $class_section) {
            $html .= '<option value="'.$class_section['class_section_id'].'" >'.$class_section['section_name'].'</option>';
        }

        $json = array(
            'success' => true,
            'html' => $html
        );

        echo json_encode($json);
        exit;
    }

    public function getPreRegistrations() {
        $lang = $this->load->language('admission/admission');
        $this->model['student'] = $this->load->model('student/student');
        $filter = array(
            'school_id' => $this->session->data['school_id'],
            'branch_id' => $this->session->data['branch_id'],
            'for_academic_year_id' => $this->session->data['academic_year_id'],
            'student_status' => 'Preregistered'
        );
        $students = $this->model['student']->getRows($filter);

        $html = '';
        //$html .= '<div class="table-responsive">';
        $html .= '  <table class="table table-bordered dataTable">';
        $html .= '      <thead>';
        $html .= '      <tr>';
        $html .= '          <th class="text-center">&nbsp;</th>';
        $html .= '          <th class="text-center">'.$lang['preregistration_no'].'</th>';
        $html .= '          <th class="text-center">'.$lang['student_name'].'</th>';
        $html .= '          <th class="text-center">'.$lang['father_name'].'</th>';
        $html .= '          <th class="text-center">'.$lang['sur_name'].'</th>';
        $html .= '          <th class="text-center">'.$lang['class'].'</th>';
        $html .= '      </tr>';
        $html .= '      </thead>';
        $html .= '      <tbody>';
        $objStudent = array();
        foreach($students as $student) {
            $objStudent[$student['student_id']] = array(
                'student_id' => $student['student_id'],
                'class_id' => $student['class_id'],
                'preregistration_identity' => $student['preregistration_identity'],
                'student_name' => $student['student_name'],
                'sur_name' => $student['sur_name'],
                'father_name' => $student['father_name'],
                'mother_name' => $student['mother_name'],
                'student_dob' => ($student['student_dob'] != '' ? stdDate($student['student_dob']) : ''),
            );
            $html .= '      <tr>';
            $html .= '          <td class="text-center">';
            $html .= '              <button class="btn btn-sm btn-primary" type="button" onclick="getStudent(\''.$student['student_id'].'\')"><i class="fa fa-check"></i></button>';
            $html .= '          </td>';
            $html .= '          <td class="text-center">'.$student['preregistration_identity'].'</td>';
            $html .= '          <td class="text-center">'.$student['student_name'].'</td>';
            $html .= '          <td class="text-center">'.$student['father_name'].'</td>';
            $html .= '          <td class="text-center">'.$student['sur_name'].'</td>';
            $html .= '          <td class="text-center">'.$student['class'].'</td>';
            $html .= '      </tr>';
        }
        $html .= '      </tbody>';
        $html .= '  </table>';
        //$html .= '</div>';

        $json = array(
            'success' => true,
            'filter' => $filter,
            'objStudents' => $objStudent,
            'title' => $lang['preregistration'],
            'html' => $html
        );

        echo json_encode($json);
        exit;
    }

    public function printSlip() {
        $student_id = $this->request->get['student_id'];
        $this->model['branch'] = $this->load->model('setup/branch');
        $branch = $this->model['branch']->getRow(array('branch_id' => $this->session->data['branch_id']));

        $this->model['admission'] = $this->load->model('admission/admission');
        $rows = $this->model['admission']->getRow(array('student_id' => $student_id));
        $data = array(
            'lang' => $this->load->language($this->getAlias()),
            'branch' => $branch,
            'rows' => $rows
        );
        try
        {
            $pdf=new mPDF('utf-8','A5-L','','',6,6,35,5);

            $pdf->SetDisplayMode('fullpage');

            $pdf->setHTMLHeader($this->getPDFHeader($data));
            $pdf->setHTMLFooter($this->getPDFFooter($data));
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
        $branch = $data['branch'];
        $this->model['image'] = $this->load->model('tool/image');
        $school_image = $this->model['image']->resize($branch['school_logo'],50,50);


        $html  = '';
        $html .= '<table class="page_header">';
        $html .= '<tr>';
        $html .= '<td style="width: 20%; text-align: left;">';
        if($branch['school_logo']) {
            $html .= '<img src="' . $school_image . '" alt="Logo" />';
        }
        $html .= '</td>';
        $html .= '<td style="width: 60%; text-align: center">';
        $html .= '<h1>' . $branch['school_name'] .'</h1>';
        $html .= '<h2>' . $branch['branch_name'] . '</h2>';
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
        $row = $data['rows'];
        //d($row, true);
        $lang = $data['lang'];
        $html = '';
        $html .= '<div style="width: 100%;">';
        $html .= '<div class="content" style="float: left; width: 45%">'.$lang['admission_date'].': '.stdDate($row['admission_date']).'</div>';
        $html .= '<div class="content" style="float: right; width: 45%">'.$lang['admission_no'].': '.$row['student_identity'].'</div>';
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
        $html .= '<div class="content" style="float: left; width: 45%">'.$lang['interview_time'].': '.$row['interview_slot'].'</div>';
        $html .= '<div class="content" style="float: right; width: 45%">'.$lang['charges'].': '.number_format($row['admission_amount'],2).'</div>';
        $html .= '</div>';
        $html .= '<div style="clear: both;"></div>';

        return $html;
    }


}
?>