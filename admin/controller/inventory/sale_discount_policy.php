<?php

class ControllerInventorySaleDiscountPolicy extends HController {

    protected $document_type_id = 35;

    protected function getAlias() {
        return 'inventory/sale_discount_policy';
    }

    protected function getPrimaryKey() {
        return 'sale_discount_policy_id';
    }

    protected function getList() {
        parent::getList();

        $this->data['action_ajax'] = $this->url->link($this->getAlias() . '/getAjaxLists', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getAjaxLists() {
        $lang = $this->load->language($this->getAlias());
        $this->model[$this->getAlias()] = $this->load->model($this->getAlias());
        $data = array();
        $aColumns = array('action','partner_name', 'start_date', 'end_date', 'created_at', 'check_box');

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
        $arrWhere[] = "`company_branch_id` = '".$this->session->data['company_branch_id']."'";
        $arrWhere[] = "`fiscal_year_id` = '".$this->session->data['fiscal_year_id']."'";
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
                'text' => $lang['edit'],
                'href' => $this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . '&' . $this->getPrimaryKey() . '=' . $aRow[$this->getPrimaryKey()], 'SSL'),
                'btn_class' => 'btn btn-primary btn-xs',
                'class' => 'fa fa-pencil'
            );

            $actions[] = array(
                'text' => $lang['delete'],
                'href' => 'javascript:void(0);',
                'click' => "ConfirmDelete('" . $this->url->link($this->getAlias() . '/delete', 'token=' . $this->session->data['token'] . '&id=' . $aRow[$this->getPrimaryKey()], 'SSL') . "')",
                'btn_class' => 'btn btn-danger btn-xs',
                'class' => 'fa fa-times'
            );

            $strAction = '';
            foreach ($actions as $action) {
                $strAction .= '<a '.(isset($action['btn_class'])?'class="'.$action['btn_class'].'"':'').' href="' . $action['href'] .'" '. (isset($action['target']) ? 'target="' . $action['target'] . '"' : '') . ' data-toggle="tooltip" title="' . $action['text'] . '" ' . (isset($action['click']) ? 'onClick="' . $action['click'] . '"' : '') . '>';
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
                } elseif ($aColumns[$i] == 'start_date') {
                    $row[] = stdDate($aRow['start_date']);
                } elseif ($aColumns[$i] == 'end_date') {
                    $row[] = stdDate($aRow['end_date']);
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

    protected function getForm() {
        parent::getForm();
        $this->model['product_category'] = $this->load->model('inventory/product_category');
        $this->data['product_categories'] = $this->model['product_category']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['product'] = $this->load->model('inventory/product');
        $products = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));
        foreach($products as $product) {
            $this->data['products'][$product['product_category_id']][]=$product;
        }

        $this->data['partner_types'] = $this->session->data['partner_types'];
        $this->data['partner_type_id'] = 2;

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partners'] = $this->model['partner']->getRows(array('company_id' => $this->session->data['company_id'], 'company_branch_id' => $this->session->data['company_branch_id'], 'partner_type_id'=>2), array('name'));

        if (isset($this->request->get['sale_discount_policy_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $this->data['isEdit'] = 1;
            $result = $this->model[$this->getAlias()]->getRow(array('sale_discount_policy_id' => $this->request->get[$this->getPrimaryKey()]));
            foreach ($result as $field => $value) {
                if ($field == 'start_date') {
                    $this->data[$field] = stdDate($value);
                } elseif ($field == 'end_date') {
                    $this->data[$field] = stdDate($value);
                } else {
                    $this->data[$field] = $value;
                }
            }
            $this->model['sale_discount_policy_detail'] = $this->load->model('inventory/sale_discount_policy_detail');
            $details = $this->model['sale_discount_policy_detail']->getRows(array('sale_discount_policy_id' => $this->request->get['sale_discount_policy_id']), array('sort_order DESC'));

            $this->data['sale_discount_policy_details'] = $details;
        }

        $this->data['action_validate'] = $this->url->link($this->getAlias().'/validate', 'token=' . $this->session->data['token'].'&sale_discount_policy_id='.$this->request->get['sale_discount_policy_id']);
        $this->data['strValidation'] = "{
            'rules': {
                'partner_id': {'remote':  {url: '" . $this->data['action_validate'] . "', type: 'post',data: {partner_type_id: function() {return $( '#partner_type_id' ).val();},partner_id: function() {return $( '#partner_id' ).val();},start_date: function() {return $( '#start_date' ).val();},end_date: function() {return $( '#end_date' ).val();}}}},
                'start_date': {'required': true, 'remote':  {url: '" . $this->data['action_validate'] . "', type: 'post',data: {partner_type_id: function() {return $( '#partner_type_id' ).val();},partner_id: function() {return $( '#partner_id' ).val();},start_date: function() {return $( '#start_date' ).val();},end_date: function() {return $( '#end_date' ).val();}}}},
                'end_date': {'required': true, 'remote':  {url: '" . $this->data['action_validate'] . "', type: 'post',data: {partner_type_id: function() {return $( '#partner_type_id' ).val();},partner_id: function() {return $( '#partner_id' ).val();},start_date: function() {return $( '#start_date' ).val();},end_date: function() {return $( '#end_date' ).val();}}}},
                'discount_count': {'required': true,'min':1},
            },
            'ignore': [],
        }";

        $this->response->setOutput($this->render());
    }

    public function validate() {
        $sale_discount_policy_id = $this->request->get['sale_discount_policy_id'];
        $post = $this->request->post;
        $this->model['discount_policy'] = $this->load->model('inventory/sale_discount_policy');
        $where = "`company_id`='".$this->session->data['company_id']."'";
        $where .= " AND `company_branch_id`='".$this->session->data['company_branch_id']."'";
        $where .= " AND `sale_discount_policy_id` != '".$sale_discount_policy_id."'";
        $where .= " AND `partner_type_id`='".$post['partner_type_id']."' AND `partner_id`='".$post['partner_id']."'";
        $where .= " AND (('".MySqlDate($post['start_date'])."' >= `start_date` AND '".MySqlDate($post['start_date'])."' <=`end_date`)";
        $where .= " OR ('".MySqlDate($post['end_date'])."' >= `start_date` AND '".MySqlDate($post['end_date'])."' <=`end_date`))";

        //d(array($sale_discount_policy_id, $where), true);
        $row = $this->model['discount_policy']->getRow($where);
        if(empty($row)) {
            echo json_encode("true");
        } else {
            echo json_encode("Conflict: Another policy exists within the period.");
        }
    }

    protected function insertData($data) {
        $data['company_id'] = $this->session->data['company_id'];
        $data['company_branch_id'] = $this->session->data['company_branch_id'];
        $data['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $data['start_date'] = MySqlDate($data['start_date']);
        $data['end_date'] = MySqlDate($data['end_date']);
        $sale_discount_policy_id = $this->model[$this->getAlias()]->add($this->getAlias(),$data);
        $this->model['policy_detail'] = $this->load->model('inventory/sale_discount_policy_detail');
        foreach($data['policies'] as $sort_order => $policy) {
            $policy['sort_order'] = $sort_order;
            $policy['sale_discount_policy_id'] = $sale_discount_policy_id;

            $this->model['policy_detail']->add($this->getAlias(), $policy);
        }
        return $sale_discount_policy_id;
    }

    protected function updateData($primary_key, $data) {
        //d($data, true);
        $data['start_date'] = MySqlDate($data['start_date']);
        $data['end_date'] = MySqlDate($data['end_date']);
        $sale_discount_policy_id = $this->model[$this->getAlias()]->edit($this->getAlias(), $primary_key,$data);
        $this->model['policy_detail'] = $this->load->model('inventory/sale_discount_policy_detail');
        $this->model['policy_detail']->deleteBulk($this->getAlias(), array('sale_discount_policy_id' => $sale_discount_policy_id));
        $sort_order = 0;
        foreach($data['policies'] as $policy) {
            $policy['sort_order'] = $sort_order;
            $policy['sale_discount_policy_id'] = $sale_discount_policy_id;

            $this->model['policy_detail']->add($this->getAlias(), $policy);
            $sort_order++;
        }
        return $sale_discount_policy_id;
    }

    protected function deleteData($primary_key) {
        $this->model['policy_detail'] = $this->load->model('inventory/sale_discount_policy_detail');
        $this->model['policy_detail']->deleteBulk($this->getAlias(), array('sale_discount_policy_id' => $primary_key));
        $this->model[$this->getAlias()]->delete($this->getAlias(), $primary_key);
    }

}

?>