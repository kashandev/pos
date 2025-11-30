<?php

class ControllerGlMappingCOA extends HController {

    protected function validateDocument() {
        return false;
    }

    protected function getAlias() {
        return 'gl/mapping_coa';
    }

    protected function getPrimaryKey() {
        return 'mapping_coa_id';
    }

    public function index() {
        $this->redirect($this->url->link($this->getAlias() . '/insert', 'token=' . $this->session->data['token'] . $url, 'SSL'));
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());

        $data = array();
        $aColumns = array('action', 'mapping_type_code', 'mapping_type_name', 'level3_display_name', 'created_at');

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
        $this->data['mapping_coas'] = $this->model[$this->getAlias()]->getRows(array('company_id' =>$this->session->data['company_id']),array('sort_order ASC'));
        $this->model['mapping_type'] = $this->load->model('gl/mapping_type');
        $this->data['mapping_types'] = $this->model['mapping_type']->getRows();

        $this->model['coa'] = $this->load->model('gl/coa');
        $where = "company_id = '".$this->session->data['company_id']."' AND coa_level3_id IS NOT NULL";
        $this->data['coas'] = $this->model['coa']->getRows($where);

        //d($this->data['lang'], true);
        $this->response->setOutput($this->render());
    }

    protected function validateForm() {

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    public function insert() {
        $this->init();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateInsert()) {
            $this->db->beginTransaction();
            $this->insertData($this->request->post);
            $this->db->commit();

            $this->session->data['success'] = $this->language->get('success_insert');

            $url = $this->getURL();
            $this->redirect($this->url->link($this->getAlias() . '/insert', 'token=' . $this->session->data['token'] . $url, 'SSL'));
        }

        $this->getForm();
    }

    protected function insertData($data) {
        $this->model[$this->getAlias()]->deleteBulk($this->getAlias(),array('company_id' =>$this->session->data['company_id']));

        foreach($data['mapping_coas'] as $sort_order => $mapping_coa) {
            $mapping_coa['company_id'] = $this->session->data['company_id'];
            $mapping_coa['sort_order'] = $sort_order+1;

            $this->model[$this->getAlias()]->add($this->getAlias(), $mapping_coa);
        }
    }

    protected function updateData($primary_key, $data) {
        d($data, true);
        $this->model[$this->getAlias()]->deleteBulk($this->getAlias(),array('company_id' =>$this->session->data['company_id']));

        foreach($data['mapping_coas'] as $sort_order => $mapping_coa) {
            $mapping_coa['company_id'] = $this->session->data['company_id'];
            $mapping_coa['sort_order'] = $sort_order+1;

            $this->model[$this->getAlias()]->add($this->getAlias(), $mapping_coa);
        }
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('delete', $this->getAlias())) {
            $this->error['warning'] = $this->language->get('error_permission_delete');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

}

?>