<?php

class ControllerGlCOALevel1 extends HController {

    protected function getAlias() {
        return 'gl/coa_level1';
    }

    protected function getPrimaryKey() {
        return 'coa_level1_id';
    }


    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {

        $this->load->language('gl/coa_level1');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());

        $data = array();
        $aColumns = array('action','gl_type','level1_code','name' ,'created_at', 'delete');

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

        if (isset($this->request->get['coa_level1_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach($result as $field => $value) {
                $this->data[$field] = $value;
            }
            $this->data['isEdit']=true;
        }

        //Tree Work Here
        $this->model['coa_level1'] = $this->load->model('gl/coa_level1');
        $this->model['coa_level2'] = $this->load->model('gl/coa_level2');
        $this->model['coa_level3'] = $this->load->model('gl/coa_level3');
        $this->model['gl_type'] = $this->load->model('common/gl_type');
        $gl_types = $this->model['gl_type']->getArrays('gl_type_id','name');
        $this->data['gl_types'] = $gl_types;
        $this->model['coa'] = $this->load->model('gl/coa');

        $this->data['href_get_level_data'] = $this->url->link($this->getAlias() . '/getLevelData', 'token=' . $this->session->data['token'] . '&coa_level1_id=' . $this->request->get['coa_level1_id'], 'SSL');
        $this->data['action_insert'] = $this->url->link($this->getAlias() . '/insert', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['action_validate_code'] = $this->url->link($this->getAlias() . '/validateCode', 'token=' . $this->session->data['token'] . '&coa_level1_id=' . $this->request->get['coa_level1_id']);
        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&coa_level1_id=' . $this->request->get['coa_level1_id']);
        $this->data['strValidation']= "{
            'rules':{
                'gl_type_id': {'required':true},
                'level1_code': {'required': true, 'minlength': 3, 'maxlength': 3, 'remote':  {url: '" . $this->data['action_validate_code'] . "', type: 'post'}},
                'name': {'required': true, 'remote':  {url: '" . $this->data['action_validate_name'] . "', type: 'post'}},
            },
        }";

        $this->response->setOutput($this->render());
    }

    public function getLevelData() {
        $coa_level1_id = $this->request->get['coa_level1_id'];
        $gl_type_id = $this->request->post['gl_type_id'];
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $rows = $this->model[$this->getAlias()]->getRows(array(
            'company_id' => $this->session->data['company_id'],
            'gl_type_id' => $gl_type_id
        ), array('level1_code'));

        $html = '';
        foreach($rows as $row) {
            $html .= '<tr>';
            $html .= '<td>'.$row['gl_type'].'</td>';
            $html .= '<td>'.$row['level1_code'].'</td>';
            $html .= '<td>'.$row['name'].'</td>';
            $html .= '</tr>';

            $level1_code = $row['level1_code'];
        }
        if($coa_level1_id != '') {
            $level1_code = '';
        } else {
            $level1_code = str_pad($level1_code+1,3,'0',STR_PAD_LEFT);
        }

        $json = array(
            'success' => true,
            'html' => $html,
            'level1_new_code' => $level1_code
        );

        echo json_encode($json);
        exit;
    }

    protected function insertData($data) {
        $data['company_id'] = $this->session->data['company_id'];
        return $this->model[$this->getAlias()]->add($this->getAlias(), $data);
    }

    protected function insertRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias() . '/insert', 'token=' . $this->session->data['token'] . $url, 'SSL'));
    }

    protected function updateRedirect($id, $data) {
        $url = $this->getURL();
        $this->redirect($this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . $url . '&' . $this->getPrimaryKey() . '=' . $id, 'SSL'));
    }

    public function validateCode()
    {
        //d($this->request->post);
        $code = $this->request->post['level1_code'];
        $company_id = $this->session->data['company_id'];
        $coa_level1_id = $this->request->get['coa_level1_id'];

        $this->load->language('gl/coa_level1');
        if ($code) {
            $this->model['coa_level1'] = $this->load->model('gl/coa_level1');
            if($coa_level1_id) {
                $where = "company_id='" . $company_id . "' AND level1_code = '".$code."' AND coa_level1_id != '".$coa_level1_id."'";
            } else {
                $where = "company_id='" . $company_id . "' AND level1_code = '".$code."'";
            }
            $coa = $this->model['coa_level1']->getRow($where);
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
        //d($this->request->post);
        $name = $this->request->post['name'];
        $company_id = $this->session->data['company_id'];
        $coa_level1_id = $this->request->get['coa_level1_id'];

        $this->load->language('gl/coa_level1');
        if ($name) {
            $this->model['coa_level1'] = $this->load->model('gl/coa_level1');
            $where = "company_id='" . $company_id . "' AND LOWER(name) = '". strtolower($name)."' AND coa_level1_id != '".$coa_level1_id."'";
            $coa = $this->model['coa_level1']->getRow($where);
            if ($coa) {
                echo json_encode($this->language->get('error_duplicate_name'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_required_name'));
        }
        exit;
    }

    protected function validateForm() {
        if ($this->request->post['name'] == '') {
            $this->error['warning'] = $this->language->get('error_required_name');
        }

        if ($this->request->post['level1_code'] == '') {
            $this->error['warning'] = $this->language->get('error_required_level1_code');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
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

            $this->model['coa_level1'] = $this->load->model('gl/coa_level1');
            $this->model['coa_level2'] = $this->load->model('gl/coa_level2');
            $lang = $this->load->language('gl/coa_level1');
            foreach($ids as $id) {
                $count = $this->model['coa_level2']->getCount(array('company_id' => $this->session->data['company_id'], 'coa_level1_id' => $id));
                if($count > 0) {
                    $coa_level1 = $this->model['coa_level1']->getRow(array('company_id' => $this->session->data['company_id'], 'coa_level1_id' => $id));
                    $error[] = sprintf($lang['error_delete'], $coa_level1['level1_display_name'], $count);
                }
            }

            $this->error = implode('<br />', $error);
        }

        if (!$this->error) {
            return true;
        } else {
            // d($this->error,true);
            $this->session->data['error_warning'] = $this->error;
            return false;
        }
    }

    public function getCOALevel2s() {
        if($this->request->server['REQUEST_METHOD'] == 'POST' && $this->request->post['coa_level1_id']) {
            $coa_level2_id = $this->request->post['coa_level2_id'];
            $this->model['coa_level2'] = $this->load->model('gl/coa_level2');
            $coa_level2s = $this->model['coa_level2']->getCOALevel2s(array('status' => 1, 'coa_level1_id' => $this->request->post['coa_level1_id']));
            $html = "";
            foreach($coa_level2s as $coa_level2) {
                $html .= "<option code='".$coa_level2['level2_code']."' value='".$coa_level2['coa_level2_id']."' ".($coa_level2['coa_level2_id']==$coa_level2_id ? 'selected="selected"' : "").">".$coa_level2['name']."</option>" . "\n";
            }
            //d($coa_level2,true);
            $json = array(
                'success' => true,
                'html' => $html
            );
        } else {
//            d(array($this->request->server['REQUEST_METHOD'], $this->request->post));
            $this->load->language('gl/coa_level1');
            $json = array(
                'success' => false,
                'error' => $this->language->get('error_select_coa_level1')
            );
        }

        $this->response->setOutput(json_encode($json));
    }
}

?>