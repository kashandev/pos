<?php

class ControllerSchoolInterviewSlot extends HController {

    protected function getAlias() {
        return 'school/interview_slot';
    }

    protected function getPrimaryKey() {
        return 'interview_slot_id';
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
        $aColumns = array('action','slot_date','time_from', 'time_to', 'slot_interval', 'created_at','check_box');

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

        $this->data['slot_date'] = stdDate();
        $this->data['time_from'] = date('H:i');
        $this->data['time_to'] = date('H:i');

        if (isset($this->request->get['interview_slot_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array('interview_slot_id' => $this->request->get['interview_slot_id']));
            foreach($result as $field => $value) {
                if($field == 'slot_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }
            $this->model['interview_slot_detail'] = $this->load->model('school/interview_slot_detail');
            $this->data['interview_slot_details'] = $this->model['interview_slot_detail']->getRows(array('interview_slot_id' => $this->request->get['interview_slot_id']), array('sort_order ASC'));
        }

        $this->data['url_generate_slot'] = $this->url->link($this->getAlias() . '/generateSlot', 'token=' . $this->session->data['token']);
        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&interview_slot_id=' . $this->request->get['interview_slot_id']);
        $this->data['strValidation']="{
            'rules':{
                'name': {'required':true, 'minlength': 3, 'remote':  {url: '" . $this->data['action_validate_name'] . "', type: 'post'}},
            },
            'messages':{
                'name': {'required': '" .$this->language->get('error_name') ."', 'minlength': '".$this->language->get('error_name') ."', 'maxlength': '".$this->language->get('error_name') ."'}
            },
        }";
        $this->response->setOutput($this->render());
    }

    public function validateName()
    {
        $name = $this->request->post['name'];
        $interview_slot_id = $this->request->get['interview_slot_id'];

        $this->load->language('school/interview_slot');
        $this->model['interview_slot'] = $this->load->model('school/interview_slot');
        $where = "LOWER(name) = '".strtolower($name)."' AND interview_slot_id != '".$interview_slot_id."'";
        $interview_slot = $this->model['interview_slot']->getRow($where);
        if ($interview_slot) {
            echo json_encode($this->language->get('error_name'));

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
        $this->model['interview_slot'] = $this->load->model('school/interview_slot');
        $this->model['interview_slot_detail'] = $this->load->model('school/interview_slot_detail');

        foreach($ids as $interview_slot_id) {
            $interview_slot = $this->model['interview_slot']->getRow(array('interview_slot_id'=>$interview_slot_id));
            $where = "interview_slot_id = '" . $interview_slot_id . "' AND (allotted = 'True' OR preregistration_identity != '')";
            $interview_slot_details = $this->model['interview_slot_detail']->getRows($where);
            if(count($interview_slot_details) > 0) {
                $arrError[] = sprintf($lang['error_delete_slot'],'Date:' . stdDate($interview_slot['slot_date']). ', From:' . $interview_slot['time_from'] . ', To:'.$interview_slot['time_to'],count($interview_slot_details));
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
        $this->model['interview_slot_detail'] = $this->load->model('school/interview_slot_detail');
        $details = $data['interview_slot_details'];
        unset($data['interview_slot_details']);

        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['slot_date'] = MySqlDate($data['slot_date']);

        $interview_slot_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        foreach($details as $detail) {
            $detail['interview_slot_id'] = $interview_slot_id;
            $detail['company_id'] = $data['company_id'];
            $detail['company_branch_id'] = $data['company_branch_id'];
            $detail['fiscal_year_id'] = $data['fiscal_year_id'];
            $detail['slot_date'] = $data['slot_date'];
            $detail['caption'] = stdDate($data['slot_date']) . ' (' . $detail['time_from'] . ' - ' . $detail['time_to'] . ')';
            $detail['slot_date_time'] = $data['slot_date'] . ' (' . $detail['time_from'] . ' - ' . $detail['time_to'] . ')';
            $this->model['interview_slot_detail']->add($this->getAlias(), $detail);
        }
    }

    protected function updateData($primary_key, $data) {
        //d($this->session->data, true);
        $this->model['interview_slot_detail'] = $this->load->model('school/interview_slot_detail');
        $details = $data['interview_slot_details'];
        unset($data['interview_slot_details']);

        $data['slot_date'] = MySqlDate($data['slot_date']);
        $interview_slot_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);

        $this->model['interview_slot_detail']->deleteBulk($this->getAlias(), array('interview_slot_id' => $primary_key));
        foreach($details as $detail) {
            $detail['interview_slot_id'] = $interview_slot_id;
            $detail['company_id'] = $this->session->data['company_id'];
            $detail['company_branch_id'] = $this->session->data['company_branch_id'];
            $detail['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
            $detail['slot_date'] = $data['slot_date'];
            $detail['caption'] = stdDate($data['slot_date']) . ' (' . date('H:i', strtotime($detail['time_from'])) . ' - ' . date('H:i', strtotime($detail['time_to'])) . ')';
            $detail['slot_date_time'] = $data['slot_date'] . ' (' . date('H:i', strtotime($detail['time_from'])) . ' - ' . date('H:i', strtotime($detail['time_to'])) . ')';
            $this->model['interview_slot_detail']->add($this->getAlias(), $detail);
        }

        return $interview_slot_id;
    }

    public function generateSlot() {
        $lang = $this->load->language($this->getAlias());

        $post = $this->request->post;
        $slot_date = $post['slot_date'];
        $time_from = $post['time_from'];
        $time_to = $post['time_to'];
        $slot_interval = $post['slot_interval'];

        $html = '';
        $sort_order = 0;

        $this->model['interview_slot'] = $this->load->model('school/interview_slot');
        $where = "";
        $where .= " company_id = '".$this->session->data['company_id']."'";
        $where .= " AND company_branch_id = '".$this->session->data['company_branch_id']."'";
        $where .= " AND fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
        $where .= " AND slot_date = '".MySqlDate($slot_date)."'";
        $where .= " AND (('".$time_from."' >= time_from AND '".$time_from."' < time_to)";
        $where .= " OR ('".$time_to."' > time_from AND '".$time_to."' <= time_to))";
        $rows = $this->model['interview_slot']->getRows($where);

        if(count($rows) > 0) {
            $data = array(
                'success' => false,
                'post' => $post,
                'error' => $lang['error_duplicate_slot'],
                'html' => $html
            );
        } else {
            $j = strtotime($time_from);
            for($i = $j+($slot_interval*60); $i<=strtotime($time_to); $i+=($slot_interval*60)) {
                $sort_order ++;
                $html .='<tr id="row_'.$sort_order.'">' . "\n";
                $html .='<td><input type="text" class="form-control" readonly="true" name="interview_slot_details['.$sort_order.'][sort_order]" value="'.$sort_order.'" /></td>' . "\n";
                $html .='<td><input type="text" class="form-control" readonly="true" name="interview_slot_details['.$sort_order.'][time_from]" value="'.date('H:i',$j).'" /></td>' . "\n";
                $html .='<td><input type="text" class="form-control" readonly="true" name="interview_slot_details['.$sort_order.'][time_to]" value="'.date('H:i',$i).'" /></td>' . "\n";
                $html .='<td><input type="text" class="form-control" readonly="true" name="interview_slot_details['.$sort_order.'][allotted]" value="False" /></td>' . "\n";
                $html .='<td class="text-center"><a onclick="$(\'#tbl_time_slot #row_'.$sort_order.'\').remove();" title="Delete" data-toggle="tooltip" href="javascript:void(0);" class="btn btn-danger btn-sm"><span class="fa fa-times"></span></a></td>' . "\n";
                $html .='<\tr>' . "\n";

                $j= $i;
            }
            $data = array(
                'success' => true,
                'post' => $post,
                'html' => $html
            );
        }

        echo json_encode($data);
    }

    protected function deleteData($primary_key) {
        $this->model['interview_slot_detail'] = $this->load->model('school/interview_slot_detail');
        $this->model['interview_slot_detail']->deleteBulk($this->getAlias(), array('interview_slot_id' => $primary_key));

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

}

?>