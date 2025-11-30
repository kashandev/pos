<?php

class ControllerGlCOAlevel2 extends HController {

    protected function getAlias() {
        return 'gl/coa_level2';
    }

    protected function getPrimaryKey() {
        return 'coa_level2_id';
    }

    protected function getList() {
        parent::getList();


        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());

        $data = array();
        $aColumns = array('action', 'level1_display_name', 'level2_code', 'name', 'created_at','delete');

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
                } elseif ($aColumns[$i] == 'delete') {
                    if($aRow['is_post']==0) {
                        $row[] = '<input type="checkbox" name="selected[]" value="' . $aRow[$this->getPrimaryKey()] . '" />';
                    } else {
                        $row[] = '';
                    }
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

        $this->model['coa_level1'] = $this->load->model('gl/coa_level1');
        $this->data['coa_level1s'] = $this->model['coa_level1']->getRows(array('company_id' => $this->session->data['company_id']), array('level1_code'));
        if (isset($this->request->get['coa_level2_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                $this->data[$field] = $value;
            }
            $this->data['isEdit']=true;
        }

        $this->data['href_get_level_data'] = $this->url->link($this->getAlias() . '/getLevelData', 'token=' . $this->session->data['token'] . '&coa_level2_id=' . $this->request->get['coa_level2_id'], 'SSL');
        $this->data['action_validate_code'] = $this->url->link($this->getAlias() . '/validateCode', 'token=' . $this->session->data['token'] . '&coa_level2_id=' . $this->request->get['coa_level2_id']);
        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&coa_level2_id=' . $this->request->get['coa_level2_id']);
        $this->data['strValidation']= "{
            'rules':{
                'coa_level1_id': {'required':true},
                'level2_code': {'required': true, 'minlength': 3, 'maxlength': 3, 'remote':  {url: '" . $this->data['action_validate_code'] . "', type: 'post', data: {coa_level1_id: function(){return $('#coa_level1_id').val();}}}},
                'name': {'required': true, 'remote':  {url: '" . $this->data['action_validate_name'] . "', type: 'post', data: {coa_level1_id: function(){return $('#coa_level1_id').val();}}}},
            },
          }";

        $this->response->setOutput($this->render());
    }

    public function getLevelData() {
        $coa_level2_id = $this->request->get['coa_level2_id'];
        $coa_level1_id = $this->request->post['coa_level1_id'];
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $filter['company_id'] = $this->session->data['company_id'];
        if($coa_level1_id != '') {
            $filter['coa_level1_id'] = $coa_level1_id;
        }
        $rows = $this->model[$this->getAlias()]->getRows($filter, array('level1_code', 'level2_code'));

        $html = '';
        foreach($rows as $row) {
            $html .= '<tr>';
            $html .= '<td>'.$row['level1_display_name'].'</td>';
            $html .= '<td>'.$row['level2_code'].'</td>';
            $html .= '<td>'.$row['name'].'</td>';
            $html .= '</tr>';

            $level2_code = $row['level2_code'];
        }
        if($coa_level2_id != '') {
            $level2_code = '';
        } else {
            $level2_code = str_pad($level2_code+1,3,'0',STR_PAD_LEFT);
        }

        $json = array(
            'success' => true,
            'html' => $html,
            'level2_new_code' => $level2_code
        );

        echo json_encode($json);
        exit;
    }

    protected function validateForm() {
        if ($this->request->post['name'] == '') {
            $this->error['name'] = $this->language->get('error_required_name');
        }

        if ($this->request->post['level2_code'] == '') {
            $this->error['level2_code'] = $this->language->get('error_required_code');
        }


        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    protected function insertData($data) {
        if(!isset($data['company_id'])) {
            $data['company_id'] = $this->session->data['company_id'];
        }
        return $this->model[$this->getAlias()]->add($this->getAlias(), $data);
    }

    protected function insertRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias() . '/insert', 'token=' . $this->session->data['token'] . $url, 'SSL'));
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

            $this->model['coa_level2'] = $this->load->model('gl/coa_level2');
            $this->model['coa_level3'] = $this->load->model('gl/coa_level3');
            $lang = $this->load->language('gl/coa_level2');
            foreach($ids as $id) {
                $count = $this->model['coa_level3']->getCount(array('company_id' => $this->session->data['company_id'], 'coa_level2_id' => $id));
                if($count > 0) {
                    $coa_level2 = $this->model['coa_level2']->getRow(array('company_id' => $this->session->data['company_id'], 'coa_level2_id' => $id));
                    $error[] = sprintf($lang['error_delete'], $coa_level2['level2_display_name'], $count);
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

    public function validateCode()
    {
        //  d($this->request->post);
        $code = $this->request->post['level2_code'];
        $id=$this->request->post['coa_level1_id'];
        $coa_level2_id = $this->request->get['coa_level2_id'];

        $this->load->language('gl/coa_level2');
        if ($code) {
            $this->model['coa_level2'] = $this->load->model('gl/coa_level2');
            if($coa_level2_id){

                $where="coa_level1_id ='" . $id . "' AND level2_code='" . $code . "' AND coa_level2_id= !'" . $coa_level2_id ."'";

            }
            else{
                $where = "coa_level1_id='" . $id . "' AND level2_code = '". $code ."'";

            }
            $coa = $this->model['coa_level2']->getRow($where);
//            d(array($this->request->post,$email,$user));
            if ($coa) {
                echo json_encode($this->language->get('error_duplicate_code'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_required_code'));
        }
        exit;
    }

    public function validateName()
    {
        //  d($this->request->post);
        $name = $this->request->post['name'];
        $id=$this->request->post['coa_level1_id'];
        $coa_level2_id = $this->request->get['coa_level2_id'];

        $this->load->language('gl/coa_level2');
        if ($name) {
            $this->model['coa_level2'] = $this->load->model('gl/coa_level2');
            $where="coa_level1_id ='" . $id . "' AND LOWER(name)='" . strtolower($name) . "' AND coa_level2_id != '" . $coa_level2_id ."'";

            $coa = $this->model['coa_level2']->getRow($where);
            if ($coa) {
                echo json_encode($this->language->get('error_duplicate_name'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_required_code'));
        }
        exit;
    }

}

?>