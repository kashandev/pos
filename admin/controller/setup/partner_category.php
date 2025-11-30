<?php

class ControllerSetupPartnerCategory extends HController
{

    protected function getAlias()
    {
        return 'setup/partner_category';
    }

    protected function getPrimaryKey()
    {
        return 'partner_category_id';
    }

    protected function validateDocument() {
        return false;
    }

    protected function getList()
    {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists()
    {
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());

        $data = array();
        $aColumns = array('action', 'name', 'created_at', 'check_box');

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

    protected function getForm()
    {
        parent::getForm();

        if (isset($this->request->get['partner_category_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array('partner_category_id' => $this->request->get['partner_category_id']));
            foreach ($result as $field => $value) {
                $this->data[$field] = $value;
            }
        }

        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&partner_category_id=' . $this->request->get['partner_category_id']);
        $this->data['strValidation']="{
            'rules':{
		        'name': {'required':true, 'minlength': 3, 'remote':  {url: '" . $this->data['action_validate_name'] . "', type: 'post'}},
            },
        }";

        $this->response->setOutput($this->render());
    }

    protected function validateForm()
    {
        if ($this->request->post['name'] == '') {
            $this->error['name'] = $this->language->get('error_required_name');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

    protected function validateDelete($id)
    {
        if (!$this->user->hasPermission('delete', $this->getAlias())) {
            $this->error['warning'][] = $this->language->get('error_permission_delete');
        } else {
            $partner_category = $this->model[$this->getAlias()]->getRow(array('partner_category_id' => $id));
            $this->model['partner'] = $this->load->model('common/partner');
            $rows = $this->model['partner']->getRows(array('partner_category_id' => $id));
            if(count($rows) > 0) {
                $this->error['warning'][] = sprintf("Cannot Delete `%s`. %d Partners associated", $partner_category['name'],count($rows));
            }
        }

        if (!$this->error) {
            return true;
        } else {
            $this->session->data['warning'] = implode('<br />', $this->error['warning']);
            return false;
        }
    }

    protected function insertData($data) {
        $data['company_id'] = $this->session->data['company_id'];
        $this->model[$this->getAlias()]->add($this->getAlias(), $data);
    }

    protected function updateData($primary_key, $data) {
        $data['company_id'] = $this->session->data['company_id'];
        $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key, $data);
    }

    public function validateName()
    {
        $name = $this->request->post['name'];
        $company_id = $this->session->data['company_id'];
        $partner_category_id = $this->request->get['partner_category_id'];

        $this->load->language('setup/partner_category');
        if ($name) {
            $this->model['partner_category'] = $this->load->model('setup/partner_category');
            $where = "company_id='" . $company_id . "' AND LOWER(name) = '". strtolower($name)."' AND partner_category_id != '".$partner_category_id."'";
            $row = $this->model['partner_category']->getRow($where);

            if ($row) {
                echo json_encode($this->language->get('error_duplicate_name'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_required_name'));
        }
        exit;
    }

}

?>