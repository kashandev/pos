<?php

class ControllerGlTransactionAccount extends HController {

    protected function getAlias() {
        return 'gl/transaction_account';
    }

    protected function getPrimaryKey() {
        return 'transaction_account_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    protected function getForm() {
        parent::getForm();

        if (isset($this->request->get['transaction_account_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model_gl_transaction_account->getTransactionAccount($this->request->get['transaction_account_id']);
            foreach($result as $field => $value) {
                $this->data[$field] = $value;
            }
        }

        $this->data['transaction_account_types'] = $this->config->get('transaction_account_types');
        $this->model['currency'] = $this->load->model('setup/currency');
        $this->data['currencys'] = $this->model['currency']->getArrays('currency_id','name');

        $this->model['coa'] = $this->load->model('gl/coa');
        $this->data['arrCOAs'] = $this->model['coa']->getArrays('coa_id','display_name',array('company_id' => $this->session->data['company_id']));


        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&manufacture_id=' . $this->request->get['manufacture_id']);
        $this->data['strValidation']= "{
            'rules':{
                'account_name': {'required':true, 'remote':  {url: '" . $this->data['action_validate_name'] . "', type: 'post'}},
                'transaction_account_type_id': {'required':true},
                'currency_id': {'required':true},
                'transaction_account_name': {'required':true},
                'number': {'required':true},
            },

            'messages':{
                'account_name': {'required': '" . $this->language->get('error_account_name') . "'},
                'transaction_account_type_id': {'required':'" . $this->language->get('error_transaction_account_type_id') . "'},
                'currency_id': {'required':'" . $this->language->get('error_currency_id') . "'},
                'transaction_account_name': {'required':'" . $this->language->get('error_transaction_account_name') . "'},
                'number': {'required':'" . $this->language->get('error_number') . "'},
		    },
          }";
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {

        $this->load->language('gl/transaction_account');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());

        $this->model['currency'] = $this->load->model('setup/currency');
        $arrCurrencys = $this->model['currency']->getArrays('currency_id','name');

        $data = array();
        $aColumns = array('action','account_name', 'currency_id' , 'status' ,'created_at');


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
        $sWhere = "WHERE company_id = " . $this->session->data['company_id'];
        if (isset($_GET['sSearch']) && $_GET['sSearch'] != "") {
            $sWhere = " AND (";
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
                if($aColumns[$i]=='action') {
                    $row[] = $strAction;
                } elseif($aColumns[$i]=='currency_id') {
                        $row[] = $arrCurrencys[$aRow['currency_id']];
                }elseif($aColumns[$i]=='status'){
                    $row[]=($aRow['status'] ==1 ? $this->language->get('text_enabled') : $this->language->get('text_disabled'));
                }
                else {
                    $row[] = $aRow[$aColumns[$i]];
                }

            }
            $output['aaData'][] = $row;

        }

        echo json_encode($output);
    }

    public function validateName()
    {
        $name = $this->request->post['account_name'];
        $transaction_account_id = $this->request->get['transaction_account_id'];
        $this->load->language('gl/transaction_account');
        if ($name) {
            $this->model['transaction_account'] = $this->load->model('gl/transaction_account');
            $manufacture = $this->model['transaction_account']->validateName($name, $transaction_account_id);

            if ($manufacture) {
                echo json_encode($this->language->get('error_duplicate_name'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_invalid'));
        }
        exit;
    }


}

?>