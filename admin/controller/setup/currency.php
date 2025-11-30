<?php

class ControllerSetupCurrency extends HController {

    protected function validateDocument() {
        return false;
    }

    protected function getAlias() {
        return 'setup/currency';
    }

    protected function getPrimaryKey() {
        return 'currency_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    protected function getForm() {
        parent::getForm();

        if (isset($this->request->get['currency_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $result = $this->model[$this->getAlias()]->getRow(array('currency_id' => $this->request->get['currency_id']));
            foreach ($result as $field => $value) {
                $this->data[$field] = $value;
            }

            $this->model['currency_rate'] = $this->load->model('setup/currency_rate');
            $currency_rates = $this->model['currency_rate']->getRows(array('currency_id' => $this->request->get['currency_id']),array('date DESC'));
            foreach($currency_rates as $currency_rate) {
                $currency_rate['date'] = stdDate($currency_rate['date']);
                $this->data['currency_rates'][] = $currency_rate;
            }
        }

        $this->data['action_validate_name'] = $this->url->link($this->getAlias() . '/validateName', 'token=' . $this->session->data['token'] . '&currency_id=' . $this->request->get['currency_id']);
        $this->data['action_validate_code'] = $this->url->link($this->getAlias() . '/validateCode', 'token=' . $this->session->data['token'] . '&currency_id=' . $this->request->get['currency_id']);
        $this->data['strValidation']="{
            'rules':{
                'name': {'required':true, 'minlength': 3, 'remote':  {url: '" . $this->data['action_validate_name'] . "', type: 'post'}},
		        'currency_code': {'required':true, 'minlength': 3, 'maxlength': 3}, 'remote':  {url: '" . $this->data['action_validate_code'] . "', type: 'post'},
		        'payable_account_id': {'required': true},
		        'email': {'email': true},
		        'mobile': {'required': true},
		        'phone': {'required': true},
             },
          }";


        $this->response->setOutput($this->render());
    }

    protected function insertData($data) {
        $this->model['currency'] = $this->load->model('setup/currency');
        $data['company_id'] = $this->session->data['company_id'];
        $currency_id = $this->model['currency']->add($this->getAlias(), $data);

        $this->model['currency_rate'] = $this->load->model('setup/currency_rate');

        foreach ($data['currency_rates'] as $detail) {
            $detail['date'] = MySqlDate($detail['date']);

            $detail['currency_id'] = $currency_id;
            $currency_detail_id= $this->model['currency_rate']->add($this->getAlias(), $detail);
        }
    }

    protected function updateData($primary_key, $data) {
//        d($data,true);
        $currency_id = $primary_key;
        $this->model['currency'] = $this->load->model('setup/currency');
        $this->model['currency']->edit($this->getAlias(), $primary_key, $data);
        $this->model['currency_rate'] = $this->load->model('setup/currency_rate');

        $this->model['currency_rate']->deleteBulk($this->getAlias(), array('currency_id' => $currency_id));

        foreach ($data['currency_rates'] as $detail) {
            $detail['date'] = MySqlDate($detail['date']);

            $detail['currency_id'] = $currency_id;

            $this->model['currency_rate']->add($this->getAlias(), $detail);
        }
    }

    public function getRate() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            $this->model['currency'] = $this->load->model('setup/currency');

            $currency_code = $this->request->post['currency_code'];
            $currency_date = MySqlDate($this->request->post['date']);
            $currency_id = $this->request->post['currency_id'];


            if(!$currency_code) {
                $currency = $this->model['currency']->getRow(array('company_id' => $this->session->data['company_id'], 'currency_id' => $currency_id));
            } else {
                $currency = $this->model['currency']->getRow(array('company_id' => $this->session->data['company_id'], 'currency_code' => $currency_code));
            }

            $this->model['currency_rate'] = $this->load->model('setup/currency_rate');
            //d(array($currency['currency_id'],$currency_date));
            $currency_rate = $this->model['currency_rate']->getCurrencyRate($currency['currency_id'],$currency_date);


            //d($currency_rate,true);
            $rate = (is_null($currency_rate['rate']) ? 0 : $currency_rate['rate']);

            $json = array(
                'success' => true,
                'rate' => $rate
            );
           // d($json,true);
        } else {
            $this->load->language('setup/currency');
            $json = array(
                'success' => false,
                'error' => $this->language->get('error_select_currency')
            );
        }

        $this->response->setOutput(json_encode($json));
    }
	
	public function getAjaxLists() {
        
		$this->load->language('setup/currency');
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action','name', 'currency_code','created_at');

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
                } else {
                    $row[] = $aRow[$aColumns[$i]];
                }

            }
            $output['aaData'][] = $row;
        }

        echo json_encode($output);
    }

    public function validateName()
    {
        $name = $this->request->post['name'];
        $currency_id = $this->request->get['currency_id'];
        $this->load->language('setup/currency');
        if ($name) {
            $this->model['currency'] = $this->load->model('setup/currency');
            $currency = $this->model['currency']->validateName($name, $currency_id);

            if ($currency) {
                echo json_encode($this->language->get('error_duplicate_name'));
            } else {
                echo json_encode("true");
            }
        } else {
            echo json_encode($this->language->get('error_invalid'));
        }
        exit;
    }

    public function validateCode()
    {
        $currency_code = $this->request->post['currency_code'];
        $currency_id = $this->request->get['currency_id'];
        $this->load->language('setup/currency');
        if ($currency_code) {
            $this->model['currency'] = $this->load->model('setup/currency');
            $currency = $this->model['currency']->validateCode($currency_code, $currency_id);

            if ($currency) {
                echo json_encode($this->language->get('error_duplicate_currency_code'));
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