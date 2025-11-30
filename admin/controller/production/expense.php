<?php

class ControllerProductionExpense extends HController {
    protected function validateDocument() {
        return false;
    }

    protected function getAlias() {
        return 'production/expense';
    }

    protected function getPrimaryKey() {
        return 'expense_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');

        $this->response->setOutput($this->render());
    }

    protected function getForm() {
        parent::getForm();

        if (isset($this->request->get['expense_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array($this->getPrimaryKey() => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                    $this->data[$field] = $value;
            }
        }

        $this->model['mapping_coa'] = $this->load->model('gl/mapping_coa');
        $this->data['coas'] = $this->model['mapping_coa']->getMappingAccounts(array('code' => 'PRDE'));

        $this->data['action_validate_product'] = $this->url->link('production/expense/validateProduct', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL');

        $this->data['strValidation'] = "{
            'rules': {
                'product_id': {'required': true, 'remote':  {url: '" . $this->data['action_validate_product'] . "', type: 'post'}},
                'unit_id': {'required': true},
            },
            'ignore': [],
        }";

        $this->response->setOutput($this->render());
    }

    protected function insertData($data) {
        $data['company_id'] = $this->session->data['company_id'];
        return $this->model[$this->getAlias()]->add($this->getAlias(), $data);
    }

    public function getAjaxLists() {

        $this->load->language('production/expense');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action', 'expense_name', 'account_head', 'created_at');

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
        $sWhere = "";
        if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
            $sWhere = "WHERE (";
            for ($i = 0; $i < count($aColumns); $i++) {
                if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch'] != '') {
                    $sWhere .= "`" . $aColumns[$i] . "` LIKE '%" . mysql_real_escape_string($_GET['sSearch']) . "%' OR ";
                }
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';
        }

        /* Individual column filtering */
        for ($i = 0; $i < count($aColumns); $i++) {
            if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
                if ($sWhere == "") {
                    $sWhere = "WHERE ";
                } else {
                    $sWhere .= " AND ";
                }
                $sWhere .= "`" . $aColumns[$i] . "` LIKE '%" . mysql_real_escape_string($_GET['sSearch_' . $i]) . "%' ";
            }
        }
        if ($sWhere != "") {
            $sWhere .= " AND company_id = '" . $this->session->data['company_id'] . "'";
        } else {
            $sWhere .= "WHERE company_id = '" . $this->session->data['company_id'] . "'";
        }

        if ($sWhere != "") {
            $data['filter']['RAW'] = substr($sWhere, 5, strlen($sWhere) - 5);
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

            if($aRow['is_post'] == 1){
                $actions[] = array(
                    'text' => $this->language->get('text_edit'),
                    'href' => $this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                    'class' => 'fa fa-pencil'
                );
            }
            else{
                $actions[] = array(
                    'text' => $this->language->get('text_edit'),
                    'href' => $this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                    'class' => 'fa fa-pencil'
                );

                $actions[] = array(
                    'text' => $this->language->get('text_delete'),
                    'href' => 'javascript:void(0);',
                    'click' => "ConfirmDelete('" . $this->url->link($this->getAlias() . '/delete', 'token=' . $this->session->data['token'] . '&id=' . $aRow[$this->getPrimaryKey()], 'SSL') . "')",
                    'class' => 'fa fa-times'
                );
            }


            $strAction = '';
            foreach ($actions as $action) {
                $strAction .= '<a href="' . $action['href'] . '" title="' . $action['text'] . '" ' . (isset($action['click']) ? 'onClick="' . $action['click'] . '"' : '') . '>';
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

    public function post() {
        $data = array(
            'is_post' => 1,
            'post_date' => date('Y-m-d H:i:s'),
            'post_by_id' => $this->user->getId()
        );

        $this->model['expense'] = $this->load->model('production/expense');
        $this->model['expense']->edit($this->getAlias(),$this->request->get['expense_id'],$data);

        $this->redirect($this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $this->request->get[$this->getPrimaryKey()], 'SSL'));

    }

    public function validateProduct() {
        $post = $this->request->post;
        $expense_id = $this->request->get['expense_id'];
        $lang = $this->load->language('production/expense');
        $product_id = $post['product_id'];
        $this->model['expense'] = $this->load->model('production/expense');

        $where = "product_id = '" . $product_id . "' AND expense_id != '".$expense_id."'";
        $product  = $this->model['expense']->getRow($where);
        if(empty($product)) {
            echo json_encode("true");
        } else {
            echo json_encode($lang['error_duplicate_product']);
        }
    }
}
?>