<?php

class ControllerSchoolClass extends HController {

    protected function getAlias() {
        return 'school/class';
    }

    protected function getPrimaryKey() {
        return 'class_id';
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
        $aColumns = array('action','class_name', 'sort_order','status', 'created_at','check_box');

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
        $arrWhere[] = "`company_branch_id` = '".$this->session->data['company_branch_id']."'";
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
                } elseif ($aColumns[$i] == 'check_box') {
                    $row[] = '<input type="checkbox" name="selected[]" value="' . $aRow[$this->getPrimaryKey()] . '" />';
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

        if (isset($this->request->get['class_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array('class_id' => $this->request->get['class_id']));
            foreach($result as $field => $value) {
                $this->data[$field] = $value;
            }

            $this->model['class_section'] = $this->load->model('school/class_section');
            $this->data['sections'] = $this->model['class_section']->getRows(array('class_id' => $this->request->get['class_id']));
        }

        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&class_id=' . $this->request->get['class_id']);
        $this->data['strValidation']="{
            'rules':{
                'name': {'required':true, 'minlength': 3, 'remote':  {url: '" . $this->data['action_validate_name'] . "', type: 'post'}},
            },
        }";
        $this->response->setOutput($this->render());
    }

    public function validateName()
    {
        $name = $this->request->post['name'];
        $class_id = $this->request->get['class_id'];

        $this->load->language('school/class');
        $this->model['class'] = $this->load->model('school/class');
        $where = "LOWER(name) = '".strtolower($name)."' AND class_id != '".$class_id."'";
        $class = $this->model['class']->getRow($where);
        if ($class) {
            echo json_encode($this->language->get('error_name'));

        } else {
            echo json_encode("true");
        }
        exit;
    }

    protected function insertData($data) {
        $this->model['class_section'] = $this->load->model('school/class_section');

        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $class_id = $this->model[$this->getAlias()]->add($this->getAlias(), $data);
        foreach($data['class_sections'] as $section) {
            $section['class_id'] = $class_id;
            $class_section_id = $this->model['class_section']->add($this->getAlias(), $section);
        }

        return $class_id;
    }

    protected function updateData($primary_key, $data) {
        $this->model['class_section'] = $this->load->model('school/class_section');
        $this->model['class_section']->deleteBulk($this->getAlias(), array('class_id' => $primary_key));

        $class_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
        foreach($data['class_sections'] as $section) {
            $section['class_id'] = $class_id;
            $class_section_id = $this->model['class_section']->add($this->getAlias(), $section);
        }

        return $class_id;
    }

    protected function deleteData($primary_key) {
        $this->model['class_section'] = $this->load->model('school/class_section');
        $this->model['class_section']->deleteBulk($this->getAlias(), array('class_id' => $primary_key));

        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }


}

?>